<?php
use Cake\ORM\TableRegistry;
$this->Conventionregistrations = TableRegistry::getTableLocator()->get('Conventionregistrations');
$this->Eventsubmissions = TableRegistry::getTableLocator()->get('Eventsubmissions');
$this->Conventionseasonroomevents = TableRegistry::getTableLocator()->get('Conventionseasonroomevents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionseasonevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Divisions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				<?php 
				if($totalEventsConventions>0)
					echo $totalEventsConventions.' event(s) selected';
				else
					'No event selected';
				?>
				<br />
				<?php echo $this->Html->link('Reset Event List', ['controller'=>'conventions', 'action' => 'reseteventlist',$slug_convention_season,$slug_convention], ['class'=>'btn btn-success', 'confirm' => 'Are you sure you want to reset event list for this convention? This will delete all events for this convention & selected season ?', 'style' => "margin-bottom:20px;"]); ?>
				<br />
				<?php echo $this->Html->link('<i class="fa fa-toggle-left"></i> Back to seasons', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false, 'class'=>'btn btn-default']);?>
				
				</div> 
				
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventions', 'action'=>'events',$slug_convention_season,$slug_convention, $separator]));
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table id="convention_events" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#ID</th>
                            <th class="sorting_paging">#DB ID Event</th>
                            <th class="sorting_paging">Event ID Number</th>
							<th class="sorting_paging">Event Name</th>
							<th class="sorting_paging">Event Type</th>
							<th class="sorting_paging">Rooms Allocated</th>
							<th class="sorting_paging">Entries</th>
							<th class="sorting_paging">Judge(s)</th>
							<th class="sorting_paging">Judging?</th>
							<th class="sorting_paging">Qualifying Score/time</th>
							<th class="sorting_paging">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($conventionseasonevents as $datarecord)
						{
							// Here check room ids alocated for this Event
							$condRoomCS = array();
							
							$eventIDCS = $datarecord->Events['id'];
							
							$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
							$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$eventIDCS."' OR 
											Conventionseasonroomevents.event_ids LIKE '".$eventIDCS.",%' OR 
											Conventionseasonroomevents.event_ids LIKE '%,".$eventIDCS.",%' OR 
											Conventionseasonroomevents.event_ids LIKE '%,".$eventIDCS."')";
							$roomCSEvent = $this->Conventionseasonroomevents->find()->where($condRoomCS)->contain(['Conventionrooms'])->all();
							$roomArrCSEvent = array();
							foreach($roomCSEvent as $roomeventcs)
							{
								$roomArrCSEvent[] = $roomeventcs->Conventionrooms['room_name']." (".$roomeventcs->room_id.")";
							}
						?> 
                            <tr>
                                <td data-title="#ID"><?php echo $datarecord->id;?></td>
								<td data-title="#DB ID Event"><?php echo $datarecord->Events['id'];?></td>
                                <td data-title="Event ID Number"><?php echo $datarecord->Events['event_id_number'];?></td>
                                <td data-title="Event Name"><?php echo $datarecord->Events['event_name'];?> (<?php echo $datarecord->Events['event_judging_type'];?>)</td>
                                <td data-title="Event Type"><?php echo $eventTypeDD[$datarecord->Events['event_type']];?></td>
								
                                <td data-title="Rooms Aloocated">
								<?php 
								if(count($roomArrCSEvent))
								{
									echo implode(", ",$roomArrCSEvent);
								}
								?></td>
                                
								<td data-title="Entries">
								<?php
								// to count entries for this event
								$condTotalEntries = array();
								$condTotalEntries[] = "(Eventsubmissions.conventionseason_id = '".$datarecord->conventionseasons_id."')";
								$condTotalEntries[] = "(Eventsubmissions.convention_id = '".$datarecord->convention_id."')";
								$condTotalEntries[] = "(Eventsubmissions.season_id = '".$datarecord->season_id."')";
								$condTotalEntries[] = "(Eventsubmissions.event_id = '".$datarecord->Events['id']."')";
								$totalEntriesEvent = $this->Eventsubmissions->find()->where($condTotalEntries)->count();
								echo $totalEntriesEvent;
								?>
								</td>
								
                                <td data-title="Judges">
								<?php
								// to get judges register for this
								$judgeNamesArr = array();
								
								// first to get all registrations for this convseason, conv and season
								$condConvreg = array();
								$condConvreg[] = "(Conventionregistrations.conventionseason_id = '".$datarecord->conventionseasons_id."')";
								$condConvreg[] = "(Conventionregistrations.convention_id = '".$datarecord->convention_id."')";
								$condConvreg[] = "(Conventionregistrations.season_id = '".$datarecord->season_id."')";
								$condConvreg[] = "(Conventionregistrations.status = '1')";
								$allConvReg = $this->Conventionregistrations->find()->where($condConvreg)->contain(['Users'])->all();
								foreach($allConvReg as $convreg)
								{
									if(!empty($convreg->judges_event_ids))
									{
										$judges_event_ids_explode = explode(",",$convreg->judges_event_ids);
										if(in_array($datarecord->event_id,$judges_event_ids_explode))
										{
											$judgeNamesArr[] = $convreg->Users['first_name'].' '.$convreg->Users['last_name'];
										}
									}
								}
								
								if(count($judgeNamesArr))
								{
									echo implode(", ", $judgeNamesArr);
								}
								?>
								</td>
								
								<td data-title="Judging">
									<?php
									if($totalEntriesEvent>0 && count($judgeNamesArr) >0)
									{
										if($datarecord->judging_ends == 1)
										{
											echo 'Closed';
										}
										else
										{
											echo 'Open';
											echo '<br />';
											
											if($datarecord->Events['event_judging_type'] == 'times')
											{
												$actionClose = 'closejudgingtimes';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'distances')
											{
												$actionClose = 'closejudgingdistances';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'scores')
											{
												$actionClose = 'closejudgingscores';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'soccer_kick')
											{
												$actionClose = 'closejudgingsoccerkick';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'spellings')
											{
												$actionClose = 'closejudgingspellings';
											}
											else
											{
												$actionClose = 'closejudging';
											}
											
											echo $this->Html->link('<i class="fa fa-close"></i> Close', ['controller' => 'results', 'action' => $actionClose,$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'Close Judging', 'class'=>'btn btn-warning btn-xs', 'confirm' => 'Are you sure you want to close judging for this event? This action cannot be undone ?']);
											
											
										}
									}
									?>
								</td>
								
								<td data-title="Qualifying Score/time">
								<?php
								//echo $datarecord->Events['event_judging_type'];
								if($datarecord->Events['event_judging_type'] == 'scores' || $datarecord->Events['event_judging_type'] == 'times' || $datarecord->Events['event_judging_type'] == 'distances')
								{
									// Allow to enter qualifying data
									echo $this->Html->link('<i class="fa fa-arrows"></i> Qualifying Data', ['controller' => 'conventions', 'action' => 'qualifyingdata',$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'Qualifying data', 'class'=>'btn btn-primary btn-xs']);
								}
								?>
								</td>
								
								<td data-title="Result">
									<?php
									if($datarecord->judging_ends == 1)
									{
										if($totalEntriesEvent>0 && count($judgeNamesArr) >0)
										{
											if($datarecord->Events['event_judging_type'] == 'times')
											{
												$actionResults = 'resulttimes';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'distances')
											{
												$actionResults = 'resultdistances';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'scores')
											{
												$actionResults = 'resultscores';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'soccer_kick')
											{
												$actionResults = 'resultsoccerkick';
											}
											else
											if($datarecord->Events['event_judging_type'] == 'spellings')
											{
												$actionResults = 'resultspellings';
											}
											else
											{
												$actionResults = 'index';
											}
											
											
											echo $this->Html->link('<i class="fa fa-pencil"></i> Result', ['controller' => 'results', 'action' => $actionResults,$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'View Results', 'class'=>'btn btn-primary btn-xs']);
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

        <div class="search_frm" style="display:none;">
            <button type="button" name="chkRecordId" onclick="checkAll(true);"  class="btn btn-info">Select All</button>
            <button type="button" name="chkRecordId" onclick="checkAll(false);" class="btn btn-info">Unselect All</button>
            <?php
            $arr = array(
                "" => "Action for selected record",
                'Activate' => "Activate",
                'Deactivate' => "Deactivate",
                //'Delete' => "Delete",
            );
            ?>
            <div class="list_sel"><?php echo $this->Form->control('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
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