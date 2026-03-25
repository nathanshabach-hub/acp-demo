<?php
$allRegistrations = isset($conventionregistrations) ? $conventionregistrations : [];
$eventNameIDDD = isset($eventNameIDDD) && is_array($eventNameIDDD) ? $eventNameIDDD : [];
$eventResultRouteMap = isset($eventResultRouteMap) && is_array($eventResultRouteMap) ? $eventResultRouteMap : [];
$slugConvention = isset($slugConvention) ? (string)$slugConvention : '';
$slugConventionSeason = isset($slugConventionSeason) ? (string)$slugConventionSeason : '';
$judgeRows = [];
$totalEventsAssigned = 0;
$latestCreatedTs = 0;

foreach ($allRegistrations as $record) {
    $isJudge = (
        ($record->Users['user_type'] == 'Judge' || $record->Users['user_type'] == 'Teacher_Parent')
        && (int)$record->Users['is_judge'] === 1
    );
    if (!$isJudge) {
        continue;
    }

    $eventCount = 0;
    $eventIds = [];
    if (!empty($record->judges_event_ids)) {
        $eventIds = array_filter(array_map('trim', explode(',', (string)$record->judges_event_ids)));
        $eventCount = count($eventIds);
    }

    $eventLabels = [];
    if (!empty($eventIds)) {
        foreach ($eventIds as $eventId) {
            $eventIdInt = (int)$eventId;
            $eventLabels[] = isset($eventNameIDDD[$eventIdInt]) ? $eventNameIDDD[$eventIdInt] : ('Event #' . $eventIdInt);
        }
    }

    $createdTs = strtotime((string)$record->created);
    if ($createdTs > $latestCreatedTs) {
        $latestCreatedTs = $createdTs;
    }

    $fullName = trim((string)$record->Users['first_name'] . ' ' . (string)$record->Users['last_name']);
    $judgeRows[] = [
        'id' => (int)$record->id,
        'slug' => (string)$record->slug,
        'name' => $fullName !== '' ? $fullName : 'N/A',
        'email' => (string)$record->Users['email_address'],
        'user_type' => (string)$record->Users['user_type'],
        'event_ids' => array_map('intval', $eventIds),
        'event_labels' => $eventLabels,
        'result_routes' => array_values(array_filter(array_map(function ($eventId) use ($eventResultRouteMap) {
            return isset($eventResultRouteMap[(int)$eventId]) ? $eventResultRouteMap[(int)$eventId] : null;
        }, array_map('intval', $eventIds)))),
        'event_count' => (int)$eventCount,
        'created' => (string)$record->created,
    ];

    $totalEventsAssigned += (int)$eventCount;
}

$totalJudges = count($judgeRows);
$avgEvents = $totalJudges > 0 ? round($totalEventsAssigned / $totalJudges, 2) : 0;
$latestRegistration = $latestCreatedTs > 0 ? date('M d, Y', $latestCreatedTs) : 'N/A';
?>

