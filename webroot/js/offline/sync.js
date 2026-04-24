/**
 * ACP Portal – Sync Queue Manager
 * Queues offline form submissions and replays them when back online.
 */
var AcpSync = (function() {

    var syncing = false;
    var listeners = [];

    /**
     * Queue a form submission for later sync.
     * @param {string} url       - The action URL
     * @param {string} method    - POST or PUT
     * @param {object} data      - Form data as plain object (no files)
     * @param {string} label     - Human-readable description (e.g. "Add Student: John Doe")
     */
    function queue(url, method, data, label) {
        var entry = {
            url: url,
            method: method || 'POST',
            data: data,
            label: label || url,
            status: 'pending',    // pending | syncing | done | failed
            attempts: 0,
            created: new Date().toISOString(),
            error: null
        };
        return AcpDB.put(AcpDB.STORES.SYNC_QUEUE, entry).then(function(saved) {
            notifyListeners();
            // Try to sync immediately if online
            if (navigator.onLine) {
                processQueue();
            }
            return saved;
        });
    }

    /**
     * Queue a file upload for later sync.
     * Files are stored as ArrayBuffers in IndexedDB.
     */
    function queueWithFiles(url, method, formData, label) {
        // Convert FormData to serialisable format
        var data = {};
        var files = [];
        var promises = [];

        // formData is a real FormData object
        for (var pair of formData.entries()) {
            if (pair[1] instanceof File) {
                (function(fieldName, file) {
                    var p = new Promise(function(resolve) {
                        var reader = new FileReader();
                        reader.onload = function() {
                            files.push({
                                field: fieldName,
                                name: file.name,
                                type: file.type,
                                buffer: reader.result
                            });
                            resolve();
                        };
                        reader.readAsArrayBuffer(file);
                    });
                    promises.push(p);
                })(pair[0], pair[1]);
            } else {
                // Handle multi-value fields (e.g. checkboxes)
                if (data[pair[0]] !== undefined) {
                    if (!Array.isArray(data[pair[0]])) {
                        data[pair[0]] = [data[pair[0]]];
                    }
                    data[pair[0]].push(pair[1]);
                } else {
                    data[pair[0]] = pair[1];
                }
            }
        }

        return Promise.all(promises).then(function() {
            var entry = {
                url: url,
                method: method || 'POST',
                data: data,
                files: files,
                label: label || url,
                status: 'pending',
                attempts: 0,
                created: new Date().toISOString(),
                error: null
            };
            return AcpDB.put(AcpDB.STORES.SYNC_QUEUE, entry).then(function(saved) {
                notifyListeners();
                if (navigator.onLine) {
                    processQueue();
                }
                return saved;
            });
        });
    }

    /**
     * Process all pending queue entries sequentially.
     */
    function processQueue() {
        if (syncing) return Promise.resolve();
        syncing = true;
        notifyListeners();

        // Verify server connectivity first
        return fetch('/api/sync/ping', { credentials: 'same-origin' })
        .then(function(resp) { return resp.json(); })
        .then(function(pingData) {
            if (!pingData || !pingData.online) {
                throw new Error('Server not reachable');
            }
            return AcpDB.getAll(AcpDB.STORES.SYNC_QUEUE);
        })
        .then(function(entries) {
            var pending = entries.filter(function(e) { return e.status === 'pending' || e.status === 'failed'; });
            pending.sort(function(a, b) { return a.id - b.id; }); // FIFO

            return pending.reduce(function(chain, entry) {
                return chain.then(function() {
                    return syncOne(entry);
                });
            }, Promise.resolve());
        }).then(function() {
            syncing = false;
            notifyListeners();
        }).catch(function() {
            syncing = false;
            notifyListeners();
        });
    }

    /**
     * Replay a single queued entry to the server.
     */
    function syncOne(entry) {
        entry.status = 'syncing';
        entry.attempts++;
        return AcpDB.put(AcpDB.STORES.SYNC_QUEUE, entry).then(function() {
            // Rebuild FormData if there are files
            var body;
            var headers = {};

            if (entry.files && entry.files.length > 0) {
                body = new FormData();
                // Add regular fields
                Object.keys(entry.data).forEach(function(key) {
                    var val = entry.data[key];
                    if (Array.isArray(val)) {
                        val.forEach(function(v) { body.append(key, v); });
                    } else {
                        body.append(key, val);
                    }
                });
                // Add files from stored ArrayBuffers
                entry.files.forEach(function(f) {
                    var blob = new Blob([f.buffer], { type: f.type });
                    body.append(f.field, blob, f.name);
                });
            } else {
                // URL-encoded form data
                body = new URLSearchParams();
                Object.keys(entry.data).forEach(function(key) {
                    var val = entry.data[key];
                    if (Array.isArray(val)) {
                        val.forEach(function(v) { body.append(key, v); });
                    } else {
                        body.append(key, val);
                    }
                });
                headers['Content-Type'] = 'application/x-www-form-urlencoded';
            }

            return fetch(entry.url, {
                method: entry.method,
                body: body,
                headers: headers,
                credentials: 'same-origin',
                redirect: 'follow'
            });
        }).then(function(response) {
            if (response.ok || response.status === 302 || response.status === 301) {
                // Success – remove from queue
                return AcpDB.remove(AcpDB.STORES.SYNC_QUEUE, entry.id).then(function() {
                    notifyListeners();
                });
            } else {
                throw new Error('Server returned ' + response.status);
            }
        }).catch(function(err) {
            entry.status = 'failed';
            entry.error = err.message || 'Network error';
            return AcpDB.put(AcpDB.STORES.SYNC_QUEUE, entry).then(function() {
                notifyListeners();
            });
        });
    }

    /**
     * Get count of pending items.
     */
    function pendingCount() {
        return AcpDB.getAll(AcpDB.STORES.SYNC_QUEUE).then(function(entries) {
            return entries.filter(function(e) { return e.status === 'pending' || e.status === 'failed'; }).length;
        });
    }

    /**
     * Get all queue entries.
     */
    function getQueue() {
        return AcpDB.getAll(AcpDB.STORES.SYNC_QUEUE);
    }

    /**
     * Remove a specific queue entry.
     */
    function removeEntry(id) {
        return AcpDB.remove(AcpDB.STORES.SYNC_QUEUE, id).then(function() {
            notifyListeners();
        });
    }

    /**
     * Register a listener for sync state changes.
     */
    function onChange(fn) {
        listeners.push(fn);
    }

    function notifyListeners() {
        pendingCount().then(function(count) {
            listeners.forEach(function(fn) {
                try { fn({ pending: count, syncing: syncing }); } catch(e) {}
            });
        });
    }

    // Auto-sync when coming back online
    window.addEventListener('online', function() {
        processQueue();
    });

    return {
        queue: queue,
        queueWithFiles: queueWithFiles,
        processQueue: processQueue,
        pendingCount: pendingCount,
        getQueue: getQueue,
        removeEntry: removeEntry,
        onChange: onChange
    };
})();
