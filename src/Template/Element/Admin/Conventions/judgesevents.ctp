<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
$this->Eventsubmissions = TableRegistry::getTableLocator()->get('Eventsubmissions');
$this->Judgeevaluations = TableRegistry::getTableLocator()->get('Judgeevaluations');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
  
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Divisions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				Events List
				</div> 
            </div>   

            <div class="tbl-resp-listing">
                <table id="judges_events" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Event Name</th>
                            <th class="sorting_paging">Event ID Number</th>
                            <th class="sorting_paging">Total Submissions</th>
                            <th class="sorting_paging">Submitted Submissions</th>
                            <th class="sorting_paging">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($judges_event_ids as $event_id)
						{		
							// to get event details
							$eventD = $this->Events->find()->where(["Events.id" =>$event_id])->first();
							
							// Count total submissions
							$condCntrTotalSub = array();
							$condCntrTotalSub[] = "(Eventsubmissions.event_id = '".$event_id."')";
							$condCntrTotalSub[] = "(Eventsubmissions.conventionseason_id = '".$conventionRegD->conventionseason_id."')";
							$totalSubmissions = $this->Eventsubmissions->find()->where($condCntrTotalSub)->count();
							
							// Now check how many evaluations done by this Judge
							$condCntrEvalDone = array();
							$condCntrEvalDone[] = "(Judgeevaluations.event_id = '".$event_id."')";
							$condCntrEvalDone[] = "(Judgeevaluations.conventionseason_id = '".$conventionRegD->conventionseason_id."')";
							$condCntrEvalDone[] = "(Judgeevaluations.uploaded_by_user_id = '".$conventionRegD->user_id."')";
							$totalEvalDone = $this->Judgeevaluations->find()->where($condCntrEvalDone)->count();
							
						?>
                            <tr>
                                <td data-title="Event Name"><?php echo $eventD->event_name; ?></td>
                                <td data-title="Event ID Number"><?php echo $eventD->event_id_number; ?></td>
                                <td data-title="Total Submissions"><?php echo $totalSubmissions; ?></td>
                                <td data-title="Submitted Submissions"><?php echo $totalEvalDone; ?></td>
                                <td data-title="Status">
								<?php
								if($totalSubmissions>0 && ($totalEvalDone == $totalSubmissions))
								{
									echo '<span style="font-size:1em;color:green;"><i class="fa fa-check" aria-hidden="true"></i></span>';
								}
								else
								{
									echo '<span style="font-size:1em;color:red;"><i class="fa fa-times" aria-hidden="true"></i></span>';
								}
								
								// To remind them when/which events still need to be judged
								echo $this->Html->link('<i class="fa fa-envelope"></i>', ['controller' => 'conventions', 'action' => 'sendremindertojudge',$conv_reg_slug,$eventD->slug], [ 'escape' => false, 'title' => 'Send reminder to Judge', 'class'=>'', 'confirm' => 'Are you sure you want to send reminder to judge for this event ?', 'style' => 'margin-left:8px;']);
								?>
								</td>
                                
                            </tr>
							<?php }?>
                    </tbody>
                </table>
            </div>
        </section>

        
         
        <?php echo $this->Form->end(); ?>
    
    </div>

<script>
$(document).ready(function() {
$('#judges_events').dataTable({
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
        $('#judges_events').dataTable.search(this.value).draw();
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