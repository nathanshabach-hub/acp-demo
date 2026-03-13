<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Add Area :: Form -> <?php echo $formD->name; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link(' Evaluation Forms ', ['controller'=>'evaluationforms', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link(' Evaluation Areas ', ['controller'=>'evaluationareas', 'action'=>'index',$form_slug], ['escape'=>false]);?></li>
          <li class="active">Add Area </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($evaluationareas, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Category <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Evaluationareas.evaluationcategory_id', $categoryDD, ['id' => 'evaluationcategory_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose Category', 'style' => 'margin-bottom:2px;']); ?>
							<script>
								$(document).ready(function() {
									$('#evaluationcategory_id').select2();
								});
							</script>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Questions <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Evaluationareas.evaluationquestion_ids', [], ['id' => 'evaluationquestion_ids', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'multiple' => 'multiple', 'style' => 'margin-bottom:2px;']); ?>
							<script>
								$(document).ready(function() {
									$('#evaluationquestion_ids').select2();
								});
							</script>
                      </div>
                    </div>
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
						<?php echo $this->Html->link('Cancel', ['controller'=>'evaluationareas', 'action' => 'index', $form_slug], ['class'=>'btn btn-default canlcel_le']); ?>
                        <?php //echo $this->Form->button('Reset', ['type'=>'reset', 'class' => 'btn btn-default canlcel_le', 'div'=>false]); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
</div>

<script type="text/javascript">
	$(document).ready(function () {								
		$('#evaluationcategory_id').change(function () {
			
			//$("#box_group_name").css("display","none");
			//$("#box_student_list").css("display","none");
			//$(".class_show_hide").css("display","none");
			
			var evaluationcategory_id = $("#evaluationcategory_id").val();
			
			if(evaluationcategory_id == 0 || evaluationcategory_id == '')
			{
				$('#evaluationquestion_ids').empty();
				alert("Please choose category.");
				return false;
			}
			
			$.ajax({
				type: 'POST',
				url: "<?php echo HTTP_PATH."/homes/getcategoryquestions/"; ?>"+evaluationcategory_id,
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
					//alert(result);return false;
					var objReturned = $.parseJSON(result);
					
					var category_questions = objReturned.category_questions;
					
					// to check if category_questions is empty
					if($.isEmptyObject(category_questions))
					{
						alert('Sorry, no question found.');
						return false;
					}
					
					//$('#evaluationquestion_ids').children('option:not(:first)').remove();
					$('#evaluationquestion_ids').empty();
					$.each(category_questions, function (key, entry) {
						$('#evaluationquestion_ids').append($('<option></option>').attr('value', entry.id).text(entry.name));
						//alert(entry.name);
					});
				}
			});
			return false;
		});
	});
</script>