<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>

<?php echo $this->Html->script('jquery/ui/jquery.ui.core.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.widget.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.position.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.datepicker.js'); ?>
<?php echo $this->Html->css('front/themes/ui-lightness/jquery.ui.all.css'); ?>
<script>
    $(function() {
        $( "#date_of_birth" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
            yearRange: '-100y:c+nn',
            maxDate: '-1d'
        });
    });
	
</script>
<style>
    #ui-datepicker-div button.ui-datepicker-current {display: none;}
</style>

<?php
if(empty($users->user_timezone))
	$users->user_timezone = 'America/Los_Angeles';
?>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Add School/Homeschool
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bank"></i> Users ', ['controller'=>'users', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Add School/Homeschool </li>
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
					
					<!--
					<div class="form-group">
                      <label class="col-sm-2 control-label">Timezone <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Users.user_timezone', $timezonesDD, ['id' => 'user_timezone', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple required', 'empty' => 'Timezone', 'autocomplete' => 'off']); ?>
                      </div>
					  <script>
							$(function() {
								$("#user_timezone").select2();
							});
						</script>
                    </div>
					-->
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Customer Code <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.customer_code', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Customer Code']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">School/HSSP Name <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.first_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'School/HSSP Name']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Main Contact Person <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.middle_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Main Contact Person']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Telephone 1 <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.phone', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Telephone 1']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Telephone 2 <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.phone2', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Telephone 2']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Email Address <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.email_address', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required email', 'placeholder'=>'Email Address']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Bill To Street <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.bill_to_street', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Bill To Street']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Bill To Block <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.bill_to_block', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Bill To Block']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Bill To City <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.bill_to_city', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Bill To City']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Bill To Zip <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.bill_to_zip', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Bill To Zip']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Bill To Country <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.bill_to_country', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Bill To Country']); ?>
                      </div>
                    </div>
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Form->button('Reset', ['type'=>'reset', 'class' => 'btn btn-default canlcel_le', 'div'=>false]); ?>
						<?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>
