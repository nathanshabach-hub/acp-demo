<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>
<?php
if(!empty($evaluationforms->tag_ids))
{
	$tag_ids_explode = explode(",",$evaluationforms->tag_ids);
}
if(!empty($evaluationforms->event_id_numbers))
{
	$event_id_numbers_explode = explode(",",$evaluationforms->event_id_numbers);
}
?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Edit Form
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link(' Evaluation Forms ', ['controller'=>'evaluationforms', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Edit Form </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($evaluationforms, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Form Name <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Evaluationforms.name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Form Name', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Tags <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Evaluationforms.tag_ids', $tagsDD, ['id' => 'tag_ids', 'label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'multiple' => 'multiple', 'value' => $tag_ids_explode]); ?>
							<script>
								$(document).ready(function() {
									$('#tag_ids').select2();
								});
							</script>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Events <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Evaluationforms.event_id_numbers', $eventNameIDDD, ['id' => 'event_id_numbers', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'multiple' => 'multiple', 'value' => $event_id_numbers_explode]); ?>
							<script>
								$(document).ready(function() {
									$('#event_id_numbers').select2();
								});
							</script>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Reference PDF File <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Evaluationforms.reference_pdf_file_name', ['id'=>'reference_pdf_file_name', 'label'=>false, 'type'=>'file',  'div'=>false, 'class'=>'form-control', 'onchange'=>'imageValidation()', 'id'=>'reference_pdf_file_name']); ?>
                      </div>
                    </div>
					
					<?php
					$imgCat = $evaluationforms->reference_pdf_file_name;
					if(file_exists(UPLOAD_JUDGING_REFERENCE_PDF_PATH.$imgCat) && !empty($imgCat))
					{
					?>
					
					<div class="form-group">
                        <label class="col-sm-2 control-label">PDF File <span class="require"></span></label>
                        <div class="col-sm-10">
						<div class="img"><b>
						<a target="_blank" href="<?php echo DISPLAY_JUDGING_REFERENCE_PDF_PATH.$evaluationforms->reference_pdf_file_name ?>">
						<?php echo $evaluationforms->reference_pdf_file_name;?>
						</a></b>
						</div>
                        </div>
                    </div>
					
					<div class="form-group">
                        <label class="col-sm-2 control-label">Remove PDF <span class="require"></span></label>
                        <div class="col-sm-10">
                            <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'evaluationforms', 'action' => 'deletepdf',$evaluationforms->slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to remove pdf ?']); ?>
							<?php echo $this->Form->input('Evaluationforms.hidd_icon', ['label'=>false, 'type'=>'hidden', 'value'=>$evaluationforms->reference_pdf_file_name]); ?>
                        </div>
                    </div>
					
					<?php
					}
					?>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Notes <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Evaluationforms.notes', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Notes', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
                    
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Evaluationforms.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'evaluationforms', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
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
        var filename = document.getElementById("reference_pdf_file_name").value;
        var filetype = ['pdf',];
        
        if (filename != '') {
            var ext = getExt(filename);
            ext = ext.toLowerCase();
            var checktype = in_array(ext, filetype);
            if (!checktype) {
                alert(ext + " file not allowed.");
				document.getElementById("reference_pdf_file_name").value = "";
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