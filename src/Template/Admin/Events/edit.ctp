<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<?php
if(!empty($events->book_ids))
{
	$events->book_ids = explode(",",$events->book_ids);
}
?>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Edit Event
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Events ', ['controller'=>'events', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Edit Event </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($events, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Event ID Number <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Events.event_id_number_no_change', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Event Number', 'autocomplete'=>'off', 'value'=>$events->event_id_number, 'readonly']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Event Name <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Events.event_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Event Name', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Division <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.division_id', $divisionDD, ['id' => 'division_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
							<script>
								$(document).ready(function() {
									$('#division_id').select2({width: '100%'});
								});
							</script>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Upload Type <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Events.upload_type', $eventUploadTypeDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Report <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.report', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Information Required <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.context_box', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Score Sheet <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.score_sheet', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Additional Documents <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.additional_documents', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Event Type <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php //echo $this->Form->select('Events.convention_type', $eventTypeDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
						  <?php echo $this->Form->select('Events.event_type', $eventTypeDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Group <span class="require"></span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Events.event_grp_name', $eventGroupNameDD, ['label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Gender <span class="require"></span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Events.event_gender', $eventGenderDD, ['label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Group Event? <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.group_event_yes_no', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Min No. <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Events.min_no', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Min No.', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Max No. <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Events.max_no', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Max No.', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Team Event? <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.team_event', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Set Up Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Events.setup_time', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Set Up Time', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Round Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Events.round_time', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Round Time', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Judging Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Events.judging_time', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Judging Time', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Event Kind ID <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.event_kind_id', $eventKindID, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Has To Be Consecutive? <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.has_to_be_consecutive', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Competitors Per Round <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Events.competitors_per_round', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Competitors Per Round', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Needs Schedule <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.needs_schedule', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Discount Allowed <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.discount_allowed', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Book <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.book_ids', $bookDD, ['id' => 'book_ids', 'label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'multiple' => 'multiple']); ?>
							<script>
								$(document).ready(function() {
									$('#book_ids').select2({width: '100%'});
								});
							</script>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Certificate Print? <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.certificate_print', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Auto Submission <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Events.auto_submission', $yesNoDD, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Judging Type <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Events.event_judging_type', $eventJudgeType, ['label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
                    
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Events.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'events', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>