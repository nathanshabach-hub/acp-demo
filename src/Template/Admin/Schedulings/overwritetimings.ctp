<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>
<script>
	window.overwritePrefillRows = <?php echo json_encode(isset($overwritePrefillRows) ? $overwritePrefillRows : []); ?>;
	window.overwriteAutoMode = <?php echo !empty($overwriteAutoMode) ? 'true' : 'false'; ?>;
	window.overwritePresetMap = <?php echo json_encode(isset($overwritePresetMap) ? $overwritePresetMap : []); ?>;
	window.overwriteSelectedPreset = <?php echo json_encode(isset($overwriteSelectedPreset) ? $overwriteSelectedPreset : 'balanced'); ?>;
	window.overwriteDefaultMax = <?php echo (int)(isset($overwriteDefaultMax) ? $overwriteDefaultMax : 6); ?>;
	window.overwriteDefaultGap = <?php echo (int)(isset($overwriteDefaultGap) ? $overwriteDefaultGap : 1); ?>;

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
			syncEventCodeFromSelection(newRow);
			updateImpactPreview();
			return newRow;
		}

		function setRowValues($row, data) {
			if (!data || !$row || !$row.length) return;
			if (data.event_id) {
				$row.find('.sched-row-event').val(String(data.event_id));
			}
			syncEventCodeFromSelection($row);
			if (data.event_code) {
				$row.find('.sched-row-event-code').val(String(data.event_code));
				applyEventCodeToSelection($row);
			}
			if (data.date) {
				$row.find('.sched-row-date').val(String(data.date));
			}
			if (data.time) {
				$row.find('.sched-row-time').val(String(data.time));
			}
			if (data.max_students) {
				$row.find('.sched-row-max').val(String(data.max_students));
			}
			if (data.time_gap_mins) {
				$row.find('.sched-row-gap').val(String(data.time_gap_mins));
			}
		}

		function applyPrefillRows() {
			var rows = window.overwritePrefillRows || [];
			if (!rows.length) return;

			var $first = $('#sched_rows_body tr.sched-row:first');
			setRowValues($first, rows[0]);

			for (var i = 1; i < rows.length; i++) {
				var $newRow = addScheduleRow();
				setRowValues($newRow, rows[i]);
			}

			updateImpactPreview();
		}

		function applyPresetDefaultsToRows(presetKey, forceValues) {
			var presets = window.overwritePresetMap || {};
			if (!presets[presetKey]) {
				return;
			}

			var maxVal = parseInt(presets[presetKey].max_students, 10);
			var gapVal = parseInt(presets[presetKey].time_gap_mins, 10);
			if (isNaN(maxVal) || maxVal <= 0) {
				maxVal = parseInt(window.overwriteDefaultMax, 10) || 6;
			}
			if (isNaN(gapVal) || gapVal <= 0) {
				gapVal = parseInt(window.overwriteDefaultGap, 10) || 1;
			}

			$('#sched_rows_body tr.sched-row').each(function(){
				var $max = $(this).find('.sched-row-max');
				var $gap = $(this).find('.sched-row-gap');
				$max.attr('data-default', String(maxVal));
				$gap.attr('data-default', String(gapVal));
				if (forceValues) {
					$max.val(String(maxVal));
					$gap.val(String(gapVal));
				}
			});

			$('#overwritePresetHelp').text('Default students/block: ' + maxVal + ' | Default gap: ' + gapVal + ' min');
		}

		function normalizeEventCode(code) {
			return $.trim(String(code || '')).toUpperCase();
		}

		function syncEventCodeFromSelection(row) {
			var $row = $(row);
			var $sel = $row.find('.sched-row-event');
			var selectedCode = $sel.find('option:selected').data('event-code');
			if (typeof selectedCode === 'undefined' || selectedCode === null) {
				selectedCode = '';
			}
			$row.find('.sched-row-event-code').val(selectedCode);
			var selectedText = $.trim($sel.find('option:selected').text());
			if (!selectedText || selectedText === 'Select event') {
				selectedText = '';
			}
			$sel.attr('title', selectedText);
		}

		function applyEventCodeToSelection(row) {
			var $row = $(row);
			var $sel = $row.find('.sched-row-event');
			var $codeInput = $row.find('.sched-row-event-code');
			var typedCode = normalizeEventCode($codeInput.val());
			if (!typedCode) {
				return;
			}

			var matchedValue = '';
			$sel.find('option').each(function(){
				var optionCode = normalizeEventCode($(this).data('event-code'));
				if (!optionCode) return;
				if (optionCode === typedCode) {
					matchedValue = $(this).val();
					return false;
				}
				var typedNum = parseInt(typedCode, 10);
				var optionNum = parseInt(optionCode, 10);
				if (!isNaN(typedNum) && !isNaN(optionNum) && String(optionNum) === String(typedNum)) {
					matchedValue = $(this).val();
					return false;
				}
			});

			if (matchedValue) {
				$sel.val(matchedValue);
				syncEventCodeFromSelection($row);
			}
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

		function updateImpactPreview(){
			var rowEventIds = [];
			$('.sched-row-event').each(function(){
				var v = $(this).val();
				if (v) {
					rowEventIds.push(v);
				}
			});

			var activeDays = 0;
			var totalStudents = 0;
			var totalScheduledRecords = 0;
			var estimatedBlocks = 0;
			var eventCounter = {};

			$('.sched-row').each(function(){
				var eventId = $.trim($(this).find('.sched-row-event').val());
				var dayDate = $.trim($(this).find('.sched-row-date').val());
				var rowMax = parseInt($(this).find('.sched-row-max').val(), 10) || 0;
				if (eventId) {
					eventCounter[eventId] = true;
				}
				if (dayDate) {
					activeDays++;
				}
				if (eventId && rowMax > 0 && window.eventStats && window.eventStats[eventId]) {
					var st = window.eventStats[eventId];
					var effectiveRecords = Math.max(parseInt(st.scheduled_records || 0, 10), parseInt(st.students || 0, 10));
					estimatedBlocks += Math.ceil(effectiveRecords / rowMax);
				}
			});

			var uniqueRows = Object.keys(eventCounter);
			$.each(uniqueRows, function(_, eventId){
				if(window.eventStats && window.eventStats[eventId]){
					totalStudents += parseInt(window.eventStats[eventId].students || 0, 10);
					totalScheduledRecords += parseInt(window.eventStats[eventId].scheduled_records || 0, 10);
				}
			});

			$('#preview_selected_events').text(uniqueRows.length);
			$('#preview_selected_days').text(activeDays);
			$('#preview_students').text(totalStudents);
			$('#preview_records').text(totalScheduledRecords);
			$('#preview_blocks').text(estimatedBlocks);
			updateRowWarnings();
		}

		function parseTimeToMinutes(timeStr) {
			if (!timeStr) return null;
			timeStr = $.trim(timeStr).toUpperCase();
			var ampmMatch = timeStr.match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/);
			if (ampmMatch) {
				var h = parseInt(ampmMatch[1], 10);
				var m = parseInt(ampmMatch[2], 10);
				var ap = ampmMatch[3];
				if (h === 12) h = 0;
				if (ap === 'PM') h += 12;
				return (h * 60) + m;
			}

			var hmsMatch = timeStr.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
			if (hmsMatch) {
				return (parseInt(hmsMatch[1], 10) * 60) + parseInt(hmsMatch[2], 10);
			}
			return null;
		}

		function formatMinutes(mins) {
			if (mins === null || mins < 0) return 'n/a';
			var h = Math.floor(mins / 60) % 24;
			var m = mins % 60;
			var suffix = h >= 12 ? 'PM' : 'AM';
			var hh = h % 12;
			if (hh === 0) hh = 12;
			return hh + ':' + (m < 10 ? '0' + m : m) + ' ' + suffix;
		}

		function updateRowWarnings(){
			var warnings = [];
			var windows = [];

			$('.sched-row').each(function(rowIdx){
				var $row = $(this);
				var eventId = $.trim($row.find('.sched-row-event').val());
				var rowDate = $.trim($row.find('.sched-row-date').val());
				var rowTime = $.trim($row.find('.sched-row-time').val());
				var rowMax = parseInt($row.find('.sched-row-max').val(), 10) || 0;
				var rowGap = parseInt($row.find('.sched-row-gap').val(), 10) || 0;

				if (!eventId || !rowDate || !rowTime || rowMax <= 0) return;

				var st = window.eventStats && window.eventStats[eventId] ? window.eventStats[eventId] : null;
				if (!st) return;

				var startMins = parseTimeToMinutes(rowTime);
				if (startMins === null) {
					warnings.push('Row ' + (rowIdx + 1) + ': invalid start time format.');
					return;
				}

				var sourceRecords = parseInt(st.scheduled_records || 0, 10);
				var participantRows = parseInt(st.students || 0, 10);
				var effectiveRecords = Math.max(sourceRecords, participantRows);
				if (effectiveRecords === 0) {
					warnings.push('Row ' + (rowIdx + 1) + ' (' + st.label + '): no existing records/participants found to schedule.');
					return;
				}

				var blocks = Math.ceil(effectiveRecords / rowMax);
				var duration = parseInt(st.duration_minutes || 0, 10);
				if (duration <= 0) {
					warnings.push('Row ' + (rowIdx + 1) + ' (' + st.label + '): event duration is 0; check setup/round/judging times.');
					return;
				}

				var totalMins = (blocks * duration) + ((blocks - 1) * Math.max(1, rowGap));
				var endMins = startMins + totalMins;
				if (endMins > (24 * 60)) {
					warnings.push('Row ' + (rowIdx + 1) + ' (' + st.label + '): estimated finish ' + formatMinutes(endMins) + ' crosses midnight.');
				}

				windows.push({
					row: rowIdx + 1,
					date: rowDate,
					start: startMins,
					end: endMins,
					label: st.label
				});
			});

			for (var i = 0; i < windows.length; i++) {
				for (var j = i + 1; j < windows.length; j++) {
					if (windows[i].date !== windows[j].date) continue;
					if (windows[i].start < windows[j].end && windows[j].start < windows[i].end) {
						warnings.push(
							'Overlap warning: Row ' + windows[i].row + ' (' + windows[i].label + ') and Row '
							+ windows[j].row + ' (' + windows[j].label + ') overlap on ' + windows[i].date + '.'
						);
					}
				}
			}

			var $box = $('#row_warning_box');
			var $list = $('#row_warning_list');
			$list.empty();
			if (warnings.length === 0) {
				$box.hide();
				return;
			}
			$.each(warnings, function(_, msg){
				$list.append('<li>' + $('<div>').text(msg).html() + '</li>');
			});
			$box.show();
		}

		$(document).on('change', '.sched-row-event, .sched-row-date', updateImpactPreview);
		$(document).on('change', '.sched-row-event', function(){
			syncEventCodeFromSelection($(this).closest('.sched-row'));
		});
		$(document).on('input blur', '.sched-row-event-code', function(){
			applyEventCodeToSelection($(this).closest('.sched-row'));
			updateImpactPreview();
		});
		$(document).on('input', '.sched-row-time, .sched-row-max, .sched-row-gap', updateImpactPreview);
		$(document).on('change', '#overwritePreset', function(){
			var presetKey = String($(this).val() || 'balanced');
			applyPresetDefaultsToRows(presetKey, true);
			updateImpactPreview();
		});
		bindRowTimePickers();
		syncEventCodeFromSelection($('#sched_rows_body tr.sched-row:first'));
		$('#overwritePreset').val(String(window.overwriteSelectedPreset || 'balanced'));
		applyPresetDefaultsToRows(String(window.overwriteSelectedPreset || 'balanced'), false);
		applyPrefillRows();
		updateImpactPreview();

		$('#schedulingWizardForm').on('submit', function(e){
			$('.sched-row').each(function(){
				applyEventCodeToSelection(this);
			});

			var validRows = 0;
			var seenEvent = {};
			var duplicateEvent = false;
			var invalidRows = false;
			$('.sched-row').each(function(){
				var eventId = $.trim($(this).find('.sched-row-event').val());
				var eventCode = $.trim($(this).find('.sched-row-event-code').val());
				var dayDate = $.trim($(this).find('.sched-row-date').val());
				var dayTime = $.trim($(this).find('.sched-row-time').val());
				var rowHasAny = (eventId !== '' || eventCode !== '' || dayDate !== '' || dayTime !== '');
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

			if (validRows === 0) {
				e.preventDefault();
				alert('Please configure at least one event row.');
				return false;
			}

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
	.overwrite-preview .row { margin: 0 0 4px 0 !important; }
	.overwrite-undo .row { margin: 0 0 4px 0 !important; }
	.overwrite-preview .label { font-size: 12px; }
	.overwrite-undo { border: 1px solid #f2dede; border-radius: 4px; background: #fff8f8; padding: 10px 12px; margin-bottom: 12px; }
	.overwrite-row-table th, .overwrite-row-table td { vertical-align: middle !important; }
	.overwrite-row-table-wrap { width: 100%; overflow-x: auto; }
	.overwrite-row-table { table-layout: fixed; min-width: 980px; }
	.overwrite-row-table th:nth-child(1), .overwrite-row-table td:nth-child(1) { width: 35%; }
	.overwrite-row-table th:nth-child(2), .overwrite-row-table td:nth-child(2) { width: 12%; }
	.overwrite-row-table th:nth-child(3), .overwrite-row-table td:nth-child(3) { width: 17%; }
	.overwrite-row-table th:nth-child(4), .overwrite-row-table td:nth-child(4) { width: 12%; }
	.overwrite-row-table th:nth-child(5), .overwrite-row-table td:nth-child(5) { width: 10%; }
	.overwrite-row-table th:nth-child(6), .overwrite-row-table td:nth-child(6) { width: 8%; }
	.overwrite-row-table th:nth-child(7), .overwrite-row-table td:nth-child(7) { width: 6%; }
	.overwrite-warning-box { border: 1px solid #faebcc; border-radius: 4px; background: #fffdf2; padding: 8px 10px; margin-top: 8px; }
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
			<?php if(!empty($overwriteAutoMode)): ?>
			<div class="alert alert-info" style="margin:10px 15px;">
				Automation mode loaded: recommended rows were prefilled from Day Load Dashboard. You can edit event, date, time, students/block, and gaps before applying.
			</div>
			<?php endif; ?>
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

						<div class="overwrite-step-row">
							<div class="overwrite-step"><b>Step 1:</b> Add one row per event</div>
							<div class="overwrite-step"><b>Step 2:</b> Pick date and start time for each row</div>
							<div class="overwrite-step"><b>Step 3:</b> Set students per block and gap, then preview/apply</div>
						</div>

						<div class="overwrite-card" style="margin-bottom:15px;">
							<div class="overwrite-panel-title">Per-Event Scheduler (Recommended)</div>
							<p class="overwrite-help" style="margin-bottom:8px;">Set each event with its own date, start time, students per block, and gap. Example: Spelling OPEN and Spelling U16 on the same date with different times.</p>
							<div class="row" style="margin-bottom:8px;">
								<div class="col-sm-4">
									<label for="overwritePreset" style="margin-bottom:4px; display:block;">Automation Preset</label>
									<select id="overwritePreset" class="form-control input-sm">
										<?php if (!empty($overwritePresetMap)) { ?>
											<?php foreach ($overwritePresetMap as $presetKey => $presetCfg) { ?>
												<option value="<?php echo h($presetKey); ?>" <?php echo (!empty($overwriteSelectedPreset) && $overwriteSelectedPreset === $presetKey) ? 'selected="selected"' : ''; ?>><?php echo h($presetCfg['label']); ?></option>
											<?php } ?>
										<?php } else { ?>
											<option value="conservative">Conservative</option>
											<option value="balanced" selected="selected">Balanced</option>
											<option value="aggressive">Aggressive</option>
										<?php } ?>
									</select>
									<div id="overwritePresetHelp" class="overwrite-help" style="margin-top:4px;"></div>
								</div>
							</div>
							<div class="overwrite-row-table-wrap">
							<table class="table table-bordered table-condensed overwrite-row-table">
								<thead>
									<tr style="background:#eef3f8;">
										<th>Event</th>
										<th>Event Number</th>
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
												<?php if(!empty($finalEventGrouped)): ?>
													<?php foreach($finalEventGrouped as $grp): ?>
														<optgroup label="<?php echo h($grp['label']); ?>">
															<?php foreach($grp['events'] as $evId => $evLabel): ?>
															<?php $evCode = isset($eventStats[$evId]['event_id_number']) ? $eventStats[$evId]['event_id_number'] : ''; ?>
															<option value="<?php echo h($evId); ?>" data-event-code="<?php echo h($evCode); ?>"><?php echo h($evLabel); ?></option>
															<?php endforeach; ?>
														</optgroup>
													<?php endforeach; ?>
												<?php else: ?>
													<?php foreach($finalEventArr as $evId => $evLabel): ?>
													<?php $evCode = isset($eventStats[$evId]['event_id_number']) ? $eventStats[$evId]['event_id_number'] : ''; ?>
													<option value="<?php echo h($evId); ?>" data-event-code="<?php echo h($evCode); ?>"><?php echo h($evLabel); ?></option>
													<?php endforeach; ?>
												<?php endif; ?>
											</select>
										</td>
										<td><input type="text" name="data[Schedulings][sched_rows][0][event_code]" class="form-control sched-row-event-code" placeholder="e.g. 001 or 707"></td>
										<td>
											<select name="data[Schedulings][sched_rows][0][date]" class="form-control sched-row-date">
												<option value="">Select day</option>
												<?php foreach($conventionDays as $cd): ?>
												<option value="<?php echo h($cd['date']); ?>"><?php echo h($cd['display']); ?></option>
												<?php endforeach; ?>
											</select>
										</td>
										<td><input type="text" name="data[Schedulings][sched_rows][0][time]" class="form-control sched-row-time mdtpicker" placeholder="e.g. 01:30 PM"></td>
										<td><input type="number" name="data[Schedulings][sched_rows][0][max_students]" class="form-control sched-row-max" min="1" value="<?php echo (int)(isset($overwriteDefaultMax) ? $overwriteDefaultMax : 6); ?>" data-default="<?php echo (int)(isset($overwriteDefaultMax) ? $overwriteDefaultMax : 6); ?>"></td>
										<td><input type="number" name="data[Schedulings][sched_rows][0][time_gap_mins]" class="form-control sched-row-gap" min="1" value="<?php echo (int)(isset($overwriteDefaultGap) ? $overwriteDefaultGap : 1); ?>" data-default="<?php echo (int)(isset($overwriteDefaultGap) ? $overwriteDefaultGap : 1); ?>"></td>
										<td><a href="#" class="btn btn-xs btn-danger remove_sched_row">Remove</a></td>
									</tr>
								</tbody>
							</table>
							</div>
							<a href="#" class="btn btn-xs btn-default" id="add_sched_row">Add another event row</a>
							<div id="row_warning_box" class="overwrite-warning-box" style="display:none;">
								<div class="overwrite-panel-title" style="margin-bottom:6px;color:#8a6d3b;">Row Warnings</div>
								<ul id="row_warning_list" style="margin:0 0 0 18px;padding:0;"></ul>
							</div>
						</div>

						<div class="overwrite-preview">
							<div class="overwrite-panel-title" style="margin-bottom:6px;">Impact Preview</div>
							<div class="row"><strong>Selected events:</strong> <span id="preview_selected_events">0</span></div>
							<div class="row"><strong>Selected day slots:</strong> <span id="preview_selected_days">0</span></div>
							<div class="row"><strong>Participant rows found:</strong> <span id="preview_students">0</span></div>
							<div class="row"><strong>Existing schedule records to overwrite:</strong> <span id="preview_records">0</span></div>
							<div class="row"><strong>Estimated time blocks:</strong> <span id="preview_blocks">0</span></div>
							<div class="overwrite-help">Use Dry Run Preview to confirm counts before applying changes.</div>
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