<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<style type="text/css">
.judge-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.judge-summary-card {
    border: 1px solid #e5e8ef;
    border-left: 4px solid #00a65a;
    border-radius: 6px;
    background: #ffffff;
    padding: 12px 14px;
}
.judge-summary-label {
    font-size: 12px;
    color: #6f7b8a;
    text-transform: uppercase;
    letter-spacing: .4px;
    margin-bottom: 4px;
}
.judge-summary-value {
    font-size: 22px;
    font-weight: 600;
    color: #1f2d3d;
    line-height: 1.2;
}
.judge-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    position: sticky;
    top: 0;
    z-index: 5;
    background: #fff;
    border: 1px solid #edf1f6;
    border-radius: 6px;
    padding: 10px;
}
.judge-toolbar-left,
.judge-toolbar-right {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}
.judge-toolbar .form-control {
    min-width: 230px;
}
.judge-role-badge {
    display: inline-block;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    padding: 4px 8px;
    color: #fff;
}
.judge-role-badge.judge {
    background: #00a65a;
}
.judge-role-badge.teacher-parent {
    background: #3c8dbc;
}
.judge-status-badge {
    display: inline-block;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    line-height: 1;
    padding: 5px 9px;
    color: #fff;
}
.judge-status-badge.all-closed {
    background: #00a65a;
}
.judge-status-badge.partial {
    background: #f39c12;
}
.judge-status-badge.all-open {
    background: #3c8dbc;
}
.judge-status-badge.no-events {
    background: #dd4b39;
}
.judge-event-tag {
    display: inline-block;
    background: #f4f8ff;
    border: 1px solid #d9e6ff;
    color: #1f4b99;
    border-radius: 3px;
    font-size: 11px;
    padding: 2px 6px;
    margin: 2px 3px 2px 0;
}
.judge-event-more {
    display: inline-flex;
    align-items: center;
    font-size: 11px;
    color: #3c8dbc;
    font-weight: 600;
    margin-left: 2px;
    border: 1px dashed #9dc0ee;
    background: #eef5ff;
    border-radius: 10px;
    padding: 1px 7px;
    cursor: pointer;
}
.judge-event-more:hover {
    background: #e2efff;
    color: #23527c;
}
.judge-table-meta {
    font-size: 11px;
    color: #7b8794;
}
.judge-action-btns {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.judge-manage-modal .modal-header {
    background: #f6f8fb;
}
.judge-results-modal .modal-header {
    background: #f7fbf7;
}
.judge-manage-modal .current-event-list {
    margin-bottom: 12px;
}
.judge-manage-modal .current-event-list .judge-event-tag {
    margin-bottom: 5px;
}
.judge-modal-form {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 8px;
}
.judge-modal-form .form-control {
    min-width: 220px;
}
.judge-modal-feedback {
    display: none;
    margin-bottom: 10px;
}
.judge-toast {
    position: fixed;
    right: 18px;
    bottom: 18px;
    z-index: 9999;
    min-width: 220px;
    max-width: 360px;
    background: #1f2d3d;
    color: #fff;
    border-radius: 6px;
    padding: 10px 12px;
    box-shadow: 0 4px 14px rgba(0,0,0,.2);
    display: none;
}
.judge-toast.success {
    background: #00a65a;
}
.judge-toast.error {
    background: #dd4b39;
}
.judge-results-summary {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.judge-results-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.judge-results-item-main {
    flex: 1;
    min-width: 0;
}
.judge-results-item-quick {
    white-space: nowrap;
}
.judge-results-modal-toolbar {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 10px;
}
body.judge-modal-fallback-open {
    overflow: hidden;
}
#judgeResultsModal.judge-modal-fallback {
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 1050;
    background: rgba(0, 0, 0, 0.45);
    overflow-y: auto;
}
#judgeResultsModal.judge-modal-fallback .modal-dialog {
    margin-top: 60px;
}
.table > thead > tr > th {
    background: #f6f8fb;
    border-bottom: 1px solid #d9dee6;
}
#alljudges-table td,
#alljudges-table th {
    vertical-align: middle;
}
</style>

