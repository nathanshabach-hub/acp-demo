<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <!-- Main content -->
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

