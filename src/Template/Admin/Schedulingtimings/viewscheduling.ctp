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
                           <?php //echo $this->Form->input('Seasons.keyword', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Search by Season Name or Year']); ?>
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
				?>
				
				
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
