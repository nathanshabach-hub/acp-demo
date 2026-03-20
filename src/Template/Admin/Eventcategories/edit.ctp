<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Edit Event Category
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-puzzle-piece"></i> Eventcategories ', ['controller'=>'eventcategories', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Edit Event Category </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($eventcategories, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Event Category Name <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Eventcategories.name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Category Name', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Max Events <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Eventcategories.max_events', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Max Events', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
                    
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->control('Eventcategories.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'eventcategories', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>