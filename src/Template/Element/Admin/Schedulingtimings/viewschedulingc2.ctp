<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
$this->Schedulingtimings = TableRegistry::getTableLocator()->get('Schedulingtimings');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$schedulingTimingsList->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Divisions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				Schedule Category :: 2 (Needs Schedule=Yes || Group Event=No || Event Kind ID=Elimination || Has To Be Consecutive=No)
				</div>  
            </div> 

            <div class="tbl-resp-listing">
				<table id="convention_events" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#DB ID</th>
                            <th class="sorting_paging">Room</th>
                            <th class="sorting_paging">Day</th>
                            <th class="sorting_paging">Start</th>
							<th class="sorting_paging">Finish</th>
							<th class="sorting_paging">Event</th>
							<th class="sorting_paging">Round</th>
							<th class="sorting_paging">Match No.</th>
							<th class="sorting_paging">Match</th>
							<th class="sorting_paging">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($schedulingTimingsList as $datarecord)
						{	
						?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="DB ID"><?php echo $datarecord->id;?></td>
                                <td data-title="Room"><?php echo !empty($datarecord->Conventionrooms) ? $datarecord->Conventionrooms['room_name'] : '<span class="label label-danger">No Room</span>';?></td>
                                <td data-title="Day"><?php echo $datarecord->day !== null ? $datarecord->day : '<span class="label label-warning">Unscheduled</span>';?></td>
                                <td data-title="Start">
								<?php 
								echo $datarecord->start_time!=NULL ? date("H:i A",strtotime($datarecord->start_time)) : '';
								?>
								</td>
                                <td data-title="Finish">
								<?php 
								echo $datarecord->finish_time!=NULL ? date("H:i A",strtotime($datarecord->finish_time)) : '';
								?>
								</td>
                                <td data-title="Event"><?php echo !empty($datarecord->Events) ? $datarecord->Events['event_name'].' ('.$datarecord->Events['event_id_number'].')' : '<span class="label label-danger">Unknown Event</span>';?></td>
								<td data-title="Round No.">Round-<?php echo $datarecord->round_number;?></td>
								<td data-title="Match No.">Match-<?php echo $datarecord->match_number;?></td>
                                <td data-title="Match">
								<?php
								
								if($datarecord->round_number > 1)
								{
									// to get match details
								
									$matchOneD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord->schtimeautoid1])->first();
									
									$matchTwoD = $this->Schedulingtimings->find()->where(["Schedulingtimings.id" => $datarecord->schtimeautoid2])->first();
									
									echo '(Winner of Match-'.$matchOneD->match_number.')';
									echo ' <b>VS</b> ';
									echo '(Winner of Match-'.$matchTwoD->match_number.')';
								}
								else
								{
									if($datarecord->user_id>0 && ($datarecord->user_id_opponent == 0 || $datarecord->user_id_opponent == NULL))
									{
										echo $datarecord->Users['first_name'].' '.$datarecord->Users['middle_name'].' '.$datarecord->Users['last_name'].' (<b>BYE</b>)';
									}
									else
									{
										echo $datarecord->Users['first_name'].' '.$datarecord->Users['middle_name'].' '.$datarecord->Users['last_name'];
										echo ' <b>VS</b> ';
										echo $datarecord->Opponentuser['first_name'].' '.$datarecord->Opponentuser['middle_name'].' '.$datarecord->Opponentuser['last_name'];
									}
								}
								
								?>
								
								</td>
								<td data-title="Edit scheduling timings">
								<?php
								if($datarecord->user_type == 'Student' && $datarecord->is_bye != 1)
								{
									echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller'=>'schedulings', 'action'=>'editschedulingtimings',$convention_season_slug,$datarecord->id], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank','title'=>'Edit scheduling timings']);
								}
								?>
								</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

         
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->control('Divisions.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>

<script>
$(document).ready(function() {
	$('#convention_events').dataTable({
		"bPaginate": true,
		//"bInfo": false,
		"bLengthChange": false,
		"pageLength": 100,
		order: [[0, 'asc']],
		//"bFilter": true,
		//"bInfo": false,
		//"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#convention_events').dataTable.search(this.value).draw();
    }); */
});
</script>


<!--
<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
-->
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