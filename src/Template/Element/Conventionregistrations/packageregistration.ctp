<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::get('Events');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
?>

<?php if ($packageregistration) { ?>

    <div class="panel-body">
        
        <?php echo $this->Form->create('Conventionregistrationstudents', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
                

            <div class="tbl-resp-listing">
                <table class="table table-bordered table-condensed cf">
                    
					<?php
					foreach ($packageregistration as $datarecord)
					{
						if(!empty($datarecord->event_ids) && $datarecord->event_ids != NULL)
						{
					?>
					
                        <tr>
                            <td class="sorting_paging">
								
								
								<table>
									<tr>
										<td colspan="2"><b><?php echo $datarecord->Students['first_name']; ?> <?php echo $datarecord->Students['middle_name']; ?> <?php echo $datarecord->Students['last_name']; ?></b></td>
									</tr>
									<tr style="font-size:12px;font-weight:normal;">
										<td width="50%">Year of Birth: <?php echo $datarecord->Students['birth_year']; ?></td>
										<td><b>Male/Female</b>: <?php echo $datarecord->Students['gender']; ?></td>
									</tr>
									
								</table>
								
								
							</td>
                        </tr>
                    
					
                     
						<?php
						// to show list of events for a student
						$condStudentEvents = array();
						$condStudentEvents[] = "(Events.id IN (".$datarecord->event_ids.") )";
						
						$studentEventList = $this->Events->find()->where($condStudentEvents)->order(["Events.event_name" => 'ASC'])->all();
						?>
						<tr>
							<td data-title="Student Events">
								<table style="font-size:14px;">
									<?php
									foreach($studentEventList as $studentev)
									{
										
									?>
									<tr>
										<td width="10%"><?php echo $studentev->event_id_number; ?></td>
										<td width="10%">&nbsp;</td>
										<td width="40%">
										<?php
										echo $studentev->event_name;
										echo '&nbsp;&nbsp;';
										if($studentev->group_event_yes_no == 1)
											echo '<b>(Group Event)</b>';
										?>
										</td>
										<td width="20%">&nbsp;</td>
										<td width="20%">
										<?php
										// to check if upload is required for this event
										if($studentev->upload_type != 'Nil' || $studentev->report == 1 || $studentev->context_box == 1 || $studentev->score_sheet == 1 || $studentev->additional_documents == 1)
										{
											$condStudentSubCheck 	= array();
											$condStudentSubCheck[] 	= "(Eventsubmissions.conventionregistration_id = '".$datarecord->conventionregistration_id."')";
											$condStudentSubCheck[] 	= "(Eventsubmissions.event_id = '".$studentev->id."')";
											
											// first of all check that this is a group event or not
											if($studentev->group_event_yes_no == 1)
											{
												// to find group for this student
												$checkStudentGroup = $this->Crstudentevents->find()->where(['Crstudentevents.conventionregistration_id' => $datarecord->conventionregistration_id,'Crstudentevents.student_id' => $datarecord->student_id,'Crstudentevents.event_id' => $studentev->id])->first();
												if(!empty($checkStudentGroup->group_name) && $checkStudentGroup->group_name != NULL)
												{
													// now check if submission done for this group or not
													$condStudentSubCheck[] = "(Eventsubmissions.group_name = '".$checkStudentGroup->group_name."')";
													
													// check if submission done or not
													$checkSubmissionStudent = $this->Eventsubmissions->find()->where($condStudentSubCheck)->count();
													if($checkSubmissionStudent>0)
													{
														echo $this->Html->image('front/green_check_icon.png',array("width" => "20","alt" => "Upload done"));
													}
													else
													{
														echo $this->Html->image('front/red_cross_icon.png',array("width" => "20","alt" => "Upload not done"));
														echo '&nbsp;&nbsp;&nbsp;';
														echo $this->Html->link('Upload', ['controller' => 'eventsubmissions', 'action' => 'submitgroupevent',$datarecord->slug,$studentev->slug,$checkStudentGroup->slug], ['escape' => false, 'class' => 'btn btn-primary']);
													}
												}
												else
												{
													echo 'Student not grouped.';
												}
											}
											else
											{
												$condStudentSubCheck[] 	= "(Eventsubmissions.student_id = '".$datarecord->student_id."')";
												
												$checkSubmissionStudent = $this->Eventsubmissions->find()->where($condStudentSubCheck)->count();
												
												if($checkSubmissionStudent>0)
												{
													echo $this->Html->image('front/green_check_icon.png',array("width" => "20","alt" => "Upload done"));
												}
												else
												{
													echo $this->Html->image('front/red_cross_icon.png',array("width" => "20","alt" => "Upload not done"));
													echo '&nbsp;&nbsp;&nbsp;';
													echo $this->Html->link('Upload', ['controller' => 'eventsubmissions', 'action' => 'submitstudentevent',$datarecord->slug,$studentev->slug], ['escape' => false, 'class' => 'btn btn-primary']);
												}
											}
											
											
											//echo $checkSubmissionStudent;
											
										}
										?>
										</td>
									</tr>
									<?php
									}
									?>
									
									<tr>
										<td width="10%">&nbsp;</td>
										<td width="10%">&nbsp;</td>
										<td width="40%">&nbsp;</td>
										<td width="20%">&nbsp;</td>
										<td width="20%">&nbsp;</td>
									</tr>
									
									<tr>
										<td colspan="5"><b>Number of Events Entered: <?php echo count(explode(",",$datarecord->event_ids)); ?></b></td>
									</tr>
								</table>
							</td>
						</tr>
						
						<tr style="background-color:#ccc;">
							<td style="background-color:#ccc;">&nbsp;</td>
						</tr>
                        
                    
					 
					
					
					
					<?php
						}
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
