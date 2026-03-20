<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>


<div class="content-wrapper">
    <section class="content-header">
      <h1>Scheduling Reports</h1>
        <p style="margin:2px 0 0 0; font-size:13px; color:#777;"><small><?php echo $conventionSD->Conventions['name']; ?> &mdash; <?php echo $conventionSD->season_year; ?></small></p>
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
          <?php if (!empty($phase4ReportParityRows)) { ?>
          <div class="panel panel-default" style="margin-bottom:15px;">
            <div class="panel-heading"><strong>Phase 4 Report Parity Checklist</strong></div>
            <div class="panel-body" style="padding-bottom:0;">
              <p style="margin-top:0;">
                Legacy Program 17 report targets are mapped below.
                <?php echo !empty($phase4ReportParityReady) ? 'Scheduling data found.' : 'No scheduling rows found yet; reports still accessible.'; ?>
              </p>
              <div class="table-responsive">
                <table class="table table-bordered table-condensed">
                  <thead>
                    <tr>
                      <th>Legacy Target</th>
                      <th>ACP Report</th>
                      <th>Print</th>
                      <th>CSV</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($phase4ReportParityRows as $parityRow) { ?>
                    <tr>
                      <td><?php echo h($parityRow['legacy_key']); ?></td>
                      <td><?php echo $this->Html->link(h($parityRow['acp_label']), $parityRow['route']); ?></td>
                      <td><?php echo !empty($parityRow['printable']) ? 'Yes' : 'No'; ?></td>
                      <td><?php echo !empty($parityRow['csv']) ? 'Yes' : 'No'; ?></td>
                      <td><?php echo h($parityRow['status']); ?></td>
                    </tr>
                  <?php } ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php } ?>
					
					
					<!-- Convention Days Starts -->
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
              echo $this->Html->link('Schedule by Student', ['controller'=>'schedulingreports', 'action' => 'bystudents',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Schedule by Student', 'style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
              echo $this->Html->link('Schedule by School', ['controller'=>'schedulingreports', 'action' => 'byschools',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Schedule by School','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
              echo $this->Html->link('Schedule by Sponsor', ['controller'=>'schedulingreports', 'action' => 'bysponsors',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Schedule by Sponsor/Supervisor','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
              echo $this->Html->link('Schedule by Event', ['controller'=>'schedulingreports', 'action' => 'byevents',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Schedule by Event','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
              echo $this->Html->link('Schedule by Location', ['controller'=>'schedulingreports', 'action' => 'byrooms',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Schedule by Location','style' =>'width:220px;text-align:center;']);
						  ?>
                      </div>
                    </div>

					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						  <?php
              echo $this->Html->link('Schedule by Match', ['controller'=>'schedulingreports', 'action' => 'bymatchshow',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Master match/performance schedule','style' =>'width:220px;text-align:center;']);
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
  