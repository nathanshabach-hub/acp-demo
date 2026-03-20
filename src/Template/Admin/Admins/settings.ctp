<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Settings 
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
          <li><a href="javascript:void(0);"><i class="fa fa-cogs"></i> Configuration</a></li>
          <li class="active">Settings </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create(NULL, ['id'=>'adminForm', 'autocomplete'=>'off']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">PayPal Email <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Settings.paypal_email', ['label'=>false, 'type'=>'text', 'div'=>false, 'class'=>'form-control required email', 'value'=>$settingsInfo->paypal_email]); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Accounts Team Email <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Settings.accounts_team_email', ['label'=>false, 'type'=>'text', 'div'=>false, 'class'=>'form-control required email', 'value'=>$settingsInfo->accounts_team_email]); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Full Registration Price (<?php echo CURR; ?>) <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Settings.full_registration_price', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'value'=>$settingsInfo->full_registration_price]); ?>
						  <!--<em class="bugdm">* Contact us form data will receive on this email address.</em>-->
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Scripture only registration (<?php echo CURR; ?>) <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Settings.scripture_only_registration_price', ['label'=>false, 'type'=>'text', 'div'=>false, 'class'=>'form-control required', 'value'=>$settingsInfo->scripture_only_registration_price]); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Scripture Trophy Discount (%) <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Settings.scripture_trophy_discount', ['label'=>false, 'min'=>0, 'max'=>100, 'type'=>'text', 'div'=>false, 'class'=>'form-control required', 'value'=>$settingsInfo->scripture_trophy_discount]); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Min. Event Students <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Settings.min_events_student', ['label'=>false, 'min'=>1, 'max'=>100, 'type'=>'number', 'div'=>false, 'class'=>'form-control required', 'value'=>$settingsInfo->min_events_student]); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Max. Event Students <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Settings.max_events_student', ['label'=>false, 'min'=>1, 'max'=>100, 'type'=>'number', 'div'=>false, 'class'=>'form-control required', 'value'=>$settingsInfo->max_events_student]); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Judges Low Score Pin <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Settings.judges_low_score_saving_pin', ['label'=>false, 'type'=>'text', 'div'=>false, 'class'=>'form-control required', 'value'=>$settingsInfo->judges_low_score_saving_pin]); ?>
                      </div>
                    </div>
					
					<!--
					<div class="form-group">
                      <label class="col-sm-2 control-label">Tax (%) <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Settings.tax_percent', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'value'=>$settingsInfo->tax_percent]); ?>
                      </div>
                    </div>
					-->
					
					

                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Submit', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['action' => 'dashboard'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>
