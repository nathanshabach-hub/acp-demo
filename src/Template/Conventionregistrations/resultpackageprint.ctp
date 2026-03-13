<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0">
	<div class="row">
		<?php //echo $this->element('user_left_menu'); ?>
		<main class="">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading">
				
				<span><h1 style="color:#000;"><?php echo $userDetails->first_name; ?></h1></span>
				<br />
				<span>Result Package - <?php echo $conventionRegD->Conventions['name']; ?> <?php echo $conventionRegD->season_year; ?></span>
				
				<?php //echo $this->Html->link('<i class="fa fa-print"></i> Print', ['controller' => 'conventionregistrations', 'action' => 'resultpackageprint'], ['escape' => false, 'class' => 'btn btn-primary', 'target' => '_blank']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/resultpackage"); ?>
			</div>
			

		</main>
	</div>
</div>
<script type="text/javascript">
<!--
window.print();
//-->
</script>