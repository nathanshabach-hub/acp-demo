<?php
use Cake\ORM\TableRegistry;
$this->Schedulingtimings = TableRegistry::getTableLocator()->get('Schedulingtimings');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>Schedule Category</h1>
        <p style="margin:2px 0 0 0; font-size:13px; color:#777;"><small><?php echo $conventionSD->Conventions['name']; ?> &mdash; <?php echo $conventionSD->season_year; ?></small></p>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Schedule Category </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title" style="color:Red;">
				Note: Schedulings must perform in ascending order from 1 to 4. 
				<br /><br />
				Firstly, start scheduling for C1 then wait to receive successfull screen. Then execute for C2.
				<br /><br />
				Do not perform scheduling for multiple categories at one time. It might generate false results.
				<br /><br />
				Schedulings will be reset for higher categories. If you perform scheduling for all 4 categories then if you re-perform scheduling for category 1, then all higher categories schedulings will reset and need to perform again. So if you perform scheduling for C2, then scheduling for C3 and C4 will reset if exists.
				</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			
			
			
			<div class="container">
				<h2>Schedule Category
				 
				<?php
				if(count($arrEventsC1) > 0 && count($arrEventsC2) > 0 && count($arrEventsC3) > 0 && count($arrEventsC4) > 0)
				{
					echo $this->Html->link('Start Scheduling', ['controller'=>'schedulingtimings', 'action' => 'startschedulec1',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le', 'confirm' => 'Are you sure you want to start scheduling?']);
				}
				?>
				</h2> 
				<table class="table table-bordered">
					<tr>
						<th>#</th>
						<th>Needs Schedule</th>
						<th>Group Event</th>
						<th>Event Kind ID</th>
						<th>Has To Be Consecutive</th>
						<th>Number of events found</th>
						<th>Start Scheduling</th>
						<th>View Scheduling</th>
					</tr>
					
					<tr>
						<td>1.</td>
						<td>Yes</td>
						<td>Yes</td>
						<td>Sequential</td>
						<td>Yes</td>
						<td><?php echo count($arrEventsC1); ?></td>
						<td>
							<?php
							if(count($arrEventsC1) > 0)
							{
								echo $this->Html->link('Start Scheduling C1', ['controller'=>'schedulingtimings', 'action' => 'startschedulec1',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le', 'confirm' => 'Are you sure you want to start scheduling for this schedule category 1?']);
							}
							?>
						</td>
						<td>
							<?php
							if(count($arrEventsC1) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 1,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,1], ['class'=>'btn btn-success canlcel_le']);
								}
								else
								{
									echo 'Schedulings not yet done';
								}
							}
							?>
						</td>
					</tr>
					
					<tr>
						<td>2.</td>
						<td>Yes</td>
						<td>No</td>
						<td>Elimination</td>
						<td>No</td>
						<td><?php echo count($arrEventsC2); ?></td>
						<td>
							<?php
							if(count($arrEventsC2) > 0)
							{
								echo $this->Html->link('Start Scheduling C2', ['controller'=>'schedulingtimings', 'action' => 'startschedulec2',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le', 'confirm' => 'Are you sure you want to start scheduling for this schedule category 2?']);
							}
							?>
						</td>
						<td>
							<?php
							if(count($arrEventsC2) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,2], ['class'=>'btn btn-success canlcel_le']);
								}
								else
								{
									echo 'Schedulings not yet done';
								}
							}
							?>
						</td>
					</tr>
					
					
					<!-- Category 3 is similar to category 2 -->
					<tr>
						<td>3.</td>
						<td>Yes</td>
						<td>Yes</td>
						<td>Elimination</td>
						<td>No</td>
						<td><?php echo count($arrEventsC3); ?></td>
						<td>
							<?php
							if(count($arrEventsC3) > 0)
							{
								echo $this->Html->link('Start Scheduling C3', ['controller'=>'schedulingtimings', 'action' => 'startschedulec3',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le', 'confirm' => 'Are you sure you want to start scheduling for this schedule category 3?']);
							}
							?>
						</td>
						<td>
							<?php
							if(count($arrEventsC3) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,3], ['class'=>'btn btn-success canlcel_le']);
								}
								else
								{
									echo 'Schedulings not yet done';
								}
							}
							?>
						</td>
					</tr>
					
					
					
					<!-- Category 4 is similar to category 1 -->
					<tr>
						<td>4.</td>
						<td>Yes</td>
						<td>No</td>
						<td>Sequential</td>
						<td>Yes</td>
						<td><?php echo count($arrEventsC4); ?></td>
						<td>
							<?php
							if(count($arrEventsC4) > 0)
							{
								echo $this->Html->link('Start Scheduling C4', ['controller'=>'schedulingtimings', 'action' => 'startschedulec4',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le', 'confirm' => 'Are you sure you want to start scheduling for this schedule category 4?']);
							}
							?>
						</td>
						<td>
							<?php
							if(count($arrEventsC4) > 0)
							{
								// now check if there is any scheduling already done
								$checkScheduling = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 4,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year])->first();
								
								if($checkScheduling)
								{
									echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,4], ['class'=>'btn btn-success canlcel_le']);
								}
								else
								{
									echo 'Schedulings not yet done';
								}
							}
							?>
						</td>
					</tr>
					
					<!-- Remove overlapping -->
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
					</tr>
					
					<tr>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>&nbsp;</td>
						<td>
						<?php
						echo $this->Html->link('Remove Overlapping', ['controller'=>'schedulingtimings', 'action' => 'removeoverlapping',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le', 'confirm' => 'Are you sure you want to start process to remove overlapping?']);
						?>
						</td>
						
					</tr>
					
					
					
					
				</table>
			</div>
			
			
			
             
                <div class="form-horizontal">
					<div class="box-body">
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
						
						<?php
						echo $this->Html->link('<< Back To Pre-check', ['controller'=>'schedulings', 'action' => 'precheck',$convention_season_slug], ['class'=>'btn btn-default canlcel_le']);
						?>
						
						
                    </div>
                  </div>
                </div> 
          </div>
    </section>
  </div>