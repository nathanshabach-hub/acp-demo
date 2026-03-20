<?php
use Cake\ORM\TableRegistry;
$this->Eventsubmissions = TableRegistry::getTableLocator()->get('Eventsubmissions');
$this->Judgeevaluations = TableRegistry::getTableLocator()->get('Judgeevaluations');


?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php //if (!$conventionseasonevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(NULL, ['id' => 'addresults', 'type' => 'file', 'class' => ' ']); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left"> Total Entries for this event: <?php echo count($eventSubmissionsCS); ?>
				</div>  
            </div>   

            <div class="tbl-resp-listing">
                <table id="results_table" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Position</th>
                            <th class="sorting_paging">Average Marks</th>
                            <th class="sorting_paging">Points Obtained</th>
                            <th class="sorting_paging">Edit Position</th>
                            <th class="sorting_paging">Edit Average Marks</th>
                            <th class="sorting_paging">School</th>
                            <th class="sorting_paging">Student/Group</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach($eventSubmissionsCS as $datarecord)
						{
							// check how many judges judged this entry and get average
							$condAvg = array();
							$condAvg[] 	= "(Judgeevaluations.eventsubmission_id = '".$datarecord->id."')";
							$condAvg[] 	= "(Judgeevaluations.convention_id = '".$datarecord->convention_id."')";
							$condAvg[] 	= "(Judgeevaluations.season_id = '".$datarecord->season_id."')";
							$condAvg[] 	= "(Judgeevaluations.event_id = '".$eventD->id."')";
							$judgeEvals = $this->Judgeevaluations->find()->where($condAvg)->all();
							
							$marksObtained = 0;
							$cntrJudging = 0;
							foreach($judgeEvals as $judgeeval)
							{
								$marksObtained = $marksObtained+$judgeeval->total_marks_obtained;
								$cntrJudging++;
							}
							
							if($cntrJudging>0)
							{
								$avgMarksSub = $marksObtained/$cntrJudging;
							}
							else
							{
								$avgMarksSub = 0;
							}
							
							$positionSub = 0;
							
							if($avgMarksSub>90)
							{
								$positionSub = 1;
							}
							else
							if($avgMarksSub<=90 && $avgMarksSub>80)
							{
								$positionSub = 2;
							}
							else
							if($avgMarksSub<=80 && $avgMarksSub>70)
							{
								$positionSub = 3;
							}
							
							// to see that if already saved results
							if($checkResultsAlready->id >0)
							{
								$positionSub 		= $arrAlreadySavedResults[$datarecord->id]['position'];
								$avgMarksSub 		= $arrAlreadySavedResults[$datarecord->id]['avg_marks'];
								$pointsObtained 	= $arrAlreadySavedResults[$datarecord->id]['points_obtained'];
							}
							
						?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Position">
									<!--<input type="number" name="result_position_<?php echo $datarecord->id; ?>" id="result_position_<?php echo $datarecord->id; ?>" value="<?php echo $positionSub;?>" />-->
									<?php echo $positionSub;?>
								</td>
								<td data-title="Average Marks">
									<!--<input type="number" name="result_avg_marks_<?php echo $datarecord->id; ?>" id="result_avg_marks_<?php echo $datarecord->id; ?>" value="<?php echo $avgMarksSub;?>" />-->
									<?php echo $avgMarksSub;?>
								</td>
								<td data-title="Points Obtained">
									<?php echo $pointsObtained;?>
								</td>
								
								<td data-title="Edit Position">
									<input type="number" name="result_position_<?php echo $datarecord->id; ?>" id="result_position_<?php echo $datarecord->id; ?>" value="<?php echo $positionSub;?>" />
								</td>
								<td data-title="Edit Average Marks">
									<input type="number" name="result_avg_marks_<?php echo $datarecord->id; ?>" id="result_avg_marks_<?php echo $datarecord->id; ?>" value="<?php echo $avgMarksSub;?>" />
								</td>
								
                                <td data-title="School"><?php echo $datarecord->Users['first_name']; ?></td>
                                <td data-title="Student/Group">
								<?php 
								if($datarecord->student_id>0)
								{
									echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
								}
								else
								if(!empty($datarecord->group_name))
								{
									echo $datarecord->group_name;
								}
								?>
								</td>
                            </tr>
                        <?php } ?>
						
							
                    </tbody>
                </table>
            </div>
        </section>
		
		<button type="submit" class="btn btn-success">Save</button>
		<?php
		if($checkResultsAlready->original_results_modified >0)
		{
			echo '<p></p><p style="color:red">Note: Original results already changed.</p>';
		}
		?>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php //} else { ?> 
<?php //}
?>

<script>
$(document).ready(function() {
$('#results_table').dataTable({
    "bPaginate": false,
    "bInfo": false,
    //"bLengthChange": false,
	//"pageLength": 500000,
	order: [[1, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#results_table').dataTable.search(this.value).draw();
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