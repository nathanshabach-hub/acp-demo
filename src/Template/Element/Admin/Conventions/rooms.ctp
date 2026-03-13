<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$convrooms->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Rooms List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventions', 'action'=>'rooms', $slug, $separator]));
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
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('id', '# DB ID'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('room_name', 'Room Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('short_description', 'Short Description'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($convrooms as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="# DB ID"><?php echo $datarecord->id;?></td>
                                <td data-title="Room Name"><?php echo $datarecord->room_name;?></td>
                                <td data-title="Short Description"><?php echo $datarecord->short_description;?></td>
								
                                <td data-title="Action">
									
									<?php
									echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'conventions', 'action' => 'editroom',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Change Prices', 'class'=>'btn btn-info btn-xs']);
									
                                    echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventions', 'action' => 'deleteroom',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']);
									
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
    <div class="admin_no_record">Sorry, no record found.</div>
<?php }
?>