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

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif'); ?></div>
<?php if (!$users->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
    <?php echo $this->Form->create('Users', ['id' => 'actionFrom', "method" => "Post"]); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Active Judges List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php
                    $this->Paginator->options(array('update' => '#listID', 'url' => ['controller' => 'users', 'action' => 'judges', $separator]));
                    echo $this->Paginator->counter();
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
							<th class="sorting_paging"><?php echo $this->Paginator->sort('middllast_name', 'Surname'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('email_address', 'Email'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('previous_convention_experience', 'Previous Experience'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('non_convention_experience', 'Non-conv Experience'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Sign Up Date'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('activation_status', 'Verified'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('status', 'Status'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user) { ?>
						<?php //pr($user); exit; ?> 
                            <tr>
                                <td data-title="First Name"><?php echo $user->first_name ? $user->first_name : 'N/A'; ?></td>
                                <td data-title="Surname"><?php echo $user->last_name ? $user->last_name : 'N/A'; ?></td>
								<td data-title="Email Address"><?php echo $user->email_address ? $user->email_address : 'N/A'; ?></td>
								<td data-title="Previous Experience"><?php echo $user->previous_convention_experience ? $user->previous_convention_experience : 'N/A'; ?></td>
								<td data-title="Non-conv Experience"><?php echo $user->non_convention_experience ? $user->non_convention_experience : 'N/A'; ?></td>
                                <td data-title="Sign Up Date"><?php echo date('M d, Y', strtotime($user->created)); ?></td>
								<td data-title="Verified">
									<?php
									if($user->status != 2)
									{
										if($user->activation_status)  echo 'Verified'; else  echo 'Not yet verified';
									}
									?>
								</td>
								
								<td data-title="Status">
									<?php
									if($user->status == 0) 
										echo 'Inactive'; 
									else
									if($user->status == 1)
										echo 'Active';
									else
									if($user->status == 2)
										echo 'Archive';
									?>
								</td>
								
                                <td data-title="Action">
                                    
									<?php
									//echo $this->Html->link('<i class="fa fa-retweet"></i>', ['controller' => 'users', 'action' => 'restoreuser', $user->slug], [ 'escape' => false, 'title' => 'Restore', 'class' => 'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to restore this school?']);
									
									//echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'users', 'action' => 'archiveuser', $user->slug], [ 'escape' => false, 'title' => 'Archive', 'class' => 'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to archive this school?']);
									?>
									

                                    <?php
									if($user->status == 1)
									{
										echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'users', 'action' => 'archivejudge', $user->slug], [ 'escape' => false, 'title' => 'Archive', 'class' => 'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to archive this judge?']);
									}
									
									if($user->status == 2)
									{
										echo $this->Html->link('<i class="fa fa-retweet"></i>', ['controller' => 'users', 'action' => 'restorejudge', $user->slug], [ 'escape' => false, 'title' => 'Restore', 'class' => 'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to restore this judge?']);
									}
									?>
									
                                    <!--<a href="#info<?php echo $user->id; ?>" rel="facebox" title="View" class="btn btn-info btn-xs eyee"><i class="fa fa-eye "></i></a>-->
                                </td>
                            </tr>
						<?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="search_frm" style="display:none;">
            <button type="button" name="chkRecordId" onclick="checkAll(true);"  class="btn btn-info">Select All</button>
            <button type="button" name="chkRecordId" onclick="checkAll(false);" class="btn btn-info">Unselect All</button>
            <?php
            $arr = array(
                "" => "Action for selected record",
                'Activate' => "Activate",
                'Deactivate' => "Deactivate",
                //'Delete' => "Delete",
            );
            ?>
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type' => 'select', 'label' => false, 'class' => "small form-control", 'id' => 'action']); ?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Users.keyword', ['label' => false, 'type' => 'hidden', 'value' => $keyword]);
        }
        ?>
    <?php echo $this->Form->end(); ?>

    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>
