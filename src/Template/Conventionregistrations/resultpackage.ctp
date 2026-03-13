<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading">
				<span>Result Package</span>
				
				<?php echo $this->Html->link('<i class="fa fa-print"></i> Print', ['controller' => 'conventionregistrations', 'action' => 'resultpackageprint'], ['escape' => false, 'class' => 'btn btn-primary', 'target' => '_blank']); ?>
				
				&nbsp;&nbsp;&nbsp;
				
				<?php echo $this->Html->link('<i class="fa fa-user"></i> Individual Student Report', ['controller' => 'conventionregistrations', 'action' => 'resultpackageindividual'], ['escape' => false, 'class' => 'btn btn-primary', 'style' => 'margin-right:20px;']); ?>
				
				&nbsp;&nbsp;&nbsp;
				
				<?php echo $this->Html->link('<i class="fa fa-user"></i> Individual Result Package', ['controller' => 'conventionregistrations', 'action' => 'students'], ['escape' => false, 'class' => 'btn btn-success', 'style' => 'margin-right:20px;']); ?>
				
				
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/resultpackage"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>