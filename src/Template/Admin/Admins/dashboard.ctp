<?php echo $this->Html->script('highcharts/highcharts.js'); ?>
<?php echo $this->Html->script('highcharts/exporting.js'); ?>
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
	?>
    <section class="content">
        <!-- Small boxes (Stat box) -->
        <div class="row">
			 
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
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrationstudents', 'action' => 'allstudents'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
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
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrationteachers', 'action' => 'allteachers'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
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
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrations', 'action' => 'allschools'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
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
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionregistrations', 'action' => 'alljudges'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
                </div>
            </div>
			
			<div class="col-lg-3 col-xs-6"> 
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo $total_conv_seas_events ? $total_conv_seas_events : '0'; ?></h3>
                        <p>Total Events</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-puzzle-piece"></i>
                    </div>
                    <?php echo $this->Html->link('More info <i class="fa fa-arrow-circle-right"></i>', ['controller' => 'conventionseasonevents', 'action' => 'allevents'], [ 'escape' => false, 'title' => 'More info', 'class' => 'small-box-footer']); ?>
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

    <!-- Dashboard Charts Section -->
    <?php if ($sess_admin_header_season_id > 0): ?>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Scheduled Events by Category <span class="help-icon" title="Number of scheduled entries in each scheduling category."><i class="fa fa-info-circle"></i></span></h3>
                    </div>
                    <div class="box-body">
                        <div id="event-distribution-chart" style="height: 280px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Schedule Status <span class="help-icon" title="Breakdown of scheduled vs unscheduled entries."><i class="fa fa-info-circle"></i></span></h3>
                    </div>
                    <div class="box-body">
                        <div id="event-status-chart" style="height: 280px;"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Participants Breakdown <span class="help-icon" title="Number of students, supervisors, schools and judges registered."><i class="fa fa-info-circle"></i></span></h3>
                    </div>
                    <div class="box-body">
                        <div id="registration-trend-chart" style="height: 280px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Events per Convention Day <span class="help-icon" title="How many events are scheduled on each day of the convention."><i class="fa fa-info-circle"></i></span></h3>
                    </div>
                    <div class="box-body">
                        <div id="schedule-timeline-chart" style="height: 280px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    $(document).ready(function() {
        if (typeof Highcharts === 'undefined') {
            $('.box-body [id$="-chart"]').html('<p style="color:#999;padding:20px;">Chart library failed to load. Please refresh the page.</p>');
            return;
        }
        // Scheduled Events by Category - Bar Chart
        var schedCategoryData = <?php echo isset($schedCategoryData) ? $schedCategoryData : '[0,0,0,0]'; ?>;
        Highcharts.chart('event-distribution-chart', {
            chart: { type: 'column' },
            title: { text: null },
            xAxis: { categories: ['Group Sequential', 'Individual Elimination', 'Group Elimination', 'Individual Sequential'] },
            yAxis: { min: 0, title: { text: 'Scheduled Entries' } },
            legend: { enabled: false },
            series: [{ name: 'Entries', data: schedCategoryData, colorByPoint: true }],
            credits: { enabled: false }
        });

        // Schedule Status Donut
        var totalScheduled   = <?php echo isset($totalScheduled)   ? (int)$totalScheduled   : 0; ?>;
        var totalUnscheduled = <?php echo isset($totalUnscheduled) ? (int)$totalUnscheduled : 0; ?>;
        Highcharts.chart('event-status-chart', {
            chart: { type: 'pie' },
            title: { text: null },
            plotOptions: { pie: { innerSize: '55%', dataLabels: { enabled: true } } },
            series: [{
                name: 'Entries',
                data: [
                    { name: 'Scheduled',   y: totalScheduled,   color: '#00a65a' },
                    { name: 'Unscheduled', y: totalUnscheduled, color: '#dd4b39' }
                ]
            }],
            credits: { enabled: false }
        });

        // Participants breakdown - bar chart
        Highcharts.chart('registration-trend-chart', {
            chart: { type: 'bar' },
            title: { text: null },
            xAxis: { categories: ['Students', 'Supervisors', 'Schools', 'Judges'] },
            yAxis: { min: 0, title: { text: 'Count' } },
            legend: { enabled: false },
            series: [{
                name: 'Count',
                colorByPoint: true,
                data: [
                    <?php echo isset($total_students)          ? (int)$total_students          : 0; ?>,
                    <?php echo isset($total_teachers_parents)  ? (int)$total_teachers_parents  : 0; ?>,
                    <?php echo isset($total_schools)           ? (int)$total_schools           : 0; ?>,
                    <?php echo isset($total_judges)            ? (int)$total_judges            : 0; ?>
                ]
            }],
            credits: { enabled: false }
        });

        // Events per Convention Day - column chart
        var dayNames     = <?php echo isset($dayNames)     ? $dayNames     : '["Monday","Tuesday","Wednesday","Thursday"]'; ?>;
        var dayCountData = <?php echo isset($dayCountData) ? $dayCountData : '[0,0,0,0]'; ?>;
        Highcharts.chart('schedule-timeline-chart', {
            chart: { type: 'column' },
            title: { text: null },
            xAxis: { categories: dayNames },
            yAxis: { min: 0, title: { text: 'Scheduled Entries' } },
            legend: { enabled: false },
            series: [{ name: 'Entries', data: dayCountData, color: '#3c8dbc' }],
            credits: { enabled: false }
        });
    });
    </script>
    <?php endif; ?>

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

