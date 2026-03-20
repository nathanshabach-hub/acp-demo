<script type="text/javascript">
	$(document).ready(function () {
		$("#addrequest").validate();
	});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3">Application for Combined Team/Group Events</h2>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Application for Combined Team/Group Events</h2>
				<?php echo $this->Form->create($combinerequests, ['id' => 'addrequest', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<label for="name">Choose Event</label>
					<div class="input">
						<?php echo $this->Form->select('Combinerequests.event_id', $eventNameIDDD, ['id' => 'event_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
						<script>
							$(document).ready(function () {
								$('#event_id').select2();
							});
						</script>
					</div>
				</div>

				<div class="form-group">
					<label for="name">Choose School To Combine With</label>
					<div class="input">
						<?php echo $this->Form->select('Combinerequests.combine_with_user_id', $schoolNamesDD, ['id' => 'combine_with_user_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
						<script>
							$(document).ready(function () {
								$('#combine_with_user_id').select2();
							});
						</script>
					</div>
				</div>
				
				<div class="form-group">
					<label for="name">Student Name/s</label>
					<div class="input">
						<?php echo $this->Form->control('Combinerequests.student_name', ['id' => 'student_name', 'label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control required']); ?>
					</div>
				</div>

				<div class="form-group form-btns">
					<label></label>
					<button type="submit" class="btn btn-secondary">Save</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('Cancel', ['controller' => 'combinerequests', 'action' => 'viewlist'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->

		</main>
	</div>
</div>