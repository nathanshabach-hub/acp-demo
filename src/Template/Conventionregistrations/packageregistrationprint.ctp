<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="container-fluid p-0">
	<div class="row">
		
		<main class="">
			
			<h5 class="mt-2"><?php echo $conventionRegD->Conventions['name'];?> <?php echo $conventionRegD->season_year;?></h5>
			<h5 style="color:#1c2452"><b><?php echo $userDetails->first_name; ?></b></h5>
			<h4 class="mt-4">Registration Check List</h4>
			

			<div class="m_content" id="listID">
				<?php echo $this->element("Conventionregistrations/packageregistration"); ?>
			</div>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>

<script type="text/javascript">
<!--
window.print();
//-->
</script>