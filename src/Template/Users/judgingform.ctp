<?php
use Cake\ORM\TableRegistry;
$this->Evaluationtags 			= TableRegistry::getTableLocator()->get('Evaluationtags');
$this->Evaluationareas 			= TableRegistry::getTableLocator()->get('Evaluationareas');
$this->Evaluationcategories 	= TableRegistry::getTableLocator()->get('Evaluationcategories');
$this->Evaluationquestions 		= TableRegistry::getTableLocator()->get('Evaluationquestions');
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
			<h2 class="mt-3">Judging Form</h2>
			<!-- dashboard-section-1 start-->
			<?php echo $this->Form->create($judgingform, ['id'=>'judgingform', 'type' => 'file', 'class' =>'', 'autocomplete' =>'off']); ?>
				<div class="dasboard-section">
					<div class="dashboard-text">
						<h2>Judging Form</h2>
						<div class="classform-container">
							<h2>JUDGES FORM - SCIENCE EXHIBIT</h2>
							<div class="checkboxrow fwbold">
								<div class="singlechecknox"><input type="checkbox" id="OPEN" name="OPEN" value="OPEN">
									<label for="OPEN"> OPEN</label><br>
								</div>
								<div class="singlechecknox"><input type="checkbox" id="Collection" name="Collection" value="Collection">
									<label for="Collection"> U/16 - Collection on only</label><br>
								</div>
								<div class="centertext">(Please <i class="fa fa-check"></i> the appropriate box)</div>
							</div>
							<div class="checkboxrow">
								<?php
								$tag_ids_explode = explode(",",$evalFormD->tag_ids);
								foreach($tag_ids_explode as $evaltag)
								{
									$tagD = $this->Evaluationtags->find()->where(['Evaluationtags.id' => $evaltag])->first();
								?>
								<div class="singlechecknox">
								<input type="checkbox" id="tag_<?php echo $tagD->id; ?>" name="tags[]" value="<?php echo $tagD->id; ?>">
									<label for="tag_<?php echo $tagD->id; ?>"> <?php echo $tagD->name; ?></label><br>
								</div>
								<?php
								}
								?>
							</div>
							<div class="tableheader">
								<div class="singlerow">
									<div class="namecolom">Name : </div>
									<div class="inputfield">Jason Holder</div>
									<div class="dobcolom">DOB : </div>
									<div class="inputfield smallinpout">12-Nov-2001</div>
								</div>
								<div class="singlerow">
									<div class="namecolom">School : </div>
									<div class="inputfield">DPS Delhi</div>
									<div class="dobcolom">Cust Code : </div>
									<div class="inputfield smallinpout">CC001</div>
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
								$evalFormAreas = $this->Evaluationareas->find()->where(['Evaluationareas.evaluationform_id' => $evalFormD->id])->order(["Evaluationareas.id" => "ASC"])->contain(["Evaluationcategories"])->all();
								foreach($evalFormAreas as $evalformarea)
								{
									$evaluationquestion_ids = $evalformarea->evaluationquestion_ids;
									
								?>
								<div class="subjectcolom">
									<div class="fulcolm">
										<?php echo $cntrCat; ?>. <?php echo $evalformarea->Evaluationcategories['name']; ?>
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
										<div class="seventycolom"><?php echo $cntrQ; ?>. <?php echo $evalquestion->question; ?></div>
										<div class="thirtyycolom">
											<div class="halfcom"><?php echo $evalquestion->max_points; ?></div>
											<div class="halfcom">
												<input type="text" name="question_marks_<?php echo $cntrQuestOuter; ?>" id="question_marks_<?php echo $cntrQuestOuter; ?>" placeholder="" class="text-center calculateTotal" min="0" max="<?php echo $evalquestion->max_points; ?>" />
												
												<input type="hidden" name="question_id_<?php echo $cntrQuestOuter; ?>" id="question_marks_<?php echo $cntrQuestOuter; ?>" value="<?php echo $evalquestion->id; ?>" />
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
								
								<div class="totals">
									<div class="seventycolom"><b>TOTAL POINTS</b></div>
									<div class="thirtyycolom">
										<div class="halfcom borderhide"><?php echo $totalPossiblePoints; ?></div>
										<div class="halfcom borderhide" id="box_points_allotted">0</div>
									</div>
								</div>
								<div class="comments">
									<textarea placeholder="COMMENT:"></textarea>
								</div>
								<div class="footerpart">
									<div class="judgename">
										Judge’s <br>Name:
									</div>
									<div class="judgename">
										Judge’s <br>Signature:
									</div>
								</div>
							</div>
						</div>
						<!-- enndd-->
					</div>
				</div>
				
				<div class="form-group form-btns text-center mb-3">
					<button type="submit" class="btn btn-success">Submit</button>
					<?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'dashboard'], ['class'=>'btn btn-secondary']); ?>
					<input type="hidden" name="max_questions" id="max_questions" value="<?php echo $cntrQuestOuter; ?>" />
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
		
		var max_questions = $('#max_questions').val();
		var totalPointsAllotted = 0;
		
		for (let cntrMaxQ = 1; cntrMaxQ <= max_questions; cntrMaxQ++)
		{	
			// to get point provided
			if($('#question_marks_'+cntrMaxQ).val())
			{
				totalPointsAllotted = parseInt(totalPointsAllotted)+parseInt($('#question_marks_'+cntrMaxQ).val());
			}
		}
		
		$("#box_points_allotted").html(totalPointsAllotted);
	})
});
</script>