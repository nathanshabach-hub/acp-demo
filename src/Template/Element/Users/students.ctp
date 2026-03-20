<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$users->isEmpty()) { ?> 
    <div class="panel-body">
        
        <?php echo $this->Form->create('Users', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <!--<div class="topn_left">Ads List</div>-->
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'users', 'action'=>'students', $separator]));
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
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('first_name', 'First Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('middle_name', 'Middle Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('last_name', 'Last Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('birth_year', 'Birth Year'); ?></th>
                            <th class="sorting_paging">Age</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('gender', 'Gender'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Sign Up Date'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('status', 'Status'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="First Name"><?php echo $datarecord->first_name;?></td>
                                <td data-title="Middle Name"><?php echo $datarecord->middle_name ? $datarecord->middle_name : 'N/A'; ?></td>
                                <td data-title="Last Name"><?php echo $datarecord->last_name;?></td>
                                <td data-title="Birth Year"><?php echo $datarecord->birth_year;?></td>
                                <td data-title="Age"><?php echo date("Y")-$datarecord->birth_year;?></td>
                                
                                <td data-title="Gender"><?php echo $datarecord->gender ? $datarecord->gender : 'N/A'; ?></td>
                                <td data-title="Sign Up Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td data-title="Status">
									<?php
									if($datarecord->status == 0) 
										echo 'Inactive'; 
									else
									if($datarecord->status == 1)
										echo 'Active';
									else
									if($datarecord->status == 2)
										echo 'Archive';
									?>
								</td>
                                <td data-title="Action">
                                    <?php
									if($datarecord->status != 2)
									{
										echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'users', 'action' => 'editstudent',$datarecord->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'']);
									}
									// to show only to school admin only
                                        if($this->request->getSession()->read("user_id") >0 && ($this->request->getSession()->read("user_type") == "School"))
									{
										if($datarecord->status == 2)
										{
											echo $this->Html->link('<i class="fa fa-retweet"></i>', ['controller' => 'users', 'action' => 'restorestudent', $datarecord->slug], [ 'escape' => false, 'title' => 'Restore', 'class' => 't', 'confirm' => 'Are you sure you want to restore this student?']);
										}
										else
										{
											echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'users', 'action' => 'archivestudent',$datarecord->slug], [ 'escape' => false, 'title' => 'Archive', 'class'=>'', 'confirm' => 'Are you sure you want to archive this student ?']);
										}
									}
									?>
                                    <?php //echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'mediafiles', 'action' => 'viewlist',$datarecord->slug], [ 'escape' => false, 'title' => 'Pictures', 'class'=>'']); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>
