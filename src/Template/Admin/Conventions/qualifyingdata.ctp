<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Enter Qualifying Scores/Time - Event :: <?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$slug_convention], ['escape'=>false]);?></li>
		  
		  <li><?php echo $this->Html->link($conventionSD->season_year, ['controller'=>'conventions', 'action'=>'events',$slug_convention_season,$slug_convention], ['escape'=>false]);?></li>
		  
          <li class="active">Qualifying Data - <?php echo $conventionSD->Conventions['name']; ?></li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create(NULL, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<?php
					if($eventD->event_judging_type == 'times')
					{	
						if($convSeasEventD->qualifying_time_score != NULL && !empty($convSeasEventD->qualifying_time_score))
						{
							$tScore = $convSeasEventD->qualifying_time_score;
							$tScoreC = $tScore->format('H:i:s.u');
							
							// now remove padded zeros
							if (strpos($tScoreC, '.') !== false) {
							list($hms, $micro) = explode('.', $tScoreC);
							$micro = rtrim($micro, '0'); // remove trailing zeros

							if ($micro === '') {
								$formattedTime = $hms;
							} else {
								$formattedTime = $hms . '.' . $micro;
							}
							} else {
								$formattedTime = $tScoreC;
							}
						}
					?>
					<div class="form-group">
                      <label class="col-sm-2 control-label"></span></label>
					  <div class="col-sm-10"><b>Please enter time in format hh:mm:ss.ms</b></div>
                      
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Qualifying Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php //echo $this->Form->input('Conventions.name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Convention Name', 'autocomplete'=>'off']); ?>
						  <input class="form-control required" type="text" name="qualifying_time_score" id="qualifying_time_score" value="<?php echo $formattedTime; ?>">
                      </div>
                    </div>
					<?php
					}
					?>
					
					<?php
					if($eventD->event_judging_type == 'distances')
					{
					?>
					<div class="form-group">
                      <label class="col-sm-2 control-label"></span></label>
					  <div class="col-sm-10"><b>Please enter distance in meters</b></div>
                      
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Qualifying Distance <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <input class="form-control required" type="text" name="qualifying_distance" id="qualifying_distance" value="<?php echo $convSeasEventD->qualifying_distance; ?>">
                      </div>
                    </div>
					<?php
					}
					?>
					
					<?php
					if($eventD->event_judging_type == 'scores')
					{
					?>
					<div class="form-group">
                      <label class="col-sm-2 control-label"></span></label>
					  <div class="col-sm-10"><b>Please enter qualifying score</b></div>
                      
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Qualifying Score <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <input class="form-control required" type="number" name="qualifying_score" id="qualifying_score" value="<?php echo $convSeasEventD->qualifying_score; ?>">
                      </div>
                    </div>
					<?php
					}
					?>
                    
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'conventions', 'action' => 'events',$slug_convention_season,$slug_convention], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>