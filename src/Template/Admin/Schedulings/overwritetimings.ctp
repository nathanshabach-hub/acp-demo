<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>
<script>
	$(document).ready(function(){
		$('.mdtpicker').mdtimepicker(); //Initializes the time picker
	});
</script>

<?php echo $this->Html->script('jquery/ui/jquery.ui.core.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.widget.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.position.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.datepicker.js'); ?>
<?php echo $this->Html->css('themes/ui-lightness/jquery.ui.all.css'); ?>
<script>
    $(function() {
        $( "#overwrite_date" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
			minDate: '0d',
            maxDate: '+2y'
        });
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Overwrite Timings - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Overwrite Timings </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title" style="color:Red;">
				Note: You can perform "Overwrite Timings" after schedulings and resolving conflicts. Overwrite timings does not have any link with conflicts. System might show conflicts after overwrite timings and those conflicts will not list under button.
				</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($schedulings, ['id'=>'schedulingWizardForm', 'type' => 'file', 'autocomplete' => 'off']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					
<!-- Event selection (checkboxes) -->
				<div class="form-group">
                      <label class="col-sm-2 control-label">Choose Events <span class="require">*</span></label>
                      <div class="col-sm-10">
					  <?php if(!empty($finalEventArr)): ?>
					  <div style="border:1px solid #ddd;padding:10px 14px;border-radius:4px;background:#fafafa;">
					  <?php foreach($finalEventArr as $evId => $evLabel): ?>
					  <div class="checkbox" style="margin:4px 0;">
						  <label style="font-weight:normal;font-size:1em;">
							  <input type="checkbox" name="data[Schedulings][event_ids][]" value="<?php echo h($evId); ?>">
							  &nbsp;<?php echo h($evLabel); ?>
						  </label>
					  </div>
					  <?php endforeach; ?>
					  </div>
					  <p class="help-block">Tick one or more events to overwrite. Each selected event will independently start from the chosen date &amp; time.</p>
					  <?php else: ?>
					  <p class="text-muted">No eligible events found for this convention season.</p>
					  <?php endif; ?>
                      </div>
                    </div>
					
					<div class="form-group">
					<label class="col-sm-2 control-label">Convention Days <span class="require">*</span></label>
					<div class="col-sm-10">
					<?php if(!empty($conventionDays)): ?>
					<p class="help-block" style="margin-bottom:6px;">Tick each day you want to overwrite and set a start time for that day. Unticked days will not be changed.</p>
					<table class="table table-bordered table-condensed" style="width:auto;min-width:500px;">
						<thead><tr style="background:#eef3f8;">
							<th style="width:40px;text-align:center;">Include</th>
							<th>Convention Day</th>
							<th style="width:180px;">Start Time</th>
						</tr></thead>
						<tbody>
						<?php foreach($conventionDays as $di => $cd): ?>
						<tr>
							<td style="text-align:center;vertical-align:middle;">
								<input type="checkbox" name="data[Schedulings][days][<?php echo $di; ?>][active]" value="1" id="day_active_<?php echo $di; ?>">
								<input type="hidden" name="data[Schedulings][days][<?php echo $di; ?>][date]" value="<?php echo h($cd['date']); ?>">
							</td>
							<td><label for="day_active_<?php echo $di; ?>" style="font-weight:normal;margin:0;cursor:pointer;"><?php echo h($cd['display']); ?></label></td>
							<td><input type="text" name="data[Schedulings][days][<?php echo $di; ?>][time]" class="form-control mdtpicker" placeholder="e.g. 09:00 AM" style="width:100%;"></td>
						</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<?php else: ?>
					<p class="text-muted">No convention days found. Please run scheduling first.</p>
					<?php endif; ?>
					</div>
				</div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Max Students <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->input('Schedulings.max_students', ['id'=>'max_students', 'label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Max Students', 'min'=>'1']); ?>
                      </div>
                    </div>

				<div class="form-group">
                      <label class="col-sm-2 control-label">Gap Between Groups (mins) <span class="require">*</span></label>
                      <div class="col-sm-10">
					  <?php echo $this->Form->input('Schedulings.time_gap_mins', ['id'=>'time_gap_mins', 'label'=>false, 'type'=>'number', 'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Minutes gap between groups', 'min'=>'0', 'value'=>'1']); ?>
					  <p class="help-block">Number of minutes to leave between each group of students. Default is 1.</p>
                      </div>
                    </div>

                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Overwrite', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'schedulings', 'action' => 'precheck', $convention_season_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>