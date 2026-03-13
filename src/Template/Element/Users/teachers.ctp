<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$users->isEmpty()) { ?> 
    <div class="panel-body">
        
        <?php echo $this->Form->create('Users', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <!--<div class="topn_left">Ads List</div>-->
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'users', 'action'=>'teachers', $separator]));
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
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('title', 'Title'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('first_name', 'First Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('last_name', 'Surname'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('email_address', 'Email Address'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('gender', 'Gender'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('is_judge', 'Judge?'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Created'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('activation_status', 'Verified'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('status', 'Status'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Title"><?php echo $datarecord->title;?></td>
                                <td data-title="First Name"><?php echo $datarecord->first_name;?></td>
                                <td data-title="Surname"><?php echo $datarecord->last_name;?></td>
                                <td data-title="Email Address"><?php echo $datarecord->email_address;?></td>
                                <td data-title="Gender"><?php echo $datarecord->gender;?></td>
                                <td data-title="Judge?"><?php if($datarecord->is_judge == 1) echo 'Yes'; else echo 'No'; ?></td>
								
                                <td data-title="Created"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                
								<td data-title="Verified">
									<?php
									if($datarecord->status != 2)
									{
										if($datarecord->activation_status)  echo 'Verified'; else  echo 'Not yet verified';
									}
									?>
								</td>
								
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
                                    if($datarecord->status == 2)
									{
										echo $this->Html->link('<i class="fa fa-retweet"></i>', ['controller' => 'users', 'action' => 'restoreteacher', $datarecord->slug], [ 'escape' => false, 'title' => 'Restore', 'class' => '', 'confirm' => 'Are you sure you want to restore this supervisor?']);
									}
									else
									{
										echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'users', 'action' => 'editteacher',$datarecord->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'']);
										
										echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'users', 'action' => 'archiveteacher',$datarecord->slug], [ 'escape' => false, 'title' => 'Archive', 'class'=>'', 'confirm' => 'Are you sure you want to archive this supervisor?']);
									}
									?>
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
