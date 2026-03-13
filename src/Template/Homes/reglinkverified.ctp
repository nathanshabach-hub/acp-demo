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
				<div class="ryt-box bg-white ">
					<div class="ryt-box-text">
						<h2 class="mb-4">Customer Code Verified !</h2>
						<div class="d-flex justify-content-between">
							<!--
							<button type="button" class="btn btn-secondary px-3"><a href="page-9.html">Continue to Registration</a></button>
							-->
							<?php echo $this->Html->link('Continue to Registration', ['controller'=>'users', 'action' => 'registration', $conventionregistration_slug], ['escape'=>false, 'type'=>'button', 'class'=>'btn btn-secondary px-3']); ?>
						</div>
					</div>
				</div>
			</div>
			
			
		</div>
	</div>
</section>