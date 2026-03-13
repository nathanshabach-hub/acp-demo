<?php
use Cake\ORM\TableRegistry;
$this->Evaluationquestions = TableRegistry::get('Evaluationquestions');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			<div class="teachers-top-heading">
				<span>Evaluation Marks:: <?php echo $submissionD->Events['event_name'];?> (<?php echo $submissionD->event_id_number; ?>)</span>
				<br />
				<span>Judge:: <?php echo $evaluationD->Judge['first_name'].' '.$evaluationD->Judge['last_name'];?></span>
				<br />
				
				<?php
				if($submissionD->student_id>0)
				{
				?>
				<span>Student: 
					<?php echo $submissionD->Students['first_name']; ?> <?php echo $submissionD->Students['middle_name']; ?> <?php echo $submissionD->Students['last_name']; ?>
				</span>
				<?php
				}
				else
				{
				?>
				
				<span>
				<?php
				// to fetch all group students
				$arrStudent = array();
				$arrStudentID = array();
				$groupstudents = $this->Crstudentevents->find()->where(["Crstudentevents.conventionregistration_id" => $submissionD->conventionregistration_id,"Crstudentevents.conventionseason_id" => $submissionD->conventionseason_id])->contain(['Students'])->order(['Crstudentevents.id' => 'ASC'])->all();
				
				foreach($groupstudents as $groupstudent)
				{
					if(!in_array($groupstudent->Students['id'],$arrStudentID))
					{
						$arrStudent[] = $groupstudent->Students['first_name'].' '.$groupstudent->Students['middle_name'].' '.$groupstudent->Students['last_name'];
						$arrStudentID[] = $groupstudent->Students['id'];
					}
					
				}
				//print_r($arrStudent);
				if(count($arrStudent))
				{
					echo implode(", ",$arrStudent);
				}
				?>
				
				</span>
				<?php
				}
				?>
				
				
				<?php echo $this->Html->link(' << Back to Evaluations', ['controller' => 'judgeevaluations', 'action' => 'evaluationslist', $event_submission_slug], ['escape' => false, 'class' => 'btn btn-primary']); ?>
			</div>
			<!-- dashboard-section-2 start-->

			<?php
			echo $evaluationD->Events['event_judging_type'];
			if($evaluationD->Events['event_judging_type'] == 'general')
			{
			?>
			<table class="table table-bordered table-condensed cf">
				<tr>
					<td colspan="4">Comments: <?php echo $evaluationD->comments ? $evaluationD->comments : 'N/A'; ?></td>
				</tr>
				<tr>
					<th>#</th>
					<th>Question</th>
					<th>Max Possible Marks</th>
					<th>Marks Obtained</th>
				</tr>
				<?php
				$cntrQ = 1;
				foreach($evaluationD->Judgeevaluationmarks as $judgevalmark)
				{
					$questionD = $this->Evaluationquestions->find()->where(["Evaluationquestions.id" => $judgevalmark->question_id])->first();
				?>
				
				<tr>
					<td><?php echo $cntrQ; ?></td>
					<td><?php echo $questionD->question; ?></td>
					<td><?php echo $judgevalmark->question_marks_possible; ?></td>
					<td><?php echo $judgevalmark->question_marks_obtained; ?></td>
				</tr>
				<?php
				$cntrQ++;
				}
				?>
				
				<tr>
					<th>&nbsp;</th>
					<th>&nbsp;</th>
					<th><?php echo $evaluationD->total_marks_possible; ?></th>
					<th><?php echo $evaluationD->total_marks_obtained; ?></th>
				</tr>
				
			</table>
			<?php
			}
			else
			if($evaluationD->Events['event_judging_type'] == 'distances')
			{
			?>
			<table class="table table-bordered table-striped table-condensed cf">
			<tr>
				<td>1st Attempt</td>
				<td>2nd Attempt</td>
				<td>3rd Attempt</td>
				<td><b>Best Score</b></td>
			</tr>
			<tr>
				<td><?php echo $evaluationD->distance_attempt_1 ?></td>
				<td><?php echo $evaluationD->distance_attempt_2 ?></td>
				<td><?php echo $evaluationD->distance_attempt_3 ?></td>
				<td><b><?php echo $evaluationD->distance_score ?></b></td>
			</tr>
			</table>
			<?php
			}
			else
			if($evaluationD->Events['event_judging_type'] == 'scores')
			{
			?>
			<table class="table table-bordered table-striped table-condensed cf">
				<tr>
					<td>Position</td>
					<td>Status</td>
					<td>Score</td>
				</tr>
				<?php
				for($cntrP=1;$cntrP<=9;$cntrP++)
				{
					$propYN = 'pos_'.$cntrP.'_yes_no';
					$propC 	= 'pos_'.$cntrP.'_score';
				?>
				<tr>
					<td><?php echo $cntrP; ?></td>
					<td><?php echo $evaluationD->$propYN ? "Yes" : "No"; ?></td>
					<td><?php echo $evaluationD->$propC ? $evaluationD->$propC : ""; ?></td>
				</tr>
				<?php
				}
				?>
				
				<tr>
					<td colspan="3">Competitors Choice</td>
				</tr>
				<tr>
					<td>X1: <?php echo $evaluationD->comp_choice_pos_1; ?></td>
					<td><?php echo $evaluationD->comp_choice_pos_1 ? "Yes" : "No"; ?></td>
					<td><?php echo $evaluationD->comp_choice_pos_1_score; ?></td>
				</tr>
				<tr>
					<td>X2: <?php echo $evaluationD->comp_choice_pos_2; ?></td>
					<td><?php echo $evaluationD->comp_choice_pos_2 ? "Yes" : "No"; ?></td>
					<td><?php echo $evaluationD->comp_choice_pos_2_score; ?></td>
				</tr>
				<tr>
					<td>X3: <?php echo $evaluationD->comp_choice_pos_3; ?></td>
					<td><?php echo $evaluationD->comp_choice_pos_3 ? "Yes" : "No"; ?></td>
					<td><?php echo $evaluationD->comp_choice_pos_3_score; ?></td>
				</tr>
				<tr>
					<td colspan="3">Total Score: <?php echo $evaluationD->all_pos_score; ?></td>
				</tr>
			</table>
			<?php
			}
			else
			if($evaluationD->Events['event_judging_type'] == 'soccer_kick')
			{
				$all_kicks = json_decode($evaluationD->soccer_kick_all_kicks);
			?>
			<table class="table table-bordered table-striped table-condensed cf">
				<tr>
					<td>Best Score</td>
					<td><?php echo $evaluationD->soccer_kick_best_kick; ?>m</td>
					<td></td>
					<td></td>
				</tr>
				<?php
				for($cntrKD=10;$cntrKD<=50;$cntrKD+=5)
				{
				?>
					<tr>
						<td><?php echo $cntrKD; ?>m</td>
						<?php
						for($cntrAtt=1;$cntrAtt<=3;$cntrAtt++)
						{
						?>
						<td>Attempt 1: 
						<?php
						if(in_array($cntrKD.'_'.$cntrAtt,$all_kicks))
						{
							echo '<b>Yes</b>';
						}
						else
						{
							echo 'No';
						}
						?>
						</td>
						<?php
						}
						?>
					</tr>
				<?php
				}
				?>
			</table>
					
			<?php
			}
			else
			if($evaluationD->Events['event_judging_type'] == 'spellings')
			{
				$all_kicks = json_decode($evaluationD->soccer_kick_all_kicks);
			?>
			<table class="table table-bordered table-striped table-condensed cf">
				<tr>
					<td>Score</td>
					<td><?php echo $evaluationD->spelling_score; ?></td>
				</tr>
			</table>
			<?php
			}
			?>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>