<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>Scheduling Reports By Sponsor</h1>
        <p style="margin:2px 0 0 0; font-size:13px; color:#777;"><small><?php echo $conventionSD->Conventions['name']; ?> &mdash; <?php echo $conventionSD->season_year; ?></small></p>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Reports By Sponsor</li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create(NULL, ['id'=>'schedulingWizardForm', 'url'=>['controller'=>'schedulingreports','action'=>'bysponsorsshow',$convention_season_slug], 'type' => 'get']); ?>
                <div class="form-horizontal">
                    <div class="box-body">

					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose Sponsor / Supervisor <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('sponsor_id', $sponsorsDD, ['id' => 'sponsor_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose Sponsor']); ?>
							<script>
							$(document).ready(function() {
								$('#sponsor_id').select2();
							});
							</script>
                      </div>
                    </div>

                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Generate Report', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action' => 'reports', $convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>
