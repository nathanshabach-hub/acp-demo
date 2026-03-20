<?php echo $this->Html->script('bootstrap.min.js'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>Room Restrictions</h1>
        <p style="margin:2px 0 0 0; font-size:13px; color:#777;"><small><?php echo $conventionSD->Conventions['name']; ?> &mdash; <?php echo $conventionSD->season_year; ?></small></p>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-calendar"></i> Schedule Category ', ['controller'=>'schedulings', 'action'=>'schedulecategory',$convention_season_slug], ['escape'=>false]);?></li>
          <li class="active">Room Restrictions</li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Change room day/time availability and re-apply to existing schedule</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			
			<div class="container">
				<h2>Room Restrictions
				&nbsp;&nbsp;
				<?php
				echo $this->Html->link('Re-apply Room Restrictions', ['controller'=>'schedulings', 'action' => 'reapplyroomrestrictions',$convention_season_slug], ['class'=>'btn btn-warning', 'confirm' => 'This will move all scheduled events that violate room restrictions to allowed days/times. Are you sure?']);
				?>
				</h2>
				
				<table class="table table-bordered table-striped">
					<thead>
						<tr>
							<th>#</th>
							<th>Room Name</th>
							<th>Available Days</th>
							<th>Available Time</th>
							<th>Scheduled Events</th>
							<th>Currently Scheduled On</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody>
					<?php
					$counter = 1;
					foreach($convRooms as $room)
					{
						$scheduledCount = isset($roomScheduleCounts[$room->id]) ? $roomScheduleCounts[$room->id] : 0;
						$scheduledDays = isset($roomScheduleDays[$room->id]) ? $roomScheduleDays[$room->id] : [];
						
						// Check if there's a restriction violation
						$hasViolation = false;
						if (!empty($room->restricted_days) && !empty($scheduledDays)) {
							$allowedDays = explode(',', $room->restricted_days);
							foreach ($scheduledDays as $sd) {
								if (!in_array($sd, $allowedDays)) {
									$hasViolation = true;
									break;
								}
							}
						}
					?>
					<tr <?php if($hasViolation) echo 'class="danger"'; ?>>
						<td><?php echo $counter; ?></td>
						<td><?php echo $room->room_name; ?></td>
						<td>
							<?php 
							if (!empty($room->restricted_days)) {
								echo '<span class="label label-info">' . str_replace(',', '</span> <span class="label label-info">', $room->restricted_days) . '</span>';
							} else {
								echo '<span class="label label-success">All Days</span>';
							}
							?>
						</td>
						<td>
							<?php 
							if (!empty($room->restricted_start_time) && !empty($room->restricted_finish_time)) {
								echo date("h:i A", strtotime($room->restricted_start_time)) . ' - ' . date("h:i A", strtotime($room->restricted_finish_time));
							} else {
								echo '<span class="label label-success">All Day</span>';
							}
							?>
						</td>
						<td>
							<?php echo $scheduledCount; ?>
							<?php if($hasViolation) echo ' <span class="label label-danger">Has Violations</span>'; ?>
						</td>
						<td>
							<?php 
							if (!empty($scheduledDays)) {
								foreach($scheduledDays as $sd) {
									$isViolating = false;
									if (!empty($room->restricted_days)) {
										$allowedDays = explode(',', $room->restricted_days);
										if (!in_array($sd, $allowedDays)) {
											$isViolating = true;
										}
									}
									if ($isViolating) {
										echo '<span class="label label-danger">' . $sd . '</span> ';
									} else {
										echo '<span class="label label-default">' . $sd . '</span> ';
									}
								}
							} else {
								echo '-';
							}
							?>
						</td>
						<td>
							<button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#roomModal<?php echo $room->id; ?>">
								<i class="fa fa-pencil"></i> Edit
							</button>
						</td>
					</tr>
					<?php
						$counter++;
					}
					?>
					</tbody>
				</table>
			</div>
			
			
			<div class="form-horizontal">
				<div class="box-body">
                    <div class="box-footer">
                        <label class="col-sm-2 control-label">&nbsp;</label>
						<?php
						echo $this->Html->link('<< Back To Schedule Category', ['controller'=>'schedulings', 'action' => 'schedulecategory',$convention_season_slug], ['class'=>'btn btn-default canlcel_le']);
						?>
                    </div>
				</div>
			</div>
          </div>
    </section>
</div>


<!-- Modals for each room -->
<?php foreach($convRooms as $room) { 
	$selectedDays = [];
	if (!empty($room->restricted_days)) {
		$selectedDays = explode(',', $room->restricted_days);
	}
?>
<div class="modal fade" id="roomModal<?php echo $room->id; ?>" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <?php echo $this->Form->create(null, ['url' => ['controller' => 'schedulings', 'action' => 'roomrestrictions', $convention_season_slug], 'type' => 'post']); ?>
      <?php echo $this->Form->hidden('room_id', ['value' => $room->id]); ?>
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">Edit Restrictions - <?php echo $room->room_name; ?></h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Available Days</label>
          <?php echo $this->Form->select('restricted_days', [
			  'Monday'=>'Monday', 'Tuesday'=>'Tuesday', 'Wednesday'=>'Wednesday', 
			  'Thursday'=>'Thursday', 'Friday'=>'Friday', 'Saturday'=>'Saturday', 'Sunday'=>'Sunday'
		  ], ['multiple' => true, 'class' => 'form-control', 'val' => $selectedDays, 'empty' => false, 'style' => 'height:150px;']); ?>
          <small class="text-muted">Hold Ctrl/Cmd to select multiple. Leave unselected for all days.</small>
        </div>
        <div class="form-group">
          <label>Available From</label>
          <?php echo $this->Form->control('restricted_start_time', ['label'=>false, 'type'=>'time', 'class'=>'form-control', 'value' => $room->restricted_start_time ? date('H:i', strtotime($room->restricted_start_time)) : '']); ?>
        </div>
        <div class="form-group">
          <label>Available To</label>
          <?php echo $this->Form->control('restricted_finish_time', ['label'=>false, 'type'=>'time', 'class'=>'form-control', 'value' => $room->restricted_finish_time ? date('H:i', strtotime($room->restricted_finish_time)) : '']); ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Restriction</button>
      </div>
      <?php echo $this->Form->end(); ?>
    </div>
  </div>
</div>
<?php } ?>
