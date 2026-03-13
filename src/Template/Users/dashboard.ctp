<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			<h2 class="mt-3">Dashboard</h2>
			
			<!-- dashboard-section-1 start-->
			<div class="dasboard-section">
				<div class="dashboard-text">
					<h2>Welcome <?php echo $userDetails->first_name; ?> (<?php echo $userDetails->email_address; ?>)</h2>
					
					
					<?php
					if(!empty($settingsD->postinfo))
					{
						echo '<p>';
						
						echo $postinfo = $settingsD->postinfo;
						
						// The Regular Expression filter
						//$reg_pattern = "/(((http|https|ftp|ftps)\:\/\/)|(www\.))[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\:[0-9]+)?(\/\S*)?/";
						 
						// make the urls to hyperlinks
						//echo preg_replace($reg_pattern, '<a style="color:#000;" href="$0" target="_blank" rel="noopener noreferrer">$0</a>', $postinfo);
						 
						echo '</p>';
						
					}
					?>
					
					<p>Please see instructional videos below for navigation of the Convention Portal. For any other questions, please contact the events team. 
					</p>
					
					<p>
						<iframe width="560" height="315" src="https://www.youtube.com/embed/bT-KQAlpMOI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
						
						<iframe width="560" height="315" src="https://www.youtube.com/embed/yGAzDK7xHrs" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
					</p>
						
					<p>
						<iframe width="560" height="315" src="https://www.youtube.com/embed/I9kG75X_obA" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
						
						<iframe width="560" height="315" src="https://www.youtube.com/embed/VUX7n29uqfo" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
					</p>
					
					<p>
						<iframe width="560" height="315" src="https://www.youtube.com/embed/JDG3Uxcow_c" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
						
						<iframe width="560" height="315" src="https://www.youtube.com/embed/GZ3vINjZ7sY" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
					</p>
					
					<p>
						<iframe width="560" height="315" src="https://www.youtube.com/embed/X-MUFvvQNCQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
						
						<iframe width="560" height="315" src="https://www.youtube.com/embed/G4vxpK0kzPQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
					</p>
					
					<p>
						<iframe width="560" height="315" src="https://www.youtube.com/embed/uysBVmzqGXU" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
					</p>
					
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					
				</div>
			</div>
			<!-- dashboard-section-1 end-->
			
		</main>
	</div>
</div>