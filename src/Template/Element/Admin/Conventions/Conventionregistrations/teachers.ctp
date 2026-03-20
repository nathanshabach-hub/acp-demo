<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionregistrationteachers->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventionregistrationteachers', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Supervisors List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventionregistrationteachers', 'action'=>'teachers', $slug, $separator]));
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
                            <th class="sorting_paging">#</th>
                            <th class="sorting_paging">Title</th>
                            <th class="sorting_paging">First Name</th>
                            <th class="sorting_paging">Last Name</th>
                            <th class="sorting_paging">Email Address</th>
                            <th class="sorting_paging">Gender</th>
                            <th class="sorting_paging">Judge?</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Registration Date'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						$cntrT = 0;
						foreach ($conventionregistrationteachers as $datarecord)
						{
							$cntrT++;
						?>
                            <tr>
                                <td data-title="#"><?php echo $cntrT;?></td>
                                <td data-title="Title"><?php echo $datarecord->Teachers['title'];?></td>
                                <td data-title="First Name"><?php echo $datarecord->Teachers['first_name'];?></td>
								<td data-title="Last Name"><?php echo $datarecord->Teachers['last_name'];?></td>
								<td data-title="Email Address"><?php echo $datarecord->Teachers['email_address'];?></td>
								<td data-title="Gender"><?php echo $datarecord->Teachers['gender'];?></td>
								<td data-title="Judge"><?php if($datarecord->Teachers['is_judge'] == 1) echo 'Yes'; else echo 'No'; ?></td>
								
                                <td data-title="Registration Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
								
                                
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
            <div class="list_sel"><?php echo $this->Form->control('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->control('Conventionregistrations.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>