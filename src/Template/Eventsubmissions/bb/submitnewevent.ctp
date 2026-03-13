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

			<h2 class="mt-3">Submit New Event</h2>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Submit New Event</h2>
				<?php echo $this->Form->create($eventsubmissions, ['id' => 'submitnewevent', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<label for="name">Choose Event</label>
					<div class="input-multiple">
						<?php echo $this->Form->select('Eventsubmissions.event_id', $eventNameIDDD, ['id' => 'event_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
						<script>
							$(document).ready(function () {
								$('#event_id').select2();
							});
						</script>
					</div>
				</div>

				<div class="form-group" id="box_group_name" style="display:none;">
					<div class="d-flex">
						<label for="name">Choose Group</label>
						<div class="input-multiple">
							<?php echo $this->Form->select('Eventsubmissions.group_name', array(), ['id' => 'group_name', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
							<script>
								$(document).ready(function () {
									$('#group_name').select2();
								});
							</script>
						</div>
					</div>
				</div>

				<div class="form-group" id="box_student_list" style="display:none;">
					<div class="d-flex">
						<label for="name">Choose Student</label>
						<div class="input-multiple">
							<?php echo $this->Form->select('Eventsubmissions.student_id', array(), ['id' => 'student_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
							<script>
								$(document).ready(function () {
									$('#student_id').select2();
								});
							</script>
						</div>
					</div>
				</div>

				<div class="form-group" id="box_upload_type" style="display:none;">
					<div class="d-flex">
						<label for="event_document">Upload File</label>
						<div class="input-multiple">
							<?php echo $this->Form->input('Eventsubmissions.event_document', ['id' => 'event_document', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidation()']); ?>
							<span class="help_text">mp3, mp4, mpeg, mov, avi, amr, ape, nmf, 3gp, pdf, doc, docx, png, jpg, jpeg, ppt and pptx files allowed.</span>
						</div>
					</div>
				</div>
				
				<div class="form-group" id="box_report" style="display:none;">
					<div class="d-flex">
						<label for="event_document">Report</label>
						<div class="input-multiple">
							<?php echo $this->Form->input('Eventsubmissions.report', ['id' => 'report', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidationReport()']); ?>
							<span class="help_text">mp3, pdf, doc, docx, png, jpg, jpeg, ppt and pptx files allowed.</span>
						</div>
					</div>
				</div>
				
				<div class="form-group" id="box_context_box" style="display:none;">
					<div class="d-flex">
						<label for="event_document">Context Box</label>
						<div class="input-multiple">
							<?php echo $this->Form->input('Eventsubmissions.context_box', ['id' => 'context_box', 'label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control required']); ?>
						</div>
					</div>
				</div>
				
				<div class="form-group" id="box_score_sheet" style="display:none;">
					<div class="d-flex">
						<label for="event_document">Score Sheet</label>
						<div class="input-multiple">
							<?php echo $this->Form->input('Eventsubmissions.score_sheet', ['id' => 'score_sheet', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidationSS()']); ?>
							<span class="help_text">mp3, pdf, doc, docx, png, jpg, jpeg, ppt and pptx files allowed.</span>
						</div>
					</div>
				</div>
				
				<div class="form-group" id="box_additional_documents" style="display:none;">
					<div class="d-flex">
						<label for="event_document">Additional Document</label>
						<div class="input-multiple">
							<?php echo $this->Form->input('Eventsubmissions.additional_documents', ['id' => 'additional_documents', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidationAD()']); ?>
							<span class="help_text">mp3, pdf, doc, docx, png, jpg, jpeg, ppt and pptx files allowed.</span>
						</div>
					</div>
				</div>
				
				<div class="form-group" id="box_book_list" style="display:none;">
					<div class="d-flex">
						<label for="name">Choose Book(s)</label>
						<div class="input-multiple">
							<?php echo $this->Form->select('Eventsubmissions.book_ids', array(), ['id' => 'book_ids', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'multiple' => 'multiple']); ?>
							<script>
								$(document).ready(function () {
									$('#book_ids').select2();
								});
							</script>
						</div>
					</div>
				</div>

				<div class="form-group form-btns class_show_hide" style="display:none;">
					<label></label>
					<button type="submit" class="btn btn-secondary">Submit Event</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('<< Back', ['controller' => 'eventsubmissions', 'action' => 'viewlist'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->

		</main>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		$('#event_id').change(function () {

			$("#box_group_name").css("display", "none");
			$("#box_student_list").css("display", "none");
			$(".class_show_hide").css("display", "none");
			
			$("#box_upload_type").css("display", "none");
			$("#box_report").css("display", "none");
			$("#box_context_box").css("display", "none");
			$("#box_score_sheet").css("display", "none");
			$("#box_additional_documents").css("display", "none");
			
			$("#box_book_list").css("display", "none");

			var event_id = $("#event_id").val();

			if (event_id == 0 || event_id == '') {
				alert("Please choose an event.");
				return false;
			}

			$.ajax({
				type: 'POST',
				url: "<?php echo HTTP_PATH . "/homes/eventsubmissions/"; ?>" + event_id,
				cache: false,
				beforeSend: function () {
					//$("#loderstatus").show();
				},
				complete: function () {
					//$("#loderstatus").hide();
				},
				success: function (result) {
					//$("#loderstatus").hide();
					//$("#test_res").html(result);
					//return false;
					//alert(result);
					//return false;
					
					var objReturned = $.parseJSON(result);

					var event_type 				= objReturned.event_type;
					var dropdown_values 		= objReturned.dropdown_values;
					
					var upload_type 			= objReturned.upload_type;
					var report 					= objReturned.report;
					var context_box 			= objReturned.context_box;
					var score_sheet 			= objReturned.score_sheet;
					var additional_documents 	= objReturned.additional_documents;
					
					var book_dropdown_values 	= objReturned.book_dropdown_values;
					//alert(book_dropdown_values);

					// to check if dropdown_values is empty
					if ($.isEmptyObject(dropdown_values)) {
						if (event_type == "group_event") {
							alert('Sorry, no group found for this event.');
						}
						else {
							alert('Sorry, no student found for this event.');
						}

						return false;
					}

					if (event_type == "group_event") {
						// for group event
						$("#box_group_name").css("display", "block");
						$(".class_show_hide").css("display", "block");

						// empty dropdown values
						$('#group_name').children('option:not(:first)').remove();
						$.each(dropdown_values, function (key, entry) {
							$('#group_name').append($('<option></option>').attr('value', entry.id).text(entry.name));
							//alert(entry.name);
						});
					}
					else
					{
						// for solo event
						$("#box_student_list").css("display", "block");
						$(".class_show_hide").css("display", "block");

						// empty dropdown values
						$('#student_id').children('option:not(:first)').remove();
						$.each(dropdown_values, function (key, entry) {
							$('#student_id').append($('<option></option>').attr('value', entry.id).text(entry.name));
							//alert(entry.name);
						});
					}
					
					// now show hide fields based on values received
					if (!($.isEmptyObject(dropdown_values)))
					{
						if(upload_type != "Nil")
						{
							$("#box_upload_type").css("display", "block");
						}
						if(report == "1")
						{
							$("#box_report").css("display", "block");
						}
						if(context_box == "1")
						{
							$("#box_context_box").css("display", "block");
						}
						if(score_sheet == "1")
						{
							$("#box_score_sheet").css("display", "block");
						}
						if(additional_documents == "1")
						{
							$("#box_additional_documents").css("display", "block");
						}
					}
					
					
					// to check for books dropdown
					if (!($.isEmptyObject(book_dropdown_values)))
					{
						$("#box_book_list").css("display", "block");
						$.each(book_dropdown_values, function (key, entry) {
							$('#book_ids').append($('<option></option>').attr('value', entry.id).text(entry.name));
							//alert(entry.name);
						});
					}
					
					
				}
			});
			return false;
		});
	});
</script>

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