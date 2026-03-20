<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>Scheduling Pre-check</h1>
        <p style="margin:2px 0 0 0; font-size:13px; color:#777;"><small><?php echo $conventionSD->Conventions['name']; ?> &mdash; <?php echo $conventionSD->season_year; ?></small></p>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Pre-check </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			
			
			
			<div class="container">
				<h2>Scheduling Pre-check</h2>
				<p>
					<?php
					echo $this->Html->link('Run All Pre-checks', ['controller'=>'schedulings', 'action' => 'runallprechecks',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le', 'confirm' => 'Are you sure you want to run all pre-checks now ?']);
					?>
				</p>
				<table class="table table-bordered">
					<tr>
						<th>#</th>
						<th>Pre-Check Status</th>
						<th>Data Found</th>
						<th>Action</th>
					</tr>
					
					<tr>
						<td>Events</td>
						<td>
							<?php
							if($schedulingD->precheck_events>0)
							{
								echo '<i class="fa fa-check"></i>';
							}
							?>
						</th>
						<td>
							<?php
							if($schedulingD->precheck_events>0)
							{
								echo 'Total event(s) found: '.$schedulingD->total_events_found;
							}
							?>
						</th>
						<td>
							Run from "Run All Pre-checks"
						</th>
					</tr>
					
					<tr>
						<td>Locations</td>
						<td>
							<?php
							if($schedulingD->precheck_locations>0)
							{
								echo '<i class="fa fa-check"></i>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_locations>0)
							{
								echo 'Total location(s) found: '.$schedulingD->total_locations_found;
							}
							?>
						</td>
						<td>
							Run from "Run All Pre-checks"
						</td>
					</tr>
					
					<tr>
						<td>Registrations</td>
						<td>
							<?php
							if($schedulingD->precheck_registrations>0)
							{
								echo '<i class="fa fa-check"></i>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_registrations>0)
							{
								echo 'Total registration(s) found: '.$schedulingD->total_registrations_found;
							}
							?>
						</td>
						<td>
							Run from "Run All Pre-checks"
						</td>
					</tr>
					
					
					<tr>
						<td>Students</td>
						<td>
							<?php
							if($schedulingD->precheck_students>0)
							{
								echo '<i class="fa fa-check"></i>';
							}
							?>
						</td>
						<td>
							<?php
							if($schedulingD->precheck_students>0)
							{
								echo 'Total student(s) found: '.$schedulingD->total_students_found;
							}
							?>
						</td>
						<td>
							Run from "Run All Pre-checks"
						</td>
					</tr>
					
				</table>
			</div>
			
			
			
            <?php echo $this->Form->create($conventions, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
					<div class="box-body">
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
						
						<?php
						echo $this->Html->link('<< Back To Seasons', ['controller'=>'conventions', 'action' => 'seasons',$convention_slug], ['class'=>'btn btn-default canlcel_le']);
						
						echo $this->Html->link('Reset All Pre-check', ['controller'=>'schedulings', 'action' => 'resetallprecheck',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le', 'confirm' => 'Are you sure you want to reset all pre-checks ?']);

						echo $this->Html->link('Scheduling Wizard', ['controller'=>'schedulings', 'action' => 'wizard',$convention_season_slug], ['class'=>'btn btn-success canlcel_le','title'=>'Scheduling Wizard']);
						
						echo $this->Html->link('View/Start Scheduling', ['controller'=>'schedulings', 'action' => 'schedulecategory',$convention_season_slug], ['class'=>'btn btn-success canlcel_le','title'=>'View/Start Scheduling']);
						
						echo $this->Html->link('Overwrite Timings', ['controller'=>'schedulings', 'action' => 'overwritetimings',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le','title'=>'Overwrite Timings']);
						
						echo $this->Html->link('Reports', ['controller'=>'schedulings', 'action' => 'reports',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Generate Reports']);
						
						
						?>
						
						
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
			
			
			
			
          </div>
		  
		  
			
			
    </section>
  </div>