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
<?php
use Cake\ORM\TableRegistry;
$this->Categories = TableRegistry::get('Categories');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif'); ?></div>
<?php if (!$users->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
    <?php echo $this->Form->create('Users', ['id' => 'actionFrom', "method" => "Post"]); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Supervisors List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php
                    $this->Paginator->options(array('update' => '#listID', 'url' => ['controller' => 'users', 'action' => 'teachers', $separator]));
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
                            <th style="width:5%">#</th>
							<th class="sorting_paging">School</th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('title', 'Title'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('first_name', 'First Name'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('last_name', 'Last Name'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('email_address', 'Email'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('gender', 'Gender'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('is_judge', 'Judge'); ?></th>
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
                                <td data-title=""><input type="checkbox" onclick="javascript:isAllSelect(this.form);" name="chkRecordId[]" value="<?php echo $user->id; ?>" /></td>
                                <td data-title="School"><?php echo $user->Schools['first_name']; ?></td>
                                <td data-title="Title"><?php echo $user->title; ?></td>
                                <td data-title="First Name"><?php echo $user->first_name; ?></td>
								<td data-title="Last Name"><?php echo $user->last_name; ?></td>
								<td data-title="Email Address"><?php echo $user->email_address; ?></td>
								<td data-title="Gender"><?php echo $user->gender; ?></td>
                                <td data-title="Judge"><?php if($user->is_judge == 1) echo 'Yes'; else echo 'No'; ?></td>
                                <td data-title="Created"><?php echo date('M d, Y', strtotime($user->created)); ?></td>
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
									if($user->status == 2)
									{
										echo $this->Html->link('<i class="fa fa-retweet"></i>', ['controller' => 'users', 'action' => 'restoreteacher', $user->slug], [ 'escape' => false, 'title' => 'Restore', 'class' => 'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to restore this supervisor?']);
									}
									else
									{
									?>
									
									<div id="loderstatus<?php echo $user->id; ?>" class="right_action_lo"><?php echo $this->Html->image("loading.gif"); ?></div>
                                    <span class="right_acdc" id="status<?php echo $user->id; ?>">
                                        <?php
                                        if ($user->status == '1') {
                                            echo $this->Html->link('<button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>', ['controller' => 'users', 'action' => 'deactivateteacher', $user->slug], [ 'escape' => false, 'title' => 'Deactivate','class'=>'deactivate']);
                                        } else {
                                            echo $this->Html->link('<button class="btn btn-danger btn-xs"><i class="fa fa-ban"></i></button>', ['controller' => 'users', 'action' => 'activateteacher', $user->slug], ['class' => "activate", 'escape' => false, 'title' => 'Activate']);
                                        }
                                        ?>
                                    </span>

                                    <?php
									echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'users', 'action' => 'editteacher', $user->slug], [ 'escape' => false, 'title' => 'Edit', 'class' => 'btn btn-primary btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'users', 'action' => 'archiveteacher', $user->slug], [ 'escape' => false, 'title' => 'Archive', 'class' => 'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to arcive this supervisor ?']);
									?>
									
									<?php
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

        <div class="search_frm">
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
