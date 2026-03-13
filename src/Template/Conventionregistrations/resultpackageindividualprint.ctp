<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0" id="print_area">
	<div class="row">
		<?php //echo $this->element('user_left_menu'); ?>
		<main class="">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			
			<!-- dashboard-section-2 start-->

			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/resultpackageindividual"); ?>
			</div>
			

		</main>
	</div>
</div>
<script type="text/javascript">
<!--
window.print();
//-->
</script>
<style type="text/css">
@media print {
  @page { margin: 0; }
  body { margin: 1.6cm; }
}
</style>