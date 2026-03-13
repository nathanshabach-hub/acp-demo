<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
       Name Tags - Students
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
          <li class="active"> Students Name Tags List </li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search">
                 
                    <div class="form-group align_box dtpickr_inputs">
                       <div class="admin_asearch">
                            <div class="ad_cancel"> </div>
                       </div>
                    </div>
                 
                <div class="add_new_record"><?php echo $this->Html->link('<i class="fa fa-print"></i> Print', ['controller'=>'nametags', 'action'=>'printnametagsstudents'], ['escape'=>false, 'class'=>'btn btn-default']);?></div>
            </div>
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Nametags/students"); ?>
            </div>
            
        </div>
    </section>
</div>
