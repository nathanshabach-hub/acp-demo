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
$this->Conventionregistrationstudents = TableRegistry::getTableLocator()->get('Conventionregistrationstudents');
$this->Conventionregistrationteachers = TableRegistry::getTableLocator()->get('Conventionregistrationteachers');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionregistrations->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventionregistrations', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
               

            <div class="tbl-resp-listing">
                <table id="convention_registraions" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#ID</th>
                            <th class="sorting_paging">Judge</th>
                            <th class="sorting_paging">Events</th>
                            <th class="sorting_paging">Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($conventionregistrations as $datarecord)
						{
						?>
                            <?php
							if(($datarecord->Users['user_type'] == "Judge" || $datarecord->Users['user_type'] == "Teacher_Parent") && $datarecord->Users['is_judge'] == 1)
							{
							?>
                            <tr>
                                <td data-title="ID"><?php echo $datarecord->id;?></td>
                                <td data-title="Judge">
									<?php 
									echo $datarecord->Users['first_name'].' '.$datarecord->Users['last_name'];
									?>
								</td>
								<td data-title="Events">
								<?php
								$judgEvent = 0;
								if(!empty($datarecord->judges_event_ids))
								{
									$judgEvent = count(explode(",",$datarecord->judges_event_ids));
								}
								echo $judgEvent;
								?>
								</td>
								
                                <td data-title="Registration Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                            </tr>
							<?php
							}
							?>
							
                        <?php
						}
						?>
                    </tbody>
                </table>
            </div>
        </section>

        
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



<script>
$(document).ready(function() {
$('#convention_registraions').dataTable({
    "bPaginate": true,
    //"bInfo": false,
    "bLengthChange": false,
	"pageLength": 100,
	order: [[0, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#convention_registraions').dataTable.search(this.value).draw();
    }); */
});
</script>

<!--
<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
-->
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<style type="text/css">
    .page-link {
        color: #1c2452 !important;
        background-color: #fff !important;
    }

    .active>.page-link,
    .page-link.active {
        background-color: #1c2452 !important;
        border-color: #1c2452 !important;
        color: #fff !important;
    }

    .pagination {
        border-radius: 0rem !important;
    }
</style>