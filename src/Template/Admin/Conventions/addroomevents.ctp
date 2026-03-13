<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Add Room Events :: Convention - <?php echo $conventionSD->Conventions['name']; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convSeasonEventD->Conventions['slug']], ['escape'=>false]);?></li>
          <li class="active">Add Room Events :: Convention - <?php echo $conventionSD->Conventions['name']; ?></li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($conventionseasonroomevents, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose Room <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Conventionseasonroomevents.room_id', $convRooms, ['id' => 'room_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' =>'Choose Room']); ?>
							<script>
							$(document).ready(function() {
								$('#room_id').select2();
							});
						</script>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose Event(s) <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Conventionseasonroomevents.event_ids', $convSeasEventDD, ['id' => 'event_ids', 'multiple' =>'multiple', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple required', 'autocomplete' => 'off', 'value' =>$convRoomIDS]); ?>
							<script>
							$(document).ready(function() {
								$('#event_ids').select2();
							});
						</script>
                      </div>
                    </div>
					
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
						<?php echo $this->Html->link('Cancel', ['controller'=>'conventions', 'action' => 'roomevents',$conventionSD->slug], ['class'=>'btn btn-default canlcel_le']); ?>
                        <?php //echo $this->Form->button('Reset', ['type'=>'reset', 'class' => 'btn btn-default canlcel_le', 'div'=>false]); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>