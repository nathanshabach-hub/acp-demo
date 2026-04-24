/**
 * ACP Portal – Offline UI Layer
 * - Shows offline/online status bar
 * - Shows pending sync badge
 * - Intercepts form submissions when offline
 * - Shows sync queue panel
 */
var AcpOfflineUI = (function() {

    var initialized = false;

    function init() {
        if (initialized) return;
        initialized = true;

        injectStatusBar();
        injectSyncBadge();
        bindConnectivityEvents();
        interceptForms();
        listenForSwMessages();

        // Update badge on load
        updateSyncBadge();

        // Register sync listener
        AcpSync.onChange(function(state) {
            updateSyncBadge();
        });
    }

    // ---- Offline/Online status bar ----
    function injectStatusBar() {
        var bar = document.createElement('div');
        bar.id = 'acp-offline-bar';
        bar.innerHTML = '<span class="acp-offline-icon">&#9888;</span> You are offline. Changes will be saved locally and synced when you reconnect.';
        bar.style.cssText = 'display:none;position:fixed;top:0;left:0;right:0;z-index:99999;' +
            'background:#e74c3c;color:#fff;padding:10px 16px;font-size:14px;text-align:center;' +
            'font-family:Arial,sans-serif;box-shadow:0 2px 8px rgba(0,0,0,0.3);';
        document.body.insertBefore(bar, document.body.firstChild);

        if (!navigator.onLine) {
            bar.style.display = 'block';
            document.body.style.paddingTop = '44px';
        }
    }

    // ---- Sync badge (floating) ----
    function injectSyncBadge() {
        var badge = document.createElement('div');
        badge.id = 'acp-sync-badge';
        badge.innerHTML = '<span class="acp-sync-icon">&#x1F504;</span> <span id="acp-sync-count">0</span> pending';
        badge.style.cssText = 'display:none;position:fixed;bottom:20px;right:20px;z-index:99998;' +
            'background:#2c3e6b;color:#fff;padding:10px 18px;border-radius:24px;font-size:13px;' +
            'font-family:Arial,sans-serif;cursor:pointer;box-shadow:0 2px 12px rgba(0,0,0,0.3);';
        badge.title = 'Click to view sync queue';
        badge.addEventListener('click', showSyncPanel);
        document.body.appendChild(badge);
    }

    function updateSyncBadge() {
        AcpSync.pendingCount().then(function(count) {
            var badge = document.getElementById('acp-sync-badge');
            if (!badge) return;
            var countEl = document.getElementById('acp-sync-count');
            if (countEl) countEl.textContent = count;
            badge.style.display = count > 0 ? 'block' : 'none';
        });
    }

    // ---- Connectivity events ----
    function bindConnectivityEvents() {
        window.addEventListener('offline', function() {
            var bar = document.getElementById('acp-offline-bar');
            if (bar) {
                bar.style.display = 'block';
                document.body.style.paddingTop = '44px';
            }
        });

        window.addEventListener('online', function() {
            var bar = document.getElementById('acp-offline-bar');
            if (bar) {
                bar.style.display = 'none';
                document.body.style.paddingTop = '';
            }
            // Show syncing message briefly
            showToast('Back online! Syncing your changes...');
            // Trigger sync
            AcpSync.processQueue();
        });
    }

    // ---- Form interception ----
    function interceptForms() {
        document.addEventListener('submit', function(e) {
            // Only intercept when offline
            if (navigator.onLine) return;

            var form = e.target;
            if (!form || form.tagName !== 'FORM') return;

            // Only intercept POST forms with known action URLs
            var method = (form.method || 'GET').toUpperCase();
            if (method !== 'POST') return;

            var action = form.action || window.location.href;

            // Check if this is a form we should handle offline
            if (!shouldInterceptForm(action)) return;

            e.preventDefault();

            var formData = new FormData(form);
            var hasFiles = false;
            for (var pair of formData.entries()) {
                if (pair[1] instanceof File && pair[1].size > 0) {
                    hasFiles = true;
                    break;
                }
            }

            var label = getFormLabel(form, action);

            var queuePromise;
            if (hasFiles) {
                queuePromise = AcpSync.queueWithFiles(action, method, formData, label);
            } else {
                // Convert to plain object
                var data = {};
                for (var p of formData.entries()) {
                    if (data[p[0]] !== undefined) {
                        if (!Array.isArray(data[p[0]])) {
                            data[p[0]] = [data[p[0]]];
                        }
                        data[p[0]].push(p[1]);
                    } else {
                        data[p[0]] = p[1];
                    }
                }
                queuePromise = AcpSync.queue(action, method, data, label);
            }

            queuePromise.then(function() {
                showToast('Saved offline! Will sync when you reconnect. (' + label + ')');
                // Request background sync
                if (navigator.serviceWorker && navigator.serviceWorker.controller) {
                    navigator.serviceWorker.controller.postMessage({ type: 'QUEUE_SYNC' });
                }
            }).catch(function(err) {
                showToast('Error saving offline: ' + err.message, true);
            });
        }, true);
    }

    /**
     * Determine if a form action URL should be intercepted for offline use.
     */
    function shouldInterceptForm(action) {
        var patterns = [
            /\/users\/addstudent/,
            /\/users\/editstudent/,
            /\/users\/addteacher/,
            /\/users\/editteacher/,
            /\/users\/conference/,
            /\/conventionregistrations\//,
            /\/eventsubmissions\//,
            /\/groups\//
        ];
        for (var i = 0; i < patterns.length; i++) {
            if (patterns[i].test(action)) return true;
        }
        return false;
    }

    /**
     * Generate a human-readable label for the queued form.
     */
    function getFormLabel(form, action) {
        // Try to extract a meaningful name from form fields
        var firstName = form.querySelector('[name*="first_name"]');
        var lastName  = form.querySelector('[name*="last_name"]');

        if (firstName && lastName && firstName.value && lastName.value) {
            if (/addstudent|editstudent/.test(action)) {
                return 'Student: ' + firstName.value + ' ' + lastName.value;
            }
            if (/addteacher|editteacher/.test(action)) {
                return 'Supervisor: ' + firstName.value + ' ' + lastName.value;
            }
        }

        if (/conference/.test(action)) return 'Conference Registration';
        if (/eventsubmissions/.test(action)) return 'Event Submission';
        if (/groups/.test(action)) return 'Group Assignment';
        if (/conventionregistrations/.test(action)) return 'Convention Registration';

        return 'Form: ' + action.split('/').pop();
    }

    // ---- Sync queue panel ----
    function showSyncPanel() {
        // Remove existing panel
        var existing = document.getElementById('acp-sync-panel');
        if (existing) { existing.remove(); return; }

        var panel = document.createElement('div');
        panel.id = 'acp-sync-panel';
        panel.style.cssText = 'position:fixed;bottom:70px;right:20px;z-index:99999;' +
            'background:#fff;border:1px solid #ccc;border-radius:8px;width:360px;max-height:400px;' +
            'overflow-y:auto;box-shadow:0 4px 20px rgba(0,0,0,0.2);font-family:Arial,sans-serif;';

        panel.innerHTML = '<div style="padding:12px 16px;border-bottom:1px solid #eee;font-weight:bold;' +
            'display:flex;justify-content:space-between;align-items:center;">' +
            '<span>Sync Queue</span>' +
            '<span id="acp-sync-close" style="cursor:pointer;font-size:18px;">&times;</span>' +
            '</div>' +
            '<div id="acp-sync-list" style="padding:8px 16px;"></div>';

        document.body.appendChild(panel);

        document.getElementById('acp-sync-close').addEventListener('click', function() {
            panel.remove();
        });

        refreshSyncPanel();
    }

    function refreshSyncPanel() {
        var list = document.getElementById('acp-sync-list');
        if (!list) return;

        AcpSync.getQueue().then(function(entries) {
            if (entries.length === 0) {
                list.innerHTML = '<p style="color:#999;font-size:13px;">No pending items.</p>';
                return;
            }

            var html = '';
            entries.forEach(function(entry) {
                var statusColor = entry.status === 'pending' ? '#f39c12' :
                                  entry.status === 'syncing' ? '#3498db' :
                                  entry.status === 'failed' ? '#e74c3c' : '#27ae60';
                var statusIcon = entry.status === 'pending' ? '&#9679;' :
                                 entry.status === 'syncing' ? '&#8635;' :
                                 entry.status === 'failed' ? '&#10060;' : '&#10004;';

                html += '<div style="padding:8px 0;border-bottom:1px solid #f0f0f0;font-size:13px;' +
                    'display:flex;justify-content:space-between;align-items:center;">' +
                    '<div><span style="color:' + statusColor + ';">' + statusIcon + '</span> ' +
                    entry.label +
                    '<div style="color:#999;font-size:11px;">' + new Date(entry.created).toLocaleString() + '</div>';

                if (entry.error) {
                    html += '<div style="color:#e74c3c;font-size:11px;">' + entry.error + '</div>';
                }

                html += '</div>' +
                    '<button data-remove-id="' + entry.id + '" style="background:none;border:none;' +
                    'color:#e74c3c;cursor:pointer;font-size:16px;" title="Remove">&times;</button>' +
                    '</div>';
            });

            list.innerHTML = html;

            // Bind remove buttons
            list.querySelectorAll('[data-remove-id]').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var id = parseInt(this.getAttribute('data-remove-id'));
                    AcpSync.removeEntry(id).then(function() {
                        refreshSyncPanel();
                    });
                });
            });
        });
    }

    // ---- Toast notifications ----
    function showToast(message, isError) {
        var toast = document.createElement('div');
        toast.style.cssText = 'position:fixed;top:60px;left:50%;transform:translateX(-50%);z-index:99999;' +
            'background:' + (isError ? '#e74c3c' : '#27ae60') + ';color:#fff;padding:12px 24px;' +
            'border-radius:6px;font-size:14px;font-family:Arial,sans-serif;' +
            'box-shadow:0 2px 12px rgba(0,0,0,0.3);transition:opacity 0.3s;';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(function() {
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 300);
        }, 4000);
    }

    // ---- Listen for SW messages ----
    function listenForSwMessages() {
        if (!navigator.serviceWorker) return;
        navigator.serviceWorker.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'PROCESS_SYNC_QUEUE') {
                AcpSync.processQueue();
            }
        });
    }

    return {
        init: init,
        showToast: showToast,
        updateSyncBadge: updateSyncBadge
    };
})();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() { AcpOfflineUI.init(); });
} else {
    AcpOfflineUI.init();
}
