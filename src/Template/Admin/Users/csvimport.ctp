<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>

<?php echo $this->Html->script('jquery/ui/jquery.ui.core.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.widget.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.position.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.datepicker.js'); ?>
<?php echo $this->Html->css('front/themes/ui-lightness/jquery.ui.all.css'); ?>
<script>
    $(function() {
        $( "#date_of_birth" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
            yearRange: '-100y:c+nn',
            maxDate: '-1d'
        });
    });
	
</script>
<style>
    #ui-datepicker-div button.ui-datepicker-current {display: none;}
</style>

<?php
if(empty($users->user_timezone))
	$users->user_timezone = 'America/Los_Angeles';
?>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Import CSV <?php echo $this->Html->link('<i class="fa fa-download"></i> Download CSV Format', ['controller'=>'users', 'action'=>'downloadcsvformat'], ['escape'=>false, 'class'=>'btn btn-default']);?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bank"></i> Users ', ['controller'=>'users', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Import CSV </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($users, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">CSV File <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Users.csv_file', ['id'=>'csv_file', 'label'=>false, 'type'=>'file',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'CSV File', 'onchange'=>'imageValidation()']); ?>
                      </div>
                    </div>
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Import', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Form->button('Reset', ['type'=>'reset', 'class' => 'btn btn-default canlcel_le', 'div'=>false]); ?>
						<?php echo $this->Html->link('Cancel', ['controller'=>'users', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
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
        var filename = document.getElementById("csv_file").value;
        var filetype = ['csv'];
        
        if (filename != '') {
            var ext = getExt(filename);
            ext = ext.toLowerCase();
            var checktype = in_array(ext, filetype);
            if (!checktype) {
                alert(ext + " file not allowed.");
				document.getElementById("csv_file").value = "";
                return false;
            } else {
                /*
				var fi = document.getElementById('csv_file');
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
