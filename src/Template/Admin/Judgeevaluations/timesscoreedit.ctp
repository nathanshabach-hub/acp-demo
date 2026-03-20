<script type="text/javascript">
    $(document).ready(function() {
        $("#timesScoreForm").validate();
    });
</script>
<?php
if($judgeEvalD->time_score != NULL && !empty($judgeEvalD->time_score))
{
	$tScore = $judgeEvalD->time_score;
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

if($judgeEvalD->withdraw_yes_no == 1)
	$checkedS = 'checked';
else
	$checkedS = '';
?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Times Score
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
		  
		   <li><?php echo $this->Html->link('<i class="fa fa-gavel"></i> <span>Judge Evaluation</span> ', array('controller'=>'judgeevaluations', 'action'=>'index'), array('escape'=>false));?></li>
          <li class="active">Edit Times Score</li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($judgeevaluations, ['id'=>'timesScoreForm']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
                    
					<div class="form-group">
                      <label class="col-sm-2 control-label">Event <span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:7px;">
                          <?php echo $judgeEvalD->Events['event_name']; ?> (<?php echo $judgeEvalD->Events['event_id_number']; ?>)
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">School <span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:7px;">
                          <?php echo $judgeEvalD->Schools['first_name']; ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Student <span class="require"></span></label>
                      <div class="col-sm-10" style="padding-top:7px;">
						<?php echo $judgeEvalD->Students['first_name']; ?> <?php echo $judgeEvalD->Students['middle_name']; ?> <?php echo $judgeEvalD->Students['last_name']; ?>
                      </div>
                    </div>
					
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Judgeevaluations.time_score', ['label'=>false, 'type'=>'text', 'div'=>false, 'class'=>'form-control required', 'placeholder'=>'', 'autocomplete'=>'off', 'value' => $formattedTime]); ?>
						  <span class="help_text"><i>Please enter time in format hh:mm:ss.ms</i></span>
                      </div>
                    </div>
					
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Place <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Judgeevaluations.place', ['label'=>false, 'type'=>'number', 'div'=>false, 'min'=>1, 'class'=>'form-control required', 'placeholder'=>'', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Withdraw </label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Judgeevaluations.withdraw_yes_no', ['label'=>false, 'type'=>'checkbox', 'div'=>false,  'placeholder'=>'', 'autocomplete'=>'off','style' => 'width:6%;', $checkedS]); ?>
                      </div>
                    </div>

                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Submit', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'judgeevaluations','action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>
