<script type="text/javascript">
$(document).ready(function () {
	$("#editprofile").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<h2 class="mt-3">Judge Experience</h2>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Judge Experience</h2>
				<?php echo $this->Form->create($users, ['id'=>'editprofile', 'type' => 'file', 'class' =>' ']); ?>
					
					<div class="form-group">
						<label for="name">Previous convention experience ?</label>
						<?php echo $this->Form->control('Users.previous_convention_experience', ['id' => '', 'label' => false, 'type' => 'textarea', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'What is your previous convention experience?']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Other non-convention experience?</label>
						<?php echo $this->Form->control('Users.non_convention_experience', ['label' => false, 'type' => 'textarea', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'What other non-convention experience do you have that would assist in your area of interest for judging?']); ?>
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