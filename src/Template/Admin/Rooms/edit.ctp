<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>Edit Room</h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span>', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]); ?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-puzzle-piece"></i> Rooms', ['controller'=>'rooms', 'action'=>'index'], ['escape'=>false]); ?></li>
          <li class="active">Edit Room</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($rooms, ['id'=>'adminForm']); ?>
                <div class="form-horizontal">
                    <div class="box-body">

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Room Name <span class="require">*</span></label>
                            <div class="col-sm-10">
                                <?php echo $this->Form->control('Rooms.name', ['label'=>false, 'type'=>'text', 'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Enter room name', 'autocomplete'=>'off']); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Description</label>
                            <div class="col-sm-10">
                                <?php echo $this->Form->control('Rooms.description', ['label'=>false, 'type'=>'textarea', 'div'=>false, 'class'=>'form-control', 'placeholder'=>'Enter room description']); ?>
                            </div>
                        </div>

                        <div class="box-footer">
                            <label class="col-sm-2 control-label">&nbsp;</label>
                            <?php echo $this->Form->button('Save', ['type'=>'submit', 'class'=>'btn btn-info', 'div'=>false]); ?>
                            <?php echo $this->Html->link('Cancel', ['controller'=>'rooms', 'action'=>'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                        </div>
                    </div>
                </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </section>
</div>
