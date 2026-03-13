<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionregistrations->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventionregistrations', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Convention Registrations List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventionregistrations', 'action'=>'index', $separator]));
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
                            <th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">School</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('season_year', 'Season Year'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('price_structure', 'Price Structure'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('price_per_student', 'Price Per Student'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Registration Date'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conventionregistrations as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="School"><?php echo $datarecord->Users['first_name'];?></td>
								<td data-title="Season Year"><?php echo $datarecord->season_year; ?></td>
								<td data-title="Price Structure"><?php echo $priceStructureCR[$datarecord->price_structure]; ?></td>
								<td data-title="Price Per Student">
								<?php 
								if(!empty($datarecord->price_structure))
								{
									echo CURR.' '.number_format($datarecord->price_per_student,2);
								}
								?></td>
                                <td data-title="Registration Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
								
                                <td data-title="Action">
                                    
                                    <?php echo $this->Html->link('<i class="fa fa-user-secret"></i>', ['controller' => 'conventionregistrations', 'action' => 'teachers',$datarecord->slug], [ 'escape' => false, 'title' => 'Supervisors', 'class'=>'btn btn-primary btn-xs']); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-group"></i>', ['controller' => 'conventionregistrations', 'action' => 'students',$datarecord->slug], [ 'escape' => false, 'title' => 'Students', 'class'=>'btn btn-primary btn-xs']); ?>
									
                                    <?php echo $this->Html->link('<i class="fa fa-heart"></i>', ['controller' => 'conventionregistrations', 'action' => 'heartevents',$datarecord->slug], [ 'escape' => false, 'title' => 'Events of the heart', 'class'=>'btn btn-primary btn-xs']); ?>
									
									<?php echo $this->Html->link('<i class="fa fa-database"></i>', ['controller' => 'eventsubmissions', 'action' => 'index',$datarecord->slug], [ 'escape' => false, 'title' => 'Events submissions', 'class'=>'btn btn-primary btn-xs']); ?>
									
                                    <?php //echo $this->Html->link('<i class="fa fa-eye"></i>', ['controller' => 'conventionregistrations', 'action' => 'viewdetails',$datarecord->slug], [ 'escape' => false, 'title' => 'View Details', 'class'=>'btn btn-primary btn-xs']); ?>
									
                                    <?php //echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventionregistrations', 'action' => 'deletedivision',$datarecord->slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']); ?>
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
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Conventionregistrations.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>