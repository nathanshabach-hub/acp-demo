<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
			View Scheduling - <?php echo $conventionSD->Conventions['name']; ?> <span class="help-icon" title="View and manage event scheduling. Progress, stats, and overflow tools are shown below."><i class="fa fa-question-circle"></i></span>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">View Scheduling - <?php echo $conventionSD->Conventions['name']; ?></li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search" style="display:nones;">
                <?php echo $this->Form->create(Null, ['id'=>'adminSearch']); ?>
                    <div class="form-group align_box dtpickr_inputs">
                       <span class="hints" style="display:none;">Search by Season Name or Year</span>
                       <span class="hint">
                           <?php //echo $this->Form->control('Seasons.keyword', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Search by Season Name or Year']); ?>
                       </span>
                      
                       <div class="admin_asearch">
                            <div class="ad_s ajshort"> <?php //echo $this->Form->button('Search', ['class'=>'btn btn-info admin_ajax_search', 'type'=>'button']); ?></div>
                            <div class="ad_cancel"> <?php //echo $this->Html->link('Clear Search', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false, 'class'=>'btn btn-default canlcel_le']);?></div>
                       </div>
                    </div>
                <?php echo $this->Form->end(); ?>
                <div class="add_new_record">
				<?php
				echo $this->Html->link('<< View/Start Scheduling', ['controller'=>'schedulings', 'action'=>'schedulecategory',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-success']);
				echo '&nbsp;&nbsp;';
				echo $this->Html->link('<< Back To Pre-check', ['controller'=>'schedulings', 'action'=>'precheck',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-default']);
				echo '&nbsp;&nbsp;';
				echo $this->Html->link('Overflow Reallocation', ['controller'=>'schedulingtimings', 'action'=>'overflowallocator', $convention_season_slug, $scheduling_category], ['escape'=>false, 'class'=>'btn btn-warning']);
				echo '&nbsp;&nbsp;';
				echo $this->Html->link('Bulk Move Audits', ['controller'=>'schedulingtimings', 'action'=>'bulkmoveaudits', $convention_season_slug], ['escape'=>false, 'class'=>'btn btn-info']);
				?>
				<div style="margin-top:12px;padding:10px;border:1px solid #ddd;background:#fafafa;">
					<?php echo $this->Form->create(null, ['url' => ['controller' => 'schedulingtimings', 'action' => 'bulkreschedule', $convention_season_slug, $scheduling_category]]); ?>
					<div class="row">
						<div class="col-sm-2">
							<?php echo $this->Form->control('BulkMove.move_type', [
								'label' => 'Move Type',
								'type' => 'select',
								'options' => ['room' => 'Move Room', 'event' => 'Move Event'],
								'default' => 'room',
								'class' => 'form-control',
								'id' => 'bulk_move_type'
							]); ?>
						</div>
						<div class="col-sm-2">
							<?php echo $this->Form->control('BulkMove.strategy', [
								'label' => 'Strategy',
								'type' => 'select',
								'options' => ['compact' => 'Compact Gap-Fill', 'strict' => 'Strict Chronological'],
								'default' => 'compact',
								'class' => 'form-control'
							]); ?>
						</div>
						<div class="col-sm-2" id="bulk_room_box">
							<?php echo $this->Form->control('BulkMove.room_id', [
								'label' => 'Room',
								'type' => 'select',
								'options' => $bulkRoomOptions,
								'empty' => 'Choose Room',
								'class' => 'form-control'
							]); ?>
						</div>
						<div class="col-sm-2" id="bulk_event_box" style="display:none;">
							<?php echo $this->Form->control('BulkMove.event_id', [
								'label' => 'Event',
								'type' => 'select',
								'options' => $bulkEventOptions,
								'empty' => 'Choose Event',
								'class' => 'form-control'
							]); ?>
						</div>
						<div class="col-sm-2">
							<?php echo $this->Form->control('BulkMove.day', [
								'label' => 'Start Day',
								'type' => 'select',
								'options' => $bulkAllowedDays,
								'class' => 'form-control'
							]); ?>
						</div>
						<div class="col-sm-2">
							<?php echo $this->Form->control('BulkMove.start_time', [
								'label' => 'Start Time',
								'type' => 'text',
								'class' => 'form-control',
								'value' => $bulkDefaultStartTime,
								'placeholder' => 'HH:MM'
							]); ?>
						</div>
						<div class="col-sm-1" style="padding-top:25px;">
							<?php echo $this->Form->button('Preview', ['class' => 'btn btn-default', 'name' => 'BulkMove[preview_only]', 'value' => '1']); ?>
						</div>
						<div class="col-sm-1" style="padding-top:25px;">
							<?php echo $this->Form->button('Run Bulk Move', ['class' => 'btn btn-primary']); ?>
						</div>
					</div>
					<p style="margin:8px 0 0 0;color:#555;">Move all rows for one Room or one Event from the selected day/time. Compact fills gaps; Strict preserves sequence and leaves non-fit rows unchanged.</p>
					<?php echo $this->Form->end(); ?>
				</div>
				<?php if (!empty($bulkPreview) && !empty($bulkPreview['rows'])) { ?>
				<div style="margin-top:12px;padding:10px;border:1px solid #e3e3e3;background:#fff;">
					<strong>Bulk Move Preview</strong>
					<p style="margin:4px 0 10px 0;color:#666;">
						Type: <?php echo h($bulkPreview['move_type']); ?>,
						Strategy: <?php echo h($bulkPreview['strategy']); ?>,
						Will move: <?php echo (int)$bulkPreview['moved']; ?>,
						Unchanged: <?php echo (int)$bulkPreview['unchanged']; ?>
					</p>
					<div style="margin-bottom:10px;">
						<?php echo $this->Form->create(null, ['url' => ['controller' => 'schedulingtimings', 'action' => 'applybulkpreview', $convention_season_slug, $scheduling_category], 'style' => 'display:inline-block;']); ?>
						<?php echo $this->Form->button('Apply Preview', ['class' => 'btn btn-success']); ?>
						<?php echo $this->Form->end(); ?>
						<?php echo $this->Form->create(null, ['url' => ['controller' => 'schedulingtimings', 'action' => 'clearbulkpreview', $convention_season_slug, $scheduling_category], 'style' => 'display:inline-block;margin-left:8px;']); ?>
						<?php echo $this->Form->button('Clear Preview', ['class' => 'btn btn-default']); ?>
						<?php echo $this->Form->end(); ?>
					</div>
					<div style="max-height:260px;overflow:auto;">
						<table class="table table-bordered table-condensed" style="margin-bottom:0;">
							<thead>
								<tr>
									<th>ID</th>
									<th>Event</th>
									<th>From</th>
									<th>To</th>
									<th>Status</th>
									<th>Reason</th>
								</tr>
							</thead>
							<tbody>
							<?php foreach ($bulkPreview['rows'] as $previewRow) { ?>
								<tr>
									<td><?php echo (int)$previewRow['id']; ?></td>
									<td><?php echo h($previewRow['event']); ?></td>
									<td>
										<?php echo h($previewRow['from_day']); ?>
										<?php if (!empty($previewRow['from_start']) && !empty($previewRow['from_finish'])) { ?>
											<br><?php echo date('h:i A', strtotime($previewRow['from_start'])); ?> - <?php echo date('h:i A', strtotime($previewRow['from_finish'])); ?>
										<?php } ?>
									</td>
									<td>
										<?php echo h($previewRow['to_day']); ?>
										<?php if (!empty($previewRow['to_start']) && !empty($previewRow['to_finish'])) { ?>
											<br><?php echo date('h:i A', strtotime($previewRow['to_start'])); ?> - <?php echo date('h:i A', strtotime($previewRow['to_finish'])); ?>
										<?php } ?>
									</td>
									<td><?php echo h($previewRow['status']); ?></td>
									<td><?php echo h($previewRow['reason']); ?></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<?php } ?>
				
				
				</div>
            </div>
			
            <div class="m_content" id="listID">
				<div id="scheduleProgressBar" style="display:none;margin-bottom:15px;">
										<span class="help-icon" title="Shows schedule run progress. Wait for completion."><i class="fa fa-info-circle"></i></span>
					<div class="progress">
						<div class="progress-bar progress-bar-striped active" role="progressbar" style="width:0%" id="scheduleProgress"></div>
					</div>
					<span id="scheduleProgressText">Running schedule...</span>
				</div>
				<div id="scheduleSummaryStats" style="margin-bottom:15px;display:none;">
										<span class="help-icon" title="Summary stats: events scheduled, conflicts, overflow."><i class="fa fa-info-circle"></i></span>
					<strong>Schedule Summary:</strong>
					<ul>
						<li id="statEventsScheduled">Events Scheduled: 0</li>
						<li id="statConflicts">Conflicts: 0</li>
						<li id="statOverflow">Overflow: 0</li>
					</ul>
				</div>
				<?php echo $this->element("Admin/Schedulingtimings/viewschedulingc".$scheduling_category); ?>
            </div>
			 
				
			 
            
        </div>
    </section>
</div>
<script>
var bulkPreviewRows = <?php echo json_encode(!empty($bulkPreview['rows']) ? $bulkPreview['rows'] : []); ?>;
// Simulate schedule run progress (replace with real AJAX if available)
function simulateScheduleRun() {
	$('#scheduleProgressBar').show();
	var progress = 0;
	var interval = setInterval(function() {
		progress += 10;
		$('#scheduleProgress').css('width', progress+'%');
		$('#scheduleProgressText').text('Running schedule... '+progress+'%');
		if(progress >= 100) {
			clearInterval(interval);
			$('#scheduleProgressBar').hide();
			$('#scheduleSummaryStats').show();
			// Example stats (replace with real values)
			$('#statEventsScheduled').text('Events Scheduled: '+$('.tbl-resp-listing tbody tr').length);
			var conflicts = $('.tbl-resp-listing tbody tr.conflict').length;
			$('#statConflicts').text('Conflicts: '+conflicts);
			var overflow = $('.tbl-resp-listing tbody tr.overflow').length;
			$('#statOverflow').text('Overflow: '+overflow);
		}
	}, 300);
}

// Highlight unscheduled/conflict events (example: add .conflict/.overflow classes)
$(document).ready(function() {
	function toggleBulkMoveInputs() {
		var moveType = $('#bulk_move_type').val();
		if (moveType === 'event') {
			$('#bulk_room_box').hide();
			$('#bulk_event_box').show();
		} else {
			$('#bulk_event_box').hide();
			$('#bulk_room_box').show();
		}
	}

	$('#bulk_move_type').on('change', toggleBulkMoveInputs);
	toggleBulkMoveInputs();

	if (Array.isArray(bulkPreviewRows) && bulkPreviewRows.length > 0) {
		var previewMap = {};
		bulkPreviewRows.forEach(function(row) {
			previewMap[String(row.id)] = row;
		});

		$('.tbl-resp-listing tbody tr[data-schedule-id]').each(function() {
			var rowId = String($(this).data('schedule-id'));
			if (!previewMap[rowId]) {
				return;
			}

			var previewRow = previewMap[rowId];
			if (previewRow.status === 'will-move') {
				$(this).css('background', '#e7f6ea');
				$(this).attr('title', 'Preview move: ' + previewRow.to_day + ' ' + previewRow.to_start + ' - ' + previewRow.to_finish);
			} else if (previewRow.status === 'unchanged') {
				$(this).css('background', '#fff3cd');
				$(this).attr('title', previewRow.reason || 'Preview left this row unchanged');
			}
		});
	}

	// Example: highlight rows with missing start/finish time as conflict
	$('.tbl-resp-listing tbody tr').each(function() {
		var start = $(this).find('td[data-title="Start"]').text();
		var finish = $(this).find('td[data-title="Finish"]').text();
		if(!start || !finish) {
			$(this).addClass('conflict');
			$(this).css('background','#ffe6e6');
		}
	});
	// Example: highlight overflow (custom logic)
	// $(this).addClass('overflow'); $(this).css('background','#fffbe6');
	// Start progress simulation
	simulateScheduleRun();
});
</script>
 
<?php
if(is_array($pendingEventsToRoomsList) && count($pendingEventsToRoomsList)>0)
{
?>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<div class="modal fade" id="myModalPendingEvents" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Pending Events <?php echo count($pendingEventsToRoomsList); ?></h4>
			</div>
			<div class="modal-body">
				<?php
				$cntrPE = 1;
				foreach($pendingEventsToRoomsList as $pendingev)
				{
				?>
					<p>
						<?php
						echo $cntrPE.'.&nbsp;&nbsp;';
						echo $pendingev;
						?>
					</p>
				<?php
				$cntrPE++;
				}
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php
}
?>
