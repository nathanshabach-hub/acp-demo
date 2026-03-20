<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$conventionseasonroomevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Divisions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
				 
				 
				
				</div> 
				
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventions', 'action'=>'events',$slug_convention_season,$slug_convention, $separator]));
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table id="convention_events" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#DB ID Room</th>
                            <th class="sorting_paging">Room</th>
							<th class="sorting_paging">Event(s)</th>
							<th class="sorting_paging"><i class=" fa fa-gavel"></i>  Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($conventionseasonroomevents as $datarecord)
						{	
						?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="#DB ID Room">#<?php echo $datarecord->id;?></td>
                                <td data-title="Room"><?php echo $datarecord->Conventionrooms['room_name'];?></td>
                                <td data-title="Event(s)">
								<?php
								$arrEventN = array();
								$spbMap = [];
								if (!empty($datarecord->students_per_block)) {
									$spbMap = (array)json_decode($datarecord->students_per_block, true);
								}
								if(!empty($datarecord->event_ids))
								{
									$conditionEV = array();
									$conditionEV[] = "(Events.id IN (".$datarecord->event_ids."))";
								
									$eventsL = $this->Events->find()->where($conditionEV)->order(["Events.event_name" => "ASC"])->all();
									foreach($eventsL as $eventn)
									{
										$regCount = $this->Crstudentevents->find()->where(['Crstudentevents.conventionseason_id' => $datarecord->conventionseasons_id, 'Crstudentevents.event_id' => $eventn->id])->count();
										$label = $eventn->event_name."(".$eventn->event_id_number.") <span class=\"label label-default\" title=\"Registered students\">".$regCount." students</span>";
										if (isset($spbMap[$eventn->id]) && (int)$spbMap[$eventn->id] > 0) {
											$label .= ' <span class="label label-info" title="Students per block">' . (int)$spbMap[$eventn->id] . '/block</span>';
										}
										$arrEventN[] = $label;
									}
								}
								if(count($arrEventN))
								{
									echo implode(", ",$arrEventN);
								}
								else
								{
									echo 'N/A';
								}
								
								?>
								</td>
                                
								<td data-title="Action">
								<?php
								echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'conventions', 'action' => 'editroomevents',$datarecord->slug,$slug_convention_season], [ 'escape' => false, 'title' => 'Edit', 'class'=>'btn btn-primary btn-xs']);
								
								echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventions', 'action' => 'deleteroomevents',$datarecord->slug,$slug_convention_season], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']);
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
            <div class="list_sel"><?php echo $this->Form->control('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->control('Divisions.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
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
$('#convention_events').dataTable({
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
        $('#convention_events').dataTable.search(this.value).draw();
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