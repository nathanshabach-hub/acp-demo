<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Users = TableRegistry::get('Users');
$this->Resultpositions = TableRegistry::get('Resultpositions');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
?>

<?php if(count($arrStudentsSchool)>0) { ?>

    <div class="panel-body">
        
        <?php echo $this->Form->create('Conventionregistrationstudents', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
                

            <div class="tbl-resp-listing">
                <table id="results_tabledd" class="table table-condensed cf">
                     
					
					<?php
					if(count($arrStudentsSchool)>0)
					{
						// now get events list
						$arrStudentsSchoolImplode = implode(",",$arrStudentsSchool);
						//$arrStudentsSchoolImplode = '7274';
						$condStudents = array();
						$condStudents[] = "(Users.id IN ($arrStudentsSchoolImplode) )";
						$students = $this->Users->find()->where($condStudents)->order(["Users.first_name" => "ASC"])->all();
					?>
					
                    <tbody>
                        <?php
						$cntrP = 0;
						foreach($students as $student)
						{
						?>
						
						<?php
						if($show_header_each_page == 1 && $cntrP>0)
						{
						?>
						<div class="teachers-top-heading">
				
							<span><h1 style="color:#000;"><?php echo $userDetails->first_name; ?></h1></span>
							<br />
							<span>Result Package Individual - <?php echo $conventionRegD->Conventions['name']; ?> <?php echo $conventionRegD->season_year; ?></span>
							
							<?php //echo $this->Html->link('<i class="fa fa-print"></i> Print', ['controller' => 'conventionregistrations', 'action' => 'resultpackageprint'], ['escape' => false, 'class' => 'btn btn-primary', 'target' => '_blank']); ?>
						</div>
						<?php
						}
						?>

						
						<?php
						
							// to get all group names 
							
							$stName = $student->first_name.' '.$student->middle_name.' '.$student->last_name;
							echo '<table class="table table-bordered" style="page-break-after: always">';
							echo '<tr style="background-color:#ccc;">
							<td><b>'.$stName.'</b></td>
							<td><b>Birth Year:</b> '.$student->birth_year.' (Age: '.(date("Y") - $student->birth_year).')</td>
							<td>'.$student->gender.'</td>
							<td>&nbsp;</td>
							</tr>';
							
							// to get all positions of this student - Individual results
							$studentpositions = $this->Resultpositions->find()->where(["Resultpositions.user_id" => $userDetails->id,"Resultpositions.conventionseason_id" => $conventionRegD->conventionseason_id,"Resultpositions.student_id" => $student->id,"Resultpositions.position >" => 0,"Resultpositions.position <=" => 6])->contain(['Events'])->order(["Resultpositions.position" => "ASC"])->all();
							
							echo '<tr>
							<td>Place</td>
							<td>Points</td>
							<td>Event</td>
							<td>&nbsp;</td>
							</tr>';
							
							foreach($studentpositions as $studentpos)
							{
						?> 
                            <tr>
								<td width="25%" data-title="Place"><?php echo $studentpos->position;?></td>
                                <td width="25%" data-title="Points"><?php echo $studentpos->points_obtained;?></td>
								<td width="35%" data-title="Event"><?php echo $studentpos->Events['event_name'];?> (<?php echo $studentpos->Events['event_id_number'];?>)
								
								<?php
								// to check if this is a command performance
								$checkStudentCP = $this->Eventsubmissions->find()->where(["Eventsubmissions.command_performance" => 1,"Eventsubmissions.event_id" => $studentpos->event_id,"Eventsubmissions.conventionseason_id" => $conventionRegD->conventionseason_id,"Eventsubmissions.student_id" => $student->id])->first();
								if($checkStudentCP)
								{
									echo '<br><span class="student_cp">This is event was nominated for a Command Performance.</span>';
								}
								?>
								</td>
								<td width="15%" data-title="Print">
									<?php
									echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'conventionregistrations', 'action' => 'participationcertificatepdf', $studentpos->slug], ['escape' => false, 'class' => '', 'target' => '_blank', 'title' => 'Print Participation Certificate']);
									
									//$lastSlug = 
									
									//echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'conventionregistrations', 'action' => 'placecertificatepdf', $studentpos->slug,$studentpos->position], ['escape' => false, 'class' => '', 'target' => '_blank', 'title' => 'Print Place Certificate']);
									
									?>
								</td>
                            </tr>
							
                        <?php
							}
							//echo '</table>';
						?>
						
						<?php
						
						// to get all positions of this student - if student is in a group
						$studentGroupEventsList = $this->Crstudentevents->find()->where(["Crstudentevents.user_id" => $userDetails->id,"Crstudentevents.conventionseason_id" => $conventionRegD->conventionseason_id,"Crstudentevents.student_id" => $student->id])->contain(['Events'])->order(["Crstudentevents.id" => "ASC"])->all();
						//print_r($studentGroupEventsList);
						
						if($studentGroupEventsList)
						{
						
						foreach($studentGroupEventsList as $studentgrpdetail)
						{
							// to check if this group secure any position in result positions
							$checkstudentgroupposition = $this->Resultpositions->find()->where(["Resultpositions.user_id" => $userDetails->id,"Resultpositions.conventionseason_id" => $conventionRegD->conventionseason_id,"Resultpositions.event_id" => $studentgrpdetail->event_id,"Resultpositions.group_name" => $studentgrpdetail->group_name,"Resultpositions.position >" => 0,"Resultpositions.position <=" => 6])->contain(['Events'])->first();
							if($checkstudentgroupposition)
							{
						
						?>
							
							<tr>
								<td width="35%" data-title="Place">Group:<?php echo $studentgrpdetail->group_name;?> Place:<?php echo $checkstudentgroupposition->position;?></td>
                                <td width="30%" data-title="Points"><?php echo $checkstudentgroupposition->points_obtained;?></td>
								<td width="35%" data-title="Event"><?php echo $checkstudentgroupposition->Events['event_name'];?> (<?php echo $checkstudentgroupposition->Events['event_id_number'];?>)</td>
								<td width="35%" data-title="Place">
								<?php
								//echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'conventionregistrations', 'action' => 'participationcertificatepdf', $studentpos->slug], ['escape' => false, 'class' => '', 'target' => '_blank', 'title' => 'Print Participation Certificate']);
								?>
								</td>
                            </tr>
							
						
						<?php
							}
						}
						}
						?>
						
						
						<?php
							echo '<tr>
							<td colspan="4">&nbsp;</td>
							</tr>';
							
						$cntrP++;
						}
						?>
						
							
                    </tbody>
					<?php
					}
					?>
					
					
                </table>
            </div>
			
        </section>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
	
	 
	
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No student event found.</div>
<?php }
?>