<div class="panel-body">
    <div class="ersu_message"><?php echo $this->Flash->render() ?></div>

    <div class="judge-summary-grid">
        <div class="judge-summary-card">
            <div class="judge-summary-label">Total Judges</div>
            <div class="judge-summary-value"><?php echo (int)$totalJudges; ?></div>
        </div>
        <div class="judge-summary-card" style="border-left-color:#3c8dbc;">
            <div class="judge-summary-label">Assigned Events</div>
            <div class="judge-summary-value"><?php echo (int)$totalEventsAssigned; ?></div>
        </div>
        <div class="judge-summary-card" style="border-left-color:#f39c12;">
            <div class="judge-summary-label">Avg Events / Judge</div>
            <div class="judge-summary-value"><?php echo number_format((float)$avgEvents, 2); ?></div>
        </div>
        <div class="judge-summary-card" style="border-left-color:#dd4b39;">
            <div class="judge-summary-label">Latest Registration</div>
            <div class="judge-summary-value" style="font-size:18px;"><?php echo h($latestRegistration); ?></div>
        </div>
    </div>

    <?php if ($totalJudges > 0) { ?>
        <div id="judge-toast" class="judge-toast"></div>

        <div class="modal fade judge-results-modal" id="judgeResultsModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="judgeResultsModalTitle">Event Results</h4>
                    </div>
                    <div class="modal-body" id="judgeResultsModalBody"></div>
                </div>
            </div>
        </div>

        <div class="judge-toolbar">
            <div class="judge-toolbar-left">
                <input type="text" id="judge-search-input" class="form-control" placeholder="Search by name, email or ID">
                <select id="judge-role-filter" class="form-control">
                    <option value="">All Roles</option>
                    <option value="Judge">Judge</option>
                    <option value="Teacher_Parent">Teacher/Parent</option>
                </select>
                <select id="judge-event-state-filter" class="form-control">
                    <option value="">All Event States</option>
                    <option value="has-events">Has Events</option>
                    <option value="no-events">No Events</option>
                </select>
            </div>
            <div class="judge-toolbar-right">
                <button type="button" id="download-judges-csv" class="btn btn-default btn-sm">
                    <i class="fa fa-download"></i> Download CSV
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table id="alljudges-table" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Judge</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Events</th>
                        <th>Status</th>
                        <th>Judging Events</th>
                        <th>Registration Date</th>
                        <th>Results</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($judgeRows as $row) { ?>
                        <?php $roleClass = $row['user_type'] == 'Judge' ? 'judge' : 'teacher-parent'; ?>
                        <tr data-judge-id="<?php echo (int)$row['id']; ?>">
                            <td><?php echo (int)$row['id']; ?></td>
                            <td>
                                <div><?php echo h($row['name']); ?></div>
                                <div class="judge-table-meta">Registration #<?php echo (int)$row['id']; ?></div>
                            </td>
                            <td><?php echo h($row['email']); ?></td>
                            <td>
                                <span class="judge-role-badge <?php echo $roleClass; ?>">
                                    <?php echo $row['user_type'] == 'Judge' ? 'Judge' : 'Teacher/Parent'; ?>
                                </span>
                            </td>
                            <td class="judge-event-count-cell"><?php echo (int)$row['event_count']; ?></td>
                            <td class="judge-status-cell">
                                <?php
                                $totalAssignedEvents = isset($row['result_routes']) ? count($row['result_routes']) : 0;
                                $closedAssignedEvents = 0;
                                foreach ($row['result_routes'] as $route) {
                                    if (is_array($route) && isset($route['judging_ends']) && (int)$route['judging_ends'] === 1) {
                                        $closedAssignedEvents++;
                                    }
                                }

                                if ($totalAssignedEvents <= 0) {
                                    echo '<span class="judge-status-badge no-events">No Events</span>';
                                } elseif ($closedAssignedEvents === $totalAssignedEvents) {
                                    echo '<span class="judge-status-badge all-closed">All Closed</span>';
                                } elseif ($closedAssignedEvents > 0) {
                                    echo '<span class="judge-status-badge partial">Partially Closed</span>';
                                } else {
                                    echo '<span class="judge-status-badge all-open">All Open</span>';
                                }
                                ?>
                            </td>
                            <td class="judge-events-display-cell">
                                <?php if (!empty($row['event_labels'])) { ?>
                                    <?php $visibleEventLabels = array_slice($row['event_labels'], 0, 3); ?>
                                    <?php foreach ($visibleEventLabels as $eventLabel) { ?>
                                        <span class="judge-event-tag"><?php echo h($eventLabel); ?></span>
                                    <?php } ?>
                                    <?php if (count($row['event_labels']) > 3) { ?>
                                        <button
                                            type="button"
                                            class="judge-event-more"
                                            title="View all assigned events"
                                            data-toggle="modal"
                                            data-target="#manageEventsModal-<?php echo (int)$row['id']; ?>"
                                        >
                                            +<?php echo count($row['event_labels']) - 3; ?> more
                                        </button>
                                    <?php } ?>
                                <?php } else { ?>
                                    <span style="color:#999;">No events assigned</span>
                                <?php } ?>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['created'])); ?></td>
                            <td class="judge-results-cell">
                                <?php if (!empty($row['result_routes']) && $slugConventionSeason !== '' && $slugConvention !== '') { ?>
                                    <?php
                                    $closedResults = 0;
                                    $openResults = 0;
                                    $firstClosedHref = '';
                                    foreach ($row['result_routes'] as $route) {
                                        if (!is_array($route)) {
                                            continue;
                                        }
                                        $routeJudgingEnds = isset($route['judging_ends']) ? (int)$route['judging_ends'] : 0;
                                        if ($routeJudgingEnds === 1 && $firstClosedHref === '' && !empty($route['event_slug']) && !empty($route['action_results'])) {
                                            $firstClosedHref = $this->Url->build([
                                                'controller' => 'Results',
                                                'action' => (string)$route['action_results'],
                                                $slugConventionSeason,
                                                $slugConvention,
                                                (string)$route['event_slug'],
                                            ]);
                                        }
                                        if ($routeJudgingEnds === 1) {
                                            $closedResults++;
                                        } else {
                                            $openResults++;
                                        }
                                    }
                                    ?>
                                    <a
                                        href="<?php echo $firstClosedHref !== '' ? h($firstClosedHref) : '#'; ?>"
                                        class="btn btn-primary btn-xs js-open-results-modal"
                                        data-judge-name="<?php echo h($row['name']); ?>"
                                        data-result-routes="<?php echo h(json_encode($row['result_routes'])); ?>"
                                        data-slug-convention-season="<?php echo h($slugConventionSeason); ?>"
                                        data-slug-convention="<?php echo h($slugConvention); ?>"
                                    >
                                        <span class="judge-results-summary">
                                            <i class="fa fa-list"></i>
                                            Results (<?php echo (int)$closedResults; ?>)
                                            <?php if ($openResults > 0) { ?>
                                                <span class="badge" style="background:#dd4b39;"><?php echo (int)$openResults; ?> open</span>
                                            <?php } ?>
                                        </span>
                                    </a>
                                <?php } else { ?>
                                    <span style="color:#999;">No result links</span>
                                <?php } ?>
                            </td>
                            <td>
                                <div class="judge-action-btns">
                                    <button type="button" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#manageEventsModal-<?php echo (int)$row['id']; ?>">Manage Events</button>
                                    <?php echo $this->Html->link('Full Editor', ['controller' => 'Conventionregistrations', 'action' => 'judgeregevents', $row['slug']], ['class' => 'btn btn-default btn-xs']); ?>
                                </div>

                                <div class="modal fade judge-manage-modal" id="manageEventsModal-<?php echo (int)$row['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                    <div class="modal-dialog modal-md" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title">Manage Events: <?php echo h($row['name']); ?></h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="alert judge-modal-feedback"></div>

                                                <div class="current-event-list js-current-event-list">
                                                    <div class="judge-table-meta" style="margin-bottom:6px;">Currently assigned events</div>
                                                    <?php if (!empty($row['event_labels'])) { ?>
                                                        <?php foreach ($row['event_labels'] as $eventLabel) { ?>
                                                            <span class="judge-event-tag"><?php echo h($eventLabel); ?></span>
                                                        <?php } ?>
                                                    <?php } else { ?>
                                                        <span style="color:#999;">No events assigned</span>
                                                    <?php } ?>
                                                </div>

                                                <?php echo $this->Form->create(null, ['url' => ['controller' => 'Conventionregistrations', 'action' => 'addjudgeevent', $row['slug']], 'type' => 'post', 'class' => 'judge-add-event-form judge-modal-form', 'data-judge-id' => (int)$row['id']]); ?>
                                                    <?php echo $this->Form->select('event_id', $eventNameIDDD, ['empty' => 'Add event...', 'class' => 'js-add-event-select']); ?>
                                                    <button type="submit" class="btn btn-success btn-sm">Add Event</button>
                                                <?php echo $this->Form->end(); ?>

                                                <?php
                                                $assignedOptions = [];
                                                foreach ($row['event_ids'] as $eventId) {
                                                    $assignedOptions[$eventId] = isset($eventNameIDDD[$eventId]) ? $eventNameIDDD[$eventId] : ('Event #' . $eventId);
                                                }
                                                ?>
                                                <?php echo $this->Form->create(null, ['url' => ['controller' => 'Conventionregistrations', 'action' => 'removejudgeevent', $row['slug']], 'type' => 'post', 'class' => 'judge-remove-event-form judge-modal-form', 'data-judge-id' => (int)$row['id']]); ?>
                                                    <?php echo $this->Form->select('event_id', $assignedOptions, ['empty' => 'Remove event...', 'class' => 'js-remove-event-select']); ?>
                                                    <button type="submit" class="btn btn-danger btn-sm js-remove-event-btn" <?php echo empty($assignedOptions) ? 'disabled="disabled"' : ''; ?>>Remove Event</button>
                                                <?php echo $this->Form->end(); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } else { ?>
        <div class="admin_no_record">No judges found for the selected season.</div>
    <?php } ?>
