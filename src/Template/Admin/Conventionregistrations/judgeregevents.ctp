<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>
<?php
if(!empty($CRDetails->judges_event_ids))
{
	$alreadyChooseEvents = explode(",",$CRDetails->judges_event_ids);
}
else
{
	$alreadyChooseEvents = array();
}
?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Judge Events :: <?php echo $CRDetails->Users['first_name'].' '.$CRDetails->Users['last_name']; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-newspaper-o"></i> Convention Registrations ', ['controller'=>'conventionregistrations', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Judge Events </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($users, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Convention <span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:6px;">
                          <?php echo $CRDetails->Conventions['name']; ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Events <span class="require"></span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Conventionregistrations.judges_event_ids', $eventNameIDDD, ['id' => 'judges_event_ids', 'label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'multiple' => 'multiple', 'value' => $alreadyChooseEvents]); ?>
						  <script>
							$(document).ready(function () {
								$('#judges_event_ids').select2();
							});
						</script>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Send Email Notification <span class="require"></span></label>
                      <div class="col-sm-10" style="margin-top:7px;">
						  <input class="" type="checkbox" name="send_email_notification" id="send_email_notification" value="1" />
                      </div>
                    </div>
					
                   
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Users.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'conventionregistrations', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>