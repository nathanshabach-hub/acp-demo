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
$this->Judgeevaluations = TableRegistry::get('Judgeevaluations');
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$eventsubmissions->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventionregistrationstudents', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Command Performance</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'Eventsubmissions', 'action'=>'commandperformance', $separator]));
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table id="command_performance" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#ID</th>
                            <th class="sorting_paging">School</th>
							<th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging">Event Number</th>
                            <th class="sorting_paging">Event Name</th>
                            <th class="sorting_paging">Group Event?</th>
                            <th class="sorting_paging">Group</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Judge</th>
							<th class="sorting_paging">Mark Date</th>
							<th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						$cntrS = 0;
						foreach ($eventsubmissions as $datarecord)
						{
							$cntrS++;
						?>
                            <tr>
                                <td data-title="#ID"><?php echo $datarecord->id;?></td>
								<td data-title="School"><?php echo $datarecord->Uploadeduser['first_name'].' '.$datarecord->Uploadeduser['last_name'];?></td>
								<td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
								<td data-title="Event Number"><?php echo $datarecord->event_id_number;?></td>
								<td data-title="Event Name"><?php echo $datarecord->Events['event_name'];?></td>
								<td data-title="Group Event?"><?php echo ($datarecord->Events['group_event_yes_no'] == 1) ? "Yes" : "No"; ?></td>
								<td data-title="Submitted For Group">
								<?php
								if(!empty($datarecord->group_name))
								{
									echo "Group ".$datarecord->group_name;
								}
								else
								{
									echo '-';
								}
								?>
								</td>
								<td data-title="Submitted For Student">
								<?php
								if($datarecord->student_id > 0)
								{
									echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
								}
								else
								{
									echo '-';
								}
								?>
								</td>
								
								<td data-title="Judge">
								<?php
								echo $datarecord->Judgecommand['first_name'].' '.$datarecord->Judgecommand['last_name'];
								?>
								</td>
								
                                <td data-title="Submitted Date"><?php echo date('M d, Y', strtotime($datarecord->modified)); ?></td>
								
								<td data-title="Action">
									
								<?php
								echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'eventsubmissions', 'action' => 'removecommandperformance',$datarecord->slug], [ 'escape' => false, 'title' => 'Remove command performance', 'class'=>'btn btn-danger btn-xs', 'confirm' => 'Are you sure you want to remove this command performance?']);
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

<?php foreach ($conventionregistrationstudents as $datarecord) { ?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <!-- Fieldset -->
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
				<?php echo $datarecord->Students['first_name'];?> <?php echo $datarecord->Students['middle_name'];?> <?php echo $datarecord->Students['last_name'];?> [Total Events: <?php echo count(explode(",",$datarecord->event_ids)); ?>]
                </legend>
                <div class="drt">
					
					<table class="table table-bordered table-striped table-condensed cf">
					
					<?php
					$condStudEvents = array();
					if($datarecord->event_ids != '' && $datarecord->event_ids != NULL)
					{
						$condStudEvents[] = "(Events.id  IN ($datarecord->event_ids) )";
					
					$eventsL = $this->Events->find()->where($condStudEvents)->all();
					?>
					<tr>
						<td>
							Event Number
						</td>
						<td>
							Event Name
						</td>
					</tr>
					<?php
					foreach($eventsL as $eventd)
					{
					?>
					
					<tr>
                        <td><?php echo $eventd->event_id_number; ?></td>
                        <td><?php echo $eventd->event_name; ?></td>
                    </tr>
					<?php
					}
					?>
					
					<?php
					}
					else
					{
					?>
                    <tr colspan="2">
                        <td>Sorry, no event found.</td>
                    </tr>
					<?php
					}
					?>
					</table>
					
					
					
                    
                </div>
            </fieldset>
        </div>
    </div>
<?php } ?>

<script>
$(document).ready(function() {
$('#command_performance').dataTable({
    "bPaginate": true,
    //"bInfo": false,
    "bLengthChange": false,
	"pageLength": 100,
	order: [[0, 'asc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#command_performance').dataTable.search(this.value).draw();
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