<script type="text/javascript">
	$(document).ready(function () {
		$("#addeventsheart").validate();
	});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3">Add Events of the Heart</h2>

			<!-- dashboard-section-3 start-->
			<div class="dashboard-form">
				<h2 class="form-title">Add Events of the Heart</h2>
				<?php echo $this->Form->create($heartevents, ['id' => 'addeventsheart', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<label for="student_id">Choose Student</label>
					<div class="input">
						<?php echo $this->Form->select('Heartevents.student_id', $studentSchoolDD, ['id' => 'student_id', 'label' => false, 'div' => false, 'class' => 'form-control js-example-basic-multiple required ', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
						<script>
							$(document).ready(function () {
								$('#student_id').select2();
							});
						</script>
					</div>
				</div>

				<div class="form-group">
					<label for="mediafile_title">Document Title</label>
					<?php echo $this->Form->control('Heartevents.mediafile_title', ['id' => 'mediafile_title', 'label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control required']); ?>
				</div>

				<div class="form-group mb-5">
					<label for="event_document">Upload File</label>
					<div class="input">
					<?php echo $this->Form->control('Heartevents.event_document', ['id' => 'event_document', 'label' => false, 'type' => 'file', 'div' => false, 'class' => 'form-control required', 'onchange' => 'imageValidation()']); ?>
					<span class="help_text">pdf, doc, docx, png, jpg and jpeg files allowed.</span>
					</div>
				</div>

				<div class="form-group form-btns">
					<label></label>
					<button type="submit" class="btn btn-secondary">Save</button>
					<!--<button type="button" class="btn btn-secondary">Cancel</button>-->
					<?php echo $this->Html->link('Cancel', ['controller' => 'heartevents', 'action' => 'viewlist'], ['class' => 'btn btn-secondary']); ?>
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
		var filetype = ['pdf', 'doc', 'docx', 'png', 'jpg', 'jpeg'];
		
		if (filename != '') {
			var ext = getExt(filename);
			ext = ext.toLowerCase();
			var checktype = in_array(ext, filetype);
			if (!checktype) {
				alert(ext + " file not allowed.");
				document.getElementById("event_document").value = "";
				return false;
			} else {
				/*
				var fi = document.getElementById('mediafile_file');
				var filesize = fi.files[0].size;//check uploaded file size
				if (filesize > 2097152) {
					alert('Maximum 2MB file size allowed for product image .');
					return false;
				}
				*/
			}
		}
		return true;
	}
</script>