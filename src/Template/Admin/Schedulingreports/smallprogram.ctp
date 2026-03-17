<div class="content-wrapper">
    <section class="content-header">
      <h1>Small Program</h1>
        <p style="margin:2px 0 0 0; font-size:13px; color:#777;"><small><?php echo $conventionSD->Conventions['name']; ?> &mdash; <?php echo $conventionSD->season_year; ?></small></p>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Small Program</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Overview of All Events by Day &amp; Time</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

			<div class="admin_search">
               <div class="admin_asearch">
                <div class="add_new_record">
				
				<?php echo $this->Html->link('<i class="fa fa-print"></i> Print', ['controller'=>'schedulingreports', 'action'=>'smallprogramprint',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']);?>
				
				<?php echo $this->Html->link('<i class="fa fa-download"></i> Download CSV', ['controller'=>'schedulingreports', 'action'=>'exportcsv',$convention_season_slug,'smallprogram'], ['escape'=>false, 'class'=>'btn btn-success']);?>

				<?php echo $this->Html->link('<i class="fa fa-table"></i> PDF Style', ['controller'=>'schedulingreports', 'action'=>'smallprogramv2',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-primary']);?>
				
				<?php echo $this->Html->link('Back', ['controller'=>'schedulings', 'action'=>'reports',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-warning']);?>
				
				</div>
            </div>
            </div>

            <div class="box-body">
			<?php if(!empty($programByDay)) { ?>
			<?php foreach($programByDay as $dayName => $events) { ?>
			
			<h4 style="margin-top:20px;"><strong><?php echo h($dayName); ?><?php if(!empty($dayDates[$dayName])): ?><span style="font-weight:normal;font-size:0.85em;color:#555;margin-left:10px;"><?php echo h($dayDates[$dayName]); ?></span><?php endif; ?></strong></h4>
			<div class="table-responsive">
                <table class="table table-bordered table-condensed table-striped">
                    <thead>
						<tr>
							<th style="width:12%;">Start</th>
							<th style="width:12%;">Finish</th>
							<th style="width:40%;">Event</th>
							<th style="width:36%;">Room / Location</th>
						</tr>
                    </thead>
                    <tbody>
					<?php foreach($events as $ev) { ?>
					<tr>
						<td><?php echo h($ev['start_time']); ?></td>
						<td><?php echo h($ev['end_time']); ?></td>
						<td><?php echo h($ev['event_name']); ?></td>
						<td><?php echo h($ev['room_name']); ?></td>
					</tr>
					<?php } ?>
                    </tbody>
                </table>
			</div>
			<?php } ?>
			<?php } else { ?>
			<p><i>No scheduled events found for this convention season.</i></p>
			<?php } ?>
            </div>
        </div>
    </section>
  </div>
