<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>
<script>
	$(document).ready(function(){
		$('.mdtpicker').mdtimepicker(); //Initializes the time picker
		window._overwriteSubmitMode = 'apply';
		var rowIndexSeed = 1;

		function bindRowTimePickers() {
			$('.sched-row-time').mdtimepicker();
		}

		function addScheduleRow() {
			var firstRow = $('#sched_rows_body tr.sched-row:first');
			var newRow = firstRow.clone();
			newRow.find('input, select').each(function(){
				var oldName = $(this).attr('name');
				if (oldName) {
					var newName = oldName.replace(/\[sched_rows\]\[[0-9]+\]/, '[sched_rows][' + rowIndexSeed + ']');
					$(this).attr('name', newName);
				}
				if ($(this).is('select')) {
					$(this).val('');
				} else {
					var defaultVal = $(this).data('default');
					$(this).val(typeof defaultVal !== 'undefined' ? defaultVal : '');
				}
			});
			$('#sched_rows_body').append(newRow);
			rowIndexSeed++;
			bindRowTimePickers();
			updateImpactPreview();
		}

		$('#add_sched_row').on('click', function(e){
			e.preventDefault();
			addScheduleRow();
		});

		$(document).on('click', '.remove_sched_row', function(e){
			e.preventDefault();
			if ($('#sched_rows_body tr.sched-row').length === 1) {
				var onlyRow = $('#sched_rows_body tr.sched-row:first');
				onlyRow.find('select').val('');
				onlyRow.find('.sched-row-time').val('');
				return;
			}
			$(this).closest('tr').remove();
			updateImpactPreview();
		});

		$('#btn_dry_run').on('click', function(){
			window._overwriteSubmitMode = 'preview';
		});
		$('#btn_apply_overwrite').on('click', function(){
			window._overwriteSubmitMode = 'apply';
		});

		$('#select_all_events').on('click', function(e){
			e.preventDefault();
			$('input[name="data[Schedulings][event_ids][]"]').prop('checked', true);
			updateImpactPreview();
		});

		$('#clear_all_events').on('click', function(e){
			e.preventDefault();
			$('input[name="data[Schedulings][event_ids][]"]').prop('checked', false);
			updateImpactPreview();
		});

		function updateImpactPreview(){
			var selectedEvents = $('input[name="data[Schedulings][event_ids][]"]:checked');
			var rowEventIds = [];
			$('.sched-row-event').each(function(){
				var v = $(this).val();
				if (v) {
					rowEventIds.push(v);
				}
			});
			var maxStudents = parseInt($('#max_students').val(), 10) || 0;
			var activeDays = $('input[name^="data[Schedulings][days]"][name$="[active]"]:checked').length;
			var totalStudents = 0;
			var totalScheduledRecords = 0;
			var eventCounter = {};

			if (rowEventIds.length > 0) {
				$.each(rowEventIds, function(_, eventId){
					eventCounter[eventId] = true;
				});
				var uniqueRows = Object.keys(eventCounter);
				$.each(uniqueRows, function(_, eventId){
					if(window.eventStats && window.eventStats[eventId]){
						totalStudents += parseInt(window.eventStats[eventId].students || 0, 10);
						totalScheduledRecords += parseInt(window.eventStats[eventId].scheduled_records || 0, 10);
					}
				});
				selectedEvents = { length: uniqueRows.length };
				activeDays = $('.sched-row-date').filter(function(){ return $(this).val() !== ''; }).length;
			}

			if (rowEventIds.length === 0) {
				selectedEvents.each(function(){
					var eventId = $(this).val();
					if(window.eventStats && window.eventStats[eventId]){
						totalStudents += parseInt(window.eventStats[eventId].students || 0, 10);
						totalScheduledRecords += parseInt(window.eventStats[eventId].scheduled_records || 0, 10);
					}
				});
			}

			var estimatedBlocks = maxStudents > 0 ? Math.ceil(Math.max(1, totalScheduledRecords) / maxStudents) : 0;
			$('#preview_selected_events').text(selectedEvents.length);
			$('#preview_selected_days').text(activeDays);
			$('#preview_students').text(totalStudents);
			$('#preview_records').text(totalScheduledRecords);
			$('#preview_blocks').text(estimatedBlocks);
		}

		$('input[name="data[Schedulings][event_ids][]"]').on('change', updateImpactPreview);
		$('input[name^="data[Schedulings][days]"][name$="[active]"]').on('change', updateImpactPreview);
		$(document).on('change', '.sched-row-event, .sched-row-date', updateImpactPreview);
		$(document).on('input', '.sched-row-time, .sched-row-max, .sched-row-gap', updateImpactPreview);
		$('#max_students').on('input', updateImpactPreview);
		bindRowTimePickers();
		updateImpactPreview();

		$('#schedulingWizardForm').on('submit', function(e){
			var selectedEvents = $('input[name="data[Schedulings][event_ids][]"]:checked').length;
			var maxStudents = parseInt($('#max_students').val(), 10);
			var activeDays = 0;
			var invalidDayTime = false;

			$('input[name^="data[Schedulings][days]"][name$="[active]"]').each(function(){
				if($(this).is(':checked')){
					activeDays++;
					var row = $(this).closest('tr');
					var timeVal = $.trim(row.find('input[name$="[time]"]').val());
					if(timeVal === ''){
						invalidDayTime = true;
					}
				}
			});

			var validRows = 0;
			var seenEvent = {};
			var duplicateEvent = false;
			var invalidRows = false;
			$('.sched-row').each(function(){
				var eventId = $.trim($(this).find('.sched-row-event').val());
				var dayDate = $.trim($(this).find('.sched-row-date').val());
				var dayTime = $.trim($(this).find('.sched-row-time').val());
				var rowHasAny = (eventId !== '' || dayDate !== '' || dayTime !== '');
				if (!rowHasAny) {
					return;
				}
				if (eventId === '' || dayDate === '' || dayTime === '') {
					invalidRows = true;
					return;
				}
				if (seenEvent[eventId]) {
					duplicateEvent = true;
					return;
				}
				seenEvent[eventId] = true;
				validRows++;
			});

			if (validRows > 0) {
				if (invalidRows) {
					e.preventDefault();
					alert('For row mode, each filled row must have Event, Date, and Time.');
					return false;
				}
				if (duplicateEvent) {
					e.preventDefault();
					alert('Please use each event only once in row mode.');
					return false;
				}
				if(window._overwriteSubmitMode === 'preview'){
					return true;
				}
				if(!confirm('This will overwrite existing timings for the configured event rows. Continue?')){
					e.preventDefault();
					return false;
				}
				return true;
			}

			if(selectedEvents === 0){
				e.preventDefault();
				alert('Please select at least one event.');
				return false;
			}

			if(!maxStudents || maxStudents < 1){
				e.preventDefault();
				alert('Please enter a valid Students Per Time Block value.');
				$('#max_students').focus();
				return false;
			}

			if(activeDays === 0){
				e.preventDefault();
				alert('Please select at least one convention day.');
				return false;
			}

			if(invalidDayTime){
				e.preventDefault();
				alert('Please provide a start time for every selected day.');
				return false;
			}

			if(window._overwriteSubmitMode === 'preview'){
				return true;
			}

			if(!confirm('This will overwrite existing timings for selected events. Continue?')){
				e.preventDefault();
				return false;
			}
		});
	});
