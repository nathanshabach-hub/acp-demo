<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>
<?php
use Cake\ORM\TableRegistry;

$this->Users = TableRegistry::get('Users');
$this->Events = TableRegistry::get('Events');
$this->Crstudentevents = TableRegistry::get('Crstudentevents');
?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Manage Convention Registrations Groups :: <?php echo $CRDetails->Conventions['name']; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-newspaper-o"></i> Convention Registrations ', ['controller'=>'conventionregistrations', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active"> Convention Registrations Groups </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            
			<table cellpadding="4" cellspacing="4">
				<?php
				foreach($arrConvGroups as $eventid => $groupstudents)
				{
					$eventD = $this->Events->find()->where(['Events.id' => $eventid])->first();
					
					$condTS = array();
					$condTS[] = "(Crstudentevents.conventionregistration_id = '".$CRDetails->id."')";
					$condTS[] = "(Crstudentevents.convention_id = '".$CRDetails->convention_id."')";
					$condTS[] = "(Crstudentevents.season_id = '".$CRDetails->season_id."')";
					$condTS[] = "(Crstudentevents.season_year = '".$CRDetails->season_year."')";
					$condTS[] = "(Crstudentevents.event_id = '".$eventD->id."')";

					$totalStudentsEvent = $this->Crstudentevents->find()->where($condTS)->count();
				?>
				<tr>
					<td>
						<h4>
							<b>&nbsp;&nbsp;
								<?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)
								(Min: <?php echo $eventD->min_no; ?>
								&nbsp;&nbsp;&nbsp; 
								Max: <?php echo $eventD->max_no; ?>)
								&nbsp;&nbsp;&nbsp;
								(Total Students in this event: <?php echo $totalStudentsEvent; ?>)
							</b>
						</h4>
					</td>
				</tr>
				<tr>
					<td>
						<div class="container">
							<div class="row">
								<?php
								foreach($groupstudents as $stgname => $studentids)
								{
								?>
								<div class="col-lg-4">
									<div class="card">
										<div class="card-body">
											<h4 class="card-title">
												Group <?php echo $stgname; ?>
												
												<?php
												//echo count($studentids);
												if (count($studentids) >= $eventD->min_no && count($studentids) <= $eventD->max_no) {
													echo '<i class="fa fa-check-circle pull-right" style="color:green;"></i>';
												} else {
													echo '<i class="fa fa-times-circle pull-right" style="color:red;" title="Group does not fulfil min/max criteria."></i>';
												}
												?>
												
											</h4>
											<div class="table-responsive table-bordered tablescroll">
												<table class="table table-striped-columns">
													<tbody>
														<?php
														if (count($studentids))
															$implodeStIDS = implode(",", $studentids);
														else
															$implodeStIDS = 0;

														$condStudentE = array();
														$condStudentE[] = "(Users.id IN ($implodeStIDS) )";

														$studentsDList = $this->Users->find()->where($condStudentE)->order(["Users.first_name" => "ASC", "Users.middle_name" => "ASC"])->all();

														foreach ($studentsDList as $studentrecord) {
														?>
														<tr>
															<td class="">
																<?php echo $studentrecord->first_name; ?>
																<?php echo $studentrecord->middle_name; ?>
																<?php echo $studentrecord->last_name; ?> 
																(<?php echo date("Y") - $studentrecord->birth_year; ?> Yrs)
															</td>
														</tr>
														<?php
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
						</div>
					</td>
				</tr>
				<?php
				}
				?>
				<tr><td>&nbsp;</td></tr>
				
			</table>
			
          </div>
    </section>
  </div>