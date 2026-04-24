<style>
:root {
    --dashboard-ink: #1f2a37;
    --dashboard-muted: #6b7785;
    --dashboard-border: #dbe3ec;
    --dashboard-surface: #ffffff;
    --dashboard-surface-soft: #f6f8fb;
    --dashboard-shadow: 0 10px 24px rgba(23, 36, 56, 0.08);
    --dashboard-brand: #204c8c;
    --dashboard-brand-soft: #eaf1fb;
    --dashboard-danger: #c85b51;
    --dashboard-danger-soft: #fbefee;
    --dashboard-success: #1f8b5c;
    --dashboard-success-soft: #ebf7f1;
    --dashboard-warning: #b98517;
    --dashboard-warning-soft: #fcf6e8;
}

.dashboard-pro {
    padding-bottom: 8px;
}

.dashboard-intro {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
    padding: 20px 22px;
    border: 1px solid var(--dashboard-border);
    border-radius: 16px;
    background: linear-gradient(135deg, #ffffff 0%, #f5f8fc 100%);
    box-shadow: var(--dashboard-shadow);
}

.dashboard-intro-title {
    margin: 0 0 6px;
    font-size: 24px;
    font-weight: 700;
    color: var(--dashboard-ink);
}

.dashboard-intro-copy {
    margin: 0;
    font-size: 13px;
    color: var(--dashboard-muted);
}

.dashboard-season-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px;
    padding: 10px 14px;
    background: var(--dashboard-brand-soft);
    color: var(--dashboard-brand);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .04em;
    text-transform: uppercase;
    white-space: nowrap;
}

.dashboard-alert-grid {
    display: grid;
    grid-template-columns: 1.2fr 1fr 1fr;
    gap: 14px;
    margin-bottom: 22px;
}

.dashboard-alert-card {
    padding: 16px 18px;
    border-radius: 14px;
    border: 1px solid var(--dashboard-border);
    background: var(--dashboard-surface);
    box-shadow: var(--dashboard-shadow);
}

.dashboard-alert-card.priority {
    border-color: #f1d2cf;
    background: linear-gradient(135deg, #fff8f7 0%, #fff0ee 100%);
}

.dashboard-alert-card.success {
    background: linear-gradient(135deg, #ffffff 0%, #f2faf6 100%);
}

.dashboard-alert-kicker {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--dashboard-muted);
}

.dashboard-alert-title {
    margin: 0 0 6px;
    font-size: 18px;
    font-weight: 700;
    color: var(--dashboard-ink);
}

.dashboard-alert-copy {
    margin: 0;
    font-size: 13px;
    color: var(--dashboard-muted);
}

.dashboard-alert-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 12px;
}

.dashboard-alert-actions a {
    font-size: 12px;
    font-weight: 700;
}

.dashboard-alert-metric {
    display: flex;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 6px;
}

.dashboard-alert-value {
    font-size: 28px;
    line-height: 1;
    font-weight: 800;
    color: var(--dashboard-ink);
}

.dashboard-alert-meta {
    font-size: 12px;
    font-weight: 700;
    color: var(--dashboard-muted);
}

.dashboard-section {
    margin-bottom: 22px;
}

.dashboard-section-heading {
    margin: 0 0 12px;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #66768a;
}

.dashboard-card {
    position: relative;
    min-height: 168px;
    padding: 18px 18px 0;
    border: 1px solid var(--dashboard-border);
    border-radius: 14px;
    background: var(--dashboard-surface);
    box-shadow: var(--dashboard-shadow);
    overflow: hidden;
}

.dashboard-card:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #2f6fe4;
}

