<script type="text/javascript">
	$(document).ready(function () {
		$("#addstudentevent").validate();
	});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3">Edit Student Event</h2>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">
					<?php echo $checkCRS->Students['first_name']; ?>
					<?php echo $checkCRS->Students['middle_name']; ?>
					<?php echo $checkCRS->Students['last_name']; ?> 
					(
						Age: <?php echo date("Y") - $checkCRS->Students['birth_year']; ?> Years
						&nbsp;
						Gender: <?php echo $checkCRS->Students['gender']; ?>
					
					)
				</h2>
				<?php echo $this->Form->create($conventionregistrationstudents, ['id' => 'addstudentevent', 'type' => 'file', 'class' => ' ']); ?>



				<div class="form-group">
					<label for="name">Choose Event(s)</label>
					<div class="input-multiple">
						<?php echo $this->Form->select('Conventionregistrationstudents.event_ids', $eventNameIDDD, ['id' => 'event_ids', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose', 'multiple' => 'multiple', 'value' => explode(",", $checkCRS->event_ids)]); ?>
						<script>
							$(document).ready(function () {
								$('#event_ids').select2();
							});
						</script>
					</div>
				</div>

				<div class="form-group form-btns">
					<label></label>
					<button type="submit" class="btn btn-secondary">Save</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'studentevents'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->

		</main>
	</div>
</div>