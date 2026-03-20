<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>
<?php
use Cake\ORM\TableRegistry;
$this->_crstudenteventsTable = TableRegistry::getTableLocator()->get('Crstudentevents');
?>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Edit Room Events :: Convention - <?php echo $conventionSD->Conventions['name']; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convSeasonEventD->Conventions['slug']], ['escape'=>false]);?></li>
          <li class="active">Edit Room Events :: Convention - <?php echo $conventionSRoomD->Conventionrooms['room_name']; ?></li>
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
                      <label class="col-sm-2 control-label">Room <span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:8px;">
						  <?php echo $conventionSRoomD->Conventionrooms['room_name']; ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Current Events</label>
                      <div class="col-sm-10" style="padding-top:8px;">
						<table>
							<?php
							foreach($roomEventsL as $datarecevent)
							{
							?>
							<tr>
								<td>
								<?php
								$regCount = $this->_crstudenteventsTable->find()->where(['Crstudentevents.conventionseason_id' => $conventionSD->id, 'Crstudentevents.event_id' => $datarecevent->id])->count();
								$spbDisplay = (isset($existingSpb[$datarecevent->id]) && (int)$existingSpb[$datarecevent->id] > 0) ? (int)$existingSpb[$datarecevent->id] : null;
								?>
								<?php echo $datarecevent->event_name; ?> (<?php echo $datarecevent->event_id_number; ?>) <span class="label label-default"><?php echo $regCount; ?> students</span><?php if ($spbDisplay): ?> <span class="label label-info" title="Students per block"><?php echo $spbDisplay; ?>/block</span><?php endif; ?>
								</td>
								<td>
								<?php
								echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventions', 'action' => 'deleteeventfromroom',$slug,$slug_convention_season,$datarecevent->id], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to remove this event from this room ?']);
								?>
								</td>
							</tr>
							<?php
							}
							?>
						</table>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Students Per Block</label>
                      <div class="col-sm-10" style="padding-top:8px;">
						  <p class="help-block" style="margin-bottom:10px;">Set how many students should be scheduled in each block. Leave blank to use the default.</p>
						  <?php
						  foreach($roomEventsL as $datarecevent)
						  {
							  $spbVal = isset($existingSpb[$datarecevent->id]) ? (int)$existingSpb[$datarecevent->id] : '';
						  ?>
						  <div class="input-group" style="margin-bottom:6px; max-width:400px;">
							  <?php
							  $spbRegCount = $this->_crstudenteventsTable->find()->where(['Crstudentevents.conventionseason_id' => $conventionSD->id, 'Crstudentevents.event_id' => $datarecevent->id])->count();
							  ?>
							  <span class="input-group-addon" style="min-width:280px; text-align:left;"><?php echo $datarecevent->event_name; ?> (<?php echo $datarecevent->event_id_number; ?>) <span class="label label-default"><?php echo $spbRegCount; ?> students</span></span>
							  <input type="number" name="students_per_block[<?php echo $datarecevent->id; ?>]" class="form-control" placeholder="Default" min="1" max="50" value="<?php echo $spbVal; ?>">
						  </div>
						  <?php
						  }
						  ?>
                      </div>
                    </div>
					
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose New Event(s)</label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Conventionseasonroomevents.event_ids', $convSeasEventDD, ['id' => 'event_ids', 'multiple' =>'multiple', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple', 'autocomplete' => 'off', 'value' =>$convRoomIDS]); ?>
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