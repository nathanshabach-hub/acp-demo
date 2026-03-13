<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			<h2 class="mt-3">Dashboard</h2>
			
			<!-- dashboard-section-1 start-->
			<div class="dasboard-section">
				<div class="dashboard-text">
					<h2>Welcome <?php echo $userDetails->first_name; ?></h2>
					<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Eligendi est, nihil doloribus quae aliquid nisi
						sunt, culpa voluptatum dolores hic temporibus, sapiente corrupti iure nemo perferendis praesentium
						aspernatur laudantium natus!
					</p>
				</div>
			</div>
			<!-- dashboard-section-1 end-->
			
		</main>
	</div>
</div>