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

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if ($pages) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Pages', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Pages List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'pages', 'action'=>'index', $separator]));
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
                            <th class="sorting_paging pagelisss"><?php echo $this->Paginator->sort('static_page_title', 'Title'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page) { ?>
                            <?php //pr($page); exit;?> 
                            <tr>
                                <td data-title="Page Name"><?php echo $page->static_page_title;?></td>
                                <td data-title="Action">
                                    <div id="loderstatus<?php echo $page->id; ?>" class="right_action_lo"><?php echo $this->Html->image("loading.gif"); ?></div>
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'pages', 'action' => 'edit',$page->slug], [ 'escape' => false, 'title' => 'Edit', 'class'=>'btn btn-primary btn-xs']); ?>
                                    <?php echo $this->Html->link('<i class="fa fa-info"></i>', '#info' . $page->id, array('escape' => false, 'title' => 'View', 'class' => 'btn btn-primary btn-xs', 'rel' => 'facebox')); ?>
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

        <?php foreach ($pages as $page) { ?>

            <div id="info<?php echo $page->id; ?>"
                 style="display: none;">
                <!-- Fieldset -->
                <div class="nzwh-wrapper">

                    <fieldset class="nzwh">
                         <legend class="head_pop">
                            <?php echo $page->static_page_title; ?>
                        </legend>
                        <div class="drt">
                    <div class="admin_pop">
                         <?php echo $page->static_page_description; ?>
                    </div>
                   


                </div>
                    </fieldset>
            </div>

        </div>
    <?php } ?>
