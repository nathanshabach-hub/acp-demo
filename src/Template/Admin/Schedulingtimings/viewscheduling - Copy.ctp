<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
			View Scheduling - <?php echo $conventionSD->Conventions['name']; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">View Scheduling - <?php echo $conventionSD->Conventions['name']; ?></li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search" style="display:nones;">
                <?php echo $this->Form->create(Null, ['id'=>'adminSearch']); ?>
                    <div class="form-group align_box dtpickr_inputs">
                       <span class="hints" style="display:none;">Search by Season Name or Year</span>
                       <span class="hint">
                           <?php //echo $this->Form->control('Seasons.keyword', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Search by Season Name or Year']); ?>
                       </span>
                      
                       <div class="admin_asearch">
                            <div class="ad_s ajshort"> <?php //echo $this->Form->button('Search', ['class'=>'btn btn-info admin_ajax_search', 'type'=>'button']); ?></div>
                            <div class="ad_cancel"> <?php //echo $this->Html->link('Clear Search', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false, 'class'=>'btn btn-default canlcel_le']);?></div>
                       </div>
                    </div>
                <?php echo $this->Form->end(); ?>
                <div class="add_new_record"><?php echo $this->Html->link('<i class="fa fa-plus"></i> Back To Pre-check', ['controller'=>'schedulings', 'action'=>'precheck',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-default']);?>
				
				
				</div>
            </div>
			
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Schedulingtimings/viewscheduling"); ?>
            </div>
			 
				
			 
            
        </div>
    </section>
</div>
 
<?php
if(is_array($pendingEventsToRoomsList) && count($pendingEventsToRoomsList)>0)
{
?>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<div class="modal fade" id="myModalPendingEvents" role="dialog">
	<div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Pending Events <?php echo count($pendingEventsToRoomsList); ?></h4>
			</div>
			<div class="modal-body">
				<?php
				$cntrPE = 1;
				foreach($pendingEventsToRoomsList as $pendingev)
				{
				?>
					<p>
						<?php
						echo $cntrPE.'.&nbsp;&nbsp;';
						echo $pendingev;
						?>
					</p>
				<?php
				$cntrPE++;
				}
				?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<?php
}
?>
