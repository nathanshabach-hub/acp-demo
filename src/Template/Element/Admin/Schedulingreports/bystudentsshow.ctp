<?php
use Cake\ORM\TableRegistry;
$this->Schedulingtimings = TableRegistry::getTableLocator()->get('Schedulingtimings');
$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if ($arrStudentSorted) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Divisions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				<h4><?php echo $schoolD->first_name; ?></h4>
				</div>  
            </div>   
			
			<?php
			// to run through each student name
			foreach($arrStudentSorted as $student_id_sorted)
			{
				$arrStudentSchedule = array();
			?>
            <div class="tbl-resp-listing">
                <table id="report_by_school_student" class="table table-bordered table-striped table-condensed cf">
                    
					<tr>
						<th class="sorting_paging" width="15%" style="font-size:18px;">
							<?php echo $arrStudentNames[$student_id_sorted]; ?> <?php //echo '#'.$student_id_sorted; ?>
						</th>
						<th class="sorting_paging" width="15%">&nbsp;</th>
						<th class="sorting_paging" width="35%">&nbsp;</th>
						<th class="sorting_paging" width="35%"><?php //echo $schoolD->first_name; ?></th>
					</tr>
					<tr>
						<td class="sorting_paging" width="15%"><b>Day</b></td>
						<td class="sorting_paging" width="15%"><b>Start</b></td>
						<td class="sorting_paging" width="35%"><b>Event</b></td>
						<td class="sorting_paging" width="35%"><b>Location</b></td>
					</tr>
					<?php
					// now fetch scheduling for this student
					$condSch = array();
					$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
					Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
					Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
					Schedulingtimings.season_year = '".$conventionSD->season_year."')";
					$condSch[] = "(Schedulingtimings.user_id = '".$student_id_sorted."' OR Schedulingtimings.user_id_opponent = '".$student_id_sorted."')";
					
					//$condSch[] = "(Schedulingtimings.is_bye != 1 OR Schedulingtimings.is_bye = NULL)";
					
					$schedulingTimingsList = $this->Schedulingtimings->find()
						->where($condSch)
						->contain(["Events","Users","Opponentuser","Conventionrooms"])
						->order(["Schedulingtimings.sch_date_time" => "ASC"])
						->all();
					foreach ($schedulingTimingsList as $datarecord)
					{
						$arrSch		= array();
						$arrSch['sch_date_time']	= $datarecord->sch_date_time;
						$arrSch['day']	= $datarecord->day;
						$arrSch['start_time']	= $datarecord->start_time!=NULL ? date("h:i A",strtotime($datarecord->start_time)) : '';
						
						$arrSch['event_name']	= $datarecord->Events['event_name'].' ('.$datarecord->Events['event_id_number'].')';
						
						$arrSch['room_name']	= $datarecord->Conventionrooms['room_name'];
						$arrSch['db_id']		= $datarecord->id;
						$arrSch['is_bye']		= $datarecord->is_bye;
						
						$arrStudentSchedule[] = $arrSch;
						
					}
					
					// Here we need to show any group events of this student
					// First lets find if this student is in any group for this convention season
					$condSG = array();
					$condSG[] = "(Crstudentevents.conventionseason_id = '".$conventionSD->id."' AND 
					Crstudentevents.convention_id = '".$conventionSD->convention_id."' AND 
					Crstudentevents.season_id = '".$conventionSD->season_id."' AND 
					Crstudentevents.season_year = '".$conventionSD->season_year."')";
					$condSG[] = "(Crstudentevents.student_id = '".$student_id_sorted."')";
					$condSG[] = "(Crstudentevents.group_name != '')";
					$studentGroups = $this->Crstudentevents->find()
						->where($condSG)
						->order(["Crstudentevents.id" => "ASC"])
						->all();
					//print_r($studentGroups);exit;
						
					if($studentGroups)
					{
						foreach($studentGroups as $studentgrprec)
						{ 
							// Now check scheduling for this group for this event and convention season
							$condSchSG = array();
							$condSchSG[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
							Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
							Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
							Schedulingtimings.season_year = '".$conventionSD->season_year."')";
							
							$condSchSG[] = "(Schedulingtimings.user_id = '".$schoolD->id."' OR Schedulingtimings.user_id_opponent = '".$schoolD->id."')";
							
							$condSchSG[] = "(Schedulingtimings.event_id = '".$studentgrprec->event_id."' AND Schedulingtimings.event_id_number = '".$studentgrprec->event_id_number."' AND Schedulingtimings.group_name = '".$studentgrprec->group_name."')";
							
							//$condSchSG[] = "(Schedulingtimings.is_bye != 1  OR Schedulingtimings.is_bye = NULL)";
							
							$schedulingStGrp = $this->Schedulingtimings->find()
								->where($condSchSG)
								->contain(["Events","Users","Opponentuser","Conventionrooms"])
								->order(["Schedulingtimings.sch_date_time" => "ASC"])
								->all();
							foreach($schedulingStGrp as $schstudgrprec)
							{
								$arrSch		= array();
								$arrSch['sch_date_time']	= $schstudgrprec->sch_date_time;
								$arrSch['day']	= $schstudgrprec->day;
								$arrSch['start_time']	= $schstudgrprec->start_time!=NULL ? date("h:i A",strtotime($schstudgrprec->start_time)) : '';
								
								$arrSch['event_name']	= $schstudgrprec->Events['event_name'].' ('.$schstudgrprec->Events['event_id_number'].')';
								
								$arrSch['room_name']	= $schstudgrprec->Conventionrooms['room_name'].' (Group: '.$schstudgrprec->group_name.')';
								
								$arrSch['db_id']		= $schstudgrprec->id;
								$arrSch['is_bye']		= $schstudgrprec->is_bye;
								
								$arrStudentSchedule[] = $arrSch;
								
							} // end foreach($schedulingStGrp as $schstudgrprec)
					
					
						} //end foreach($studentGroups as $studentgrprec)
						
					
					?>
					
					<?php
					usort($arrStudentSchedule, function ($a, $b) {
						return $a['sch_date_time'] <=> $b['sch_date_time'];
					});
					/* echo '<pre>';
					print_r($arrStudentSchedule);
					echo '</pre>';exit; */
					// Now show events of student
					foreach($arrStudentSchedule as $studentsch)
					{
						$byeLabel = ((int)$studentsch['is_bye'] === 1) ? ' <span class="label label-warning">BYE</span>' : '';
					?>
						<tr>
							<td data-title="Day" width="15%"><?php echo $studentsch['day'];?> <?php //echo $studentsch['db_id'];?></td>
							<td data-title="Start" width="15%"><?php echo $studentsch['start_time'];?></td>
							<td data-title="Event" width="35%"><?php echo $studentsch['event_name']; ?><?php echo $byeLabel; ?></td>
							<td data-title="Location" width="35%"><?php echo $studentsch['room_name'];?></td>
							</td>
						</tr>
					<?php
					}
					
					?>
					
					<?php
					} //end if($studentGroups)
					?>
					
                </table>
            </div>
			<div class="page-break-after"></div>
			
			<?php }?>
        </section>

         
        
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>