<script type="text/javascript">
$(document).ready(function () {
	$("#school_frm").validate();
});
</script>

<section class="position-relative">
	<div class=" left-element ">
		<?php echo $this->Html->image('front/left-element.png'); ?>
	</div>
	<div class=" ryt-element ">
		<?php echo $this->Html->image('front/ryt-element.png'); ?>
	</div>
	<div class="container">
		<div class="row center-section align-items-center">
			<?php echo $this->element('Homes/home_left_content'); ?>
			
			
			<div class="col-lg-6 ">
				<div class="ryt-box bg-white w-100">
					<div class="ryt-box-text form-group">
						<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
							<?php echo $this->Form->create($users, ['id'=>'school_frm', 'type' => 'file', 'class' => 'frm_acc']); ?>
							<h2 class="mb-4">Register using previous details</h2>
							<div>
								<div class="lables">
									<span class="mb-0 me-1 col-4">Customer Code</span>
									<!--<input type="text" placeholder="Accelerate School for Convention" class=" w-75">-->
									<?php echo $this->Form->control('Users.customer_code', ['label'=>false, 'type'=>'text', 'autocomplete'=>'off', 'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Customer Code']); ?>
								</div>
								<div class="lables">
									<span class="mb-0 me-1 col-4 ">Password</span>
									<!--<input type="text" placeholder="John Smith" class="w-75">-->
									<?php echo $this->Form->control('Users.password', ['label'=>false, 'type'=>'password', 'autocomplete'=>'off', 'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Password']); ?>
								</div>
								
								<div class="btns col-8 float-end">
									<span class=" mb-3 w-100 ">
									<?php echo $this->Html->link('Forgot Password?', ['controller'=>'users', 'action' => 'forgotpassword'], ['escape'=>false, 'class' => 'text-primary ms-1']); ?>
									<!--<a href="" class="text-primary ms-1">Forgot Password?</a>-->
									</span>
									<button type="submit" class="btn btn-secondary px-3 ms-1">Continue to Registration</button>
								</div>
							</div>
						<?php echo $this->Form->end(); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>