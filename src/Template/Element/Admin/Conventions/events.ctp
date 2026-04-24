<?php
use Cake\ORM\TableRegistry;
$this->Conventionregistrations = TableRegistry::getTableLocator()->get('Conventionregistrations');
$this->Eventsubmissions = TableRegistry::getTableLocator()->get('Eventsubmissions');
$this->Conventionseasonroomevents = TableRegistry::getTableLocator()->get('Conventionseasonroomevents');
?>
<style>
.events-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 18px;
}
.events-header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.events-stat-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eaf7ee;
    color: #2d7a46;
    border: 1px solid #c3e6cb;
    border-radius: 20px;
    padding: 6px 14px;
    font-size: 13px;
    font-weight: 600;
}
.events-stat-badge i {
    font-size: 14px;
}
#convention_events_wrapper .dataTables_filter {
    margin-bottom: 10px;
}
#convention_events {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #dee2e8;
}
#convention_events thead th {
    background: #f0f3f8;
    color: #3c4858;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 10px 12px;
    border-bottom: 2px solid #d1d9e6;
    white-space: nowrap;
}
#convention_events tbody td {
    padding: 10px 12px;
    vertical-align: middle;
    font-size: 13px;
    border-bottom: 1px solid #eef0f4;
}
#convention_events tbody tr:hover {
    background: #f7f9fc;
}
#convention_events tbody tr:last-child td {
    border-bottom: none;
}
.event-name-cell {
    font-weight: 600;
    color: #1f2d3d;
}
.event-type-tag {
    display: inline-block;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 2px 8px;
    border-radius: 10px;
    background: #e8edf3;
    color: #5a6a7e;
}
.event-type-tag.academic { background: #e3f2fd; color: #1565c0; }
.event-type-tag.sports { background: #fce4ec; color: #c62828; }
.event-type-tag.arts { background: #f3e5f5; color: #7b1fa2; }
.room-tag {
    display: inline-block;
    background: #f5f6fa;
    border: 1px solid #dde1ea;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 11px;
    color: #5a6a7e;
    margin: 1px 2px;
}
.judge-name-tag {
    display: inline-block;
    background: #fff8e1;
    border: 1px solid #ffe082;
    border-radius: 4px;
    padding: 2px 6px;
    font-size: 11px;
    color: #795548;
    margin: 1px 2px;
}
.status-open {
    display: inline-block;
    background: #e8f5e9;
    color: #2e7d32;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 10px;
    text-transform: uppercase;
}
.status-closed {
    display: inline-block;
    background: #fce4ec;
    color: #c62828;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 10px;
    text-transform: uppercase;
}
.result-actions {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
}
.entries-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 24px;
    background: #e3f2fd;
    color: #1565c0;
    font-weight: 700;
    font-size: 12px;
    border-radius: 12px;
    padding: 0 8px;
}
.entries-count.zero {
    background: #f5f5f5;
    color: #999;
}
</style>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionseasonevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Divisions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="events-header-bar">
                <div class="events-header-left">
                    <span class="events-stat-badge"><i class="fa fa-calendar-check-o"></i> <?php echo $totalEventsConventions; ?> Event<?php echo $totalEventsConventions != 1 ? 's' : ''; ?></span>
                    <?php echo $this->Html->link('<i class="fa fa-toggle-left"></i> Back to Seasons', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false, 'class'=>'btn btn-default btn-sm']);?>
                    <?php echo $this->Html->link('<i class="fa fa-refresh"></i> Reset Event List', ['controller'=>'conventions', 'action' => 'reseteventlist',$slug_convention_season,$slug_convention], ['escape'=>false, 'class'=>'btn btn-danger btn-sm', 'confirm' => 'Are you sure you want to reset event list for this convention? This will delete all events for this convention & selected season ?']); ?>
                </div>
            </div>

            <div class="tbl-resp-listing">
                <table id="convention_events" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf">
                        <tr>
                            <th>#</th>
							<th>Event Name</th>
							<th>Type</th>
							<th>Rooms</th>
							<th>Entries</th>
							<th>Judge(s)</th>
							<th>Judging</th>
							<th>Qualifying</th>
							<th>Result</th>
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
                                <td><?php echo $datarecord->Events['event_id_number'];?></td>
                                <td class="event-name-cell"><?php echo $datarecord->Events['event_name'];?></td>
                                <td><?php
								$evtType = $datarecord->Events['event_type'];
								$typeClass = '';
								if(stripos($evtType, 'academic') !== false || stripos($evtType, 'spelling') !== false || stripos($evtType, 'science') !== false) $typeClass = 'academic';
								elseif(stripos($evtType, 'sport') !== false || stripos($evtType, 'athletic') !== false) $typeClass = 'sports';
								elseif(stripos($evtType, 'art') !== false || stripos($evtType, 'music') !== false) $typeClass = 'arts';
								?><span class="event-type-tag <?php echo $typeClass; ?>"><?php echo $eventTypeDD[$evtType];?></span></td>
								
                                <td>
								<?php 
								if(count($roomArrCSEvent))
								{
									foreach($roomArrCSEvent as $roomName) {
										echo '<span class="room-tag">' . $roomName . '</span>';
									}
								}
								else
								{
									echo '<span style="color:#ccc;">—</span>';
								}
								?></td>
                                
								<td>
								<?php
								$condTotalEntries = array();
								$condTotalEntries[] = "(Eventsubmissions.conventionseason_id = '".$datarecord->conventionseasons_id."')";
								$condTotalEntries[] = "(Eventsubmissions.convention_id = '".$datarecord->convention_id."')";
								$condTotalEntries[] = "(Eventsubmissions.season_id = '".$datarecord->season_id."')";
								$condTotalEntries[] = "(Eventsubmissions.event_id = '".$datarecord->Events['id']."')";
								$totalEntriesEvent = $this->Eventsubmissions->find()->where($condTotalEntries)->count();
								?><span class="entries-count <?php echo $totalEntriesEvent == 0 ? 'zero' : ''; ?>"><?php echo $totalEntriesEvent; ?></span>
								</td>
								
                                <td>
								<?php
								$judgeNamesArr = array();
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
									foreach($judgeNamesArr as $jn) {
										echo '<span class="judge-name-tag">' . $jn . '</span>';
									}
								}
								else
								{
									echo '<span style="color:#ccc;">—</span>';
								}
								?>
								</td>
								
								<td>
									<?php
									if($totalEntriesEvent>0 && count($judgeNamesArr) >0)
									{
										if($datarecord->judging_ends == 1)
										{
											echo '<span class="status-closed"><i class="fa fa-lock"></i> Closed</span>';
										}
										else
										{
											echo '<span class="status-open"><i class="fa fa-unlock"></i> Open</span>';
											
											if($datarecord->Events['event_judging_type'] == 'times')
											{
												$actionClose = 'closejudgingtimes';
											}
											else if($datarecord->Events['event_judging_type'] == 'distances')
											{
												$actionClose = 'closejudgingdistances';
											}
											else if($datarecord->Events['event_judging_type'] == 'scores')
											{
												$actionClose = 'closejudgingscores';
											}
											else if($datarecord->Events['event_judging_type'] == 'soccer_kick')
											{
												$actionClose = 'closejudgingsoccerkick';
											}
											else if($datarecord->Events['event_judging_type'] == 'spellings')
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
									else
									{
										echo '<span style="color:#ccc;">—</span>';
									}
									?>
								</td>
								
								<td>
								<?php
								if($datarecord->Events['event_judging_type'] == 'scores' || $datarecord->Events['event_judging_type'] == 'times' || $datarecord->Events['event_judging_type'] == 'distances')
								{
									echo $this->Html->link('<i class="fa fa-arrows"></i> Qualifying', ['controller' => 'conventions', 'action' => 'qualifyingdata',$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'Qualifying data', 'class'=>'btn btn-primary btn-xs']);
								}
								else
								{
									echo '<span style="color:#ccc;">—</span>';
								}
								?>
								</td>
								
								<td>
									<div class="result-actions">
									<?php
									$_resultsReleased = (int)$datarecord->results_released;
									if($_resultsReleased == 1)
									{
										if($datarecord->Events['event_judging_type'] == 'times')
										{
											$actionResults = 'resulttimes';
										}
										else if($datarecord->Events['event_judging_type'] == 'distances')
										{
											$actionResults = 'resultdistances';
										}
										else if($datarecord->Events['event_judging_type'] == 'scores')
										{
											$actionResults = 'resultscores';
										}
										else if($datarecord->Events['event_judging_type'] == 'soccer_kick')
										{
											$actionResults = 'resultsoccerkick';
										}
										else if($datarecord->Events['event_judging_type'] == 'spellings')
										{
											$actionResults = 'resultspellings';
										}
										else
										{
											$actionResults = 'index';
										}
										echo '<span class="status-closed"><i class="fa fa-lock"></i> Released</span>';
										echo $this->Html->link('<i class="fa fa-pencil"></i> Result', ['controller' => 'results', 'action' => $actionResults,$slug_convention_season,$slug_convention,$datarecord->Events['slug']], [ 'escape' => false, 'title' => 'View Results', 'class'=>'btn btn-primary btn-xs']);
										echo $this->Html->link('<i class="fa fa-undo"></i> Open', ['controller' => 'conventions', 'action' => 'openresults',$slug_convention_season,$slug_convention,$datarecord->Events['id']], [ 'escape' => false, 'title' => 'Re-open Results', 'class'=>'btn btn-success btn-xs', 'confirm' => 'Are you sure you want to re-open results for this event?']);
									}
									else if($datarecord->judging_ends == 1 && $totalEntriesEvent>0 && count($judgeNamesArr) >0)
									{
										echo '<span class="status-open"><i class="fa fa-unlock"></i> Pending</span>';
										echo $this->Html->link('<i class="fa fa-close"></i> Close', ['controller' => 'conventions', 'action' => 'closeresults',$slug_convention_season,$slug_convention,$datarecord->Events['id']], [ 'escape' => false, 'title' => 'Close Results', 'class'=>'btn btn-warning btn-xs', 'confirm' => 'Are you sure you want to close/release results for this event?']);
									}
									else
									{
										echo '<span style="color:#ccc;">—</span>';
									}
									?>
									</div>
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