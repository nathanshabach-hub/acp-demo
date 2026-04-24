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
					    <iframe width="560" height="315" src="https://www.youtube.com/embed/r398Y2db2nc?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX" title="ACP 1: Introduction and Registration" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
					    
					    <iframe width="560" height="315" src="https://www.youtube.com/embed/dcBTlI2_w20?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX" title="ACP 2: Global List Information" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
					</p>
						
					<p>
					    <iframe width="560" height="315" src="https://www.youtube.com/embed/Zk2dhRuNsDo?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX" title="ACP 3: Price Structure, Supervisor and Student Registration" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>

						<iframe width="560" height="315" src="https://www.youtube.com/embed/Cyn9-uJKeuY?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX" title="ACP 4: Student Event Registration" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
					</p>
					
					<p>
						<iframe width="560" height="315" src="https://www.youtube.com/embed/6G-03VkSMdY?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX" title="ACP 5: Events of the Heart" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>

						<iframe width="560" height="315" src="https://www.youtube.com/embed/G4vxpK0kzPQ?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX" title="ACP 6: Judges Portal Tutorial" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
					</p>
					
					<p>
						<iframe width="560" height="315" src="https://www.youtube.com/embed/X-MUFvvQNCQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
						
						<iframe width="560" height="315" src="https://www.youtube.com/embed/uysBVmzqGXU?list=PL4xufnmI4bVnF63e3fYwzF-gGUO6incFX" title="ACP 8: Results List and Judges Forms" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
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

