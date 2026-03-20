<?php
use Cake\ORM\TableRegistry;
$this->Users 				= TableRegistry::getTableLocator()->get('Users');
$this->Judgeevaluations 	= TableRegistry::getTableLocator()->get('Judgeevaluations');
$this->Crstudentevents 		= TableRegistry::getTableLocator()->get('Crstudentevents');
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$eventsubmissions->isEmpty()) { ?> 
    <div class="panel-body">
        
        <?php echo $this->Form->create('Conventionregistrations', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

            <div class="tbl-resp-listing">
                <table id="group_events_table" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th class="sorting_paging">
							<?php
							if($eventD->group_event_yes_no == 1)
							{
								echo 'Group';
							}
							else
							{
								echo "Student Name";
							}
							?>
							</th>
							<th class="sorting_paging">School</th>
							<th class="sorting_paging">Submission Date</th>
							
							<th class="sorting_paging">1st Attempt</th>
							<th class="sorting_paging">2nd Attempt</th>
							<th class="sorting_paging">3rd Attempt</th>
							
							<th class="sorting_paging">Withdrawn</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$cntrES = 0;
						foreach($eventsubmissions as $datarecord)
						{
							$cntrES++;
							
						?>
						<tr>
						<td data-title="Student Name / Group">
						<?php
						if($eventD->group_event_yes_no == 1)
						{
							echo $datarecord->group_name.'<br>';
							
							// now fetch all name of students of this group
							//echo '--'.$datarecord->id;
							$condAllUGroup = array();
							$condAllUGroup[] = "(Crstudentevents.conventionregistration_id = '".$datarecord->conventionregistration_id."' AND Crstudentevents.event_id = '".$datarecord->event_id."' AND Crstudentevents.group_name = '".$datarecord->group_name."')";
							
							$listAllUGroup = $this->Crstudentevents->find()->where($condAllUGroup)->contain(["Students"])->limit(4)->all();
							
							$arrNG = array();
							foreach($listAllUGroup as $datamembgroup)
							{
								$nameV =  $datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name'];
								$arrNG[] = $nameV;
							}
							
							echo '('.implode(", ",$arrNG).')';
						?>
						
							<a title="View all members of group" href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal_<?php echo $datarecord->id; ?>" style="width:45px;">
								  <i class="fa fa-eye"></i>
							</a>
							<div class="modal fade" id="exampleModal_<?php echo $datarecord->id; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
							  <div class="modal-dialog">
								<div class="modal-content">
								
								  <div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">Group <?php echo $datarecord->group_name; ?></h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								  </div>
								  
								  <div class="modal-body">
									
									<table class="table">
										<thead>
											<tr>
												<th scope="col">#</th>
												<th scope="col">Name</th>
											</tr>
										</thead>
										<tbody>
											<?php
											// now fetch all students of this group
											$listAllUGroup = $this->Crstudentevents->find()->where($condAllUGroup)->contain(["Students"])->all();
											$cntrMM = 1;
											foreach($listAllUGroup as $datamembgroup)
											{
											?>
											<tr>
												<td scope="row"><?php echo $cntrMM; ?></td>
												<td colspan="2"><?php echo $datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name']; ?></td>
											</tr>
											<?php
											$cntrMM++;
											}
											?>
										</tbody>
									</table>
									
								  </div>
								
								</div>
							  </div>
							</div>
						
						<?php
							
							
						}
						else
						{
							echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
						}
						?>
						</td>
						<td data-title="School"><?php echo $datarecord->Users['first_name']; ?></td>
						<td data-title="Submission Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
						
						<?php
						$judge_id 	= $this->request->getSession()->read("user_id");
						
						$condCheckJudging = array();
						$condCheckJudging[] = "(Judgeevaluations.eventsubmission_id = '".$datarecord->id."')";
						$condCheckJudging[] = "(Judgeevaluations.uploaded_by_user_id = '".$judge_id."')";
						
						$getJudgeEvalData = $this->Judgeevaluations->find()->where($condCheckJudging)->first();
						
						// to check withdrawn
						if($getJudgeEvalData->withdraw_yes_no == 1)
							$checkedWithdr = 'checked';
						else
							$checkedWithdr = '';
						?>
						
						<td data-title="Distance 1st Attempt">
						<input type="text" name="distance_attempt_1_<?php echo $cntrES; ?>" id="distance_attempt_1_<?php echo $cntrES; ?>" value="<?php echo $getJudgeEvalData->distance_attempt_1; ?>" />
						</td>
						
						<td data-title="Distance 2nd Attempt">
						<input type="text" name="distance_attempt_2_<?php echo $cntrES; ?>" id="distance_attempt_2_<?php echo $cntrES; ?>" value="<?php echo $getJudgeEvalData->distance_attempt_2; ?>" />
						</td>
						
						<td data-title="Distance 3rd Attempt">
						<input type="text" name="distance_attempt_3_<?php echo $cntrES; ?>" id="distance_attempt_3_<?php echo $cntrES; ?>" value="<?php echo $getJudgeEvalData->distance_attempt_3; ?>" />
						</td>
						
						<td data-title="Withdrawn">
						<input type="checkbox" name="withdrawn_<?php echo $cntrES; ?>" id="withdrawn_<?php echo $cntrES; ?>" <?php echo $checkedWithdr; ?> />
						
						<input type="hidden" name="submission_id_<?php echo $cntrES; ?>" id="submission_id_<?php echo $datarecord->id; ?>" value="<?php echo $datarecord->id; ?>" />
						</td>
					</tr>
						<?php } ?>
						
					</tbody>
					
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>
						<input type="submit" value="Submit" class="btn btn-primary" />
						<input type="hidden" name="total_records" id="total_records" value="<?php echo $cntrES; ?>" />
						</td>
						<td></td>
					</tr>
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

<style>
.moretext {display: none;}
</style>





