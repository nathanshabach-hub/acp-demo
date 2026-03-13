<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading">
				<span>Spellings Event Entries :: <?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)</span>
				<?php echo $this->Html->link('<< Back', ['controller' => 'conventionregistrations', 'action' => 'judgeevents',$conv_reg_slug], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content" id="listID">
				<?php echo $this->element("Eventsubmissions/spellingseventsentries"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>