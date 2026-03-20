<?php
use Cake\ORM\TableRegistry;
$this->Schedulingtimings = TableRegistry::getTableLocator()->get('Schedulingtimings');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();

		function applyDayLoadTableFilters() {
			var $table = $('#dayLoadTable');
			if (!$table.length) return;

			var query = $.trim(String($('#dayLoadFilterQuery').val() || '')).toLowerCase();
			var statusFilter = String($('#dayLoadFilterStatus').val() || '');
			var dayFilter = String($('#dayLoadFilterDay').val() || '');
			var sortField = String($('#dayLoadSortField').val() || 'load');
			var sortDir = String($('#dayLoadSortDir').val() || 'desc');

			var rows = $('#dayLoadBody tr').get();
			rows.sort(function(a, b) {
				var av = $(a).data(sortField);
				var bv = $(b).data(sortField);
				if (sortField === 'date' || sortField === 'day' || sortField === 'status') {
					av = String(av || '');
					bv = String(bv || '');
					if (sortDir === 'asc') return av.localeCompare(bv);
					return bv.localeCompare(av);
				}
				av = parseFloat(av || 0);
				bv = parseFloat(bv || 0);
				if (sortDir === 'asc') return av - bv;
				return bv - av;
			});

			$.each(rows, function(_, row) {
				$('#dayLoadBody').append(row);
			});

			var visibleCount = 0;
			$('#dayLoadBody tr').each(function(){
				var $tr = $(this);
				var rowStatus = String($tr.data('status') || '');
				var rowDay = String($tr.data('day') || '');
				var rowText = String($tr.text() || '').toLowerCase();

				var keep = true;
				if (statusFilter && rowStatus !== statusFilter) keep = false;
				if (dayFilter && rowDay !== dayFilter) keep = false;
				if (query && rowText.indexOf(query) === -1) keep = false;

				$tr.toggle(keep);
				if (keep) visibleCount++;
			});

			$('#dayLoadVisibleCount').text(String(visibleCount));
		}

		$(document).on('input change', '#dayLoadFilterQuery, #dayLoadFilterStatus, #dayLoadFilterDay, #dayLoadSortField, #dayLoadSortDir', applyDayLoadTableFilters);
		$(document).on('click', '#dayLoadFilterReset', function(e){
			e.preventDefault();
			$('#dayLoadFilterQuery').val('');
			$('#dayLoadFilterStatus').val('');
			$('#dayLoadFilterDay').val('');
			$('#dayLoadSortField').val('load');
			$('#dayLoadSortDir').val('desc');
			applyDayLoadTableFilters();
		});

		$(document).on('click', 'a.dayload-auto-link', function(){
			var preset = String($('#dayLoadAutomationPreset').val() || 'balanced');
			var href = String($(this).attr('href') || '');
			if (!href) return;

			if (href.indexOf('prefill_preset=') !== -1) {
				href = href.replace(/([?&])prefill_preset=[^&]*/g, '$1prefill_preset=' + encodeURIComponent(preset));
			} else {
				href += (href.indexOf('?') === -1 ? '?' : '&') + 'prefill_preset=' + encodeURIComponent(preset);
			}
			$(this).attr('href', href);
		});

		applyDayLoadTableFilters();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>Schedule Category</h1>
        <p style="margin:2px 0 0 0; font-size:13px; color:#777;"><small><?php echo $conventionSD->Conventions['name']; ?> &mdash; <?php echo $conventionSD->season_year; ?></small></p>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Schedule Category </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title" style="color:Red;">
				Note: Schedulings for all categories will be done when you press "Start Scheduling" button.
				<br /><br />
				If this button is not visible, it might be possible that there is no event found in any of these below 4 categories.
				<br /><br />
				After pressing "Start Scheduling" button, please sit back and relax. Scheduling process took some time.
				<br /><br />
				After pressing "Start Scheduling" button, all previous scheduling will reset and start from scratch for this convention season.
				<br /><br />
				You can perform "Overwrite Timings" after schedulings and resolving conflicts. Overwrite timings does not have any link with conflicts.
				</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			
			
			
			<div class="container">
				<h2>Schedule Category
				 
				<?php
				if(count($arrEventsC1) > 0 && count($arrEventsC2) > 0 && count($arrEventsC3) > 0 && count($arrEventsC4) > 0)
				{
					echo $this->Html->link('Start Scheduling', ['controller'=>'schedulingtimings', 'action' => 'startschedulec1',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le', 'confirm' => 'Are you sure you want to start scheduling?']);
				}
				?>
				&nbsp;&nbsp;
				<?php echo $this->Html->link('Post-Schedule Overview', ['controller'=>'schedulingtimings', 'action'=>'postscheduleoverview', $convention_season_slug], ['escape'=>false, 'class'=>'btn btn-info']); ?>
				</h2> 



				<?php if (!empty($dayLoadRows)) { ?>
				<div class="panel panel-default" style="margin-bottom:15px;">
					<div class="panel-heading"><strong>Day Load Dashboard (Trial)</strong></div>
					<div class="panel-body" style="padding-bottom:5px;">
						<p style="margin-bottom:10px;">
							Target slots/day: <strong><?php echo number_format((float)$dayLoadMeta['target_slots_per_day'], 2); ?></strong>
							| Total slots: <strong><?php echo (int)$dayLoadMeta['total_slots']; ?></strong>
							| Active days: <strong><?php echo (int)$dayLoadMeta['day_count']; ?></strong>
							| Visible rows: <strong id="dayLoadVisibleCount"><?php echo (int)count($dayLoadRows); ?></strong>
						</p>
						<div class="row" style="margin-bottom:8px;">
							<div class="col-sm-3" style="margin-bottom:6px;">
								<label for="dayLoadAutomationPreset" style="margin-bottom:4px; display:block;">Automation Preset</label>
								<select id="dayLoadAutomationPreset" class="form-control input-sm">
									<option value="conservative">Conservative</option>
									<option value="balanced" selected="selected">Balanced</option>
									<option value="aggressive">Aggressive</option>
								</select>
							</div>
						</div>
						<div class="row" style="margin-bottom:10px;">
							<div class="col-sm-3" style="margin-bottom:6px;">
								<input type="text" id="dayLoadFilterQuery" class="form-control input-sm" placeholder="Filter text (event/day/date)">
							</div>
							<div class="col-sm-2" style="margin-bottom:6px;">
								<select id="dayLoadFilterStatus" class="form-control input-sm">
									<option value="">All Status</option>
									<option value="Overloaded">Overloaded</option>
									<option value="Balanced">Balanced</option>
									<option value="Underloaded">Underloaded</option>
								</select>
							</div>
							<div class="col-sm-2" style="margin-bottom:6px;">
								<select id="dayLoadFilterDay" class="form-control input-sm">
									<option value="">All Days</option>
									<?php foreach ($dayLoadRows as $dayOpt) { ?>
									<option value="<?php echo h($dayOpt['day_name']); ?>"><?php echo h($dayOpt['day_name']); ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-sm-2" style="margin-bottom:6px;">
								<select id="dayLoadSortField" class="form-control input-sm">
									<option value="load">Sort by Load %</option>
									<option value="slots">Sort by Slots</option>
									<option value="sessions">Sort by Sessions</option>
									<option value="events">Sort by Unique Events</option>
									<option value="rooms">Sort by Unique Rooms</option>
									<option value="date">Sort by Date</option>
								</select>
							</div>
							<div class="col-sm-2" style="margin-bottom:6px;">
								<select id="dayLoadSortDir" class="form-control input-sm">
									<option value="desc">Desc</option>
									<option value="asc">Asc</option>
								</select>
							</div>
							<div class="col-sm-1" style="margin-bottom:6px;">
								<a href="#" id="dayLoadFilterReset" class="btn btn-default btn-sm">Reset</a>
							</div>
						</div>
						<div class="table-responsive">
							<table class="table table-bordered table-condensed" id="dayLoadTable">
								<tr>
									<th>Day</th>
									<th>Date</th>
									<th>Sessions</th>
									<th>Participant Slots</th>
									<th>Unique Events</th>
									<th>Unique Rooms</th>
									<th>Load %</th>
									<th>Status</th>
									<th>Available Times</th>
									<th>Overloaded Events</th>
								</tr>
								<tbody id="dayLoadBody">
								<?php foreach ($dayLoadRows as $drow) { ?>
								<tr data-day="<?php echo h($drow['day_name']); ?>" data-date="<?php echo h($drow['date']); ?>" data-sessions="<?php echo (int)$drow['sessions']; ?>" data-slots="<?php echo (int)$drow['participant_slots']; ?>" data-events="<?php echo (int)$drow['unique_events']; ?>" data-rooms="<?php echo (int)$drow['unique_rooms']; ?>" data-load="<?php echo (float)$drow['load_pct']; ?>" data-status="<?php echo h($drow['status']); ?>">
									<td><?php echo h($drow['day_name']); ?></td>
									<td><?php echo h($drow['date']); ?></td>
									<td><?php echo (int)$drow['sessions']; ?></td>
									<td><?php echo (int)$drow['participant_slots']; ?></td>
									<td><?php echo (int)$drow['unique_events']; ?></td>
									<td><?php echo (int)$drow['unique_rooms']; ?></td>
									<td><?php echo number_format((float)$drow['load_pct'], 1); ?>%</td>
									<td><span class="label label-<?php echo h($drow['status_class']); ?>"><?php echo h($drow['status']); ?></span></td>
									<td>
										<?php if (!empty($drow['available_windows'])) { ?>
											<?php foreach ($drow['available_windows'] as $aw) { ?>
												<?php
												$autoQuery = [
													'prefill_date' => $drow['date'],
													'prefill_time' => $aw['start_time'],
													'prefill_event_ids' => !empty($drow['overloaded_event_ids']) ? implode(',', $drow['overloaded_event_ids']) : '',
													'prefill_preset' => 'balanced',
													'auto' => 1,
												];
												echo $this->Html->link(
													h($aw['label']).' ('.(int)$aw['rooms'].' rooms)',
													['controller'=>'schedulings', 'action' => 'overwritetimings', $convention_season_slug, '?' => $autoQuery],
													['class' => 'dayload-auto-link', 'style' => 'display:block; margin-bottom:2px; color:#2e7d32; font-weight:600;']
												);
												?>
											<?php } ?>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
									<td>
										<?php if (!empty($drow['overloaded_events'])) { ?>
											<?php foreach ($drow['overloaded_events'] as $oev) { ?>
												<?php
												$eventOnlyQuery = [
													'prefill_date' => $drow['date'],
													'prefill_time' => !empty($drow['available_windows'][0]['start_time']) ? $drow['available_windows'][0]['start_time'] : '',
													'prefill_event_id' => (int)$oev['event_id'],
													'prefill_preset' => 'balanced',
													'auto' => 1,
												];
												echo $this->Html->link(
													h($oev['label']).' ('.(int)$oev['slots'].')',
													['controller'=>'schedulings', 'action' => 'overwritetimings', $convention_season_slug, '?' => $eventOnlyQuery],
													['class' => 'dayload-auto-link', 'style' => 'display:block; color:#a94442; font-weight:600; margin-bottom:2px;']
												);
												?>
											<?php } ?>
										<?php } else { ?>
											-
										<?php } ?>
									</td>
								</tr>
								<?php } ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<?php } else { ?>
				<div class="alert alert-info" style="margin-bottom:15px;">Day Load Dashboard: no scheduled rows found yet for categories 1-4.</div>
				<?php } ?>

				<table class="table table-bordered">
					<tr>
						<th>#</th>
						<th>Needs Schedule</th>
						<th>Group Event</th>
						<th>Event Kind ID</th>
						<th>Has To Be Consecutive</th>
						<th>Number of events found</th>
						<th>View Scheduling</th>
					</tr>
					
					<tr>
						<td>1.</td>
						<td>Yes</td>
						<td>Yes</td>
						<td>Sequential</td>
						<td>Yes</td>
						<td><?php echo count($arrEventsC1); ?></td>
						<td>
							<?php
							if(count($arrEventsC1) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 1,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,1], ['class'=>'btn btn-success canlcel_le']);
								}
								else
								{
									echo 'Schedulings not yet done';
								}
							}
							?>
						</td>
					</tr>
					
					<tr>
						<td>2.</td>
						<td>Yes</td>
						<td>No</td>
						<td>Elimination</td>
						<td>No</td>
						<td><?php echo count($arrEventsC2); ?></td>
						<td>
							<?php
							if(count($arrEventsC2) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,2], ['class'=>'btn btn-success canlcel_le']);
								}
								else
								{
									echo 'Schedulings not yet done';
								}
							}
							?>
						</td>
					</tr>
					
					
					<!-- Category 3 is similar to category 2 -->
					<tr>
						<td>3.</td>
						<td>Yes</td>
						<td>Yes</td>
						<td>Elimination</td>
						<td>No</td>
						<td><?php echo count($arrEventsC3); ?></td>
						<td>
							<?php
							if(count($arrEventsC3) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,3], ['class'=>'btn btn-success canlcel_le']);
								}
								else
								{
									echo 'Schedulings not yet done';
								}
							}
							?>
						</td>
					</tr>
					
					
					
					<!-- Category 4 is similar to category 1 -->
					<tr>
						<td>4.</td>
						<td>Yes</td>
						<td>No</td>
						<td>Sequential</td>
						<td>Yes</td>
						<td><?php echo count($arrEventsC4); ?></td>
						<td>
							<?php
							if(count($arrEventsC4) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 4,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,4], ['class'=>'btn btn-success canlcel_le']);
								}
								else
								{
									echo 'Schedulings not yet done';
								}
							}
							?>
						</td>
					</tr>
					
					<?php
						/* echo $this->Html->link('Remove Overlapping', ['controller'=>'schedulingtimings', 'action' => 'removeoverlapping',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le', 'confirm' => 'Are you sure you want to start process to remove overlapping?']); */
						?>
					
					
					
					
				</table>
			</div>
			
			
			
             
                <div class="form-horizontal">
					<div class="box-body">
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
						
						<?php
						echo $this->Html->link('<< Back To Pre-check', ['controller'=>'schedulings', 'action' => 'precheck',$convention_season_slug], ['class'=>'btn btn-default canlcel_le']);
						
						echo $this->Html->link('Room Restrictions', ['controller'=>'schedulings', 'action' => 'roomrestrictions',$convention_season_slug], ['class'=>'btn btn-info canlcel_le', 'title'=>'Change room day/time availability after scheduling']);

						$schedulingD_check = null;
						try {
							$Schedulings = \Cake\ORM\TableRegistry::getTableLocator()->get('Schedulings');
							$schedulingD_check = $Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
						} catch(\Exception $e) {}
						
						$_totalConflict = 0;
						if($schedulingD_check) {
							if(!empty($schedulingD_check->conflict_user_ids)) $_totalConflict += count(explode(',', $schedulingD_check->conflict_user_ids));
							if(!empty($schedulingD_check->conflict_user_ids_group)) $_totalConflict += count(explode(',', $schedulingD_check->conflict_user_ids_group));
						}
						if($_totalConflict > 0) {
							echo $this->Html->link('Resolve Conflicts ('.$_totalConflict.' found)', ['controller'=>'schedulings', 'action'=>'resolveconflicts',$convention_season_slug,'?'=>['ref'=>'schedulecategory']], ['class'=>'btn btn-danger canlcel_le', 'title'=>'Auto-resolve scheduling conflicts']);
						} else {
							echo '<a class="btn btn-success disabled canlcel_le">&#10003; No Conflicts</a>';
						}
						?>
						
						&nbsp;&nbsp;
						<?php
						// Finalize / Lock schedule button
						if($schedulingD_check) {
							if($schedulingD_check->is_finalized == 1) {
								echo $this->Form->postLink('&#10003; Schedule Finalized — Unlock', ['controller'=>'schedulings', 'action'=>'finalizeschedule',$convention_season_slug], ['class'=>'btn btn-success canlcel_le', 'escape'=>false, 'confirm'=>'Are you sure you want to UNLOCK this schedule for editing?']);
							} else {
								echo '<a class="btn btn-default disabled canlcel_le">Not Yet Finalized</a>';
								echo $this->Form->postLink('Finalize &amp; Lock Schedule', ['controller'=>'schedulings', 'action'=>'finalizeschedule',$convention_season_slug], ['class'=>'btn btn-danger canlcel_le', 'escape'=>false, 'confirm'=>'Are you sure you want to FINALIZE and LOCK this schedule? Sponsors/attendees can then be given the schedule.']);
							}
						}
						?>
						
						
                    </div>
                  </div>
                </div> 
          </div>
    </section>
  </div>