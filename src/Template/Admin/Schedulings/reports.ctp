<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>


<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Scheduling Reports - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Reports </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php //echo $this->Form->create($schedulings, ['id'=>'schedulingWizardForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					
					<!-- Convention Days Starts -->
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
						  echo $this->Html->link('Report By Students', ['controller'=>'schedulingreports', 'action' => 'bystudents',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Report By Schools/Students', 'style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
						  echo $this->Html->link('Report By School', ['controller'=>'schedulingreports', 'action' => 'byschools',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Report By School','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
						  echo $this->Html->link('Report By Sponsor', ['controller'=>'schedulingreports', 'action' => 'bysponsors',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Report By Sponsor/Supervisor','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
						  echo $this->Html->link('Report By Events/Sport', ['controller'=>'schedulingreports', 'action' => 'byevents',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Report By Events/Sport','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
						  echo $this->Html->link('Report By Rooms/Location', ['controller'=>'schedulingreports', 'action' => 'byrooms',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Report By Rooms','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
						  echo $this->Html->link('Small Program', ['controller'=>'schedulingreports', 'action' => 'smallprogram',$convention_season_slug], ['class'=>'btn btn-success canlcel_le','title'=>'Overview of all events for sponsors and visitors','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>

          <div class="form-group">
              <label class="col-sm-2 control-label">&nbsp;</label>
              <div class="col-sm-10">
              <?php
              echo $this->Html->link('Customize Small Program v2', ['controller'=>'schedulingreports', 'action' => 'smallprogramv2customize',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le','title'=>'Edit Small Program v2 title, notes and styling','style' =>'width:220px;text-align:center;']);
              ?>
              </div>
            </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
						  echo $this->Html->link('Location Time Allocation', ['controller'=>'schedulingreports', 'action' => 'locationtimeallocation',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le','title'=>'Required vs Available time per room (diagnostic)','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					
                  </div>
                </div>
            <?php //echo $this->Form->end(); ?>
          </div>
    </section>
  </div>
  