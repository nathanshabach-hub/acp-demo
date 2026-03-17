<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>
<script>
	$(document).ready(function(){
		$('.mdtpicker').mdtimepicker(); //Initializes the time picker
	});
</script>

<?php echo $this->Html->script('jquery/ui/jquery.ui.core.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.widget.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.position.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.datepicker.js'); ?>
<?php echo $this->Html->css('themes/ui-lightness/jquery.ui.all.css'); ?>
<script>
    $(function() {
        $( "#start_date" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
			minDate: '0d',
            maxDate: '+2y'
        });
    });
</script>

<?php
if(!empty($schedulings->start_date) && isset($schedulings->start_date))
{
	$schedulings->start_date = date("Y-m-d", strtotime($schedulings->start_date));
}

if(!empty($schedulings->normal_starting_time) && isset($schedulings->normal_starting_time))
{
	$schedulings->normal_starting_time= date("h:i A", strtotime($schedulings->normal_starting_time));
}
if(!empty($schedulings->normal_finish_time) && isset($schedulings->normal_finish_time))
{
	$schedulings->normal_finish_time= date("h:i A", strtotime($schedulings->normal_finish_time));
}
if(!empty($schedulings->lunch_time_start) && isset($schedulings->lunch_time_start))
{
	$schedulings->lunch_time_start= date("h:i A", strtotime($schedulings->lunch_time_start));
}
if(!empty($schedulings->lunch_time_end) && isset($schedulings->lunch_time_end))
{
	$schedulings->lunch_time_end = date("h:i A", strtotime($schedulings->lunch_time_end));
}

// to check if start on different time on first day
$box_starting_different_time_first_day_yes_no = "none";
if($schedulings->starting_different_time_first_day_yes_no)
{
	$box_starting_different_time_first_day_yes_no = "block";
	
	if(!empty($schedulings->different_first_day_start_time) && isset($schedulings->different_first_day_start_time))
	{
		$schedulings->different_first_day_start_time = date("h:i A", strtotime($schedulings->different_first_day_start_time));
	}
	if(!empty($schedulings->different_first_day_end_time) && isset($schedulings->different_first_day_end_time))
	{
		$schedulings->different_first_day_end_time = date("h:i A", strtotime($schedulings->different_first_day_end_time));
	}
}

$box_judging_breaks_yes_no = "none";
if($schedulings->judging_breaks_yes_no)
{
	$box_judging_breaks_yes_no = "block";
	
	if(!empty($schedulings->judging_breaks_morning_break_starting_time) && isset($schedulings->judging_breaks_morning_break_starting_time))
	{
		$schedulings->judging_breaks_morning_break_starting_time= date("h:i A", strtotime($schedulings->judging_breaks_morning_break_starting_time));
	}
	if(!empty($schedulings->judging_breaks_morning_break_finish_time) && isset($schedulings->judging_breaks_morning_break_finish_time))
	{
		$schedulings->judging_breaks_morning_break_finish_time= date("h:i A", strtotime($schedulings->judging_breaks_morning_break_finish_time));
	}
	if(!empty($schedulings->judging_breaks_afternoon_break_start_time) && isset($schedulings->judging_breaks_afternoon_break_start_time))
	{
		$schedulings->judging_breaks_afternoon_break_start_time= date("h:i A", strtotime($schedulings->judging_breaks_afternoon_break_start_time));
	}
	if(!empty($schedulings->judging_breaks_afternoon_break_finish_time) && isset($schedulings->judging_breaks_afternoon_break_finish_time))
	{
		$schedulings->judging_breaks_afternoon_break_finish_time= date("h:i A", strtotime($schedulings->judging_breaks_afternoon_break_finish_time));
	}
}


$box_sports_day_yes_no = "none";
if($schedulings->sports_day_yes_no)
{
	$box_sports_day_yes_no = "block";
	
	if(!empty($schedulings->sports_day_starting_time) && isset($schedulings->sports_day_starting_time))
	{
		$schedulings->sports_day_starting_time= date("h:i A", strtotime($schedulings->sports_day_starting_time));
	}
	if(!empty($schedulings->sports_day_finish_time) && isset($schedulings->sports_day_finish_time))
	{
		$schedulings->sports_day_finish_time= date("h:i A", strtotime($schedulings->sports_day_finish_time));
	}
}

$box_sports_day_having_events_after_sport_yes_no = "none";

