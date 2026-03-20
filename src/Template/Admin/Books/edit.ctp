<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<?php echo $this->Html->script('jquery/ui/jquery.ui.core.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.widget.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.position.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.datepicker.js'); ?>
<?php echo $this->Html->css('themes/ui-lightness/jquery.ui.all.css'); ?>
<script>
    $(function() {
        $( "#registration_start_date" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
            yearRange: '-2y:c+nn',
            maxDate: '+2y'
        });
    });
</script>
<script>
    $(function() {
        $( "#registration_end_date" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
            yearRange: '-2y:c+nn',
            maxDate: '+2y'
        });
    });
</script>
<style>
    #ui-datepicker-div button.ui-datepicker-current {display: none;}
</style>

<?php
if(!empty($books->registration_start_date) && $books->registration_start_date != NULL)
{
	$books->registration_start_date = date("Y-m-d",strtotime($books->registration_start_date));
}

if(!empty($books->registration_end_date) && $books->registration_end_date != NULL)
{
	$books->registration_end_date = date("Y-m-d",strtotime($books->registration_end_date));
}
?>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Edit Book
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Books ', ['controller'=>'books', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Edit Book </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($books, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Book Year <span class="require"></span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Books.book_year_no_change', ['label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Book Year', 'autocomplete'=>'off', 'value'=>$books->book_year, 'readonly']); ?>
                      </div>
                    </div>
                    
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->control('Books.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'books', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>