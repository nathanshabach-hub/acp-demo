<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Edit Supervisors
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-user-secret"></i> Supervisors ', ['controller'=>'users', 'action'=>'teachers'], ['escape'=>false]);?></li>
          <li class="active">Edit Supervisors </li>
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
                      <label class="col-sm-2 control-label">Choose School <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Users.school_id', $schoolsDD, ['id' => 'school_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'readonly','disabled', 'value' => $users->school_id]); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Title <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Users.title', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Title']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">First Name <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Users.first_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'First Name']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Surname <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Users.last_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Surname']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Email Address <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Users.email_address_old', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required email', 'placeholder'=>'Email Address', 'value'=>$users->email_address,'readonly']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Gender <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Users.gender', $genderDD, ['id' => 'gender', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Judge? <span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:7px;">
                          <b><?php echo $yesNoDD[$users->is_judge]; ?></b>
                      </div>
                    </div>
					
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Password <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Users.password', ['label'=>false, 'type'=>'password',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Password']); ?>
                          <em class="bugdm">* Note: If You want to change User's password, only then fill password below otherwise leave it blank.</em>
                      </div>
                    </div>
					
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Confirm Password <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Users.confirm_password', ['label'=>false, 'type'=>'password',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Confirm Password', 'equalTo'=>'#users-password']); ?>
                      </div>
                    </div>
					
					
                   
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Users.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'teachers'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>