<?php echo $this->Html->script('ajax-pagging.js'); ?>
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
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search" style="display:none;">
                <?php echo $this->Form->create(Null, ['id'=>'adminSearch']); ?>
                    <div class="form-group align_box dtpickr_inputs">
                       <span class="hints">Search by Season Name or Year</span>
                       <span class="hint">
                           <?php echo $this->Form->input('Seasons.keyword', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Search by Season Name or Year']); ?>
                       </span>
                      
                       <div class="admin_asearch">
                            <div class="ad_s ajshort"> <?php echo $this->Form->button('Search', ['class'=>'btn btn-info admin_ajax_search', 'type'=>'button']); ?></div>
                            <div class="ad_cancel"> <?php echo $this->Html->link('Clear Search', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false, 'class'=>'btn btn-default canlcel_le']);?></div>
                       </div>
                    </div>
                <?php echo $this->Form->end(); ?>
                <div class="add_new_record"><?php echo $this->Html->link('<i class="fa fa-plus"></i> Add Season', ['controller'=>'seasons', 'action'=>'add'], ['escape'=>false, 'class'=>'btn btn-default']);?></div>
            </div>
			<?php if (!$conventionseasonevents->isEmpty()) { ?>
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Conventions/events"); ?>
            </div>
			<?php
			}
			else
			{
			?>
				<div class="admin_no_record" id="import_button_sections">
				
				<br />
				
				<?php echo $this->Html->link('<i class="fa fa-upload"></i> Import From Global Event List', ['controller'=>'conventions', 'action'=>'importeventsfromglobal',$slug_convention_season,$slug_convention], ['escape'=>false, 'class'=>'btn btn-default', 'id' =>'import_from_global_button']);?>
				<br />
				
				<?php
				if($prevSeasonConventionFound == 1)
				{
					echo $this->Html->link('<i class="fa fa-upload"></i> Import From Previous Season Event List', ['controller'=>'conventions', 'action'=>'importeventsfromprevyear',$slug_convention_season,$slug_convention,$prevConvSeasonAutoID], ['escape'=>false, 'class'=>'btn btn-default', 'id' => 'import_from_prev_button']);
				}
				else
				{
					echo '<small style="font-size:11px;">This convention not found in previous season.</small>';
				}
				?>
				
				<br />
				<?php echo $this->Html->link('<i class="fa fa-toggle-left"></i> Back to seasons', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false, 'class'=>'btn btn-default']);?>
				</div>
				
				
				
				<div class="admin_no_record" id="loader_box" style="display:none;">
					<?php echo $this->Html->image('loader_large_blue.gif'); ?>
				</div>
				
			<?php
			}
			?>
            
        </div>
    </section>
</div>

<script>
$("#import_from_global_button").click(function(){
    if(confirm("Are you sure you want to import events from global events list ?")){
        $("#import_button_sections").hide();
        $("#loader_box").show();
    }
    else{
        return false;
    }
});
</script>

<script>
$("#import_from_prev_button").click(function(){
    if(confirm("Are you sure you want to import events from previous season list ?")){
        $("#import_button_sections").hide();
        $("#loader_box").show();
    }
    else{
        return false;
    }
});
</script>
