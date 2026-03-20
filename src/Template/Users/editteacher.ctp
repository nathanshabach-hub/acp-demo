<script type="text/javascript">
$(document).ready(function () {
	$("#editprofile").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<h2 class="mt-3">Edit Supervisors Info</h2>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Edit Supervisors Info</h2>
				<?php echo $this->Form->create($users, ['id'=>'editprofile', 'type' => 'file', 'class' =>' ']); ?>
					
					<div class="form-group">
						<label for="name">Title</label>
						<?php echo $this->Form->control('Users.title', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Title']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">First Name</label>
						<?php echo $this->Form->control('Users.first_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'First Name']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Surname</label>
						<?php echo $this->Form->control('Users.last_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Surname']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Email Address</label>
						<?php echo $this->Form->control('Users.email_address_old', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required email', 'placeholder'=>'Email Address', 'value'=>$users->email_address,'readonly','disabled']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Gender</label>
						<div class="input text">
						<?php echo $this->Form->select('Users.gender', $genderDD, ['id' => 'gender', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?></div>
					</div>
					
					<div class="form-group">
						<label for="name">Judge?</label>
						<div class="input text">
						<?php echo $this->Form->select('Users.is_judge', $yesNoDD, ['id' => 'is_judge', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?></div>
					</div>
					
					<div class="form-group form-btns">
						<label></label>
						<button type="submit" class="btn btn-secondary">Save</button>
						<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
						<?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'teachers'], ['class'=>'btn btn-secondary']); ?>
					</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->
			
		</main>
	</div>
</div>