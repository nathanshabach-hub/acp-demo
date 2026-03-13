<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Add Division
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-puzzle-piece"></i> Divisions ', ['controller'=>'divisions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Add Division </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($divisions, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Category <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Divisions.eventcategory_id', $eventCatDD, ['id' => 'eventcategory_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
							<script>
								$(document).ready(function() {
									$('#eventcategory_id').select2();
								});
							</script>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Division Name <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Divisions.name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Division Name', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Max Events <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Divisions.max_events', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Max Events', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
						<?php echo $this->Html->link('Cancel', ['controller'=>'divisions', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                        <?php //echo $this->Form->button('Reset', ['type'=>'reset', 'class' => 'btn btn-default canlcel_le', 'div'=>false]); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>