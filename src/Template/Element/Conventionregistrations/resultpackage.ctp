<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Users = TableRegistry::get('Users');
$this->Resultpositions = TableRegistry::get('Resultpositions');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
?>

<?php if(count($arrConvSeasonEvent)>0) { ?>

    <div class="panel-body">
        
        <?php echo $this->Form->create('Conventionregistrationstudents', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
                

            <div class="tbl-resp-listing">
                <table id="results_tabledd" class="table table-bordered table-condensed cf">
                     
					
					<?php
					if(count($arrConvSeasonEvent)>0)
					{
						// now get events list
						$arrConvSeasonEventImplode = implode(",",$arrConvSeasonEvent);
						$condEvents = array();
						$condEvents[] = "(Events.id IN ($arrConvSeasonEventImplode) )";
						$events = $this->Events->find()->where($condEvents)->order(["Events.event_id_number" => "ASC"])->all();
					?>
					
                    <tbody>
                        <?php
						foreach($events as $event)
						{
							// to check position
							//"Resultpositions.user_id" => $userDetails->id,
							$countpositions = $this->Resultpositions->find()->where(["Resultpositions.user_id" => $userDetails->id,"Resultpositions.conventionseason_id" => $conventionRegD->conventionseason_id,"Resultpositions.event_id" => $event->id,"Resultpositions.position >" => 0,"Resultpositions.position <=" => 6])->order(["Resultpositions.position" => "ASC"])->count();
							//print_r($overallpositions[0]->id>0);
							if($countpositions>0)
							{
								echo '<tr style="background-color:#E7E7E7;"><td colspan="4">'.$event->event_name.' ('.$event->event_id_number.')</td></tr>';
								
								$overallpositions = $this->Resultpositions->find()->where(["Resultpositions.user_id" => $userDetails->id,"Resultpositions.conventionseason_id" => $conventionRegD->conventionseason_id,"Resultpositions.event_id" => $event->id,"Resultpositions.position >" => 0,"Resultpositions.position <=" => 6])->order(["Resultpositions.position" => "ASC"])->all();
								
								foreach($overallpositions as $ovpos)
								{
									$showName 			= '';
									$showSchoolName  	= '';
									
									if($ovpos->student_id>0)
									{
										$studentD = $this->Users->find()->where(["Users.id" => $ovpos->student_id])->contain(['Schools'])->first();
										$showName = $studentD->first_name.' '.$studentD->middle_name.' '.$studentD->last_name;
										
										$showSchoolName = $studentD->Schools['first_name'];
										
										// to check if this is a command performance
										$checkStudentCP = $this->Eventsubmissions->find()->where(["Eventsubmissions.command_performance" => 1,"Eventsubmissions.event_id" => $event->id,"Eventsubmissions.conventionseason_id" => $conventionRegD->conventionseason_id,"Eventsubmissions.student_id" => $studentD->id])->first();
										
									}
									else
									if(!empty($ovpos->group_name))
									{
										$arrGrpStudent = array();
										$groupstudents = $this->Crstudentevents->find()->where(["Crstudentevents.user_id" => $userDetails->id,"Crstudentevents.conventionseason_id" => $ovpos->conventionseason_id,"Crstudentevents.event_id" => $event->id,"Crstudentevents.group_name " => $ovpos->group_name])->order(["Crstudentevents.id" => "ASC"])->all();
										foreach($groupstudents as $grpstudent)
										{
											$studentDG = $this->Users->find()->where(["Users.id" => $grpstudent->student_id])->contain(['Schools'])->first();
											$grpStName = $studentDG->first_name.' '.$studentDG->middle_name.' '.$studentDG->last_name;
											
											$arrGrpStudent[] = $grpStName;
											
											$showSchoolName = $studentDG->Schools['first_name'];
										}
										
										if(count($arrGrpStudent)>0)
										{
											$showName = implode(", ",$arrGrpStudent);
										}
									}
									
									
									
									
							
						?> 
                            <tr>
								<td width="20%" data-title="Position"><?php echo $ovpos->position;?></td>
                                <td width="40%" data-title="Student / Group"><?php echo $showName;?>
								<?php
								if($checkStudentCP)
								{
									echo '<br><span class="student_cp">This is event was nominated for a Command Performance.</span>';
								}
								?>
								</td>
								<td width="30%" data-title="School"><?php echo $showSchoolName;?></td>
								<td width="10%" data-title="Print Certificate">
								<?php echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'conventionregistrations', 'action' => 'placecertificatepdf', $ovpos->slug, $ovpos->position], ['escape' => false, 'class' => '', 'target' => '_blank', 'title' => 'Print Place Certificate']); ?>
								</td>
                            </tr>
							
                        <?php
							}
							}
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
