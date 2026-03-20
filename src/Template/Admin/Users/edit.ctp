<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Edit School/Homeschool
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bank"></i> School/Homeschool ', ['controller'=>'users', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Edit School/Homeschool </li>
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
                      <label class="col-sm-2 control-label">Customer Code <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.customer_code_no_save', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Customer Code', 'value'=>$users->customer_code,'readonly']); ?>
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
                      <label class="col-sm-2 control-label">Email Address <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.email_address', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required email', 'placeholder'=>'Email Address']); ?>
						  
                          <?php echo $this->Form->control('Users.email_address_old', ['label'=>false, 'type'=>'hidden',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Email Address', 'value'=>$userD->email_address]); ?>
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
					
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Password <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.password', ['label'=>false, 'type'=>'password',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Password']); ?>
                          <em class="bugdm">* Note: If You want to change User's password, only then fill password below otherwise leave it blank.</em>
                      </div>
                    </div>
					
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Confirm Password <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.confirm_password', ['label'=>false, 'type'=>'password',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Confirm Password', 'equalTo'=>'#users-password']); ?>
                      </div>
                    </div>
					
					
                   
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->control('Users.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>