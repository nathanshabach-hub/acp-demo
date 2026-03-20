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
<?php if (!$libraries->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Courses', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Library List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'libraries', 'action'=>'index', $separator]));
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
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('name', 'Library Title'); ?></th>
							<th class="sorting_paging">Category<?php //echo $this->Paginator->sort('category', 'Category'); ?></th>
							<th class="sorting_paging">Customer Name<?php //echo $this->Paginator->sort('first_name', 'Customer Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('search_term', 'Search Term'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Created'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($libraries as $library) { ?>
                            <?php //pr($library); exit;?> 
                            <tr>
                                <td data-title=""><input type="checkbox" onclick="javascript:isAllSelect(this.form);" name="chkRecordId[]" value="<?php echo $library->id; ?>" /></td>
                                <td data-title="Library Title"><?php echo $library->name;?></td>
								<td data-title="Category"><?php echo $library->Librarycategories['name'];?></td>
								<td data-title="Customer">
								<?php
								
								if ($library->user_id>0) {
									if(!empty($library->Users['first_name']))
										echo $library->Users['first_name']." ".$library->Users['last_name'];
									else
										echo 'N/A';
								} else {
									echo 'N/A';
								}
								
								?>
								</td>
								<td data-title="Library Title"><?php echo $library->search_term;?></td>
                                <td data-title="Created"><?php echo date('M d, Y', strtotime($library->created)); ?></td>
                                <td data-title="Action">
                                    <div id="loderstatus<?php echo $library->id; ?>" class="right_action_lo"><?php echo $this->Html->image("loading.gif"); ?></div>
                                    <span class="right_acdc" id="status<?php echo $library->id; ?>">
                                        <?php
                                        if ($library->status == '1') {
                                            echo $this->Html->link('<button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>', ['controller' => 'libraries', 'action' => 'deactivatelibrary',$library->slug], [ 'escape' => false, 'title' => 'Deactivate']);
                                        } else {
                                            echo $this->Html->link('<button class="btn btn-danger btn-xs"><i class="fa fa-ban"></i></button>', ['controller' => 'libraries', 'action' => 'activatelibrary', $library->slug], [ 'escape' => false, 'title' => 'Activate']);
                                        }
                                        ?>
                                    </span>
                                    
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'libraries', 'action' => 'edit',$library->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'btn btn-primary btn-xs']); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'libraries', 'action' => 'deletecourse',$library->slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']); ?>
									<a href="#info<?php echo $library->id; ?>" rel="facebox" title="View" class="btn btn-info btn-xs eyee"><i class="fa fa-eye "></i></a>
                                    <?php echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'mediafiles', 'action' => 'index','library',$library->slug], [ 'escape' => false, 'title' => 'Media', 'class'=>'btn btn-warning btn-xs action-list delete-list']); ?>
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
            <div class="list_sel"><?php echo $this->Form->control('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->control('Courses.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>

<?php foreach ($libraries as $library) { ?>
    <div id="info<?php echo $library->id; ?>" style="display: none;">
        <!-- Fieldset -->
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
    <?php echo $library->name; ?>
                </legend>
                <div class="drt">
                    
                    <div class="admin_pop">
                        <span>Library Title : </span>  <label><?php echo $library->name	 ? nl2br($library->name) : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Category : </span>  <label><?php echo $library->Librarycategories['name'];?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Customer : </span>  <label>
						<?php							
							if ($library->user_id) {
								echo $library->users['first_name']." ".$library->users['last_name'];
							} else {
								echo "N/A";
							}
						?>
						</label>
                    </div>
					<div class="admin_pop">
                        <span>Library Description : </span>  <label><?php echo $library->description	 ? nl2br($library->description) : 'N/A'; ?></label>
                    </div>
					
					<div class="admin_pop">
                        <span>Search Term : </span>  <label><?php echo $library->search_term;?></label>
                    </div>
					 
                    
                </div>
            </fieldset>
        </div>
    </div>
<?php } ?>