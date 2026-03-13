<?php
use Cake\ORM\TableRegistry;
$this->Evaluationtags 			= TableRegistry::get('Evaluationtags');
$this->Evaluationareas 			= TableRegistry::get('Evaluationareas');
$this->Evaluationcategories 	= TableRegistry::get('Evaluationcategories');
$this->Evaluationquestions 		= TableRegistry::get('Evaluationquestions');

$arrAlreadyData = array();
if($checkEvalJudge->id>0)
{	
	if(!empty($checkEvalJudge->division_ids))
	{
		$selected_division_ids_explode = explode(",",$checkEvalJudge->division_ids);
	}
	
	if(!empty($checkEvalJudge->tag_ids))
	{
		$selected_tag_ids_explode = explode(",",$checkEvalJudge->tag_ids);
	}
	
	foreach($checkEvalJudge->Judgeevaluationmarks as $evalmarks)
	{
		$arrAlreadyData[$evalmarks->question_id] = $evalmarks->question_marks_obtained;
	}
}
?>
<script type="text/javascript">
$(document).ready(function () {
	$("#judgingform").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			<h2 class="mt-3">Judging Form :: <?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)</h2>
			<?php echo $this->Html->link('<< Back', ['controller' => 'conventionregistrations', 'action' => 'judgeevententries',$conv_reg_slug,$eventD->slug], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			<!-- dashboard-section-1 start-->
			<?php echo $this->Form->create($judgingform, ['id'=>'judgingform', 'type' => 'file', 'class' =>'', 'autocomplete' =>'off']); ?>
				<div class="dasboard-section">
					<div class="dashboard-text">
						<h2>Judging Form</h2>
						<div class="classform-container">
							<h2>JUDGES FORM - <?php echo $evalFormD->name; ?></h2>
							<div class="checkboxrow fwbold">
								<div class="singlechecknox">
									<input type="checkbox" id="tag_open" name="division_ids[]" value="Open" <?php if(is_array($selected_division_ids_explode) && in_array("Open",$selected_division_ids_explode)) { echo 'checked'; } ?>>
									<label for="tag_open"> OPEN</label><br>
								</div>
								<div class="singlechecknox">
									<input type="checkbox" id="tag_collection" name="division_ids[]" value="Collection" <?php if(is_array($selected_division_ids_explode) && in_array("Collection",$selected_division_ids_explode)) { echo 'checked'; } ?>>
									<label for="Collection"> U/16 - Collection on only</label><br>
								</div>
								<div class="centertext">(Please <i class="fa fa-check"></i> the appropriate box)</div>
							</div>
							<?php
							if(!empty($evalFormD->tag_ids))
							{
							?>
							<div class="checkboxrow">
								<?php
								$tag_ids_explode = explode(",",$evalFormD->tag_ids);
								foreach($tag_ids_explode as $evaltag)
								{
									$tagD = $this->Evaluationtags->find()->where(['Evaluationtags.id' => $evaltag])->first();
									
									$checkedTag = '';
									if(is_array($selected_tag_ids_explode) && in_array($evaltag,$selected_tag_ids_explode))
									{
										$checkedTag = 'checked';
									}
								?>
								<div class="singlechecknox">
								<input type="checkbox" id="tag_<?php echo $tagD->id; ?>" name="tags[]" value="<?php echo $tagD->id; ?>" <?php echo $checkedTag; ?>>
									<label for="tag_<?php echo $tagD->id; ?>"> <?php echo $tagD->name; ?></label><br>
								</div>
								<?php
								}
								?>
							</div>
							<?php
							}
							?>
							
							<div class="tableheader">
								<div class="singlerow">
									<div class="namecolom">Name/Group : </div>
									<div class="inputfield"><?php echo $stGrpName; ?>
									<?php
									if(count($groupMembersList)>0)
									{
										echo "(".implode(", ",array_slice($groupMembersList,0,2)).")";
										
									?>
									<a title="View all members of group" href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal_show" style="width:45px;">
											<i class="fa fa-eye"></i>
										</a>
									<div class="modal fade" id="exampleModal_show" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
													$cntrMM = 1;
													foreach($groupMembersList as $datamembgroup)
													{
													?>
													<tr>
														<td scope="row"><?php echo $cntrMM; ?></td>
														<td colspan="2"><?php echo $datamembgroup; ?></td>
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
									?>
									
									
									</div>
									<div class="dobcolom">Birth Year : </div>
									<div class="inputfield smallinpout"><?php echo $studentBY ? $studentBY : 'N/A'; ?></div>
								</div>
								<div class="singlerow">
									<div class="namecolom">School : </div>
									<div class="inputfield"><?php echo $eventsubmissionD->Users['first_name']; ?></div>
									<div class="dobcolom">Cust Code : </div>
									<div class="inputfield smallinpout"><?php echo $eventsubmissionD->Users['customer_code']; ?></div>
								</div>
							</div>
							<p class="simpletext">(JUDGES! Please remember that items MUST be consistent with a Biblical Worldview)</p>
							<div class="EVALUATIONtable">
								<div class="headerparttable">
									<div class="seventycolom">AREAS OF EVALUATION</div>
									<div class="thirtyycolom">
										<div class="fullcom">POINTS</div>
										<div class="halfcom borderright">POSSIBLE</div>
										<div class="halfcom">AWARDED</div>
									</div>
								</div>
								<?php
								// to fetch evaluation form areas
								$totalPossiblePoints = 0;
								$cntrCat=1;
								$cntrQuestOuter=1;
								$evalFormAreas = $this->Evaluationareas->find()->where(['Evaluationareas.evaluationform_id' => $evalFormD->id,'Evaluationareas.evaluationcategory_id !=' => 16])->order(["Evaluationareas.id" => "ASC"])->contain(["Evaluationcategories"])->all();
								foreach($evalFormAreas as $evalformarea)
								{
									$evaluationquestion_ids = $evalformarea->evaluationquestion_ids;
									
								?>
								<div class="subjectcolom">
									<div class="fulcolm">
										<?php echo $romanNumbers[$cntrCat]; ?>. <?php echo $evalformarea->Evaluationcategories['name']; ?>
									</div>
									<?php
									$cntrQ=1;
									$condQList = array();
									$condQList[] = "( Evaluationquestions.id IN ($evaluationquestion_ids) )";
									
									$questionList = $this->Evaluationquestions->find()->where($condQList)->order(["Evaluationquestions.id" => "ASC"])->all();
									
									foreach($questionList as $evalquestion)
									{
										$totalPossiblePoints = $totalPossiblePoints+$evalquestion->max_points;
									?>
									<div class="wraprow">
										<div class="seventycolom"><?php echo $alphabetArr[$cntrQ-1]; ?>. <?php echo $evalquestion->question; ?></div>
										<div class="thirtyycolom">
											<div class="halfcom"><?php echo $evalquestion->max_points; ?></div>
											<div class="halfcom">
												<input type="text" name="question_marks_obtained_<?php echo $cntrQuestOuter; ?>" id="question_marks_obtained_<?php echo $cntrQuestOuter; ?>" placeholder="" class="text-center calculateTotal" min="0" max="<?php echo $evalquestion->max_points; ?>" value="<?php echo $arrAlreadyData[$evalquestion->id]; ?>" />
												
												<input type="hidden" name="question_id_<?php echo $cntrQuestOuter; ?>" id="question_id_<?php echo $cntrQuestOuter; ?>" value="<?php echo $evalquestion->id; ?>" />
												
												<input type="hidden" name="question_marks_possible_<?php echo $cntrQuestOuter; ?>" id="question_marks_possible_<?php echo $cntrQuestOuter; ?>" value="<?php echo $evalquestion->max_points; ?>" />
											</div>
										</div>
									</div>
									<?php
									$cntrQ++;
									$cntrQuestOuter++;
									}
									?>
								</div>
								<?php
								$cntrCat++;
								}
								?>
								
								
								<?php
								// to check if any negative marking question added or not
								$evalAreaNegative = $this->Evaluationareas->find()->where(['Evaluationareas.evaluationform_id' => $evalFormD->id,'Evaluationareas.evaluationcategory_id' => 16])->order(["Evaluationareas.id" => "ASC"])->contain(["Evaluationcategories"])->first();
								//print_r($evalAreaNegative);
								if($evalAreaNegative)
								{
									$negativeQuestionD = $this->Evaluationquestions->find()->where(["Evaluationquestions.id" => $evalAreaNegative->evaluationquestion_ids])->first();
								?>
								<div class="totals" style="border-top:1px solid #000;">
									<div class="seventycolom" style="border-top:1px solid #000;">
										<?php echo $negativeQuestionD->question; ?>
									</div>
									<div class="thirtyycolom" style="border-top:none;">
										<div class="halfcom borderhide">-<?php echo $negativeQuestionD->max_points; ?></div>
										<div class="halfcom borderhide" id="box_points_negative">
											<input type="text" name="negative_question_marks_obtained" id="negative_question_marks_obtained" placeholder="" class="text-center calculateTotal" min="-<?php echo $negativeQuestionD->max_points; ?>" max="0" value="<?php echo $arrAlreadyData[$negativeQuestionD->id]; ?>" />
											
											<input type="hidden" name="negative_question_id" id="negative_question_id" value="<?php echo $negativeQuestionD->id; ?>" />
												
											<input type="hidden" name="negative_question_marks_possible" id="negative_question_marks_possible" value="-<?php echo $negativeQuestionD->max_points; ?>" />
										</div>
									</div>
								</div>
								<?php
								}
								?>
								
								<?php
								if(!empty($evalFormD->notes))
								{
								?>
								<div class="comments">
									<div class="seventycolom"><?php echo $evalFormD->notes; ?></div>
								</div>
								<?php
								}
								?>
								
								<div class="totals" style="border-top:1px solid #000;">
									<div class="seventycolom"><b>TOTAL POINTS</b></div>
									<div class="thirtyycolom">
										<div class="halfcom borderhide"><?php echo $totalPossiblePoints; ?></div>
										<div class="halfcom borderhide" id="box_points_allotted"><?php echo $checkEvalJudge->total_marks_obtained; ?></div>
										<input type="hidden" id="calc_points_allotted" name="calc_points_allotted" value="<?php echo $checkEvalJudge->total_marks_obtained; ?>" />
									</div>
								</div>
								
								
								
								<div class="comments">
									<textarea placeholder="COMMENT:" name="comments" id="comments"><?php echo $checkEvalJudge->comments; ?></textarea>
								</div>
								<div class="footerpart">
									<div class="judgename">
										Judge’s Name: <b><?php echo $userDetails->first_name; ?> <?php echo $userDetails->last_name; ?></b>
									</div>
									<div class="judgename">
										Judge’s Signature:
									</div>
								</div>
							</div>
						</div>
						<!-- enndd-->
					</div>
				</div>
				
				<div class="form-group form-btns text-center mb-3">
					<button type="submit" class="btn btn-success">Submit</button>
					<?php echo $this->Html->link('Cancel', ['controller'=>'conventionregistrations', 'action' => 'judgeevententries',$convRegD->slug,$eventD->slug], ['class'=>'btn btn-secondary']); ?>
					<input type="hidden" name="max_questions" id="max_questions" value="<?php echo $cntrQuestOuter-1; ?>" />
				</div>
				
			<?php echo $this->Form->end(); ?>
			<!-- dashboard-section-1 end-->
		</main>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function () {
	$(".calculateTotal").on("keyup change", function(e) {
		//alert('dddddd');return false;
		
		//alert(Math.abs($('#negative_question_marks_obtained').val()));
		
		var max_questions = $('#max_questions').val();
		var totalPointsAllotted = 0;
		
		for (let cntrMaxQ = 1; cntrMaxQ <= max_questions; cntrMaxQ++)
		{	
			// to get point provided
			if($('#question_marks_obtained_'+cntrMaxQ).val())
			{
				totalPointsAllotted = parseInt(totalPointsAllotted)+parseInt($('#question_marks_obtained_'+cntrMaxQ).val());
			}
		}
		
		// to check if any negative question is there
		if($('#negative_question_marks_obtained').val())
		{
			var negativeVal = Math.abs($('#negative_question_marks_obtained').val());
			totalPointsAllotted = parseInt(totalPointsAllotted)-negativeVal;
		}
		
		$("#box_points_allotted").html(totalPointsAllotted);
		$("#calc_points_allotted").val(totalPointsAllotted);
	})
});
</script>