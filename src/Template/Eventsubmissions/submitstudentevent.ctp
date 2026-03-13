<script type="text/javascript">
	$(document).ready(function () {
		$("#submitnewevent").validate();
	});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3">Submit Student Event</h2>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Submit New Event</h2>
				<?php echo $this->Form->create($eventsubmissions, ['id' => 'submitnewevent', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<label for="name">Event</label>
					<div class="input-multiple">
						<?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)
					</div>
				</div>

				<div class="form-group">
					<label for="name">Student</label>
					<div class="input-multiple">
						<?php echo $convRegStudentD->Students['first_name']; ?> <?php echo $convRegStudentD->Students['middle_name']; ?> <?php echo $convRegStudentD->Students['last_name']; ?>
					</div>
				</div>

				<?php
				if(strtolower($eventD->upload_type) != 'nil')
				{
				?>
				<div class="form-group">
					<label for="event_document">Upload File</label>
					<div class="input-multiple">
						<?php echo $this->Form->input('Eventsubmissions.event_document', ['id' => 'event_document', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidation()']); ?>
						<span class="help_text">mp3, mp4, mpeg, mov, avi, amr, ape, nmf, 3gp, pdf, doc, docx, png, jpg, jpeg, ppt and pptx files allowed.</span>
					</div>
				</div>
				<?php
				}
				?>
				
				
				<?php
				if($eventD->report == 1)
				{
				?>
				<div class="form-group">
					<label for="event_document">Report</label>
					<div class="input-multiple">
						<?php echo $this->Form->input('Eventsubmissions.report', ['id' => 'report', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidationReport()']); ?>
						<span class="help_text">mp3, pdf, doc, docx, png, jpg, jpeg, ppt and pptx files allowed.</span>
					</div>
				</div>
				<?php
				}
				?>
				
				
				<?php
				if($eventD->context_box == 1)
				{
				?>
				<div class="form-group">
					<label for="event_document">Context Box</label>
					<div class="input-multiple">
						<?php echo $this->Form->input('Eventsubmissions.context_box', ['id' => 'context_box', 'label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control required']); ?>
					</div>
				</div>
				<?php
				}
				?>
				
				
				<?php
				if($eventD->score_sheet == 1)
				{
				?>
				<div class="form-group">
					<label for="event_document">Score Sheet</label>
					<div class="input-multiple">
						<?php echo $this->Form->input('Eventsubmissions.score_sheet', ['id' => 'score_sheet', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidationSS()']); ?>
						<span class="help_text">mp3, pdf, doc, docx, png, jpg, jpeg, ppt and pptx files allowed.</span>
					</div>
				</div>
				<?php
				}
				?>
				
				
				<?php
				if($eventD->additional_documents == 1)
				{
				?>
				<div class="form-group">
					<label for="event_document">Additional Document</label>
					<div class="input-multiple">
						<?php echo $this->Form->input('Eventsubmissions.additional_documents', ['id' => 'additional_documents', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidationAD()']); ?>
						<span class="help_text">mp3, pdf, doc, docx, png, jpg, jpeg, ppt and pptx files allowed.</span>
					</div>
				</div>
				<?php
				}
				?>
				
				<?php
				if(!empty($eventD->book_ids))
				{
				?>
				<div class="form-group">
					<label for="name">Choose Book(s)</label>
					<div class="input-multiple">
						<?php echo $this->Form->select('Eventsubmissions.book_ids', $booksDD, ['id' => 'book_ids', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose', 'multiple' => 'multiple']); ?>
						<script>
							$(document).ready(function () {
								$('#book_ids').select2();
							});
						</script>
					</div>
				</div>
				<?php
				}
				?>

				<div class="form-group form-btns class_show_hide">
					<label></label>
					<button type="submit" class="btn btn-secondary">Submit Event</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('<< Back', ['controller' => 'conventionregistrations', 'action' => 'packageregistration'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->

		</main>
	</div>
</div>


<script>
	function in_array(needle, haystack) {
		for (var i = 0, j = haystack.length; i < j; i++) {
			if (needle == haystack[i])
				return true;
		}
		return false;
	}

	function getExt(filename) {
		var dot_pos = filename.lastIndexOf(".");
		if (dot_pos == -1)
			return;
		return filename.substr(dot_pos + 1).toLowerCase();
	}

	function imageValidation() {
		var filename = document.getElementById("event_document").value;
		var filetype = ['mp3', 'mp4', 'mpeg', 'mov', 'avi', 'amr', 'ape', 'nmf', '3gp', 'pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'ppt', 'pptx'];
		
		if (filename != '') {
			var ext = getExt(filename);
			ext = ext.toLowerCase();
			var checktype = in_array(ext, filetype);
			if (!checktype) {
				alert(ext + " file not allowed.");
				document.getElementById("event_document").value = "";
				return false;
			} else {
			}
		}
		return true;
	}
	
	function imageValidationReport() {
		var filename = document.getElementById("report").value;
		var filetype = ['mp3', 'pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'ppt', 'pptx'];
		
		if (filename != '') {
			var ext = getExt(filename);
			ext = ext.toLowerCase();
			var checktype = in_array(ext, filetype);
			if (!checktype) {
				alert(ext + " file not allowed.");
				document.getElementById("report").value = "";
				return false;
			} else {
			}
		}
		return true;
	}
	
	function imageValidationSS() {
		var filename = document.getElementById("score_sheet").value;
		var filetype = ['mp3', 'pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'ppt', 'pptx'];
		
		if (filename != '') {
			var ext = getExt(filename);
			ext = ext.toLowerCase();
			var checktype = in_array(ext, filetype);
			if (!checktype) {
				alert(ext + " file not allowed.");
				document.getElementById("score_sheet").value = "";
				return false;
			} else {
			}
		}
		return true;
	}
	
	function imageValidationAD() {
		var filename = document.getElementById("additional_documents").value;
		var filetype = ['mp3', 'pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg', 'ppt', 'pptx'];
		
		if (filename != '') {
			var ext = getExt(filename);
			ext = ext.toLowerCase();
			var checktype = in_array(ext, filetype);
			if (!checktype) {
				alert(ext + " file not allowed.");
				document.getElementById("additional_documents").value = "";
				return false;
			} else {
			}
		}
		return true;
	}
</script>