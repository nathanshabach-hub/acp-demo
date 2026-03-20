<script type="text/javascript">
$(document).ready(function () {
	$("#check_customer_code").validate();
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
						<?php echo $this->Form->create(NULL, ['id'=>'check_customer_code', 'type' => 'file']); ?>
						<h2 class="mb-4">Check Customer Code</h2>
						 
						<!--<input type="text" placeholder="ASC01" class="mb-4 w-100">-->
						<?php echo $this->Form->control('Users.customer_code', ['label'=>false, 'type'=>'text', 'autocomplete'=>'off', 'div'=>false, 'class'=>'form-control required mb-0 w-100', 'placeholder'=>'Enter Code', 'autofocus'=>'']); ?>
						
						<div class="d-flex justify-content-between">
							<!--<button type="button" class="btn btn-secondary px-3"><a href="page-5.html">Check</a> </button>-->
							<?php echo $this->Form->button('Check', ['type'=>'submit', 'class' => 'btn btn-secondary px-3 mt-4', 'div'=>false]); ?>
						</div>
						
						<?php
						if($code_found == 1)
						{
						?>
							<h2 class="mb-4 mt-4" style="color:green;">Customer Code Found!</h2>
							
							<?php
							// now check that account is verified or not
							if($account_verified == 1)
							{
								// now check if already registered for this convention and season
								if($already_registered)
								{
									echo $this->Html->link('Login', ['controller'=>'users', 'action' => 'login'], ['escape'=>false, 'type'=>'button', 'class'=>'btn btn-secondary px-3']);
									echo '<span class="mt-3">You have already registered for this convention. Please login and continue.</span>';
								}
								else
								{
									echo $this->Html->link('Register using previous details', ['controller'=>'users', 'action' => 'login', $convention_slug,$season_id], ['escape'=>false, 'type'=>'button', 'class'=>'btn btn-secondary px-3']);
								}
							}
							else
							{
								// if account not verified, then show registratin button
								echo $this->Html->link('Send Registration Link', ['controller'=>'homes', 'action' => 'sendconvreglink', $convention_slug,$customer_code,$season_id], ['escape'=>false, 'type'=>'button', 'class'=>'btn btn-secondary px-3']);
								
								echo '<span class="mt-3">Please note: Registration link will be sent to the email address connected with your  		customer code. If you do not recieve a login link, please contact the events team  to update your email address.
								</span>';
							}
							?>
						
						<?php
						}
						else
						if($code_not_found == 1)
						{
						?>
							<h2 class="mb-4 mt-4" style="color:red;">Customer Code Not Found ! Please try again, or go to contact page</h2>
							<!--<button type="button" class="btn btn-secondary px-3"><a href="">Go to Contact Page</a> </button>-->
							<?php //echo $this->Html->link('Go to Contact Page', ['controller'=>'homes', 'action' => 'contactus', $convention_slug], ['escape'=>false, 'type'=>'button', 'class'=>'btn btn-secondary px-3']); ?>
							<a href="http://www.scee.edu.au/contact-2" type="button" class="btn btn-secondary px-3">Go to Contact Page</a>
						<?php
						}
						?>
						 
						
						<?php echo $this->Form->end(); ?>
						
					</div>
				</div>
			</div>
			
			
		</div>
	</div>
</section>