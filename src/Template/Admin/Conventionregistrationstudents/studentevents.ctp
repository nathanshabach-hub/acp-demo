<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Events List - <?php echo $convRegStudentD->Students['first_name'].' '.$convRegStudentD->Students['middle_name'].' '.$convRegStudentD->Students['last_name']; ?> (<?php echo $convRegStudentD->Users['first_name']; ?>)
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
		  <li><?php echo $this->Html->link('<i class="fa fa-group"></i> <span>Students</span> ', array('controller'=>'conventionregistrationstudents', 'action'=>'allstudents'), array('escape'=>false));?></li>
          <li class="active"> Convention Registrations Student Events List </li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Conventionregistrationstudents/studentevents"); ?>
            </div>
            
        </div>
    </section>
</div>
