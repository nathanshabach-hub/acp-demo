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
$this->Events = TableRegistry::get('Events');
$this->Heartevents = TableRegistry::get('Heartevents');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$combinerequests->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Combinerequests', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Combined Team/Group Events List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'combinerequests', 'action'=>'index', $separator]));
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table id="convention_registraions" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Convention</th>
							<th class="sorting_paging"><?php echo $this->Paginator->sort('season_year', 'Season Year'); ?></th>
                            <th class="sorting_paging">Event Number</th>
                            <th class="sorting_paging">Event</th>
                            <th class="sorting_paging">Request By School</th>
                            <th class="sorting_paging">Combine With School School</th>
                            <th class="sorting_paging">Student Name</th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('created', 'Request Date'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('status', 'Status'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($combinerequests as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
								<td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Event Number"><?php echo $datarecord->Events['event_id_number'];?></td>
                                <td data-title="Event"><?php echo $datarecord->Events['event_name'];?></td>
                                <td data-title="Request By School"><?php echo $datarecord->Users['first_name'];?></td>
                                <td data-title="Combine With School School"><?php echo $datarecord->Combineduser['first_name'];?></td>
                                <td data-title="Student Name"><?php echo $datarecord->student_name;?></td>
                                <td data-title="Request Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td data-title="Status">
								<?php
								if($datarecord->status == 0)
								{
									echo 'Declined';
								}
								else
								if($datarecord->status == 1)
								{
									echo 'Approved';
								}
								else
								if($datarecord->status == 2)
								{
									echo 'Pending';
								}
								?>
								</td>
								
                                <td data-title="Action">
                                    
                                    <?php
									if($datarecord->status == 2)
									{
										echo $this->Html->link('<i class="fa fa-check"></i>', ['controller' => 'combinerequests', 'action' => 'approverequest', $datarecord->slug], [ 'escape' => false, 'title' => 'Approve', 'class' => 'btn btn-primary btn-xs', 'confirm' => 'Are you sure you want to approve this request?']);
										
										echo $this->Html->link('<i class="fa fa-times"></i>', ['controller' => 'combinerequests', 'action' => 'declinerequest', $datarecord->slug], [ 'escape' => false, 'title' => 'Decline', 'class' => 'btn btn-warning btn-xs', 'confirm' => 'Are you sure you want to decline this request?']);
									}
									?>
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


<?php
foreach ($conventionregistrations as $datarecord)
{
	if(!empty($datarecord->judges_event_ids))
	{
?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <!-- Fieldset -->
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
				Events Selected By: <?php echo $datarecord->Users['first_name'].' '.$datarecord->Users['last_name']; ?>
                </legend>
                <div class="drt">
					
					<?php
					$cntrE = 1;
					$condEv = array();
					$condEv[] = "(Events.id IN (".$datarecord->judges_event_ids.") )";
					$judgeEvents = $this->Events->find()->where($condEv)->order(["Events.event_name" =>"ASC"])->all();
					foreach($judgeEvents as $judgeevent)
					{
					?>
					<div class="admin_pop">
                        <span><?php echo $cntrE.'. '; echo $judgeevent->event_name; ?> </span>  <label>
						<?php echo $judgeevent->event_id_number; ?>
						</label>
                    </div>
					<?php
					$cntrE++;
					}
					?>
                    
                </div>
            </fieldset>
        </div>
    </div>
<?php }} ?>


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