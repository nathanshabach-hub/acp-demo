<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
  
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Divisions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				Judges
				
				
				</div> 
            </div>   

            <div class="tbl-resp-listing">
                <table id="convention_judges" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Judge Name</th>
                            <th class="sorting_paging">Judge Email</th>
                            <th class="sorting_paging">Total Events</th>
                            <th class="sorting_paging">Event(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($judgeslist as $datarecord)
						{		
							if($datarecord->Users['user_type'] == 'Judge' || ($datarecord->Users['user_type'] == 'Teacher_Parent' && $datarecord->Users['is_judge'] == 1))
							{
								$totalEvents = 0;
								$eventsList = 'N/A';
								if(!empty($datarecord->judges_event_ids) && $datarecord->judges_event_ids != NULL)
								{
									// to count entries for this event
									$condJudgeEvents = array();
									$condJudgeEvents[] = "(Events.id IN (".$datarecord->judges_event_ids.") )";
									$judgeEvents = $this->Events->find()->where($condJudgeEvents)->order(["Events.event_name" => 'ASC'])->all();
									$arrJE = array();
									foreach($judgeEvents as $judgeev)
									{
										$arrJE[] = $judgeev->event_name.' ('.$judgeev->event_id_number.')';
									}
									if(count($arrJE))
									{
										$eventsList = implode(", ",$arrJE);
										$totalEvents = count($arrJE);
									}
								}
						?>
                            <tr>
                                <td data-title="Judge Name">
								
								<?php
								$jName = $datarecord->Users['first_name'].' '.$datarecord->Users['last_name'];
								echo $this->Html->link($jName, ['controller' => 'conventions', 'action' => 'judgesevents',$datarecord->slug], [ 'escape' => false, 'title' => 'View Events of Judges', 'class'=>'btn btn-primary btn-xs']);
								
								//echo $datarecord->Users['first_name'].' '.$datarecord->Users['last_name'];?>
								
								</td>
                                <td data-title="Judge Email"><?php echo $datarecord->Users['email_address'];?></td>
                                <td data-title="Total Events"><?php echo $totalEvents;?></td>
								<td data-title="Event(s)"><?php echo $eventsList; ?></td>
                                
                            </tr>
							<?php } } ?>
                    </tbody>
                </table>
            </div>
        </section>

        
         
        <?php echo $this->Form->end(); ?>
    
    </div>

<script>
$(document).ready(function() {
$('#convention_judges').dataTable({
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
        $('#convention_judges').dataTable.search(this.value).draw();
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