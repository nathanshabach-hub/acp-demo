<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
			Overall Positions - <?php echo $conventionD->name; ?> > <?php echo $conventionSD->season_year; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false]);?></li>
          <li class="active">Overall Positions</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search" style="display:nonex;">
                <?php echo $this->Form->create(Null, ['id'=>'adminSearch']); ?>
                    <div class="form-group align_box dtpickr_inputs">
                       <span class="hints"> </span>
                        
                      
                       
                    </div>
                <?php echo $this->Form->end(); ?>
                <div class="add_new_record">
				
				<?php
				
				echo $this->Html->link('Randomize Result', ['controller'=>'results', 'action'=>'overallpositions',$slug_convention_season,$slug_convention], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']);
				
				echo $this->Html->link('<i class="fa fa-print"></i> Print', ['controller'=>'results', 'action'=>'overallpositionsprint',$slug_convention_season,$slug_convention], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']);
				
				?>
				
				</div>
            </div>
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Results/overallpositions"); ?>
            </div>
            
        </div>
    </section>
</div>
