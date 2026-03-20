<?php
use Cake\ORM\TableRegistry;
$this->Eventsubmissions = TableRegistry::getTableLocator()->get('Eventsubmissions');
$this->Judgeevaluations = TableRegistry::getTableLocator()->get('Judgeevaluations');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$events->isEmpty()) { ?> 
    <div class="panel-body">
        
        <?php echo $this->Form->create('Conventionregistrations', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

            <div class="tbl-resp-listing">
                <table id="group_events_table" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th class="sorting_paging">Event Name</th>
							<th class="sorting_paging">Event Number</th>
							<th class="sorting_paging">Group Event</th>
							<th class="sorting_paging">Total Entries</th>
							<th class="sorting_paging">Marked Entries</th>
							<th class="sorting_paging">View Entries</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($events as $datarecord) { ?>
						<tr>
						<td data-title="Event Name"><?php echo $datarecord->event_name;?> <?php //echo $datarecord->event_judging_type; ?></td>
						<td data-title="Event Number"><?php echo $datarecord->event_id_number;?></td>
						<td data-title="Group Event"><?php if($datarecord->group_event_yes_no == 1) echo 'Yes'; else echo 'No';?></td>
						<td data-title="Total Entries">
						<?php
						$condEventEntries = array();
						//$condEventEntries[] = "(Eventsubmissions.conventionregistration_id = '".$conventionRegD->id."')";
						//$condEventEntries[] = "(Eventsubmissions.conventionseason_id = '".$conventionRegD->conventionseason_id."')";
						$condEventEntries[] = "(Eventsubmissions.convention_id = '".$conventionRegD->convention_id."' AND Eventsubmissions.season_id = '".$conventionRegD->season_id."' AND Eventsubmissions.season_year = '".$conventionRegD->season_year."' AND Eventsubmissions.event_id = '".$datarecord->id."')";
						
						if($datarecord->group_event_yes_no == 1)
						{
							$condEventEntries[] = "(Eventsubmissions.student_id = '0')";
						}
						else
						{
							$condEventEntries[] = "(Eventsubmissions.student_id >0)";
						}
								
						$totalEntriesSubmitted = $this->Eventsubmissions->find()->where($condEventEntries)->count();
						echo $totalEntriesSubmitted;
						?>
						</td>
						
						<td data-title="Marked Entries">
						
						<?php
						$totalEntriesM = 0;
						// to count that how many entries are marked
						$listTotalEntries = $this->Eventsubmissions->find()->where($condEventEntries)->all();
						foreach($listTotalEntries as $eventsub)
						{
							// to check if this entry has been marked or not
							$condCheckMark = array();
							$condCheckMark[] = "(Judgeevaluations.eventsubmission_id = '".$eventsub->id."')";
							$condCheckMark[] = "(Judgeevaluations.uploaded_by_user_id = '".$this->request->getSession()->read("user_id")."')";
							$checkEntryMark = $this->Judgeevaluations->find()->where($condCheckMark)->first();
							if($checkEntryMark)
							{
								$totalEntriesM++;
							}
						}
						echo $totalEntriesM;
						?>
						</td>
						
						<td data-title="View Entries">
						<?php
						if($totalEntriesSubmitted>0)
						{
							// now open respective list page based on event judging type
							if($datarecord->event_judging_type == 'times')
							{
								echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'eventsubmissions', 'action' => 'timeseventsentries',$conventionRegD->slug,$datarecord->slug], [ 'escape' => false, 'title' => 'View Times Event Entries Submitted', 'class'=>'']);
							}
							else
							if($datarecord->event_judging_type == 'distances')
							{
								echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'eventsubmissions', 'action' => 'distanceseventsentries',$conventionRegD->slug,$datarecord->slug], [ 'escape' => false, 'title' => 'View Distances Event Entries Submitted', 'class'=>'']);
							}
							else
							if($datarecord->event_judging_type == 'scores')
							{
								echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'eventsubmissions', 'action' => 'scoreseventsentries',$conventionRegD->slug,$datarecord->slug], [ 'escape' => false, 'title' => 'View Scores Events Entries Submitted', 'class'=>'']);
							}
							else
							if($datarecord->event_judging_type == 'soccer_kick')
							{
								echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'eventsubmissions', 'action' => 'soccerkickeventsentries',$conventionRegD->slug,$datarecord->slug], [ 'escape' => false, 'title' => 'View Soccer Kick Events Entries Submitted', 'class'=>'']);
							}
							else
							if($datarecord->event_judging_type == 'spellings')
							{
								echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'eventsubmissions', 'action' => 'spellingseventsentries',$conventionRegD->slug,$datarecord->slug], [ 'escape' => false, 'title' => 'View Spellings Events Entries Submitted', 'class'=>'']);
							}
							else
							{
								echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'conventionregistrations', 'action' => 'judgeevententries',$conventionRegD->slug,$datarecord->slug], [ 'escape' => false, 'title' => 'View Entries Submitted', 'class'=>'']);
							}
						}
						?>
						</td>
					</tr>
						<?php } ?>
					</tbody>
				</table>
            </div>
        </section>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>

<script>
$(document).ready(function() {
$('#group_events_table').dataTable({
    //"bPaginate": false,
    "bLengthChange": false,
	"pageLength": 50,
	order: [[0, 'asc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	$('#searchInput').on('keyup', function() {
        $('#group_events_table').dataTable.search(this.value).draw();
    });
});
</script>

<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<style type="text/css">
    .page-link {
        color: #1c2452 !important;
        background-color: #fff !important;
    }

    .active>.page-link,
    .page-link.active {
        background-color: #1c2452 !important;
        border-color: #1c2452 !important;
        color: #fff !important;
    }

    .pagination {
        border-radius: 0rem !important;
    }
</style>





