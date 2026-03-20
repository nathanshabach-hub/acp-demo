<script type="text/javascript">
$(document).ready(function () {
	$("#editprofile").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<h2 class="mt-3">Change Password</h2>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
				<h2 class="form-title">Change Password</h2>
				<?php echo $this->Form->create($users, ['id'=>'editprofile', 'type' => 'file', 'class' =>' ']); ?>
					
					<div class="form-group">
						<label for="name">Old Password</label>
						<?php echo $this->Form->control('Users.old_password', ['id'=>'old_password', 'label'=>false, 'type'=>'password',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Old Password','autocomplete' => 'off']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">New Password</label>
						<?php echo $this->Form->control('Users.new_password', ['id'=>'new_password', 'label'=>false, 'type'=>'password',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'New Password', 'autocomplete' => 'off','minlength' => 6]); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Confirm Password</label>
						<?php echo $this->Form->control('Users.confirm_password', ['id'=>'confirm_password', 'label'=>false, 'type'=>'password',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Confirm Password','autocomplete' => 'off','minlength' => 6, 'equalTo' => '#new_password']); ?>
					</div>
					
					<div class="form-group form-btns">
						<label></label>
						<button type="submit" class="btn btn-secondary">Submit</button>
						<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
						<?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'dashboard'], ['class'=>'btn btn-secondary']); ?>
					</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->
			
		</main>
	</div>
</div>