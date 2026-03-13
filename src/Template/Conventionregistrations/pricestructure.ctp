<script type="text/javascript">
$(document).ready(function () {
	$("#addteacher").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
		
		<div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
		
			<h2 class="mt-3">Price Structure</h2>
			  
			<?php
			if($paymentDone == "No")
			{
			?>
			<div class="dashboard-form">
				<h2 class="form-title">Choose Price Structure</h2>
				<?php echo $this->Form->create(NULL, ['id'=>'addteacher', 'type' => 'file', 'class' =>' ']); ?>
					
					<div class="form-group">
						<!--<label for="name">Choose Teachers</label>-->
						
						<?php echo $this->Form->select('Conventionregistrations.price_structure', $priceStructureDD, ['id' => 'price_structure', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple required', 'autocomplete' => 'off', 'value' =>$conventionRegD->price_structure, 'empty' => 'Choose']); ?>
						<em>Note: You cannot change price structure once you made payment for any student/supervisor for this convention registration.</em>
					</div>
					
					<div class="form-group form-btns">
						<label></label>
						<button type="submit" class="btn btn-secondary">Save</button>
						<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
						<?php echo $this->Html->link('Cancel', ['controller'=>'conventionregistrations', 'action' => 'teachers'], ['class'=>'btn btn-secondary']); ?>
					</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<?php
			}
			else
			{
			?>
			<div class="dashboard-form">
				<h2 class="form-title">Sorry, you cannot change the selected price structure for this convention as student payments have already been made.</h2>
					
					<div class="form-group">
						<!--<label for="name">Choose Teachers</label>-->
						
						Your selection for this convention registration: <?php echo $priceStructureDD[$checkPaymentConvReg->price_structure]; ?>
					</div>
					
					<div class="form-group form-btns">
						<label></label>
						<?php echo $this->Html->link('Back to dashboard', ['controller'=>'users', 'action' => 'dashboard'], ['class'=>'btn btn-secondary']); ?>
					</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<?php
			}
			?>
			
		</main>
	</div>
</div>