<?php
use Cake\ORM\TableRegistry;
$this->Evaluationquestions = TableRegistry::getTableLocator()->get('Evaluationquestions');
$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
?>
<div class="container-fluid p-0">
	<div class="row">
		<?php //echo $this->element('user_left_menu'); ?>
		<main class="col-md-12">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>
			
			<?php
			foreach($judgeevaluations as $evaluationD)
			{
			?>
			
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
			</div>
			<!-- dashboard-section-2 start-->

			
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
			?>


			<!-- dashboard-section-2 end-->

		</main>
	</div>
</div>

<script type="text/javascript">
<!--
window.print();
//-->
</script>