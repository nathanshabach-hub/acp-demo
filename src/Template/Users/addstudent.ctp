<script type="text/javascript">
$(document).ready(function () {
	$("#addstudent").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
		
		<div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
		
			<h2 class="mt-3">Add Student Info</h2>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Add Student Info</h2>
				<?php echo $this->Form->create($users, ['id'=>'addstudent', 'type' => 'file', 'class' =>' ']); ?>
					
					<div class="form-group">
						<label for="name">First Name</label>
						<?php echo $this->Form->control('Users.first_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'First Name']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Middle Name</label>
						<?php echo $this->Form->control('Users.middle_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Middle Name']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Last Name</label>
						<?php echo $this->Form->control('Users.last_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Last Name']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Birth Year</label>
						<div class="input text">
						<?php echo $this->Form->select('Users.birth_year', $birthYearDD, ['id' => 'gender', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?></div>
					</div>
					
					<div class="form-group">
						<label for="name">Gender</label>
						<div class="input text">
						<?php echo $this->Form->select('Users.gender', $genderDD, ['id' => 'gender', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?></div>
					</div>
					
					<div class="form-group form-btns">
						<label></label>
						<button type="submit" class="btn btn-secondary">Save</button>
						<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
						<?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'students'], ['class'=>'btn btn-secondary']); ?>
					</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->
			
		</main>
	</div>
</div>