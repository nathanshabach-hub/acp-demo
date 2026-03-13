<?php echo $this->Html->script('ckeditor/ckeditor.js'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        CKEDITOR.replace( 'Pages[static_page_description]', {
            toolbarGroups:
                    [
    //{ name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
	//{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
	//{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker' ] },
	//{ name: 'forms' },
	//'/',
	//{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
	//{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
	//{ name: 'links' },
	//{ name: 'insert' },
	'/',
	{ name: 'styles' },
	{ name: 'colors' },
	//{ name: 'tools' },
	//{ name: 'others' },
	//{ name: 'about' }
                    ],
            //filebrowserUploadUrl : '<?php echo HTTP_PATH;?>/admin/pages/pageimages',
            language: '',
            height: 300,
            //uiColor: '#884EA1'
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Edit Page
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-file-text-o"></i> Pages ', ['controller'=>'pages', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Edit Page </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($pages, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Page Title <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Pages.static_page_title', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Page Title', 'autocomplete'=>'off']); ?>
                      </div>
                    </div>
                    <div class="form-group">
                      <label class="col-sm-2 control-label">Page Description <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Pages.static_page_description', array('label'=>false, 'type'=>'textarea', 'class'=>'form-control required', 'autocomplete'=>'off')); ?>
                      </div>
                    </div>
                    
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Pages.id', ['label'=>false, 'type'=>'hidden']); ?>
                        <?php echo $this->Form->button('Submit', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['controller'=>'pages', 'action' => 'index'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>
