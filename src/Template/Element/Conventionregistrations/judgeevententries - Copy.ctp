<?php
use Cake\ORM\TableRegistry;
$this->Users 				= TableRegistry::getTableLocator()->get('Users');
$this->Judgeevaluations 	= TableRegistry::getTableLocator()->get('Judgeevaluations');
$this->Crstudentevents 		= TableRegistry::getTableLocator()->get('Crstudentevents');
?>

<script>
function myFunction(record_id) { 
  var moreText = document.getElementById("more_"+record_id);
  var btnText = document.getElementById("myBtn_"+record_id);

  if (btnText.innerHTML == "Show") {
    //dots.style.display = "inline";
    btnText.innerHTML = "Hide"; 
    moreText.style.display = "block";
  } else {
    //dots.style.display = "none";
    btnText.innerHTML = "Show"; 
    moreText.style.display = "none";
  }
}
</script>

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
							<th class="sorting_paging">Context</th>
							<th class="sorting_paging">Submitted File</th>
							<th class="sorting_paging">Submission Date</th>
							<th class="sorting_paging">Guideline Breach</th>
							<th class="sorting_paging">Command Performance</th>
							<th class="sorting_paging">Completed</th>
							<th class="sorting_paging">Score</th>
							<th class="sorting_paging">Judges Evaluation</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($eventsubmissions as $datarecord) { ?>
						<tr>
						<td data-title="Student Name / Group">
						<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal_<?php echo $datarecord->id; ?>">
						  Launch Modal
						</button>
						
						<div class="modal fade" id="exampleModal_<?php echo $datarecord->id; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
						  <div class="modal-dialog">
							<div class="modal-content">
							
							  <div class="modal-header">
								<h5 class="modal-title" id="exampleModalLabel">Modal Title <?php echo $datarecord->id; ?></h5>
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							  </div>
							  
							  <div class="modal-body">
								This is the modal body content.
							  </div>
							
							</div>
						  </div>
						</div>
						
						<?php
						if($eventD->group_event_yes_no == 1)
						{
							echo $datarecord->group_name.'<br>';
							echo '<br /> <button class="btn btn-warning" type="button" onclick="myFunction('.$datarecord->id.')" id="myBtn_'.$datarecord->id.'">Show</button>';
							
							// now fetch all name of students of this group
							//echo '--'.$datarecord->id;
							$condAllUGroup = array();
							$condAllUGroup[] = "(Crstudentevents.conventionregistration_id = '".$datarecord->conventionregistration_id."' AND Crstudentevents.event_id = '".$datarecord->event_id."' AND Crstudentevents.group_name = '".$datarecord->group_name."')";
							
							$listAllUGroup = $this->Crstudentevents->find()->where($condAllUGroup)->contain(["Students"])->limit(4)->all();
							
							echo '<span style="display:none;" id="more_'.$datarecord->id.'">';
							
							foreach($listAllUGroup as $datamembgroup)
							{
								echo $datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name'];
								echo '<br />';
							}
							
							echo '</span>';

							
							
						}
						else
						{
							echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
						}
						?>
						</td>
						<td data-title="School"><?php echo $datarecord->Users['first_name']; ?></td>
						<td data-title="Context"><?php echo ($datarecord->context_box) ? $datarecord->context_box : "N/A"; ?></td>
						<td data-title="Submitted File">
						<?php
							$imgToShow = $datarecord->mediafile_file_system_name;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<a target="_blank" title="'.$datarecord->Events['upload_type'].'" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->report;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<br /><a style="color:#000;" target="_blank" title="Report" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->score_sheet;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<br /><a style="color:#000;" target="_blank" title="Score Sheet" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->additional_documents;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<br /><a style="color:#000;" target="_blank" title="Additional Documents" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						</td>
						<td data-title="Submission Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
						
						<td data-title="Guideline Breach">
							<?php
							if($datarecord->guideline_breach == 0)
							{
								echo $this->Html->link('Mark Breach', ['controller' => 'judgeevaluations', 'action' => 'markbreach',$datarecord->slug], [ 'escape' => false, 'title' => 'Mark Guideline Breach', 'class'=>'', 'confirm' => 'Are you sure you want to mark this entry as guideline breach?', 'class' => 'btn btn-info']);
							}
							else
							if($datarecord->guideline_breach == 1)
							{
								echo 'Awaiting action from admin';
							}
							else
							if($datarecord->guideline_breach == 2)
							{
								echo 'Approved By Admin';
							}
							?>
						</td>
						
						<td data-title="Command Performance">
							<?php
							if($datarecord->guideline_breach == 0)
							{
								if($datarecord->command_performance == 1)
								{
									echo 'Yes';
								}
								else
								{
									echo $this->Html->link('Mark Command', ['controller' => 'judgeevaluations', 'action' => 'markcommand',$datarecord->slug], [ 'escape' => false, 'title' => 'Mark Command Performance', 'class'=>'', 'confirm' => 'Are you sure you want to mark this entry as command performance?', 'class' => 'btn btn-success']);
								}
							}
							?>
						</td>
						
						<td data-title="Completed">
							<?php
							// to check if this entry judged by this judge or not
							$condCheckJudging = array();
							$condCheckJudging[] = "(Judgeevaluations.eventsubmission_id = '".$datarecord->id."')";
							$condCheckJudging[] = "(Judgeevaluations.uploaded_by_user_id = '".$this->request->getSession()->read("user_id")."')";
							$checkJudging = $this->Judgeevaluations->find()->where($condCheckJudging)->count();
							if($checkJudging>0)
							{
								echo '<i title="Entry judged by you" class="fa fa-check-circle"></i>';
							}
							?>
						</td>
						
						<td data-title="Score">
						<?php
						// to get score by judge
						$getScores = $this->Judgeevaluations->find()->where($condCheckJudging)->first();
						echo "$getScores->total_marks_obtained/$getScores->total_marks_possible";
						?>
						</td>
						
						
						
						<td data-title="Action">
						<?php
						if($datarecord->guideline_breach != 2)
						{
							echo $this->Html->link('<i class="fa fa-street-view"></i>', ['controller' => 'judgeevaluations', 'action' => 'addnew',$conv_reg_slug,$datarecord->slug], [ 'escape' => false, 'title' => 'Submit Evaluation', 'class'=>'', 'confirm' => 'Are you sure you want to submit evaluation for this entry?']);
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





