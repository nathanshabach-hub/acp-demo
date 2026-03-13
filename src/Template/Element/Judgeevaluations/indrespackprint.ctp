
<div class="m-4">
	<div class="table-responsive">
		<table class="table table-bordered table-hover align-middle">
			<tbody>
				
				<tr>
					<td width="40%"><b>Event</b></td>
					<td width="30%"><b>Place</b></td>
					<td><b>Point</b></td>
				</tr>
				<?php
				foreach($eventsList as $eventrec)
				{
					$posVal 			= '';
					$pointsVal 			= '';
					$commandPerfText 			= '';
						
					// Now check results based on group or individual event
					if($eventrec->group_event_yes_no == 0)
					{
						// Fetch points and position - non group event
						$condPOS = array();
						$condPOS[] = "(Resultpositions.conventionregistration_id = '".$conventionRegD->id."' )";
						$condPOS[] = "(Resultpositions.event_id = '".$eventrec->id."' )";
						$condPOS[] = "(Resultpositions.student_id = '".$convRegStudentD->student_id."' )";
						
						$studentPosition 	= $this->Resultpositions->find()->where($condPOS)->first();
						$posVal 			= $studentPosition->position;
						$pointsVal 			= $studentPosition->points_obtained;
						
						// to check if this is a command performance
						$checkStudentCP = $this->Eventsubmissions->find()->where(["Eventsubmissions.command_performance" => 1,"Eventsubmissions.event_id" => $eventrec->id,"Eventsubmissions.conventionseason_id" => $conventionRegD->conventionseason_id,"Eventsubmissions.student_id" => $convRegStudentD->student_id])->first();
						if($checkStudentCP)
						{
							$commandPerfText = '<br><span class="student_cp">This is event was nominated for a Command Performance.</span>';
						}
					}
					else
					{
						// Group event - Firstly check group of this user
						$condGrp = array();
						$condGrp[] = "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."' )";
						$condGrp[] = "(Crstudentevents.event_id = '".$eventrec->id."' )";
						$condGrp[] = "(Crstudentevents.student_id = '".$convRegStudentD->student_id."' )";
						$checkGroup = $this->Crstudentevents->find()->where($condGrp)->select(['group_name'])->first();
						if(!empty($checkGroup->group_name))
						{
							// Check position of this Group
							$condPOS = array();
							$condPOS[] = "(Resultpositions.conventionregistration_id = '".$conventionRegD->id."' )";
							$condPOS[] = "(Resultpositions.event_id = '".$eventrec->id."' )";
							$condPOS[] = "(Resultpositions.group_name = '".$checkGroup->group_name."' )";
							
							$studentPosition 	= $this->Resultpositions->find()->where($condPOS)->first();
							$posVal 			= $studentPosition->position;
							$pointsVal 			= $studentPosition->points_obtained;
							
						}
					}
				?>
				<tr>
					<td><?php echo $eventrec->event_name; ?> (<?php echo $eventrec->event_id_number; ?>)
					<?php echo $commandPerfText; ?>
					</td>
					
					<td>
					
					<?php
					if($eventrec->group_event_yes_no == 0)
					{
						if($posVal>=1 && $posVal<=6) { echo $posVal;}
					}
					else
					{
						echo 'Group: '.$checkGroup->group_name;
						if($posVal>=1 && $posVal<=6)
						{ 
							echo ' Place: '.$posVal;
						}
					}
					?>
					</td>
					
					<td><?php if($posVal>=1 && $posVal<=6) { echo $pointsVal;} ?>
					
					</td>
				</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>
	
</div>


