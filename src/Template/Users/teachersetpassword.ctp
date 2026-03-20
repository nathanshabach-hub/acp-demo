<script type="text/javascript">
$(document).ready(function () {
	$("#reset_password_frm").validate();
});
</script>

<section class="position-relative">
	<div class=" left-element w-25">
		<?php echo $this->Html->image('front/left-element.png'); ?>
	</div>
	<div class=" ryt-element w-25">
		<?php echo $this->Html->image('front/ryt-element.png'); ?>
	</div>
	<div class="container">
		<div class="row center-section align-items-center">
			<?php echo $this->element('Homes/home_left_content'); ?>
			
			
			<div class="col-lg-6 ">
				<div class="ryt-box bg-white w-100">
					<div class="ryt-box-text form-group">
						<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
							<?php echo $this->Form->create($users, ['id'=>'reset_password_frm', 'type' => 'file']); ?>
							<h2 class="mb-4">Set Your Password</h2>
							<div>
								<div class="lables d-flex align-items-center mb-3
									justify-content-between">
									<span class="mb-0 me-1 col-4">New Password</span>
									<?php echo $this->Form->control('Users.password', ['minlength' => '6', 'type' => 'password', 'maxlength' => '40', 'label' => false, 'div' => false, 'id' => 'password', 'class' => "form-control required w-75", 'placeholder' => 'New Password']); ?>
								</div>
								
								<div class="lables d-flex align-items-center mb-3
									justify-content-between">
									<span class="mb-0 me-1 col-4">Confirm Password</span>
									<?php echo $this->Form->control('Users.confirm_password', ['label' => '', 'type' => 'password', 'label' => false, 'div' => false, 'equalTo' => '#password', 'class' => "form-control required w-75", 'placeholder' => 'Confirm Password']); ?> 
								</div>
								
								<div class="btns col-8 float-end">
									 
									<button type="submit" class="btn btn-secondary px-3 ms-1">Submit</button>
								</div>
							</div>
						<?php echo $this->Form->end(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>