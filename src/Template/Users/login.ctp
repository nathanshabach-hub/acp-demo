<script type="text/javascript">
	$(document).ready(function () {
		$("#login_frm").validate();
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
						<?php echo $this->Form->create($users, ['id' => 'login_frm', 'type' => 'file']); ?>
						<h2 class="mb-4">Login</h2>
						<div>
							<div class="lables">
								<span class="col-4">Email Address</span>
								<?php echo $this->Form->input('Users.email_address', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required email', 'placeholder' => 'Email Address']); ?>
							</div>

							<div class="lables">
								<span class="col-4">Password</span>
								<?php echo $this->Form->input('Users.password', ['label' => '', 'type' => 'password', 'label' => false, 'div' => false, 'class' => "form-control required", 'placeholder' => 'Password']); ?>
							</div>

							<div class="lables">
								<span class="col-4">Choose Type</span>
								<div class="w-100">
									<?php echo $this->Form->select('Users.user_type', $loginUserTypes, ['id' => 'user_type', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
								</div>
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