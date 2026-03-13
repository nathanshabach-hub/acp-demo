<!--
<h1><?php echo $pageContent->static_page_title; ?></h1>
    
    <br>
    <ul class="list-group">
		<li class="list-group-item">		   
		  <p>
			<?php echo nl2br($pageContent->static_page_description);
			?>
		  </p>		  
		</li>  
</ul>

-->

<section class="bottom_content">
	<div class="container">
		<div class="row">
			<div class="col-xs-12 col-sm-7 col-md-7 pull-left">
				<div class="left_container">
					<h2><?php echo $pageContent->static_page_title; ?></h2>

					<div class="box">
						 
						<?php echo nl2br($pageContent->static_page_description);
			?>
					</div>


				</div>

			</div>  

		
		<?php echo $this->element('inner_right_box'); ?>

	</div>   


</section>