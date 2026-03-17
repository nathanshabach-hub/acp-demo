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
                <div style="margin-bottom:10px;">
                    <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteRooms">Bulk Delete</button>
                </div>
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
                        <form id="bulkRoomForm" method="post" action="<?php echo $this->Url->build(['controller'=>'conventions','action'=>'bulkroom',$slug]); ?>">
                        <?php foreach ($convrooms as $datarecord) { ?>
                            <tr>
                                <td data-title="# DB ID">
                                    <input type="checkbox" name="room_ids[]" value="<?php echo $datarecord->id; ?>" class="bulk-select-room" />
                                    <?php echo $datarecord->id; ?>
                                </td>
                                <td data-title="Room Name">
                                    <span class="room-name-display" id="room-name-<?php echo $datarecord->id; ?>"><?php echo h($datarecord->room_name); ?></span>
                                    <input type="text" class="room-name-edit form-control" style="display:none;" value="<?php echo h($datarecord->room_name); ?>" data-room-id="<?php echo $datarecord->id; ?>" />
                                </td>
                                <td data-title="Short Description">
                                    <span class="room-desc-display" id="room-desc-<?php echo $datarecord->id; ?>"><?php echo h($datarecord->short_description); ?></span>
                                    <input type="text" class="room-desc-edit form-control" style="display:none;" value="<?php echo h($datarecord->short_description); ?>" data-room-id="<?php echo $datarecord->id; ?>" />
                                </td>
                                <td data-title="Action">
                                    <button type="button" class="btn btn-info btn-xs edit-room-btn" data-room-id="<?php echo $datarecord->id; ?>"><i class="fa fa-pencil"></i></button>
                                    <button type="button" class="btn btn-success btn-xs save-room-btn" style="display:none;" data-room-id="<?php echo $datarecord->id; ?>"><i class="fa fa-check"></i></button>
                                    <button type="button" class="btn btn-default btn-xs cancel-room-btn" style="display:none;" data-room-id="<?php echo $datarecord->id; ?>"><i class="fa fa-times"></i></button>
                                    <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventions', 'action' => 'deleteroom',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']); ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </form>
                    <script>
                    $(document).ready(function() {
                        // Inline edit
                        $('.edit-room-btn').on('click', function() {
                            var id = $(this).data('room-id');
                            $('#room-name-' + id).hide();
                            $('#room-desc-' + id).hide();
                            $('.room-name-edit[data-room-id="' + id + '"]').show();
                            $('.room-desc-edit[data-room-id="' + id + '"]').show();
                            $(this).hide();
                            $('.save-room-btn[data-room-id="' + id + '"]').show();
                            $('.cancel-room-btn[data-room-id="' + id + '"]').show();
                        });
                        $('.cancel-room-btn').on('click', function() {
                            var id = $(this).data('room-id');
                            $('#room-name-' + id).show();
                            $('#room-desc-' + id).show();
                            $('.room-name-edit[data-room-id="' + id + '"]').hide();
                            $('.room-desc-edit[data-room-id="' + id + '"]').hide();
                            $('.edit-room-btn[data-room-id="' + id + '"]').show();
                            $('.save-room-btn[data-room-id="' + id + '"]').hide();
                            $(this).hide();
                        });
                        $('.save-room-btn').on('click', function() {
                            var id = $(this).data('room-id');
                            var name = $('.room-name-edit[data-room-id="' + id + '"]').val();
                            var desc = $('.room-desc-edit[data-room-id="' + id + '"]').val();
                            // AJAX call to update room
                            $.ajax({
                                url: '/acp_demo/conventions/updateroom/' + id,
                                method: 'POST',
                                data: { room_name: name, short_description: desc },
                                success: function(resp) {
                                    $('#room-name-' + id).text(name).show();
                                    $('#room-desc-' + id).text(desc).show();
                                    $('.room-name-edit[data-room-id="' + id + '"]').hide();
                                    $('.room-desc-edit[data-room-id="' + id + '"]').hide();
                                    $('.edit-room-btn[data-room-id="' + id + '"]').show();
                                    $('.save-room-btn[data-room-id="' + id + '"]').hide();
                                    $('.cancel-room-btn[data-room-id="' + id + '"]').hide();
                                }
                            });
                        });
                        // Bulk delete
                        $('#bulkDeleteRooms').on('click', function() {
                            var selected = $('.bulk-select-room:checked').map(function(){ return $(this).val(); }).get();
                            if(selected.length === 0) { alert('Select at least one room.'); return; }
                            if(!confirm('Delete selected rooms?')) return;
                            $.ajax({
                                url: '/acp_demo/conventions/bulkdeleteroom',
                                method: 'POST',
                                data: { room_ids: selected },
                                success: function(resp) { location.reload(); }
                            });
                        });
                    });
                    </script>
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