<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Scheduling Reports By Sponsor - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Reports By Sponsor</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
			
			<div class="admin_search">
               <div class="admin_asearch">
                <div class="add_new_record">
				
				<?php echo $this->Html->link('<i class="fa fa-print"></i> Print', ['controller'=>'schedulingreports', 'action'=>'bysponsorsshowprint',$convention_season_slug,$sponsor_id], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']);?>
				
				<?php echo $this->Html->link('Back', ['controller'=>'schedulingreports', 'action'=>'bysponsors',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-warning']);?>
				
				</div>
            </div>
            </div>
           
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Schedulingreports/bysponsorsshow"); ?>
            </div>
			 
        </div>
    </section>
  </div>
