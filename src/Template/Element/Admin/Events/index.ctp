<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$events->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Events', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Events List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'events', 'action'=>'index', $separator]));
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
                            
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('event_id_number', 'Number'); ?></th>
                            <th class="sorting_paging">Division</th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('event_name', 'Event Name'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('upload_type', 'Need Schedule'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('report', 'Group Event'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('context_box', 'Event Kind ID'); ?></th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('score_sheet', 'Has To Be Consecutive'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title=""><input type="checkbox" onclick="javascript:isAllSelect(this.form);" name="chkRecordId[]" value="<?php echo $datarecord->id; ?>" /></td>
                                 
                                <td data-title="Event Number"><?php echo $datarecord->event_id_number ? $datarecord->event_id_number : 'N/A'; ?></td>
								<td data-title="Division"><?php echo $datarecord->Divisions['name'] ? $datarecord->Divisions['name'] : 'N/A'; ?></td>
								<td data-title="Event Name"><?php echo $datarecord->event_name ? $datarecord->event_name : 'N/A'; ?></td>
								<td data-title="Need Schedule?"><?php echo ($datarecord->needs_schedule == '1') ? "Yes" : "No"; ?></td>
								<td data-title="Group Event"><?php echo ($datarecord->group_event_yes_no == '1') ? "Yes" : "No"; ?></td>
								<td data-title="Event Kind ID"><?php echo $datarecord->event_kind_id; ?></td>
								<td data-title="Has To Be Consecutive"><?php echo ($datarecord->has_to_be_consecutive == '1') ? "Yes" : "No"; ?></td>
                                
                                <td data-title="Action">
                                    <div id="loderstatus<?php echo $datarecord->id; ?>" class="right_action_lo"><?php echo $this->Html->image("loading.gif"); ?></div>
                                    <span class="right_acdc" id="status<?php echo $datarecord->id; ?>">
                                        <?php
                                        if ($datarecord->status == '1') {
                                            echo $this->Html->link('<button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>', ['controller' => 'events', 'action' => 'deactivateevent',$datarecord->slug], [ 'escape' => false, 'title' => 'Deactivate']);
                                        } else {
                                            echo $this->Html->link('<button class="btn btn-danger btn-xs"><i class="fa fa-ban"></i></button>', ['controller' => 'events', 'action' => 'activateevent', $datarecord->slug], [ 'escape' => false, 'title' => 'Activate']);
                                        }
                                        ?>
                                    </span>
                                    
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'events', 'action' => 'edit',$datarecord->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'btn btn-primary btn-xs']); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'events', 'action' => 'deleteevent',$datarecord->slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']); ?>
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
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Events.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>