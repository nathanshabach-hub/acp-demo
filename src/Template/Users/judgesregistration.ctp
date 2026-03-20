<script type="text/javascript">
	$(document).ready(function () {
		$("#judgesreg_frm").validate();
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
						<div class="ersu_message">
							<?php echo $this->Flash->render() ?>
						</div>
						<?php echo $this->Form->create($users, ['id' => 'judgesreg_frm', 'type' => 'file']); ?>
						<h2 class="mb-4">Judges Registration</h2>
						<div>
							
							<div class="lables">
								<span class="col-4">First Name</span>
								<?php echo $this->Form->control('Users.first_name', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'First Name']); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Surname</span>
								<?php echo $this->Form->control('Users.last_name', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'Surname']); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Email Address</span>
								<?php echo $this->Form->control('Users.email_address', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required email', 'placeholder' => 'Email Address']); ?>
							</div>

							<div class="lables">
								<span class="col-4">Password</span>
								<?php echo $this->Form->control('Users.password', ['label' => '', 'type' => 'password', 'label' => false, 'div' => false, 'class' => "form-control required", 'placeholder' => 'Password', 'minlength'=>6]); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Confirm Password</span>
								<?php echo $this->Form->control('Users.confirm_password', ['id' => 'confirm_password','label' => '', 'type' => 'password', 'label' => false, 'div' => false, 'class' => "form-control required", 'placeholder' => 'Confirm Password', 'equalTo'=>'#users-password']); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Previous convention experience ? </span>
								<?php echo $this->Form->control('Users.previous_convention_experience', ['id' => '', 'label' => false, 'type' => 'textarea', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'What is your previous convention experience?']); ?>
							</div>
							
							<div class="lables">
								<span class="col-4">Other non-convention experience? </span>
								<?php echo $this->Form->control('Users.non_convention_experience', ['label' => false, 'type' => 'textarea', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'What other non-convention experience do you have that would assist in your area of interest for judging?']); ?>
							</div>

							<div class="btns col-8 float-end">
								<span class=" mb-3 w-100">
									<?php echo $this->Html->link('Forgot Password?', ['controller' => 'users', 'action' => 'forgotpassword'], ['escape' => false, 'class' => 'text-primary ms-1']); ?>
									<!--<a href="" class="text-primary ms-1">Forgot Password?</a>-->
								</span>
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