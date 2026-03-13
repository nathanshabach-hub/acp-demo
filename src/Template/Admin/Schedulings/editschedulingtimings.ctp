<script type="text/javascript">
    $(document).ready(function() {
        $("#editTimingsForm").validate();
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

if(!empty($schedulingtimingsD->start_time) && isset($schedulingtimingsD->start_time))
{
	$currentStartTime= date("h:i A", strtotime($schedulingtimingsD->start_time));
}

if(!empty($schedulingtimingsD->finish_time) && isset($schedulingtimingsD->finish_time))
{
	$currentFinishTime= date("h:i A", strtotime($schedulingtimingsD->finish_time));
}

?>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Edit Scheduling Timings - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
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
                      <label class="col-sm-2 control-label"><h3>Current Details </h3><span class="require"></span></label>
                      <div class="col-sm-10">
							&nbsp;
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">#ID</label>
                      <div class="col-sm-10" style="padding-top:6px;">
							<?php echo $schedulingtimingsD->id; ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Room</label>
                      <div class="col-sm-10" style="padding-top:6px;">
							<?php echo $schedulingtimingsD->Conventionrooms['room_name']; ?> (#<?php echo $schedulingtimingsD->room_id; ?>)
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Day</label>
                      <div class="col-sm-10" style="padding-top:6px;">
							<?php echo $schedulingtimingsD->day; ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Start Time</label>
                      <div class="col-sm-10" style="padding-top:6px;">
							<?php echo $schedulingtimingsD->start_time!=NULL ? date("H:i A",strtotime($schedulingtimingsD->start_time)) : ''; ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Finish Time</label>
                      <div class="col-sm-10" style="padding-top:6px;">
							<?php echo $schedulingtimingsD->finish_time!=NULL ? date("H:i A",strtotime($schedulingtimingsD->finish_time)) : ''; ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Event</label>
                      <div class="col-sm-10" style="padding-top:6px;">
							<?php echo $schedulingtimingsD->Events['event_name']; ?> (<?php echo $schedulingtimingsD->Events['event_id_number']; ?>)
                      </div>
                    </div>
					
					
					
					
					
					
					
					<!-- Times Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label"><h3>New Timings </h3><span class="require"></span></label>
                      <div class="col-sm-10">
                          &nbsp;
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">New Start Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulingtimings.new_start_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'New Start Time', 'value'=>$currentStartTime]); ?>
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">New Finish Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulingtimings.new_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'New Start Time', 'value'=>$currentFinishTime]); ?>
                      </div>
                    </div>
					
					
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action' => 'schedulecategory', $convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>

  


  