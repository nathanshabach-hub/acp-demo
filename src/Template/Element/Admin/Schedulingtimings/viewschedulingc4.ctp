<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$schedulingTimingsList->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Divisions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				Schedule Category :: 4 (Needs Schedule=Yes || Group Event=No || Event Kind ID=Sequential || Has To Be Consecutive=Yes)
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
							<th class="sorting_paging">Match</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($schedulingTimingsList as $datarecord)
						{
							$isUnscheduled = ($datarecord->day === null || $datarecord->start_time === null);
							$rowStyle = $isUnscheduled ? ' style="background:#ffe6e6;"' : '';
						?>
                            <?php //pr($datarecord); exit;?> 
                            <tr data-schedule-id="<?php echo (int)$datarecord->id; ?>"<?php echo $rowStyle; ?>>
                                <td data-title="DB ID"><?php echo $datarecord->id;?></td>
                                <td data-title="Room"><?php echo !empty($datarecord->Conventionrooms) ? $datarecord->Conventionrooms['room_name'] : '<span class="label label-danger">No Room</span>';?></td>
                                <td data-title="Day"><?php echo $datarecord->day !== null ? $datarecord->day : '<span class="label label-warning">Unscheduled</span>';?></td>
                                <td data-title="Start">
								<?php 
								echo $datarecord->start_time !== null ? date("H:i A",strtotime($datarecord->start_time)) : '-';
								?>
								</td>
                                <td data-title="Finish">
								<?php 
								echo $datarecord->finish_time !== null ? date("H:i A",strtotime($datarecord->finish_time)) : '-';
								?>
								</td>
                                <td data-title="Event"><?php echo !empty($datarecord->Events) ? $datarecord->Events['event_name'].' ('.$datarecord->Events['event_id_number'].')' : '<span class="label label-danger">Unknown Event</span>';?></td>
                                <td data-title="Match">
								<?php
								if (!empty($datarecord->Users)) {
									echo $datarecord->Users['first_name'].' '.$datarecord->Users['middle_name'].' '.$datarecord->Users['last_name'];
								} else {
									echo '<span class="label label-danger">Unknown</span>';
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