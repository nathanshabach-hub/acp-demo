<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>

<?php if (!$rooms->isEmpty()) { ?>
    <div class="panel-body">
        <?php echo $this->Form->create('Rooms', ['id'=>'actionForm', "method" => "Post"]); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Global Rooms List &nbsp; <?php echo $this->Html->link('<i class="fa fa-plus"></i> Add Room Name', ['controller'=>'rooms', 'action'=>'add'], ['escape'=>false, 'class'=>'btn btn-default btn-xs']); ?></div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'rooms', 'action'=>'index', $separator]));
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
                            <th class="sorting_paging"><input type="checkbox" id="checkAll" onclick="checkAllRecords(this)"></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('id', '# ID'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('name', 'Room Name'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('description', 'Description'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('status', 'Status'); ?></th>
                            <th class="action_dvv"><i class="fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rooms as $datarecord) { ?>
                            <tr>
                                <td><input type="checkbox" name="chkRecordId[]" value="<?php echo $datarecord->id; ?>" class="chk-item"></td>
                                <td data-title="# ID"><?php echo $datarecord->id; ?></td>
                                <td data-title="Room Name"><?php echo h($datarecord->name); ?></td>
                                <td data-title="Description"><?php echo h($datarecord->description); ?></td>
                                <td data-title="Status">
                                    <?php if ($datarecord->status == 1) { ?>
                                        <span class="label label-success">Active</span>
                                    <?php } else { ?>
                                        <span class="label label-danger">Inactive</span>
                                    <?php } ?>
                                </td>
                                <td data-title="Action">
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'rooms', 'action' => 'edit', $datarecord->slug], ['escape' => false, 'title' => 'Edit', 'class'=>'btn btn-primary btn-xs']); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'rooms', 'action' => 'delete', $datarecord->slug], ['escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs', 'confirm' => 'Are you sure?']); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="search_frm">
            <button type="submit" class="btn btn-sm btn-warning" name="action" value="Activate">Activate</button>
            <button type="submit" class="btn btn-sm btn-info" name="action" value="Deactivate">Deactivate</button>
            <button type="submit" class="btn btn-sm btn-danger" name="action" value="Delete" onclick="return confirm('Are you sure?')">Delete</button>
        </div>
        <?php echo $this->Form->end(); ?>
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div style="text-align: right; padding: 10px 15px;">
        No record found. &nbsp; <?php echo $this->Html->link('<i class="fa fa-plus"></i> Add Room Name', ['controller'=>'rooms', 'action'=>'add'], ['escape'=>false, 'class'=>'btn btn-default btn-sm']); ?>
    </div>
<?php } ?>

<script>
function checkAllRecords(obj) {
    var checked = obj.checked;
    $('.chk-item').each(function() {
        this.checked = checked;
    });
}
</script>
