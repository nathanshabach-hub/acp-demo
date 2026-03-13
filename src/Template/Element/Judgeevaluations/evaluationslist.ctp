<?php
use Cake\ORM\TableRegistry;
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$this->Judgeevaluations = TableRegistry::get('Judgeevaluations');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$judgeevaluations->isEmpty()) { ?> 
    <div class="panel-body">
        
        <?php echo $this->Form->create('Eventsubmissions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

            <div class="tbl-resp-listing">
                <table id="group_events_table" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th class="sorting_paging"># Evaluation</th>
							<th class="sorting_paging">Judge</th>
							<th class="sorting_paging">Marks</th>
							<th class="sorting_paging">Date</th>
							<th class="sorting_paging"><i class=" fa fa-gavel"></i> Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($judgeevaluations as $datarecord) { ?>
						<tr>
						<td data-title="# Evaluation"><?php echo $datarecord->id;?></td>
						<td data-title="Judge"><?php echo $datarecord->Judge['first_name'].' '.$datarecord->Judge['last_name'];?></td>
						<td data-title="Marks">
						<?php
						/* if($datarecord->did_not_attend == 0)
							echo "$datarecord->total_marks_obtained/$datarecord->total_marks_possible";
						else
							echo 'Did not attend'; */
						
						
						
						if($datarecord->Events['event_judging_type'] == 'times')
						{
							//
							if($datarecord->time_score != NULL && !empty($datarecord->time_score))
							{
								$tScore = $datarecord->time_score;
								$tScoreC = $tScore->format('H:i:s.u');
								
								// now remove padded zeros
								if (strpos($tScoreC, '.') !== false) {
								list($hms, $micro) = explode('.', $tScoreC);
								$micro = rtrim($micro, '0'); // remove trailing zeros

								if ($micro === '') {
									$formattedTime = $hms;
								} else {
									$formattedTime = $hms . '.' . $micro;
								}
								} else {
									$formattedTime = $tScoreC;
								}
								
								echo $formattedTime;
							}
							//
						}
						else
						if($datarecord->Events['event_judging_type'] == 'distances')
						{
							echo $datarecord->distance_score;
						}
						else
						if($datarecord->Events['event_judging_type'] == 'scores')
						{
							echo $datarecord->all_pos_score;
						}
						else
						if($datarecord->Events['event_judging_type'] == 'soccer_kick')
						{
							echo $datarecord->soccer_kick_best_kick.'m';
						}
						else
						if($datarecord->Events['event_judging_type'] == 'spellings')
						{
							echo $datarecord->spelling_score;
						}
						else
						if($datarecord->did_not_attend == 0)
						{
							echo "$datarecord->total_marks_obtained/$datarecord->total_marks_possible";
						}
						else
						if($datarecord->did_not_attend == 1)
						{
							echo "Did not attend";
						}
						
						
						?>
						</td>
						<td data-title="Date"><?php echo date("m/d/Y",strtotime($datarecord->created));?></td>
						
						
						<td data-title="Action">
							<?php
							if($userDetails->user_type == "School" && $datarecord->did_not_attend == 0 && $datarecord->Events['event_judging_type'] != 'times')
							{
								echo $this->Html->link('<i class="fa fa-eye"></i>', ['controller' => 'judgeevaluations', 'action' => 'viewevaluationdetails',$event_submission_slug,$datarecord->slug], [ 'escape' => false, 'title' => 'View Evaluation Questions', 'class'=>'']);
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
	order: [[6, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#group_events_table').dataTable.search(this.value).draw();
    }); */
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





