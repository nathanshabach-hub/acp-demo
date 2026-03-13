<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Judge Evaluations
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
          <li class="active"> Judge Evaluation</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search">
                <?php echo $this->Form->create(Null, ['id'=>'adminSearch']); ?>
                    <div class="form-group align_box dtpickr_inputs">
                       <span class="hints">&nbsp;</span>
                       <span class="hint" style="display:none;">
                           <?php echo $this->Form->select('Judgeevaluations.convention_id', $conventionsDD, ['id' => 'convention_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'All Conventions', 'style' => 'margin-bottom:2px;']); ?>
							<script>
								$(document).ready(function() {
									$('#convention_id').select2();
								});
							</script>
                       </span>
					   
					   <span class="hint" style="display:none;">
                           <?php echo $this->Form->select('Judgeevaluations.season_year', $seasonsDD, ['id' => 'season_year', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'All Seasons', 'style' => 'margin-bottom:2px;']); ?>
							<script>
								$(document).ready(function() {
									//$('#season_year').select2();
								});
							</script>
                       </span>
					   
					   <span class="hint" style="display:none;">
                           <?php echo $this->Form->select('Judgeevaluations.event_id', $eventsDD, ['id' => 'event_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'All Events', 'style' => 'margin-bottom:2px;']); ?>
							<script>
								$(document).ready(function() {
									$('#event_id').select2();
								});
							</script>
                       </span>
                      
                       <div class="admin_asearch" style="display:none;">
                            <div class="ad_s ajshort"> <?php echo $this->Form->button('Search', ['class'=>'btn btn-info admin_ajax_search', 'type'=>'button']); ?></div>
                            <div class="ad_cancel"> <?php echo $this->Html->link('Clear Search', ['controller'=>'judgeevaluations', 'action'=>'index'], ['escape'=>false, 'class'=>'btn btn-default canlcel_le']);?></div>
                       </div>
                    </div>
                <?php echo $this->Form->end(); ?>
                <div class="add_new_record"><?php //echo $this->Html->link('<i class="fa fa-plus"></i> Add Division', ['controller'=>'conventionregistrations', 'action'=>'add'], ['escape'=>false, 'class'=>'btn btn-default']);?></div>
            </div>
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Judgeevaluations/index"); ?>
            </div>
            
        </div>
    </section>
</div>
