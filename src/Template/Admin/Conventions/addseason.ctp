<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<?php echo $this->Html->script('jquery/ui/jquery.ui.core.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.widget.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.position.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.datepicker.js'); ?>
<?php echo $this->Html->css('themes/ui-lightness/jquery.ui.all.css'); ?>
<script>
    $(function() {
        $( "#registration_start_date" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
            yearRange: '-1y:+1y',
            maxDate: '+2y'
        });
    });
</script>
<script>
    $(function() {
        $( "#registration_end_date" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
            yearRange: '-1y:+1y',
            maxDate: '+2y'
        });
    });
</script>
<style>
    #ui-datepicker-div button.ui-datepicker-current {display: none;}
</style>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Add Season -  <?php echo $conventionD->name; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$slug], ['escape'=>false]);?></li>
          <li class="active">Add Season -  <?php echo $conventionD->name; ?> </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($conventionseasons, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose Season Year <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Conventionseasons.season_id', $seasonsDD, ['id' => 'season_id','label' => false, 'div' => false, 'class' => 'form-control required', 'empty' => 'Choose Season Year', 'autocomplete' => 'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Registration Start Date <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Conventionseasons.registration_start_date', ['id'=>'registration_start_date', 'label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Registration Start Date', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Registration End Date <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Conventionseasons.registration_end_date', ['id'=>'registration_end_date', 'label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Registration End Date', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Student registration (<?php echo CURR; ?>) <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Conventionseasons.student_registration_fees', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required']); ?>
						  <!--<em class="bugdm">* Contact us form data will receive on this email address.</em>-->
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Non-competitor registration (<?php echo CURR; ?>) <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Conventionseasons.non_competitor_registration_fees', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required']); ?>
						  <!--<em class="bugdm">* Contact us form data will receive on this email address.</em>-->
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Non-affiliate registration (<?php echo CURR; ?>) <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Conventionseasons.non_affiliate_registration_fees', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required']); ?>
						  <!--<em class="bugdm">* Contact us form data will receive on this email address.</em>-->
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Supervisor registration (<?php echo CURR; ?>) <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Conventionseasons.supervisor_registration_fees', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required']); ?>
						  <!--<em class="bugdm">* Contact us form data will receive on this email address.</em>-->
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Min. Event Students <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Conventionseasons.min_events_student', ['label'=>false, 'min'=>1, 'max'=>100, 'type'=>'number', 'div'=>false, 'class'=>'form-control required']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Max. Event Students <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Conventionseasons.max_events_student', ['label'=>false, 'min'=>1, 'max'=>100, 'type'=>'number', 'div'=>false, 'class'=>'form-control required']); ?>
                      </div>
                    </div>
					
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
						<?php echo $this->Html->link('Cancel', ['controller'=>'conventions', 'action' => 'seasons',$slug], ['class'=>'btn btn-default canlcel_le']); ?>
                        <?php //echo $this->Form->button('Reset', ['type'=>'reset', 'class' => 'btn btn-default canlcel_le', 'div'=>false]); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>