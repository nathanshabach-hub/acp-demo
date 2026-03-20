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
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$conventionSD->Conventions['slug']], ['escape'=>false]);?></li>
					<li class="active">Add Room Events</li>
      </ol>
			<div style="clear: both;"></div>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			<div style="padding: 0 15px 10px 15px; text-align: right;">
				<?php if(count($pendingEventsToRoomsList)>0) { ?>
				<button type="button" class="btn btn-success" data-toggle="modal" data-target="#myModalPendingEvents">Pending Events (<?php echo count($pendingEventsToRoomsList); ?>)</button>
				<?php } ?>
			</div>
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
						  <?php echo $this->Form->select('Conventionseasonroomevents.event_ids', $convSeasEventDD, ['id' => 'event_ids', 'multiple' =>'multiple', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple required', 'autocomplete' => 'off', 'value' => []]); ?>
							<script>
							$(document).ready(function() {
								$('#event_ids').select2();
							});
						</script>
                      </div>
                    </div>
					
					<div class="form-group" id="spb-container" style="display:none;">
                      <label class="col-sm-2 control-label">Students Per Block</label>
                      <div class="col-sm-10">
						  <p class="help-block" style="margin-bottom:10px;">Set how many students should be scheduled in each block for the selected events. Leave blank to use the default.</p>
						  <div id="spb-inputs"></div>
                      </div>
                    </div>
					
					<script>
					$(document).ready(function() {
						var eventLabels = <?php echo json_encode($convSeasEventDD); ?>;
						
						function updateSpbInputs() {
							var selected = $('#event_ids').val() || [];
							var container = $('#spb-inputs');
							container.empty();
							
							if (selected.length > 0) {
								$('#spb-container').show();
								$.each(selected, function(i, eventId) {
									var label = eventLabels[eventId] || 'Event #' + eventId;
									var existing = '';
									container.append(
										'<div class="input-group" style="margin-bottom:6px; max-width:400px;">' +
										'<span class="input-group-addon" style="min-width:220px; text-align:left;">' + label + '</span>' +
										'<input type="number" name="students_per_block[' + eventId + ']" class="form-control" placeholder="Default" min="1" max="50" value="' + existing + '">' +
										'</div>'
									);
								});
							} else {
								$('#spb-container').hide();
							}
						}
						
						$('#event_ids').on('change', updateSpbInputs);
						updateSpbInputs();
					});
					</script>
					
					
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

<?php if(count($pendingEventsToRoomsList)>0) { ?>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<div class="modal fade" id="myModalPendingEvents" role="dialog">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Pending Events <?php echo count($pendingEventsToRoomsList); ?></h4>
			</div>
			<div class="modal-body">
				<?php
				$cntrPE = 1;
				foreach($pendingEventsToRoomsList as $pendingev) {
				?>
					<p><?php echo $cntrPE.'.&nbsp;&nbsp;'.$pendingev; ?></p>
				<?php
				$cntrPE++;
				}
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php } ?>