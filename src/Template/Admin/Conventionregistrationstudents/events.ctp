<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Manage Events Registered
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-newspaper-o"></i> Convention Registrations ', ['controller'=>'conventionregistrations', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active"> Manage Events Registered </li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search">
                <?php echo $this->Form->create(Null, ['id'=>'adminSearch']); ?>
                    <div class="form-group align_box dtpickr_inputs">
                       <h3>
					   <?php echo $CRDetails->Conventions['name']; ?> - 
					   <?php echo $CRDetails->Users['first_name']; ?>
					   </h3>
                        
					   
					   
                      
                        
                    </div>
                <?php echo $this->Form->end(); ?>
                <div class="add_new_record"><?php //echo $this->Html->link('<i class="fa fa-plus"></i> Add Division', ['controller'=>'conventionregistrations', 'action'=>'add'], ['escape'=>false, 'class'=>'btn btn-default']);?></div>
            </div>
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Conventionregistrationstudents/events"); ?>
            </div>
            
        </div>
    </section>
</div>
