<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Convention Registrations Judges
                 <?php if (!empty($convSeasonD) && !empty($convSeasonD->slug)) { ?>
                         <small style="margin-left:8px; color:#7f8c8d;">Season: <?php echo h($convSeasonD->slug); ?></small>
                 <?php } ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
          <li class="active"> Convention Registrations Judges List </li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Conventionregistrations/alljudges"); ?>
            </div>
            
        </div>
    </section>
</div>
