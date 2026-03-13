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
			
			<?php
			$events->convention_id = $conventionD->id;
			?>
			<div class="col-lg-6 ">
				<div class="ryt-box bg-white ">
					<div class="ryt-box-text">
						<?php echo $this->Form->create($events, ['id'=>'home_conventions', 'type' => 'file']); ?>
						<h2 class="mb-4">Select Convention</h2>
						<!--
						<select class="form-select mb-4" aria-label="Default select example">
							<option selected>Open this select menu</option>
							<option value="1">One</option>
							<option value="2">Two</option>
							<option value="3">Three</option>
						</select>
						-->
						
						<?php echo $this->Form->select('Events.convention_id', $conventionDD, ['id' => 'convention_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose Convention']); ?>
						<script>
							$(document).ready(function() {
								$('#convention_id').select2();
							});
						</script>
						
						<script>
							$(document).ready(function(){
							   $('#convention_id').change(function(){
								   document.getElementById('home_conventions').submit(); 
								});
							});
						</script>
						
						<div class="d-flex justify-content-between">
							
							<!--
							<a type="button" class="btn btn-secondary px-3" href="page-4.html">Register</a>
							<a type="button" class="btn btn-secondary px-3" href="page-12.html">Login</a>
							-->
							
							<?php echo $this->Html->link('Register', ['controller'=>'users', 'action' => 'register', $slug], ['escape'=>false, 'type'=>'button', 'class'=>'btn btn-secondary px-3']); ?>
							
							<?php echo $this->Html->link('Login', ['controller'=>'users', 'action' => 'login', $slug], ['escape'=>false, 'type'=>'button', 'class'=>'btn btn-secondary px-3']); ?>
						</div>
						
						<?php echo $this->Form->end(); ?>
						
						<!--<button type="button" class="btn btn-secondary px-3"><a href="">Next</a> </button>-->
					</div>
				</div>
			</div>
			
			
		</div>
	</div>
</section>