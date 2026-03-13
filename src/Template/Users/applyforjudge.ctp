<script type="text/javascript">
$(document).ready(function () {
	$("#applyforjudge").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<h2 class="mt-3">Judge Profile</h2>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Apply For Judge</h2>
				<?php echo $this->Form->create($users, ['id'=>'applyforjudge', 'type' => 'file', 'class' =>' ']); ?>
					
					<?php
					// for teacher_parent or supervisor
					if ($userDetails->is_judge == "0")
					{
					?>
					<div class="form-group">
						<label for="name">Previous convention experience ?</label>
						<?php echo $this->Form->input('Users.previous_convention_experience', ['label'=>false, 'type'=>'textarea',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'What is your previous convention experience?']); ?>
					</div>
					
					<div class="form-group">
						<label for="name">Other non-convention experience?</label>
						<?php echo $this->Form->input('Users.non_convention_experience', ['label'=>false, 'type'=>'textarea',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'What other non-convention experience do you have that would assist in your area of interest for judging?']); ?>
					</div>
					
					<div class="form-group form-btns">
						<label></label>
						<button type="submit" class="btn btn-secondary">Apply</button>
						<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
						<?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'dashboard'], ['class'=>'btn btn-secondary']); ?>
					</div>
					<?php
					}
					?>
					
					<?php
					// for teacher_parent or supervisor
					if ($userDetails->is_judge == "1")
					{
					?>
					<div class="form-group">
						<label for="name">Previous convention experience ?</label>
						<?php echo $userDetails->previous_convention_experience; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Other non-convention experience?</label>
						<?php echo $userDetails->non_convention_experience; ?>
					</div>
					
					<div class="form-group">
						<label for="name"></label>
						<b style="color:green;">Approved By admin</b>
					</div>
					<?php
					}
					?>
					
					<?php
					// for teacher_parent or supervisor
					if ($userDetails->is_judge == "2")
					{
					?>
					<div class="form-group">
						<label for="name">Previous convention experience ?</label>
						<?php echo $userDetails->previous_convention_experience; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Other non-convention experience?</label>
						<?php echo $userDetails->non_convention_experience; ?>
					</div>
					
					<div class="form-group">
						<label for="name"></label>
						<b style="color:yellow;">Waiting for approval from admin</b>
					</div>
					
					<div class="form-group form-btns">
						<label></label>
						
						<?php echo $this->Html->link('Back to dashboard', ['controller'=>'users', 'action' => 'dashboard'], ['class'=>'btn btn-secondary']); ?>
					</div>
					
					<?php
					}
					?>
					
					
					<?php
					// for teacher_parent or supervisor
					if ($userDetails->is_judge == "3")
					{
					?>
					<div class="form-group">
						<label for="name">Previous convention experience ?</label>
						<?php echo $userDetails->previous_convention_experience; ?>
					</div>
					
					<div class="form-group">
						<label for="name">Other non-convention experience?</label>
						<?php echo $userDetails->non_convention_experience; ?>
					</div>
					
					<div class="form-group">
						<label for="name"></label>
						<b style="color:red;">Rejected By admin</b>
					</div>
					<?php
					}
					?>
					
					
					
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->
			
		</main>
	</div>
</div>