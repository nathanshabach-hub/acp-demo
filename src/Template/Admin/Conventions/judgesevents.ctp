<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
			<?php echo $conventionRegD->Users['first_name']; ?> <?php echo $conventionRegD->Users['last_name']; ?> - <?php echo $conventionRegD->Conventions['name']; ?> (<?php echo $conventionRegD->season_year; ?>)
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$conventionRegD->Conventions['slug']], ['escape'=>false]);?></li>
		  
		  <li><?php echo $this->Html->link('<i class="fa fa-user-secret"></i> Judges ', ['controller'=>'conventions', 'action'=>'judges',$conventionRegD->Conventionseasons['slug'],$conventionRegD->Conventions['slug']], ['escape'=>false]);?></li>
		  
          <li class="active">Judges Events <?php echo $conventionRegD->season_year; ?></li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search" style="display:none;">
                <?php echo $this->Form->create(Null, ['id'=>'adminSearch']); ?>
                    <div class="form-group align_box dtpickr_inputs">
                       <span class="hints">Search by Season Name or Year</span>
                       <span class="hint">
                           <?php echo $this->Form->control('Seasons.keyword', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Search by Season Name or Year']); ?>
                       </span>
                      
                       <div class="admin_asearch">
                            <div class="ad_s ajshort"> <?php echo $this->Form->button('Search', ['class'=>'btn btn-info admin_ajax_search', 'type'=>'button']); ?></div>
                            <div class="ad_cancel"> <?php echo $this->Html->link('Clear Search', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false, 'class'=>'btn btn-default canlcel_le']);?></div>
                       </div>
                    </div>
                <?php echo $this->Form->end(); ?>
                <div class="add_new_record"><?php echo $this->Html->link('<i class="fa fa-plus"></i> Add Season', ['controller'=>'seasons', 'action'=>'add'], ['escape'=>false, 'class'=>'btn btn-default']);?></div>
            </div>
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Conventions/judgesevents"); ?>
            </div>
			
            
        </div>
    </section>
</div>