</div>

<script>
$(document).ready(function() {
    var table = null;
    var hasDataTable = !!($.fn.DataTable && $('#alljudges-table').length > 0);

    if (hasDataTable) {
        table = $('#alljudges-table').DataTable({
            pageLength: 50,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
            order: [[0, 'desc']],
            dom: 'tip',
            columnDefs: [
                { orderable: false, searchable: false, targets: [9] }
            ],
            language: {
                paginate: {
                    previous: 'Prev',
                    next: 'Next'
                }
            }
        });

        $('#judge-search-input').on('keyup', function () {
            table.search(this.value).draw();
        });

        $('#judge-role-filter').on('change', function () {
            var val = this.value;
            if (!val) {
                table.column(3).search('').draw();
                return;
            }
            var label = val === 'Teacher_Parent' ? 'Teacher/Parent' : val;
            table.column(3).search('^' + label + '$', true, false).draw();
        });

        $.fn.dataTable.ext.search.push(function (settings, data) {
            if (!settings || settings.nTable.id !== 'alljudges-table') {
                return true;
            }

            var filterVal = $('#judge-event-state-filter').val();
            if (!filterVal) {
                return true;
            }

            var eventCount = parseInt(data[4], 10) || 0;
            if (filterVal === 'has-events') {
                return eventCount > 0;
            }
            if (filterVal === 'no-events') {
                return eventCount === 0;
            }
            return true;
        });

        $('#judge-event-state-filter').on('change', function () {
            table.draw();
        });
    }

    $('#download-judges-csv').on('click', function () {
        var headers = ['ID', 'Judge', 'Email', 'Role', 'Events', 'Status', 'Judging Events', 'Registration Date', 'Results'];
        var lines = [headers.join(',')];

        if (hasDataTable && table) {
            table.rows({ search: 'applied' }).every(function () {
                var row = this.data();
                var cols = [row[0], row[1], row[2], $(row[3]).text().trim(), row[4], $(row[5]).text().trim(), $(row[6]).text().replace(/\s+/g, ' ').trim(), row[7], $(row[8]).text().replace(/\s+/g, ' ').trim()];
                var escaped = cols.map(function (v) {
                    var str = String(v == null ? '' : v).replace(/"/g, '""');
                    return '"' + str + '"';
                });
                lines.push(escaped.join(','));
            });
        } else {
            $('#alljudges-table tbody tr').each(function () {
                var cols = [];
                $(this).find('td').each(function (idx) {
                    if (idx > 8) {
                        return;
                    }
                    cols.push($(this).text().replace(/\s+/g, ' ').trim());
                });
                var escaped = cols.map(function (v) {
                    var str = String(v == null ? '' : v).replace(/"/g, '""');
                    return '"' + str + '"';
                });
                lines.push(escaped.join(','));
            });
        }

        var csv = lines.join('\n');
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = 'all-judges.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    });

    function escapeHtml(text) {
        return String(text == null ? '' : text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showToast(message, isSuccess) {
        var toast = $('#judge-toast');
        if (!toast.length) {
            return;
        }
        toast.stop(true, true)
            .removeClass('success error')
            .addClass(isSuccess ? 'success' : 'error')
            .text(message || '')
            .fadeIn(120)
            .delay(2000)
            .fadeOut(250);
    }

    function setSelectOptions($select, options, emptyLabel) {
        if (!$select || !$select.length) {
            return;
        }

        var html = '<option value="">' + escapeHtml(emptyLabel || 'Select...') + '</option>';
        (options || []).forEach(function (opt) {
            html += '<option value="' + escapeHtml(opt.id) + '">' + escapeHtml(opt.label) + '</option>';
        });
        $select.html(html);
    }

    function buildEventChipsHtml(assignedEvents, judgeId) {
        if (!assignedEvents || !assignedEvents.length) {
            return '<span style="color:#999;">No events assigned</span>';
        }

        var visibleEvents = assignedEvents.slice(0, 3);
        var html = '';
        visibleEvents.forEach(function (ev) {
            html += '<span class="judge-event-tag">' + escapeHtml(ev.label) + '</span>';
        });

        if (assignedEvents.length > 3) {
            var moreCount = assignedEvents.length - 3;
            html += '<button type="button" class="judge-event-more" title="View all assigned events" data-toggle="modal" data-target="#manageEventsModal-' + escapeHtml(judgeId) + '">+' + moreCount + ' more</button>';
        }
        return html;
    }

    function buildJudgingStatusHtml(resultRoutes) {
        var routes = Array.isArray(resultRoutes) ? resultRoutes : [];
        if (routes.length === 0) {
            return '<span class="judge-status-badge no-events">No Events</span>';
        }

        var closedCount = 0;
        routes.forEach(function (route) {
            if (route && parseInt(route.judging_ends, 10) === 1) {
                closedCount += 1;
            }
        });

        if (closedCount === routes.length) {
            return '<span class="judge-status-badge all-closed">All Closed</span>';
        }
        if (closedCount > 0) {
            return '<span class="judge-status-badge partial">Partially Closed</span>';
        }
        return '<span class="judge-status-badge all-open">All Open</span>';
    }

    function buildResultLinksHtml(resultRoutes, slugConventionSeasonVal, slugConventionVal, judgeName) {
        if (!resultRoutes || !resultRoutes.length || !slugConventionSeasonVal || !slugConventionVal) {
            return '<span style="color:#999;">No result links</span>';
        }

        var closedCount = 0;
        var openCount = 0;
        var firstClosedHref = '';
        resultRoutes.forEach(function (route) {
            if (parseInt(route.judging_ends, 10) === 1) {
                closedCount += 1;
                if (!firstClosedHref && route.event_slug && route.action_results) {
                    firstClosedHref = '/admin/results/' + encodeURIComponent(route.action_results) + '/' + encodeURIComponent(slugConventionSeasonVal) + '/' + encodeURIComponent(slugConventionVal) + '/' + encodeURIComponent(route.event_slug);
                }
            } else {
                openCount += 1;
            }
        });

        return '<a href="' + (firstClosedHref ? escapeHtml(firstClosedHref) : '#') + '" class="btn btn-primary btn-xs js-open-results-modal" data-judge-name="' + escapeHtml(judgeName || 'Judge') + '" data-result-routes="' + escapeHtml(JSON.stringify(resultRoutes)) + '" data-slug-convention-season="' + escapeHtml(slugConventionSeasonVal) + '" data-slug-convention="' + escapeHtml(slugConventionVal) + '"><span class="judge-results-summary"><i class="fa fa-list"></i> Results (' + closedCount + ')' + (openCount > 0 ? '<span class="badge" style="background:#dd4b39;">' + openCount + ' open</span>' : '') + '</span></a>';
    }

    function renderResultsModal(resultRoutes, slugConventionSeasonVal, slugConventionVal, judgeName) {
        var modalTitle = $('#judgeResultsModalTitle');
        var modalBody = $('#judgeResultsModalBody');
        if (!modalTitle.length || !modalBody.length) {
            return;
        }

        modalTitle.text('Event Results: ' + (judgeName || 'Judge'));
        if (!resultRoutes || !resultRoutes.length || !slugConventionSeasonVal || !slugConventionVal) {
            modalBody.html('<span style="color:#999;">No result links available.</span>');
            return;
        }

        var closedLinks = [];
        var html = '<div class="list-group">';
        resultRoutes.forEach(function (route) {
            if (!route || typeof route !== 'object') {
                return;
            }
            var label = route.event_label || 'Event';
            var judgingEnds = parseInt(route.judging_ends, 10) === 1;
            if (judgingEnds && route.event_slug && route.action_results) {
                var href = '/admin/results/' + encodeURIComponent(route.action_results) + '/' + encodeURIComponent(slugConventionSeasonVal) + '/' + encodeURIComponent(slugConventionVal) + '/' + encodeURIComponent(route.event_slug);
                closedLinks.push(href);
                html += '<div class="list-group-item judge-results-item">';
                html += '<a class="judge-results-item-main" href="' + href + '"><i class="fa fa-pencil text-primary"></i> ' + escapeHtml(label) + '</a>';
                html += '<a class="btn btn-default btn-xs judge-results-item-quick" href="' + href + '" target="_blank" rel="noopener"><i class="fa fa-external-link"></i></a>';
                html += '</div>';
            } else {
                html += '<div class="list-group-item"><span class="judge-event-tag" style="background:#f9f2f2; border-color:#f1cccc; color:#b94a48;">' + escapeHtml(label) + ' (Open)</span></div>';
            }
        });
        html += '</div>';

        var linksJson = encodeURIComponent(JSON.stringify(closedLinks));
        var openAllBtnClass = closedLinks.length > 0 ? 'btn-success' : 'btn-default';
        var openAllBtnDisabled = closedLinks.length > 0 ? '' : ' disabled="disabled"';
        var openAllBtnTitle = closedLinks.length > 0 ? '' : ' title="No closed results available yet"';
        html = '<div class="judge-results-modal-toolbar"><button type="button" class="btn ' + openAllBtnClass + ' btn-xs js-open-all-results" data-links="' + linksJson + '"' + openAllBtnDisabled + openAllBtnTitle + '><i class="fa fa-external-link"></i> Open all closed results (' + closedLinks.length + ')</button></div>' + html;

        modalBody.html(html);
    }

    function showResultsModalFallback() {
        var modal = $('#judgeResultsModal');
        if (!modal.length) {
            return;
        }
        modal.addClass('judge-modal-fallback').show();
        $('body').addClass('judge-modal-fallback-open');
    }

    function hideResultsModalFallback() {
        var modal = $('#judgeResultsModal');
        if (!modal.length) {
            return;
        }
        modal.removeClass('judge-modal-fallback').hide();
        $('body').removeClass('judge-modal-fallback-open');
    }

    function buildModalEventListHtml(assignedEvents) {
        var html = '<div class="judge-table-meta" style="margin-bottom:6px;">Currently assigned events</div>';
        if (!assignedEvents || !assignedEvents.length) {
            html += '<span style="color:#999;">No events assigned</span>';
            return html;
        }

        assignedEvents.forEach(function (ev) {
            html += '<span class="judge-event-tag">' + escapeHtml(ev.label) + '</span>';
        });
        return html;
    }

    function refreshJudgeUI(payload) {
        if (!payload || !payload.judge_id) {
            return;
        }

        var judgeId = payload.judge_id;
        var row = $('tr[data-judge-id="' + judgeId + '"]');
        if (!row.length) {
            return;
        }

        row.find('.judge-event-count-cell').text(payload.event_count || 0);
        row.find('.judge-status-cell').html(buildJudgingStatusHtml(payload.result_routes || []));
        row.find('.judge-events-display-cell').html(buildEventChipsHtml(payload.assigned_events || [], judgeId));
        var judgeName = row.find('td:eq(1) > div:first').text().trim();
        row.find('.judge-results-cell').html(buildResultLinksHtml(payload.result_routes || [], payload.slug_convention_season || '', payload.slug_convention || '', judgeName));

        var modal = $('#manageEventsModal-' + judgeId);
        if (modal.length) {
            modal.find('.js-current-event-list').html(buildModalEventListHtml(payload.assigned_events || []));
            setSelectOptions(modal.find('.js-add-event-select'), payload.available_events || [], 'Add event...');
            setSelectOptions(modal.find('.js-remove-event-select'), payload.assigned_events || [], 'Remove event...');
            modal.find('.js-remove-event-btn').prop('disabled', !(payload.assigned_events && payload.assigned_events.length));
        }

        if (table && typeof table.row === 'function') {
            table.row(row).invalidate().draw(false);
        }
    }

    function showModalFeedback($form, message, isSuccess) {
        var feedback = $form.closest('.modal-body').find('.judge-modal-feedback');
        if (!feedback.length) {
            return;
        }
        feedback
            .removeClass('alert-success alert-danger')
            .addClass(isSuccess ? 'alert-success' : 'alert-danger')
            .text(message || '')
            .show();
    }

    $(document).on('submit', '.judge-add-event-form', function (e) {
        e.preventDefault();

        var form = $(this);
        var selectedVal = $(this).find('select[name="event_id"]').val();
        if (!selectedVal) {
            alert('Please select an event to add.');
            return;
        }

        var submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json'
        }).done(function (resp) {
            var success = !!(resp && resp.success);
            var message = (resp && resp.message) ? resp.message : (success ? 'Saved.' : 'Unable to save.');
            showModalFeedback(form, message, success);
            showToast(message, success);
            if (success && resp.data) {
                refreshJudgeUI(resp.data);
            }
        }).fail(function () {
            var message = 'Unable to update events right now. Please try again.';
            showModalFeedback(form, message, false);
            showToast(message, false);
        }).always(function () {
            submitBtn.prop('disabled', false);
        });
    });

    $(document).on('submit', '.judge-remove-event-form', function (e) {
        e.preventDefault();

        var form = $(this);
        var selectEl = $(this).find('select[name="event_id"]');
        var selectedVal = selectEl.val();
        if (!selectedVal) {
            alert('Please select an event to remove.');
            return;
        }

        var eventLabel = selectEl.find('option:selected').text();
        if (!confirm('Remove "' + eventLabel + '" from this judge?')) {
            return;
        }

        var submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            dataType: 'json'
        }).done(function (resp) {
            var success = !!(resp && resp.success);
            var message = (resp && resp.message) ? resp.message : (success ? 'Saved.' : 'Unable to save.');
            showModalFeedback(form, message, success);
            showToast(message, success);
            if (success && resp.data) {
                refreshJudgeUI(resp.data);
            }
        }).fail(function () {
            var message = 'Unable to update events right now. Please try again.';
            showModalFeedback(form, message, false);
            showToast(message, false);
        }).always(function () {
            submitBtn.prop('disabled', false);
        });
    });

    $(document).on('click', '.js-open-results-modal', function (e) {
        var button = $(this);
        var href = button.attr('href') || '';

        // Always allow direct navigation when a real link exists.
        if (href && href !== '#') {
            return;
        }

        e.preventDefault();
        e.stopPropagation();

        var slugConventionSeasonVal = button.attr('data-slug-convention-season') || '';
        var slugConventionVal = button.attr('data-slug-convention') || '';
        var judgeName = button.attr('data-judge-name') || 'Judge';
        var routesJson = button.attr('data-result-routes') || '[]';
        var resultRoutes = [];

        try {
            resultRoutes = JSON.parse(routesJson);
        } catch (err) {
            resultRoutes = [];
        }

        renderResultsModal(resultRoutes, slugConventionSeasonVal, slugConventionVal, judgeName);
        if ($.fn.modal && $('#judgeResultsModal').length) {
            $('#judgeResultsModal').modal('show');
        } else {
            showResultsModalFallback();
        }
    });

    $(document).on('click', '#judgeResultsModal .close, #judgeResultsModal [data-dismiss="modal"]', function (e) {
        if ($.fn.modal) {
            return;
        }
        e.preventDefault();
        hideResultsModalFallback();
    });

    $(document).on('click', '#judgeResultsModal', function (e) {
        if ($.fn.modal) {
            return;
        }
        if ($(e.target).is('#judgeResultsModal')) {
            hideResultsModalFallback();
        }
    });

    $(document).on('click', '.js-open-all-results', function () {
        if ($(this).is(':disabled')) {
            return;
        }

        var linksRaw = $(this).attr('data-links') || '[]';
        var links = [];
        try {
            links = JSON.parse(decodeURIComponent(linksRaw));
        } catch (err) {
            links = [];
        }

        if (!Array.isArray(links) || links.length === 0) {
            return;
        }

        if (links.length > 5) {
            var proceedBulkOpen = confirm('This will open ' + links.length + ' result pages in new tabs. Continue?');
            if (!proceedBulkOpen) {
                return;
            }
        }

        links.forEach(function (href, idx) {
            setTimeout(function () {
                window.open(href, '_blank');
            }, idx * 120);
        });
    });
});
</script>