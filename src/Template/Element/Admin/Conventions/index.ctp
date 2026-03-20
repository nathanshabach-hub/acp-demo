<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventions->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Conventions List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventions', 'action'=>'index', $separator]));
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
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('name', 'Convention Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('convention_type', 'Convention Type'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('address', 'Address'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Created'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conventions as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title=""><input type="checkbox" onclick="javascript:isAllSelect(this.form);" name="chkRecordId[]" value="<?php echo $datarecord->id; ?>" /></td>
                                <td data-title="Convention Name"><?php echo $datarecord->name;?></td>
                                <td data-title="Convention Type"><?php echo $conventionTypeDD[$datarecord->convention_type];?></td>
                                <td data-title="Address"><?php echo $datarecord->address;?></td>
								
                                <td data-title="Created"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td data-title="Action">
                                    <div id="loderstatus<?php echo $datarecord->id; ?>" class="right_action_lo"><?php echo $this->Html->image("loading.gif"); ?></div>
                                    <span class="right_acdc" id="status<?php echo $datarecord->id; ?>">
                                        <?php
                                        if ($datarecord->status == '1') {
                                            echo $this->Html->link('<button class="btn btn-success btn-xs"><i class="fa fa-check"></i></button>', ['controller' => 'conventions', 'action' => 'deactivateconvention',$datarecord->slug], [ 'escape' => false, 'title' => 'Deactivate']);
                                        } else {
                                            echo $this->Html->link('<button class="btn btn-danger btn-xs"><i class="fa fa-ban"></i></button>', ['controller' => 'conventions', 'action' => 'activateconvention', $datarecord->slug], [ 'escape' => false, 'title' => 'Activate']);
                                        }
                                        ?>
                                    </span>
                                    
                                    <?php
									echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'conventions', 'action' => 'edit',$datarecord->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'btn btn-primary btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-bullhorn"></i>', ['controller' => 'conventions', 'action' => 'seasons',$datarecord->slug], [ 'escape' => false, 'title' => 'Manage Seasons', 'class'=>'btn btn-info btn-xs']);
									

									?>
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
            <div class="list_sel"><?php echo $this->Form->control('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->control('Conventions.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>