<script type="text/javascript">
	$(document).ready(function () {
		$("#addteacher").validate();
	});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3">Add Supervisor Info</h2>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Add Supervisors Info</h2>
				<?php echo $this->Form->create($conventionregistrationteachers, ['id' => 'addteacher', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<label for="name">Choose Supervisor</label>
					<div class="input">
					<?php echo $this->Form->select('Conventionregistrationteachers.teacher_id', $teacherSchoolDD, ['id' => 'teacher_id', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
					<script>
						$(document).ready(function () {
							$('#teacher_id').select2();
						});
					</script>
				</div>
			</div>

			<div class="form-group form-btns">
				<label></label>
				<button type="submit" class="btn btn-secondary">Save</button>
				<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
				<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'teachers'], ['class' => 'btn btn-secondary']); ?>
			</div>
			<?php echo $this->Form->end(); ?>
	</div>
	<!-- dashboard-section-3 end-->

	</main>
</div>
</div>