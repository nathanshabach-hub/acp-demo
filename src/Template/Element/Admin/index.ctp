<?php

use Cake\ORM\TableRegistry;

$this->Categories = TableRegistry::get('Categories');
?>
<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>
<script type="text/javascript">
    $(document).ready(function ($) {
        $('.close_image').hide();
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo HTTP_IMAGE ?>/loading.gif',
            closeImage: '<?php echo HTTP_IMAGE ?>/close.png'
        })        
    })            
</script>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$trucks->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Trucks', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Trucks List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'trucks', 'action'=>'index', $separator]));
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th style="width:5%">#</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('name', 'Truck Name'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('first_name', 'Owner Name'); ?></th>
							<th class="sorting_paging">Categories<?php //echo $this->Paginator->sort('category_id', 'Category'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('truck_plan', 'Plan'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('truck_menu_type', 'Menu Type'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Created'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trucks as $truck) { ?>
                            <?php //pr($truck); exit;?> 
                            <tr>
                                <td data-title=""><input type="checkbox" onclick="javascript:isAllSelect(this.form);" name="chkRecordId[]" value="<?php echo $truck->id; ?>" /></td>
                                <td data-title="Truck Name"><?php echo $truck->name;?></td>
								<td data-title="Truck Owner Name">
								<?php
								
								if ($truck->user_id>0) {
									if(!empty($truck->Users['first_name']))
										echo $truck->Users['first_name']." ".$truck->Users['last_name'];
									else
										echo 'N/A';
								}
								
								?>
								</td>
                                <td data-title="Created"><?php //echo $truck->Categories['name']; ?>
								<?php 
								//echo $truck->Categories['name'];
								if(!empty($truck->category_id))
								{
									$truckCatNamesArr = array();
									$truckCats = explode(",",$truck->category_id);
									//pr($bussCats);
									foreach($truckCats as $category)
									{
										$condition = array();
										$condition[] = "(Categories.id = '".$category."')";
										//$condition[] = "(Categories.status = '1')";
										$catName = $this->Categories->find()->where($condition)->first();
										$truckCatNamesArr[] = $catName->name;
									}
									
									if(count($truckCatNamesArr))
										echo implode(", ",$truckCatNamesArr);
									
								}
								else
								{							
									echo 'N/A';
								}
								
								?>
								
								
								</td>
								<td data-title="Created"><?php 
								
								$planname = $truck->truck_plan;
								if($truck->featured_payment_status == '0' && $truck->truck_plan == 'Featured'){
									$planname = $truck->truck_plan .'(Payment In Progress)';
								}else{
									if($truck->truck_plan == 'Featured'){
										if(strtotime($truck->expiry_date) < strtotime(date('Y-m-d H:i:s'))){
											$planname = "Free";
										}
									}
								}
								echo $planname;
								//echo $truck->truck_plan;
								?></td>
								<td data-title="Created"><?php echo $truck->truck_menu_type; ?></td>
								<td data-title="Created"><?php echo date('M d, Y', strtotime($truck->created)); ?></td>
                                <td data-title="Action">
                                    <div id="loderstatus<?php echo $truck->id; ?>" class="right_action_lo"><?php echo $this->Html->image("loading.gif"); ?></div>
                                    <span class="right_acdc" id="status<?php echo $truck->id; ?>">
                                        <?php
                                        if ($truck->status == '1') {
                                            echo $this->Html->link('<button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>', ['controller' => 'trucks', 'action' => 'deactivatetruck',$truck->slug], [ 'escape' => false, 'title' => 'Deactivate']);
                                        } else {
                                            echo $this->Html->link('<button class="btn btn-danger btn-xs"><i class="fa fa-ban"></i></button>', ['controller' => 'trucks', 'action' => 'activatetruck', $truck->slug], [ 'escape' => false, 'title' => 'Activate']);
                                        }
                                        ?>
                                    </span>
                                    
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'trucks', 'action' => 'edit',$truck->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'btn btn-primary btn-xs']); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'trucks', 'action' => 'deletetruck',$truck->slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']); ?>
									<a href="#info<?php echo $truck->id; ?>" rel="facebox" title="View" class="btn btn-info btn-xs eyee"><i class="fa fa-eye "></i></a>
                                    <?php echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'mediafiles', 'action' => 'index',$truck->slug], [ 'escape' => false, 'title' => 'Images', 'class'=>'btn btn-warning btn-xs action-list delete-list']); ?>
									
									<?php echo $this->Html->link('<i class="fa fa-th"></i>', ['controller' => 'trucks', 'action' => 'editmenu',$truck->slug], [ 'escape' => false, 'title' => 'Menu', 'class'=>'btn btn-warning btn-xs action-list delete-list']); ?>
									
									<?php echo $this->Html->link('<i class="fa fa-bus"></i>', ['controller' => 'trucks', 'action' => 'schedule',$truck->slug], [ 'escape' => false, 'title' => 'Schedule', 'class'=>'btn btn-warning btn-xs action-list delete-list']); ?>
									
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="search_frm">
            <button type="button" name="chkRecordId" onclick="checkAll(true);"  class="btn btn-info">Select All</button>
            <button type="button" name="chkRecordId" onclick="checkAll(false);" class="btn btn-info">Unselect All</button>
            <?php
            $arr = array(
                "" => "Action for selected record",
                'Activate' => "Activate",
                'Deactivate' => "Deactivate"
            );
            ?>
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Trucks.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>

