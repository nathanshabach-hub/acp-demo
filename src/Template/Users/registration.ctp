<script type="text/javascript">
$(document).ready(function () {
	$("#school_frm").validate();
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
							<?php echo $this->Form->create($users, ['id'=>'school_frm', 'type' => 'file']); ?>
							<h2 class="mb-4">Enter or Update details</h2>
							<div>
								<div class="lables d-flex align-items-center mb-3
									justify-content-between">
									<span class="mb-0 me-1 col-4">School/HSSP Name</span>
									<!--<input type="text" placeholder="Accelerate School for Convention" class=" w-75">-->
									<?php echo $this->Form->input('Users.first_name', ['label'=>false, 'type'=>'text', 'autocomplete'=>'off', 'div'=>false, 'class'=>'form-control required w-75', 'placeholder'=>'Enter School Name']); ?>
								</div>
								<div class="lables d-flex align-items-center mb-3
									justify-content-between">
									<span class="mb-0 me-1 col-4 ">Main Contact Person</span>
									<!--<input type="text" placeholder="John Smith" class="w-75">-->
									<?php echo $this->Form->input('Users.middle_name', ['label'=>false, 'type'=>'text', 'autocomplete'=>'off', 'div'=>false, 'class'=>'form-control required w-75', 'placeholder'=>'Main Contact Person']); ?>
								</div>
								<div class="lables d-flex align-items-center mb-3
									justify-content-between">
									<span class="mb-0 me-1 col-4">Email</span>
									<!--<input type="email" placeholder="john.smith@acs.edu.au" class="w-75">-->
									<?php echo $this->Form->input('Users.email_address_no_change', ['label'=>false, 'type'=>'email', 'autocomplete'=>'off', 'div'=>false, 'class'=>'form-control required email w-75', 'placeholder'=>'Email Address', 'value'=>$users->email_address, 'readonly', 'disabled']); ?>
								</div>
								<div class="lables d-flex align-items-center mb-3
									justify-content-between">
									<span class="mb-0 me-1 col-4">Phone</span>
									<!--<input type="number" placeholder="+61 73777 7787" class="w-75">-->
									<?php echo $this->Form->input('Users.phone', ['label'=>false, 'type'=>'text', 'autocomplete'=>'off', 'div'=>false, 'class'=>'form-control required w-75', 'placeholder'=>'Phone']); ?>
								</div>
								<div class="lables d-flex align-items-center mb-3
									justify-content-between">
									<span class="mb-0 me-1 col-4">Password</span>
									<!--<input type="number" placeholder="+61 73777 7787" class="w-75">-->
									<?php echo $this->Form->input('Users.password', ['label'=>false, 'type'=>'password', 'autocomplete'=>'off', 'div'=>false, 'class'=>'form-control required w-75', 'placeholder'=>'Password']); ?>
								</div>
								<div class="btns col-8 float-end">
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