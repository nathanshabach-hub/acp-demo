<div class="content-wrapper">
    <section class="content-header">
      <h1>
			Manage Events - <?php echo $conventionD->name; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false]);?></li>
          <li class="active">Events <?php echo $conventionSD->season_year; ?></li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            
			
			<?php echo $this->Html->image('loader_large_blue.gif'); ?>
             
			
            
        </div>
    </section>
</div>