if($schedulings->sports_day_having_events_after_sport_yes_no)
{
	$box_sports_day_having_events_after_sport_yes_no = "block";
	
	if(!empty($schedulings->sports_day_other_starting_time) && isset($schedulings->sports_day_other_starting_time))
	{
		$schedulings->sports_day_other_starting_time= date("h:i A", strtotime($schedulings->sports_day_other_starting_time));
	}
	if(!empty($schedulings->sports_day_other_finish_time) && isset($schedulings->sports_day_other_finish_time))
	{
		$schedulings->sports_day_other_finish_time= date("h:i A", strtotime($schedulings->sports_day_other_finish_time));
	}
}
?>

<div class="content-wrapper">
    <section class="content-header">
      <h1>Scheduling Wizard</h1>
        <p style="margin:2px 0 0 0; font-size:13px; color:#777;"><small><?php echo $conventionSD->Conventions['name']; ?> &mdash; <?php echo $conventionSD->season_year; ?></small></p>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Wizard </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($schedulings, ['id'=>'schedulingWizardForm', 'type' => 'file', 'autocomplete' => 'off']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					
					<!-- Convention Days Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label"><h3>Convention Days </h3><span class="require"></span></label>
                      <div class="col-sm-10">
                          &nbsp;
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Start Date <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->input('Schedulings.start_date', ['id'=>'start_date', 'label'=>false, 'type'=>'test',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Start Date']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">First Day <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Schedulings.first_day', $weekDays, ['id' => 'first_day', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Number of Days <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.number_of_days', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required number', 'placeholder'=>'Number of Days']); ?>
                      </div>
                    </div>
					<!-- Convention Days Ends -->
					
					
					
					<!-- Times Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label"><h3>Times </h3><span class="require"></span></label>
                      <div class="col-sm-10">
                          &nbsp;
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Normal Starting Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.normal_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Normal Starting Time']); ?>
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Normal Finish Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.normal_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Normal Finish Time']); ?>
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Lunch Time Start <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.lunch_time_start', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Lunch Time Start']); ?>
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Lunch Time End <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.lunch_time_end', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Lunch Time End']); ?>
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
							<?php echo $this->Form->checkbox('Schedulings.starting_different_time_first_day_yes_no', ['value'=>'1','id'=>'starting_different_time_first_day_yes_no']); ?>
							We are starting at a different time on the first day
                      </div>
                    </div>
					
					<div id="box_starting_different_time_first_day_yes_no" style="display:<?php echo $box_starting_different_time_first_day_yes_no; ?>;">
						<div class="form-group">
						  <label class="col-sm-2 control-label">First Day Start Time <span class="require">*</span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->input('Schedulings.different_first_day_start_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'First Day Start Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">First Day End Time <span class="require">*</span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->input('Schedulings.different_first_day_end_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'First Day End Time']); ?>
						  </div>
						</div>
                    </div>
					<!-- Times Ends -->
					
					
					
					
					<!-- Judging Breaks Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label"><h3>Judging Breaks </h3><span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:30px;">
                          Check the box if you want to schedule breaks for music and platform judges. (They need it but the schedule might be so tight they can't fit one in). We recommend trying to generate the schedule with breaks first and take them out if it can't be done.
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						<?php echo $this->Form->checkbox('Schedulings.judging_breaks_yes_no', ['value'=>'1','id'=>'judging_breaks_yes_no']); ?> 						
						Yes we are having judging breaks
                      </div>
                    </div>
					
					<div id="box_judging_breaks_yes_no" style="display:<?php echo $box_judging_breaks_yes_no; ?>;">
					
						<div class="form-group">
						  <label class="col-sm-2 control-label">Morning Break Starting Time<span class="require">*</span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.judging_breaks_morning_break_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Morning Break Starting Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Morning Break Finish Time<span class="require">*</span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.judging_breaks_morning_break_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Morning Break Finish Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Afternoon Break Start Time<span class="require">*</span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.judging_breaks_afternoon_break_start_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Afternoon Break Start Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Afternoon Break Finish Time<span class="require">*</span></label>
						  <div class="col-sm-10">
							<?php echo $this->Form->input('Schedulings.judging_breaks_afternoon_break_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Afternoon Break Finish Time']); ?>
						  </div>
						</div>
					
					</div>
					<!-- Judging Breaks Ends -->
					
					
					
					
					
					<!-- Sports Day Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label"><h3>Sports Day </h3><span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:30px;">
                          Check the box if you are having sports day (for track and field). And then choose the day from the list. If you're only having half the day for sport and you want other events in the afternoon then check the box and fill the times for other event.
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						<?php echo $this->Form->checkbox('Schedulings.sports_day_yes_no', ['value'=>'1','id'=>'sports_day_yes_no']); ?> 						
						Yes we are having a Sports Day
                      </div>
                    </div>
					
					<div id="box_sports_day_yes_no" style="display:<?php echo $box_sports_day_yes_no; ?>;">
					
						<div class="form-group">
						  <label class="col-sm-2 control-label">Sports Day <span class="require"></span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->select('Schedulings.sports_day', $weekDays, ['id' => 'sports_day', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Starting Time <span class="require"></span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.sports_day_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Starting Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Finish Time <span class="require"></span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.sports_day_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Finish Time']); ?>
						  </div>
						</div>
					
                    </div>
					
					
                    <div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						<?php echo $this->Form->checkbox('Schedulings.sports_day_having_events_after_sport_yes_no', ['value'=>'1','id'=>'sports_day_having_events_after_sport_yes_no']); ?> 
						We are having more events after sport
                      </div>
                    </div>
					
					<div id="box_sports_day_having_events_after_sport_yes_no" style="display:<?php echo $box_sports_day_having_events_after_sport_yes_no; ?>;">
					
						<div class="form-group">
						  <label class="col-sm-2 control-label">Starting Time <span class="require"></span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.sports_day_other_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Starting Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Finish Time <span class="require"></span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->input('Schedulings.sports_day_other_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Finish Time']); ?>
						  </div>
						</div>
					
					</div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10" style="color:red;">
							Don't forget to allow travel time between the sports venue and the convention site.
                      </div>
                    </div>
					<!-- Sports Day Ends -->
					
					
					
					<!-- Settling Time Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label"><h3>Student Travel Time </h3><span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:30px;">
                          Minimum number of minutes a student needs between consecutive events. This gives students time to get from one event/room to the next (e.g. collect instruments). Default is 15 minutes.
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Settling Time (minutes) <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.settling_time_minutes', ['label'=>false, 'type'=>'number', 'min'=>'0', 'max'=>'120', 'div'=>false, 'class'=>'form-control required number', 'placeholder'=>'Settling Time in Minutes (default 15)']); ?>
                      </div>
                    </div>
					<!-- Settling Time Ends -->
					
					
					
					<!-- Elimination Rounds Buffer Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label"><h3>Elimination Events </h3><span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:30px;">
                          For elimination events (Chess, Basketball, etc.), how many rounds should be scheduled before allowing conflicts with other events for the same student? This assumes fewer and fewer students progress each round. Default is 3.
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Preliminary Rounds Before Conflicts Allowed <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.elimination_rounds_buffer', ['label'=>false, 'type'=>'number', 'min'=>'1', 'max'=>'10', 'div'=>false, 'class'=>'form-control required number', 'placeholder'=>'Elimination Rounds Buffer (default 3)']); ?>
                      </div>
                    </div>
					<!-- Elimination Rounds Buffer Ends -->
					
					
					
					<!-- Schedule Release Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label"><h3>Schedule Release </h3><span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:30px;">
                          Set a date/time when the schedule will be released and visible to students and schools. Leave blank to keep hidden.
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Release Date &amp; Time </label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.schedule_release_date', ['label'=>false, 'type'=>'text', 'div'=>false, 'class'=>'form-control', 'placeholder'=>'YYYY-MM-DD HH:MM (leave blank to keep hidden)', 'id'=>'schedule_release_date']); ?>
                      </div>
                    </div>
					<!-- Schedule Release Ends -->
					
					
					
					
					
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Schedulings.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action' => 'precheck', $convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>

<script type="text/javascript">
    $(document).ready(function() {
		
		$("#starting_different_time_first_day_yes_no").click(function() {
			
			if($("#starting_different_time_first_day_yes_no").prop('checked') == true)
			{
				$("#box_starting_different_time_first_day_yes_no").css("display", "block");
			}
			else
			{
				$("#box_starting_different_time_first_day_yes_no").css("display", "none");
			}
		});
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
		
		$("#judging_breaks_yes_no").click(function() {
			
			if($("#judging_breaks_yes_no").prop('checked') == true)
			{
				$("#box_judging_breaks_yes_no").css("display", "block");
			}
			else
			{
				$("#box_judging_breaks_yes_no").css("display", "none");
			}
		});
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
		
		$("#sports_day_yes_no").click(function() {
			
			if($("#sports_day_yes_no").prop('checked') == true)
			{
				$("#box_sports_day_yes_no").css("display", "block");
			}
			else
			{
				$("#box_sports_day_yes_no").css("display", "none");
			}
		});
    });
</script>

<script type="text/javascript">
    $(document).ready(function() {
		
		$("#sports_day_having_events_after_sport_yes_no").click(function() {
			
			if($("#sports_day_having_events_after_sport_yes_no").prop('checked') == true)
			{
				$("#box_sports_day_having_events_after_sport_yes_no").css("display", "block");
			}
			else
			{
				$("#box_sports_day_having_events_after_sport_yes_no").css("display", "none");
			}
		});
    });
</script>

  