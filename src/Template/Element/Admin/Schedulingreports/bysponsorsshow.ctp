<?php
use Cake\ORM\TableRegistry;
$this->Schedulingtimings = TableRegistry::getTableLocator()->get('Schedulingtimings');
$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if ($arrStudentSorted) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				<h4><?php echo h($sponsorD->first_name); ?> (<?php echo h($schoolD->first_name); ?>)</h4>
				</div>  
            </div>   
			
			<?php
			foreach($arrStudentSorted as $student_id_sorted)
			{
				$arrStudentSchedule = array();
			?>
            <div class="tbl-resp-listing">
                <table id="report_by_sponsor_student" class="table table-bordered table-striped table-condensed cf">
                    
					<tr>
						<th class="sorting_paging" width="15%" style="font-size:18px;" colspan="4">
							<?php echo h($arrStudentNames[$student_id_sorted]); ?>
						</th>
					</tr>
					<tr>
						<td class="sorting_paging" width="15%"><b>Day</b></td>
						<td class="sorting_paging" width="15%"><b>Start</b></td>
						<td class="sorting_paging" width="35%"><b>Event</b></td>
						<td class="sorting_paging" width="35%"><b>Location</b></td>
					</tr>
					<?php
					// Individual events for this student
					$condSch = array();
					$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
					Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
					Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
					Schedulingtimings.season_year = '".$conventionSD->season_year."')";
					$condSch[] = "(Schedulingtimings.user_id = '".$student_id_sorted."' OR Schedulingtimings.user_id_opponent = '".$student_id_sorted."')";
					
					$schedulingTimingsList = $this->Schedulingtimings->find()
						->where($condSch)
						->contain(["Events","Users","Opponentuser","Conventionrooms"])
						->order(["Schedulingtimings.sch_date_time" => "ASC"])
						->all();
					foreach ($schedulingTimingsList as $datarecord)
					{
						$arrSch = array();
						$arrSch['sch_date_time'] = $datarecord->sch_date_time;
						$arrSch['day']           = $datarecord->day;
						$arrSch['start_time']    = $datarecord->start_time!=NULL ? date("h:i A",strtotime($datarecord->start_time)) : '';
						$arrSch['event_name']    = $datarecord->Events['event_name'].' ('.$datarecord->Events['event_id_number'].')';
						$arrSch['room_name']     = $datarecord->Conventionrooms['room_name'];
						$arrSch['is_bye']        = $datarecord->is_bye;
						$arrStudentSchedule[] = $arrSch;
					}
					
					// Group events for this student
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
					
					if($studentGroups)
					{
						foreach($studentGroups as $studentgrprec)
						{ 
							$condSchSG = array();
							$condSchSG[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
							Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
							Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
							Schedulingtimings.season_year = '".$conventionSD->season_year."')";
							$condSchSG[] = "(Schedulingtimings.event_id = '".$studentgrprec->event_id."' AND Schedulingtimings.event_id_number = '".$studentgrprec->event_id_number."' AND Schedulingtimings.group_name = '".$studentgrprec->group_name."')";
							
							$schedulingStGrp = $this->Schedulingtimings->find()
								->where($condSchSG)
								->contain(["Events","Users","Opponentuser","Conventionrooms"])
								->order(["Schedulingtimings.sch_date_time" => "ASC"])
								->all();
							foreach($schedulingStGrp as $schstudgrprec)
							{
								$arrSch = array();
								$arrSch['sch_date_time'] = $schstudgrprec->sch_date_time;
								$arrSch['day']           = $schstudgrprec->day;
								$arrSch['start_time']    = $schstudgrprec->start_time!=NULL ? date("h:i A",strtotime($schstudgrprec->start_time)) : '';
								$arrSch['event_name']    = $schstudgrprec->Events['event_name'].' ('.$schstudgrprec->Events['event_id_number'].') [Group: '.h($schstudgrprec->group_name).']';
								$arrSch['room_name']     = $schstudgrprec->Conventionrooms['room_name'];
								$arrSch['is_bye']        = $schstudgrprec->is_bye;
								$arrStudentSchedule[] = $arrSch;
							}
						}
					}
					
					// Sort by sch_date_time
					usort($arrStudentSchedule, function($a, $b) {
						return strcmp($a['sch_date_time'], $b['sch_date_time']);
					});
					
					foreach($arrStudentSchedule as $arrSchRec)
					{
						$byeLabel = '';
						if($arrSchRec['is_bye'] == 1) { $byeLabel = ' <span class="label label-warning">BYE</span>'; }
					?>
					<tr>
						<td><?php echo h($arrSchRec['day']); ?></td>
						<td><?php echo h($arrSchRec['start_time']); ?></td>
						<td><?php echo h($arrSchRec['event_name']); ?><?php echo $byeLabel; ?></td>
						<td><?php echo h($arrSchRec['room_name']); ?></td>
					</tr>
					<?php
					}
					
					if(empty($arrStudentSchedule))
					{
					?>
					<tr>
						<td colspan="4"><i>No schedule found for this student.</i></td>
					</tr>
					<?php
					}
					?>
                </table>
            </div>
			<br/>
			<?php
			}
			?>
        </section>
    </div>
<?php } else { ?>
    <div class="panel-body">
        <p>No students found for this sponsor in this convention season.</p>
    </div>
<?php } ?>
