<script type="text/javascript">
	$(document).ready(function () {
		$("#judgeregister").validate();
	});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3">Register For Convention :: <?php echo $conventionD->name; ?></h2>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Season: <?php echo $convSeasonD->season_year; ?></h2>
				<?php echo $this->Form->create($conventionregistrations, ['id' => 'judgeregister', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<label for="name">Choose Events of Interest </label>
					<div class="input">
					<?php echo $this->Form->select('Conventionregistrations.judges_event_ids', $eventNameIDDD, ['id' => 'judges_event_ids', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple required', 'autocomplete' => 'off', 'multiple' => 'multiple']); ?>
					<span class='help_text'><small>Note: not all events will be allocated to you. Events are allocated on a needs basis and we do take into consideration event workload.</small></span>
					<script>
						$(document).ready(function () {
							$('#judges_event_ids').select2();
						});
					</script>
				</div>
			</div>

			<div class="form-group form-btns">
				<label></label>
				<button type="submit" class="btn btn-secondary">Save</button>
				<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
				<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'myregistrations'], ['class' => 'btn btn-secondary']); ?>
			</div>
			<?php echo $this->Form->end(); ?>
	</div>
	<!-- dashboard-section-3 end-->

	</main>
</div>
</div>