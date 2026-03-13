<script type="text/javascript">
$(document).ready(function () {
	$("#judgingform").validate();
});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			
			
			
			<h2 class="mt-3">Judges Evaluation :: Low Score :: <?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)</h2>
			
			<p style='color:red;'>Scores of less than 50 require approval. To place, a student must reach 70 or more points. Remembering the purpose of student convention, please reassess your scores for possible increases to encourage students. If still below 50, please discuss with master control.</p>
			  
			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<?php echo $this->Form->create($judgingform, ['id'=>'judgingform', 'type' => 'file', 'class' =>'', 'autocomplete' =>'off']); ?>
				<h2 class="form-title">Your Score :: <?php echo $judges_evaluation_low_score; ?></h2>
				
					
					<div class="form-group">
						<label for="name">Enter Pin To Save Low Score</label>
						<?php echo $this->Form->input('low_score_pin', ['id'=>'low_score_pin', 'label'=>false, 'type'=>'password',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Enter Pin To Save Low Score','autocomplete' => 'off']); ?>
					</div>
					
					<div class="form-group form-btns">
						<label></label>
						<button type="submit" class="btn btn-secondary">Save</button>
						<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
						<?php echo $this->Html->link('Re-evaluate', ['controller'=>'judgeevaluations', 'action' => 'addnew',$conv_reg_slug,$event_submission_slug], ['class'=>'btn btn-secondary']); ?>
					</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->
			
		</main>
	</div>
</div>