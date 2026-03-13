<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Edit Student
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-group"></i> Students ', ['controller'=>'users', 'action'=>'students'], ['escape'=>false]);?></li>
          <li class="active">Edit Student </li>
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
                      <label class="col-sm-2 control-label">First Name <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Users.first_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'First Name']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Middle Name <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Users.middle_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Middle Name']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Surname <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Users.last_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Surname']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Birth Year <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Users.birth_year', $birthYearDD, ['id' => 'birth_year', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Gender <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Users.gender', $genderDD, ['id' => 'gender', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
                      </div>
                    </div>
                   
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Users.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'students'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>