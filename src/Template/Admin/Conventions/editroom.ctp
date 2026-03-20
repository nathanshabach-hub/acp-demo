<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Edit Room -  <?php echo $conventionD->name; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-registered"></i> Rooms ', ['controller'=>'conventions', 'action'=>'rooms',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Edit Room -  <?php echo $conventionD->name; ?> </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($conventionrooms, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Room Name <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->control('Conventionrooms.room_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Room Name', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Short Description <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->control('Conventionrooms.short_description', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Short Description', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
						<?php echo $this->Html->link('Cancel', ['controller'=>'conventions', 'action' => 'rooms',$convention_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                        <?php //echo $this->Form->button('Reset', ['type'=>'reset', 'class' => 'btn btn-default canlcel_le', 'div'=>false]); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>