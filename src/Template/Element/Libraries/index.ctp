

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$libraries->isEmpty()) { //pr($libraries); exit; ?>
    <div class="panel-body">
	
	<ul class="list-group">
		
	<div class="topn_right ajshort" id="pagingLinks" align="right">
		<?php 
			$this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'libraries', 'action'=>'index', $separator]));
			echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
			echo $this->Paginator->prev('« Prev');
			echo $this->Paginator->numbers();
			echo $this->Paginator->next('Next »');			
		?>
	</div>
	<br />
	<li>&nbsp;</li>
	 
	 
		<?php foreach ($libraries as $library) { //pr($course); exit; ?>
		<li class="list-group-item">
		  <h4><?php echo $this->Html->link($library->name, ['controller' => 'libraries', 'action' => 'details',$library->slug], [ 'escape' => false, 'title' => 'View Details']); ?></h4>
		  <p>
			<?php 
			if(strlen($library->description)>100)
			{
				echo substr($library->description,0,100)."...";
			}
			else
			{
				echo $library->description;  
			}
			?>
		  </p>
		  <div class="new-row">
			  <div class="date-row"><?php echo date("d F Y",strtotime($library->created)); ?></div>
			  <div class="user-nema"><i class="fa fa-user-o" aria-hidden="true"></i>
			  <?php
			  if($library->user_id>0)
			  {
				if(!empty($library->Users['first_name']))
				{
					echo $library->Users['first_name'];
					if(!empty($library->Users['country']))
					{
						echo ", ".$library->Users['country'];
					}
				}
			  }
			  else
			  {
				  echo 'Admin';
			  }
			  ?>
			  </div>
		  </div>
		</li>
		<?php } ?>
	
  
	</ul>
         
         
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="alert alert-danger">Sorry, no record found.</div>
	<div style="height:250px;">&nbsp;</div>
<?php }
?>