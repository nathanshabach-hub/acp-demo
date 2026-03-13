<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>
<script>
	$(document).ready(function(){
		$('.mdtpicker').mdtimepicker(); //Initializes the time picker

		$('#select_all_events').on('click', function(e){
			e.preventDefault();
			$('input[name="data[Schedulings][event_ids][]"]').prop('checked', true);
		});

		$('#clear_all_events').on('click', function(e){
			e.preventDefault();
			$('input[name="data[Schedulings][event_ids][]"]').prop('checked', false);
		});

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

			if(!confirm('This will overwrite existing timings for selected events. Continue?')){
				e.preventDefault();
				return false;
			}
		});
	});
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
						<?php echo $this->Form->button('Apply Overwrite Timings', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action' => 'precheck', $convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>