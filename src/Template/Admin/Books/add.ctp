<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Add Book
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Books ', ['controller'=>'books', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Add Book </li>
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
                      <label class="col-sm-2 control-label">Book Name <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->control('Books.book_name', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Book Name', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Save', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
						<?php echo $this->Html->link('Cancel', ['controller'=>'books', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                        <?php //echo $this->Form->button('Reset', ['type'=>'reset', 'class' => 'btn btn-default canlcel_le', 'div'=>false]); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>