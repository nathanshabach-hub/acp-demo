<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading">
				<span>View/Edit Event Submissions</span>
				<?php echo $this->Html->link(' + Submit New Event', ['controller' => 'eventsubmissions', 'action' => 'submitnewevent'], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content" id="listID">
				<?php echo $this->element("Eventsubmissions/viewlist"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>