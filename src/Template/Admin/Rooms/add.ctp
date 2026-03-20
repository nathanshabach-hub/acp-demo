<div class="content-wrapper">
    <section class="content-header">
      <h1>Add Rooms</h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span>', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]); ?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-puzzle-piece"></i> Rooms', ['controller'=>'rooms', 'action'=>'index'], ['escape'=>false]); ?></li>
          <li class="active">Add Rooms</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <form id="adminForm" method="post" action="<?php echo $this->Url->build(['controller'=>'rooms','action'=>'add']); ?>">
                <div class="form-horizontal">
                    <div class="box-body">

                        <div id="room-rows">
                            <div class="room-row form-group" style="margin-bottom:6px;">
                                <label class="col-sm-2 control-label">Room Name <span class="require">*</span></label>
                                <div class="col-sm-8">
                                    <input type="text" name="room_names[]" class="form-control" placeholder="Enter room name" autocomplete="off" required>
                                </div>
                                <div class="col-sm-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-row" style="display:none;"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <button type="button" id="add-row" class="btn btn-default btn-sm"><i class="fa fa-plus"></i> Add Another Room</button>
                            </div>
                        </div>

                        <div class="box-footer">
                            <label class="col-sm-2 control-label">&nbsp;</label>
                            <button type="submit" class="btn btn-info">Save All</button>
                            <?php echo $this->Html->link('Cancel', ['controller'=>'rooms', 'action'=>'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#add-row').on('click', function() {
        var row = '<div class="room-row form-group" style="margin-bottom:6px;">'
            + '<label class="col-sm-2 control-label">Room Name <span class="require">*</span></label>'
            + '<div class="col-sm-8"><input type="text" name="room_names[]" class="form-control" placeholder="Enter room name" autocomplete="off" required></div>'
            + '<div class="col-sm-2"><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-minus"></i></button></div>'
            + '</div>';
        $('#room-rows').append(row);
        updateRemoveButtons();
    });

    $(document).on('click', '.remove-row', function() {
        $(this).closest('.room-row').remove();
        updateRemoveButtons();
    });

    function updateRemoveButtons() {
        var rows = $('.room-row');
        rows.find('.remove-row').toggle(rows.length > 1);
    }
});
</script>