.dashboard-card.tone-navy:before { background: #263a7a; }
.dashboard-card.tone-blue:before { background: #2f6fe4; }
.dashboard-card.tone-cyan:before { background: #1f9acb; }
.dashboard-card.tone-green:before { background: #19a86b; }
.dashboard-card.tone-gold:before { background: #db9b16; }
.dashboard-card.tone-plum:before { background: #5c6ac4; }
.dashboard-card.tone-red:before { background: #d75a5a; }
.dashboard-card.tone-olive:before { background: #5c8d35; }

.dashboard-card-label {
    margin: 0 0 8px;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #718194;
}

.dashboard-card-value {
    margin: 0;
    font-size: 34px;
    line-height: 1;
    font-weight: 800;
    color: var(--dashboard-ink);
}

.dashboard-card-copy {
    margin: 8px 0 0;
    min-height: 34px;
    font-size: 13px;
    color: var(--dashboard-muted);
}

.dashboard-card-icon {
    position: absolute;
    top: 18px;
    right: 18px;
    font-size: 34px;
    color: rgba(45, 72, 109, 0.16);
}

.dashboard-card-link {
    display: block;
    margin: 16px -18px 0;
    padding: 12px 18px;
    border-top: 1px solid #ebf0f5;
    font-size: 12px;
    font-weight: 700;
    color: var(--dashboard-brand);
    background: var(--dashboard-surface-soft);
}

.dashboard-card-link:hover,
.dashboard-card-link:focus {
    color: #1f4d90;
    background: #f4f8fc;
}

.dashboard-panel {
    height: 100%;
    border: 1px solid var(--dashboard-border);
    border-radius: 14px;
    background: var(--dashboard-surface);
    box-shadow: var(--dashboard-shadow);
    overflow: hidden;
}

.dashboard-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 16px 18px;
    border-bottom: 1px solid #ebf0f5;
    background: var(--dashboard-surface-soft);
}

.dashboard-panel-title {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: var(--dashboard-ink);
}

.dashboard-panel-copy {
    margin: 4px 0 0;
    font-size: 12px;
    color: var(--dashboard-muted);
}

.dashboard-panel-body {
    padding: 16px 18px 18px;
}

.dashboard-mini-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px dashed #e6edf4;
}

.dashboard-mini-stat:first-child {
    padding-top: 0;
}

.dashboard-mini-stat:last-child {
    padding-bottom: 0;
    border-bottom: 0;
}

.dashboard-mini-label {
    font-size: 13px;
    font-weight: 600;
    color: #304153;
}

.dashboard-mini-meta {
    display: block;
    margin-top: 3px;
    font-size: 12px;
    color: #7a8897;
}

.dashboard-mini-value {
    min-width: 42px;
    text-align: center;
    border-radius: 999px;
    padding: 5px 10px;
    background: var(--dashboard-brand-soft);
    color: var(--dashboard-brand);
    font-size: 12px;
    font-weight: 800;
}

.dashboard-top-item {
    padding: 12px 14px;
    border: 1px solid #e8eef5;
    border-radius: 12px;
    background: #f9fbfd;
    margin-bottom: 12px;
}

.dashboard-top-item:last-child {
    margin-bottom: 0;
}

.dashboard-top-kicker {
    margin: 0 0 6px;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #7a8897;
}

.dashboard-top-name {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: var(--dashboard-ink);
}

.dashboard-top-subcopy {
    margin: 6px 0 0;
    font-size: 12px;
    color: var(--dashboard-muted);
}

.dashboard-top-score {
    float: right;
    margin-left: 12px;
    padding: 5px 10px;
    border-radius: 999px;
    background: var(--dashboard-brand);
    color: #fff;
    font-size: 12px;
    font-weight: 800;
}

.dashboard-empty-state {
    margin: 0;
    padding: 14px;
    border: 1px dashed #d7e2ee;
    border-radius: 12px;
    background: #fafcff;
    color: #768496;
    font-size: 13px;
}

.dashboard-inline-links {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 16px;
}

.dashboard-inline-links a {
    font-size: 12px;
    font-weight: 700;
}

@media (max-width: 991px) {
    .dashboard-intro {
        flex-direction: column;
        align-items: flex-start;
    }

    .dashboard-alert-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard <span class="help-icon" title="This dashboard gives you a quick overview of convention stats and charts. Hover over each chart for details."><i class="fa fa-question-circle"></i></span>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

	<?php
	if($sess_admin_header_season_id>0)
	{
	    $schedCategoryDataArray = json_decode(isset($schedCategoryData) ? $schedCategoryData : '[0,0,0,0]', true);
	    if (!is_array($schedCategoryDataArray)) {
	        $schedCategoryDataArray = [0, 0, 0, 0];
	    }
	    $schedCategoryDataArray = array_pad(array_map('intval', $schedCategoryDataArray), 4, 0);

	    $dayNamesArray = json_decode(isset($dayNames) ? $dayNames : '[]', true);
	    $dayCountDataArray = json_decode(isset($dayCountData) ? $dayCountData : '[]', true);
	    if (!is_array($dayNamesArray)) {
	        $dayNamesArray = [];
	    }
	    if (!is_array($dayCountDataArray)) {
	        $dayCountDataArray = [];
	    }

	    $topEventLabelsArray = json_decode(isset($topEventLabels) ? $topEventLabels : '[]', true);
	    $topEventCountsArray = json_decode(isset($topEventCounts) ? $topEventCounts : '[]', true);
	    $unregisteredEventLabelsArray = json_decode(isset($unregisteredEventLabels) ? $unregisteredEventLabels : '[]', true);
	    if (!is_array($topEventLabelsArray)) {
	        $topEventLabelsArray = [];
	    }
	    if (!is_array($topEventCountsArray)) {
	        $topEventCountsArray = [];
	    }
	    if (!is_array($unregisteredEventLabelsArray)) {
	        $unregisteredEventLabelsArray = [];
	    }

	    $totalScheduledInt = isset($totalScheduled) ? (int)$totalScheduled : 0;
	    $totalUnscheduledInt = isset($totalUnscheduled) ? (int)$totalUnscheduled : 0;
	    $totalScheduleEntries = max(1, $totalScheduledInt + $totalUnscheduledInt);
	    $scheduledPct = (int)round(($totalScheduledInt / $totalScheduleEntries) * 100);

	    $dayPeak = '';
	    $dayPeakCount = 0;
	    if (!empty($dayNamesArray) && !empty($dayCountDataArray)) {
	        foreach ($dayNamesArray as $dayIdx => $dayName) {
	            $count = isset($dayCountDataArray[$dayIdx]) ? (int)$dayCountDataArray[$dayIdx] : 0;
	            if ($count > $dayPeakCount) {
	                $dayPeakCount = $count;
	                $dayPeak = $dayName;
	            }
	        }
	    }

	    $topEventName = !empty($topEventLabelsArray) ? $topEventLabelsArray[0] : 'No entries yet';
	    $topEventCount = !empty($topEventCountsArray) ? (int)$topEventCountsArray[0] : 0;
	    $noRegCount = count($unregisteredEventLabelsArray);
	    $activeSeasonLabel = !empty($conv_season_slug) ? ucwords(str_replace('-', ' ', $conv_season_slug)) : 'Current season';
        $needsAttention = $totalUnscheduledInt > 0 || $noRegCount > 0;

	    $overviewCards = [
	        [
	            'label' => 'Students',
	            'value' => (int)$total_students,
                'copy' => 'Registered student participants.',
	            'icon' => 'fa-group',
	            'tone' => 'navy',
	            'url' => ['controller' => 'conventionregistrationstudents', 'action' => 'allstudents'],
	        ],
	        [
	            'label' => 'Supervisors',
	            'value' => (int)$total_teachers_parents,
                'copy' => 'Teachers and parents on active registrations.',
	            'icon' => 'fa-user-secret',
	            'tone' => 'blue',
	            'url' => ['controller' => 'conventionregistrationteachers', 'action' => 'allteachers'],
	        ],
	        [
	            'label' => 'Schools/Homeschools',
	            'value' => (int)$total_schools,
                'copy' => 'Participating schools and homeschools.',
	            'icon' => 'fa-bank',
	            'tone' => 'cyan',
	            'url' => ['controller' => 'conventionregistrations', 'action' => 'allschools'],
	        ],
	        [
	            'label' => 'Judges',
	            'value' => (int)$total_judges,
                'copy' => 'Judges linked to convention registrations.',
	            'icon' => 'fa-bookmark',
	            'tone' => 'green',
	            'url' => ['controller' => 'conventionregistrations', 'action' => 'alljudges'],
	        ],
	        [
	            'label' => 'Total Events',
	            'value' => (int)$total_conv_seas_events,
                'copy' => 'Configured events available for scheduling.',
	            'icon' => 'fa-puzzle-piece',
	            'tone' => 'gold',
	            'url' => ['controller' => 'conventionseasonevents', 'action' => 'allevents'],
	        ],
	        [
	            'label' => 'Transactions',
	            'value' => (int)$total_transactions,
                'copy' => 'Recorded payment transactions this season.',
	            'icon' => 'fa-dollar',
	            'tone' => 'plum',
	            'url' => ['controller' => 'transactions', 'action' => 'index'],
	        ],
	    ];

	    $healthCards = [
	        [
	            'label' => 'Scheduled Entries',
	            'value' => $totalScheduledInt,
	            'copy' => $scheduledPct . '% of all entries are scheduled.',
	            'icon' => 'fa-check-circle',
	            'tone' => 'green',
	            'url' => ['controller' => 'Admins', 'action' => 'chartview', 'schedule-status'],
	        ],
	        [
	            'label' => 'Unscheduled Entries',
	            'value' => $totalUnscheduledInt,
	            'copy' => $totalUnscheduledInt > 0 ? 'Needs scheduling attention.' : 'No pending schedule work.',
	            'icon' => 'fa-exclamation-circle',
	            'tone' => 'red',
	            'url' => ['controller' => 'Admins', 'action' => 'chartview', 'schedule-status'],
	        ],
	        [
	            'label' => 'Peak Convention Day',
	            'value' => $dayPeakCount,
                'copy' => $dayPeak ? 'Busiest day: ' . $dayPeak : 'No daily schedule data yet.',
	            'icon' => 'fa-calendar',
	            'tone' => 'blue',
	            'url' => ['controller' => 'Admins', 'action' => 'chartview', 'events-per-day'],
	        ],
	        [
	            'label' => 'No-Registration Events',
	            'value' => $noRegCount,
	            'copy' => $noRegCount > 0 ? 'Configured events still waiting for entries.' : 'All events have at least one entry.',
	            'icon' => 'fa-bullhorn',
	            'tone' => 'olive',
	            'url' => ['controller' => 'Admins', 'action' => 'chartview', 'events-with-no-registrations'],
	        ],
	    ];
	?>
    <section class="content dashboard-pro">
        <div class="dashboard-intro">
            <div>
                <h2 class="dashboard-intro-title">Convention Operations Dashboard</h2>
                <p class="dashboard-intro-copy">A concise view of registrations, schedule health, and the items that need action first.</p>
            </div>
            <div class="dashboard-season-chip"><i class="fa fa-calendar-check-o"></i> Active Season: <?php echo h($activeSeasonLabel); ?></div>
        </div>

        <div class="dashboard-alert-grid">
            <div class="dashboard-alert-card <?php echo $needsAttention ? 'priority' : 'success'; ?>">
                <div class="dashboard-alert-kicker"><i class="fa <?php echo $needsAttention ? 'fa-bell-o' : 'fa-check-circle'; ?>"></i> Action Needed</div>
                <h3 class="dashboard-alert-title"><?php echo $needsAttention ? 'Scheduling follow-up is still open' : 'Core scheduling checks are in good shape'; ?></h3>
                <p class="dashboard-alert-copy"><?php echo $needsAttention ? $totalUnscheduledInt . ' unscheduled entries and ' . $noRegCount . ' no-registration events still need review.' : 'There are no unscheduled entries and every configured event has at least one registration.'; ?></p>
                <div class="dashboard-alert-actions">
                    <?php if ($totalUnscheduledInt > 0): ?>
                        <?php echo $this->Html->link('Review unscheduled entries', ['controller' => 'Admins', 'action' => 'chartview', 'schedule-status']); ?>
                    <?php endif; ?>
                    <?php if ($noRegCount > 0): ?>
                        <?php echo $this->Html->link('Review no-registration events', ['controller' => 'Admins', 'action' => 'chartview', 'events-with-no-registrations']); ?>
                    <?php endif; ?>
                    <?php if (!$needsAttention): ?>
                        <?php echo $this->Html->link('Review schedule distribution', ['controller' => 'Admins', 'action' => 'chartview', 'events-per-day']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-alert-card">
                <div class="dashboard-alert-kicker"><i class="fa fa-line-chart"></i> Demand Signal</div>
                <div class="dashboard-alert-metric">
                    <span class="dashboard-alert-value"><?php echo $topEventCount; ?></span>
                    <span class="dashboard-alert-meta">entries</span>
                </div>
                <h3 class="dashboard-alert-title"><?php echo h($topEventName); ?></h3>
                <p class="dashboard-alert-copy">Current highest-volume event in the active season.</p>
            </div>

            <div class="dashboard-alert-card">
                <div class="dashboard-alert-kicker"><i class="fa fa-calendar"></i> Schedule Load</div>
                <div class="dashboard-alert-metric">
                    <span class="dashboard-alert-value"><?php echo $dayPeakCount; ?></span>
                    <span class="dashboard-alert-meta">scheduled</span>
                </div>
                <h3 class="dashboard-alert-title"><?php echo $dayPeak ? h($dayPeak) : 'No peak day yet'; ?></h3>
                <p class="dashboard-alert-copy"><?php echo $dayPeak ? 'Current busiest convention day.' : 'Daily scheduling data will appear here once available.'; ?></p>
            </div>
        </div>

        <div class="dashboard-section">
            <h3 class="dashboard-section-heading">Overview</h3>
            <div class="row">
                <?php foreach ($overviewCards as $card): ?>
                    <div class="col-lg-4 col-md-6 col-sm-6">
                        <div class="dashboard-card tone-<?php echo h($card['tone']); ?>">
                            <div class="dashboard-card-icon"><i class="fa <?php echo h($card['icon']); ?>"></i></div>
                            <p class="dashboard-card-label"><?php echo h($card['label']); ?></p>
                            <p class="dashboard-card-value"><?php echo (int)$card['value']; ?></p>
                            <p class="dashboard-card-copy"><?php echo h($card['copy']); ?></p>
                            <?php echo $this->Html->link('Open details <i class="fa fa-arrow-circle-right"></i>', $card['url'], ['escape' => false, 'class' => 'dashboard-card-link']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="dashboard-section">
            <h3 class="dashboard-section-heading">Scheduling Health</h3>
            <div class="row">
                <?php foreach ($healthCards as $card): ?>
                    <div class="col-lg-3 col-md-6 col-sm-6">
                        <div class="dashboard-card tone-<?php echo h($card['tone']); ?>">
                            <div class="dashboard-card-icon"><i class="fa <?php echo h($card['icon']); ?>"></i></div>
                            <p class="dashboard-card-label"><?php echo h($card['label']); ?></p>
                            <p class="dashboard-card-value"><?php echo (int)$card['value']; ?></p>
                            <p class="dashboard-card-copy"><?php echo h($card['copy']); ?></p>
                            <?php echo $this->Html->link('Review <i class="fa fa-arrow-circle-right"></i>', $card['url'], ['escape' => false, 'class' => 'dashboard-card-link']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="dashboard-section">
            <h3 class="dashboard-section-heading">Insights</h3>
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="dashboard-panel">
                        <div class="dashboard-panel-header">
                            <div>
                                <h4 class="dashboard-panel-title">Scheduling Breakdown</h4>
                                <p class="dashboard-panel-copy">A compact view of how scheduled entries are distributed across event categories.</p>
                            </div>
                            <i class="fa fa-sitemap" style="color:#7f8da1;"></i>
                        </div>
                        <div class="dashboard-panel-body">
                            <div class="dashboard-mini-stat">
                                <div>
                                    <span class="dashboard-mini-label">Group Sequential</span>
                                    <span class="dashboard-mini-meta">Team performances that run in sequence</span>
                                </div>
                                <span class="dashboard-mini-value"><?php echo isset($schedCategoryDataArray[0]) ? (int)$schedCategoryDataArray[0] : 0; ?></span>
                            </div>
                            <div class="dashboard-mini-stat">
                                <div>
                                    <span class="dashboard-mini-label">Individual Elimination</span>
                                    <span class="dashboard-mini-meta">Head-to-head elimination event entries</span>
                                </div>
                                <span class="dashboard-mini-value"><?php echo isset($schedCategoryDataArray[1]) ? (int)$schedCategoryDataArray[1] : 0; ?></span>
                            </div>
                            <div class="dashboard-mini-stat">
                                <div>
                                    <span class="dashboard-mini-label">Group Elimination</span>
                                    <span class="dashboard-mini-meta">Team elimination scheduling load</span>
                                </div>
                                <span class="dashboard-mini-value"><?php echo isset($schedCategoryDataArray[2]) ? (int)$schedCategoryDataArray[2] : 0; ?></span>
                            </div>
                            <div class="dashboard-mini-stat">
                                <div>
                                    <span class="dashboard-mini-label">Individual Sequential</span>
                                    <span class="dashboard-mini-meta">Individually sequenced performances and presentations</span>
                                </div>
                                <span class="dashboard-mini-value"><?php echo isset($schedCategoryDataArray[3]) ? (int)$schedCategoryDataArray[3] : 0; ?></span>
                            </div>
                            <div class="dashboard-inline-links">
                                <?php echo $this->Html->link('View category detail', ['controller' => 'Admins', 'action' => 'chartview', 'scheduled-by-category']); ?>
                                <?php echo $this->Html->link('View daily distribution', ['controller' => 'Admins', 'action' => 'chartview', 'events-per-day']); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12">
                    <div class="dashboard-panel">
                        <div class="dashboard-panel-header">
                            <div>
                                <h4 class="dashboard-panel-title">Demand And Attention</h4>
                                <p class="dashboard-panel-copy">Highlights from the strongest-performing event and the events that may need promotion.</p>
                            </div>
                            <i class="fa fa-line-chart" style="color:#7f8da1;"></i>
                        </div>
                        <div class="dashboard-panel-body">
                            <div class="dashboard-top-item">
                                <span class="dashboard-top-score"><?php echo $topEventCount; ?></span>
                                <p class="dashboard-top-kicker">Most Entered Event</p>
                                <p class="dashboard-top-name"><?php echo h($topEventName); ?></p>
                                <p class="dashboard-top-subcopy">Highest registration volume across all configured events in this season.</p>
                            </div>

                            <?php if (!empty($unregisteredEventLabelsArray)): ?>
                                <?php foreach (array_slice($unregisteredEventLabelsArray, 0, 4) as $zeroEventLabel): ?>
                                    <div class="dashboard-mini-stat">
                                        <div>
                                            <span class="dashboard-mini-label"><?php echo h($zeroEventLabel); ?></span>
                                            <span class="dashboard-mini-meta">No registrations yet</span>
                                        </div>
                                        <span class="dashboard-mini-value">0</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="dashboard-empty-state">All configured events currently have at least one registration.</p>
                            <?php endif; ?>

                            <div class="dashboard-inline-links">
                                <?php echo $this->Html->link('View top entered events', ['controller' => 'Admins', 'action' => 'chartview', 'most-entered-events']); ?>
                                <?php echo $this->Html->link('View no-registration events', ['controller' => 'Admins', 'action' => 'chartview', 'events-with-no-registrations']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

	<?php
	}
	else
	{
	?>
	<section class="content">
        <!-- if no season selected -->
        <div class="row">
			<div class="col-lg-3 col-xs-6">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?php echo $total_seasons ? $total_seasons : '0'; ?></h3>
                        <p>Seasons</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bars"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'seasons', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			 
			
			<div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo $total_events ? $total_events : '0'; ?></h3>
                        <p>Global Events</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-puzzle-piece"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'events', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
			<div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo $total_conventions ? $total_conventions : '0'; ?></h3>
                        <p>Conventions</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bars"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventions', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
			<div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3><?php echo $total_divisions ? $total_divisions : '0'; ?></h3>
                        <p>Divisions</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-tasks"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'divisions', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
			<!------Users Count------>
            <div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3><?php echo $total_schools ? $total_schools : '0'; ?></h3>
                        <p>Schools/Homeschools</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bank"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'users', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
			<div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-light-blue">
                    <div class="inner">
                        <h3><?php echo $total_teachers_parents ? $total_teachers_parents : '0'; ?></h3>
                        <p>Supervisors</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-user-secret"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'users', 'action' => 'teachers'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
			<div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo $total_judges ? $total_judges : '0'; ?></h3>
                        <p>Judges</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bookmark"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'users', 'action' => 'judges'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
			<div class="col-lg-3 col-xs-6">
                <!-- small box -->
                <div class="small-box bg-navy">
                    <div class="inner">
                        <h3><?php echo $total_students ? $total_students : '0'; ?></h3>
                        <p>Students</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-group"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'users', 'action' => 'students'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
			
			
			<div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-olive">
                    <div class="inner">
                        <h3><?php echo $total_registrations ? $total_registrations : '0'; ?></h3>
                        <p>Convention Registrations</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-newspaper-o"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrations', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
			<div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-lime">
                    <div class="inner">
                        <h3><?php echo $total_transactions ? $total_transactions : '0'; ?></h3>
                        <p>Transactions</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-dollar"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'transactions', 'action' => 'index'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
    </section>
	<?php
	}
	?>
</div>