<?php foreach ($trucks as $truck) { ?>
    <div id="info<?php echo $truck->id; ?>" style="display: none;">
        <!-- Fieldset -->
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
    <?php echo $truck->name; ?>
                </legend>
                <div class="drt">

                    <div class="admin_pop">
                        <span>Plan : </span>  <label>
                            <?php 
								$planname = $truck->truck_plan;
								if($truck->featured_payment_status == '0' && $truck->truck_plan == 'Featured'){
									$planname = $truck->truck_plan .'(Payment In Progress)';
								}else{
									if($truck->truck_plan == 'Featured'){
										if(strtotime($truck->expiry_date) < strtotime(date('Y-m-d H:i:s'))){
											$planname = "Free";
										}
									}
								}
								echo $planname;
								 ?>
                            <?php //echo $truck->truck_plan	 ? $truck->truck_plan : 'N/A'; ?></label>
                    </div>
                    <?php if($planname == 'Featured'){?>
                        
                        <div class="admin_pop">
                        <span>Plan Start Date : </span>  <label>
                            <?php 
								echo $truck->start_date?$truck->start_date:'N/A';
								 ?>
								 </label>
                    </div>
                        <div class="admin_pop">
                        <span>Plan Expiry Date : </span>  <label>
                            <?php 
								echo $truck->expiry_date?$truck->expiry_date:'N/A';
								 ?>
								 </label>
                    </div>
                        <div class="admin_pop">
                        <span>Remaining Installment : </span>  <label>
                            <?php 
								echo $truck->totalPaymentRemaining;
								 ?>
								 </label>
                    </div>
                        
                        
                        
                        
                    <?php } ?>
					
					<div class="admin_pop">
                        <span>Customer : </span>  <label>
						<?php							
							if ($truck->user_id) {
								echo $truck->Users['first_name']." ".$truck->Users['last_name'];
							} else {
								echo "N/A";
							}
						?>
						</label>
                    </div>
					
					<div class="admin_pop">
                        <span>Categories : </span>  <label><?php //echo $truck->Categories['name']	 ? $truck->Categories['name'] : 'N/A'; ?>
						<?php 
						//echo $truck->Categories['name'];
						if(!empty($truck->category_id))
						{
							$truckCatNamesArr = array();
							$truckCats = explode(",",$truck->category_id);
							//pr($bussCats);
							foreach($truckCats as $category)
							{
								$condition = array();
								$condition[] = "(Categories.id = '".$category."')";
								//$condition[] = "(Categories.status = '1')";
								$catName = $this->Categories->find()->where($condition)->first();
								$truckCatNamesArr[] = $catName->name;
							}
							
							if(count($truckCatNamesArr))
								echo implode(", ",$truckCatNamesArr);
							
						}
						else
						{							
							echo 'N/A';
						}
						
						?>
						</label>
                    </div>
					
					<div class="admin_pop">
                        <span>Truck Name : </span>  <label><?php echo $truck->name	 ? $truck->name : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Truck Description : </span>  <label><?php echo $truck->description	 ? nl2br($truck->description) : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Website : </span>  <label><?php echo $truck->website	 ? $truck->website : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Phone Number : </span>  <label><?php echo $truck->phone	 ? $truck->phone : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Email : </span>  <label><?php echo $truck->email	 ? $truck->email : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Address : </span>  <label><?php echo $truck->address	 ? $truck->address : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>City : </span>  <label><?php echo $truck->city	 ? $truck->city : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>State : </span>  <label><?php echo $truck->state	 ? $truck->state : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Zip : </span>  <label><?php echo $truck->zip	 ? $truck->zip : 'N/A'; ?></label>
                    </div>
					
					
					<div class="admin_pop">
                        <span>Facebook : </span>  <label><?php echo $truck->facebook	 ? $truck->facebook : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Twitter : </span>  <label><?php echo $truck->twitter	 ? $truck->twitter : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Instagram : </span>  <label><?php echo $truck->instagram	 ? $truck->instagram : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Google+ : </span>  <label><?php echo $truck->googleplus	 ? $truck->googleplus : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Pinterest : </span>  <label><?php echo $truck->pinterest	 ? $truck->pinterest : 'N/A'; ?></label>
                    </div>
					 
                    
                </div>
            </fieldset>
        </div>
    </div>
<?php } ?>