</script>
<script>
	window.eventStats = <?php echo json_encode(isset($eventStats) ? $eventStats : []); ?>;
</script>

<?php echo $this->Html->script('jquery/ui/jquery.ui.core.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.widget.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.position.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.datepicker.js'); ?>
<?php echo $this->Html->css('themes/ui-lightness/jquery.ui.all.css'); ?>
<script>
    $(function() {
        $( "#overwrite_date" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
			minDate: '0d',
            maxDate: '+2y'
        });
    });
</script>
<style>
	.overwrite-step-row { margin: 10px 0 20px; display: flex; gap: 10px; flex-wrap: wrap; }
	.overwrite-step { background: #f4f7fb; border: 1px solid #dbe3ee; border-radius: 4px; padding: 8px 12px; font-size: 13px; }
	.overwrite-step b { color: #1f3f66; }
	.overwrite-panel-title { font-size: 15px; font-weight: 600; margin-bottom: 8px; color: #1f3f66; }
	.overwrite-help { color: #666; font-size: 12px; margin-top: 6px; }
	.overwrite-actions-inline { margin-bottom: 8px; }
	.overwrite-actions-inline a { margin-right: 10px; }
	.overwrite-required { color: #d73925; }
	.overwrite-card { border: 1px solid #e6e6e6; border-radius: 4px; padding: 12px; background: #fcfcfc; }
	.overwrite-preview { border: 1px solid #d6e9c6; border-radius: 4px; background: #f8fff3; padding: 10px 12px; margin-top: 10px; }
	.overwrite-preview .row { margin-bottom: 4px; }
	.overwrite-preview .label { font-size: 12px; }
	.overwrite-undo { border: 1px solid #f2dede; border-radius: 4px; background: #fff8f8; padding: 10px 12px; margin-bottom: 12px; }
	.overwrite-row-table th, .overwrite-row-table td { vertical-align: middle !important; }
</style>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Overwrite Timings - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Overwrite Timings </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title" style="color:Red;">
				Use this tool only after scheduling and conflict resolution. It updates existing timing slots for selected events.
				</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($schedulings, ['id'=>'schedulingWizardForm', 'type' => 'file', 'autocomplete' => 'off']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
						<?php if(!empty($latestOverwriteAudit)): ?>
						<div class="overwrite-undo">
							<div class="overwrite-panel-title" style="color:#a94442;">Undo Last Overwrite</div>
							<div class="row"><strong>Last batch ID:</strong> <?php echo h($latestOverwriteAudit['id']); ?></div>
							<div class="row"><strong>Affected records:</strong> <?php echo h($latestOverwriteAudit['affected_records']); ?></div>
							<div class="row"><strong>Created:</strong> <?php echo h($latestOverwriteAudit['created']); ?></div>
							<div class="overwrite-help">Use this only if the most recent overwrite was a mistake. This restores the previous timing values for that batch.</div>
							<div style="margin-top:8px;">
								<?php echo $this->Form->postLink('Undo Last Overwrite', ['controller' => 'schedulings', 'action' => 'undooverwritetimings', $convention_season_slug], ['class' => 'btn btn-danger btn-sm', 'confirm' => 'Undo the latest overwrite batch? This action will restore previous timings.']); ?>
							</div>
						</div>
						<?php endif; ?>

						<div class="overwrite-card" style="margin-bottom:15px;">
							<div class="overwrite-panel-title">Per-Event Scheduler (Recommended)</div>
							<p class="overwrite-help" style="margin-bottom:8px;">Set each event with its own date, start time, students per block, and gap. Example: Spelling OPEN and Spelling U16 on the same date with different times.</p>
							<table class="table table-bordered table-condensed overwrite-row-table">
								<thead>
									<tr style="background:#eef3f8;">
										<th>Event</th>
										<th>Date</th>
										<th>Start Time</th>
										<th>Students/Block</th>
										<th>Gap (mins)</th>
										<th></th>
									</tr>
								</thead>
								<tbody id="sched_rows_body">
									<tr class="sched-row">
										<td>
											<select name="data[Schedulings][sched_rows][0][event_id]" class="form-control sched-row-event">
												<option value="">Select event</option>
												<?php foreach($finalEventArr as $evId => $evLabel): ?>
												<option value="<?php echo h($evId); ?>"><?php echo h($evLabel); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td>
											<select name="data[Schedulings][sched_rows][0][date]" class="form-control sched-row-date">
												<option value="">Select day</option>
												<?php foreach($conventionDays as $cd): ?>
												<option value="<?php echo h($cd['date']); ?>"><?php echo h($cd['display']); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td><input type="text" name="data[Schedulings][sched_rows][0][time]" class="form-control sched-row-time mdtpicker" placeholder="e.g. 01:30 PM"></td>
										<td><input type="number" name="data[Schedulings][sched_rows][0][max_students]" class="form-control sched-row-max" min="1" value="6" data-default="6"></td>
										<td><input type="number" name="data[Schedulings][sched_rows][0][time_gap_mins]" class="form-control sched-row-gap" min="1" value="1" data-default="1"></td>
										<td><a href="#" class="btn btn-xs btn-danger remove_sched_row">Remove</a></td>
									</tr>
								</tbody>
							</table>
							<a href="#" class="btn btn-xs btn-default" id="add_sched_row">Add another event row</a>
						</div>

						<div class="overwrite-step-row">
							<div class="overwrite-step"><b>Step 1:</b> Select event(s)</div>
							<div class="overwrite-step"><b>Step 2:</b> Select day(s) and start time(s)</div>
							<div class="overwrite-step"><b>Step 3:</b> Set students per block and gap</div>
						</div>
					
					
<!-- Event selection (checkboxes) -->
				<div class="form-group">
				      <label class="col-sm-2 control-label">Step 1: Select Events <span class="overwrite-required">*</span></label>
                      <div class="col-sm-10">
					  <?php if(!empty($finalEventArr)): ?>
					  <div class="overwrite-card">
					  <div class="overwrite-actions-inline">
						  <a href="#" id="select_all_events">Select all</a>
						  <a href="#" id="clear_all_events">Clear all</a>
					  </div>
					  <?php foreach($finalEventArr as $evId => $evLabel): ?>
					  <div class="checkbox" style="margin:4px 0;">
						  <label style="font-weight:normal;font-size:1em;">
							  <input type="checkbox" name="data[Schedulings][event_ids][]" value="<?php echo h($evId); ?>">
							  &nbsp;<?php echo h($evLabel); ?>
						  </label>
					  </div>
					  <?php endforeach; ?>
					  </div>
					  <p class="overwrite-help">Tip: Event labels show event name, event number, and current participant count.</p>
					  <?php else: ?>
					  <p class="text-muted">No eligible events found for this convention season.</p>
					  <?php endif; ?>
                      </div>
                    </div>
					
					<div class="form-group">
					<label class="col-sm-2 control-label">Step 2: Convention Days <span class="overwrite-required">*</span></label>
					<div class="col-sm-10">
					<?php if(!empty($conventionDays)): ?>
					<p class="overwrite-help" style="margin-bottom:6px;">Select each day to update, then enter a start time for that day.</p>
					<table class="table table-bordered table-condensed" style="width:auto;min-width:500px;">
						<thead><tr style="background:#eef3f8;">
							<th style="width:40px;text-align:center;">Include</th>
							<th>Convention Day</th>
							<th style="width:180px;">Start Time</th>
						</tr></thead>
						<tbody>
						<?php foreach($conventionDays as $di => $cd): ?>
						<tr>
							<td style="text-align:center;vertical-align:middle;">
								<input type="checkbox" name="data[Schedulings][days][<?php echo $di; ?>][active]" value="1" id="day_active_<?php echo $di; ?>">
								<input type="hidden" name="data[Schedulings][days][<?php echo $di; ?>][date]" value="<?php echo h($cd['date']); ?>">
							</td>
							<td><label for="day_active_<?php echo $di; ?>" style="font-weight:normal;margin:0;cursor:pointer;"><?php echo h($cd['display']); ?></label></td>
							<td><input type="text" name="data[Schedulings][days][<?php echo $di; ?>][time]" class="form-control mdtpicker" placeholder="e.g. 09:00 AM" style="width:100%;"></td>
						</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<?php else: ?>
					<p class="text-muted">No convention days found. Please run scheduling first.</p>
					<?php endif; ?>
					<div class="overwrite-preview">
						<div class="overwrite-panel-title" style="margin-bottom:6px;">Impact Preview</div>
						<div class="row"><strong>Selected events:</strong> <span id="preview_selected_events">0</span></div>
						<div class="row"><strong>Selected day slots:</strong> <span id="preview_selected_days">0</span></div>
						<div class="row"><strong>Participant rows found:</strong> <span id="preview_students">0</span></div>
						<div class="row"><strong>Existing schedule records to overwrite:</strong> <span id="preview_records">0</span></div>
						<div class="row"><strong>Estimated time blocks:</strong> <span id="preview_blocks">0</span></div>
						<div class="overwrite-help">Use Dry Run Preview to confirm counts before applying changes.</div>
					</div>
					</div>
				</div>
					<div class="form-group">
				      <label class="col-sm-2 control-label">Step 3: Students Per Time Block <span class="overwrite-required">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->input('Schedulings.max_students', ['id'=>'max_students', 'label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Max Students', 'min'=>'1']); ?>
						  <p class="overwrite-help">Example: enter 6 to place 6 students in each time block before moving to the next block.</p>
                      </div>
                    </div>

				<div class="form-group">
				      <label class="col-sm-2 control-label">Break Between Blocks (minutes) <span class="overwrite-required">*</span></label>
                      <div class="col-sm-10">
					  <?php echo $this->Form->input('Schedulings.time_gap_mins', ['id'=>'time_gap_mins', 'label'=>false, 'type'=>'number', 'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Minutes gap between groups', 'min'=>'0', 'value'=>'1']); ?>
					  <p class="overwrite-help">Default is 1 minute. Increase this if judges/rooms need transition time between blocks.</p>
                      </div>
                    </div>

                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
						<button type="submit" class="btn btn-warning" id="btn_dry_run" name="data[Schedulings][preview_only]" value="1">Dry Run Preview</button>
						<?php echo $this->Form->button('Apply Overwrite Timings', ['type'=>'submit', 'id' => 'btn_apply_overwrite', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action' => 'precheck', $convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>