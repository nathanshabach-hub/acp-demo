<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Scheduling Reports By Students - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Reports By Students</li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create(NULL, ['id'=>'schedulingWizardForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose School <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Schedulingreports.school_id', $schoolsDD, ['id' => 'school_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose School']); ?>
							<script>
							$(document).ready(function() {
								$('#school_id').select2();
							});
							</script>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Choose Students <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->select('Schedulingreports.student_id', array(), ['id' => 'student_id', 'label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'empty' => 'All']); ?>
						  <script>
								$(document).ready(function () {
									$('#student_id').select2();
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
  
<script type="text/javascript">
	$(document).ready(function () {								
		$('#school_id').change(function () {
			
			var conv_season_id = <?php echo $conventionSD->id; ?>;
			var school_id = $("#school_id").val();
			
			if(school_id == 0)
			{
				alert("Please choose school.");
				return false;
			}
			
			//alert(conv_season_id);
			//alert(school_id);
			
			$.ajax({
				type: 'POST',
				url: "<?php echo HTTP_PATH."/homes/getstudentsofschool/"; ?>"+conv_season_id+"/"+school_id,
				cache: false,
				beforeSend: function () {
					//$("#loderstatus").show();
				},
				complete: function () {
					//$("#loderstatus").hide();
				},
				success: function (result) {
					//alert(result);return false;
					//console.log(result);
					var objReturned = $.parseJSON(result);
					
					var student_list = objReturned.student_list;
					// empty dropdown values
					$('#student_id').children('option:not(:first)').remove();
					$.each(student_list, function (key, entry) {
						$('#student_id').append($('<option></option>').attr('value', entry.id).text(entry.name));
						//alert(entry.name);
					});
				}
			});
			return false;
		});
	});
</script>

 
  