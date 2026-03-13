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
<?php if ($conventionregistrations) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventionregistrations', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
              

            <div class="tbl-resp-listing">
                <table id="convention_registraions" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#ID</th>
                            <th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Email</th>
                            <th class="sorting_paging">School</th>
                            <th class="sorting_paging">Judge/Supervisor</th>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging">Price Structure</th>
                            <th class="sorting_paging">Price Per Student</th>
                            <th class="sorting_paging">Registration Date</th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conventionregistrations as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="ID"><?php echo $datarecord->id;?></td>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Email"><?php echo $datarecord->Users['email_address'];?></td>
                                <td data-title="School">
									<?php 
									if($datarecord->Users['user_type'] == 'School')
									{
										echo $datarecord->Users['first_name'];
									}
									else
									{
										echo 'N/A';
									}
									?>
								</td>
                                <td data-title="Judge">
									<?php 
									if($datarecord->Users['user_type'] == 'Judge')
									{
										echo $datarecord->Users['first_name'].' '.$datarecord->Users['last_name'];
									}
									else
									if($datarecord->Users['user_type'] == 'Teacher_Parent')
									{
										echo $datarecord->Users['first_name'].' '.$datarecord->Users['last_name'];
									}
									
									else
									{
										echo 'N/A';
									}
									?>
								</td>
								<td data-title="Season Year"><?php echo $datarecord->season_year; ?></td>
								<td data-title="Price Structure">
									<?php
										echo $priceStructureCR[$datarecord->price_structure] ? $priceStructureCR[$datarecord->price_structure] : 'N/A';
									?>
								</td>
								<td data-title="Price Per Student">
								<?php 
								if(!empty($datarecord->price_structure))
								{
									echo CURR.' '.number_format($datarecord->price_per_student,2);
								}
								else
								{
									echo 'N/A';
								}
								?></td>
                                <td data-title="Registration Date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
								
                                <td data-title="Action">
                                    
                                    <?php
									if($datarecord->Users['user_type'] == 'School')
									{
										if($datarecord->status == 1)
										{
											echo $this->Html->link('<i class="fa fa-user-secret"></i>', ['controller' => 'conventionregistrations', 'action' => 'teachers',$datarecord->slug], [ 'escape' => false, 'title' => 'Supervisors', 'class'=>'btn btn-primary btn-xs']);
											
											echo $this->Html->link('<i class="fa fa-group"></i>', ['controller' => 'conventionregistrations', 'action' => 'students',$datarecord->slug], [ 'escape' => false, 'title' => 'Students', 'class'=>'btn btn-primary btn-xs']);
											
											// to check if there is any heart event exists
											$checkHE = $this->Heartevents->find()->where(['Heartevents.conventionregistration_id' => $datarecord->id])->count();
											if($checkHE>0)
											{
												echo $this->Html->link('<i class="fa fa-heart"></i>', ['controller' => 'conventionregistrations', 'action' => 'heartevents',$datarecord->slug], [ 'escape' => false, 'title' => 'Events of the heart', 'class'=>'btn btn-primary btn-xs']);
											}
											
											// to check if there is any event submission exists
											$checkES = $this->Eventsubmissions->find()->where(['Eventsubmissions.conventionregistration_id' => $datarecord->id])->count();
											if($checkES>0)
											{
												echo $this->Html->link('<i class="fa fa-database"></i>', ['controller' => 'eventsubmissions', 'action' => 'index',$datarecord->slug], [ 'escape' => false, 'title' => 'Events submissions', 'class'=>'btn btn-primary btn-xs']);
											}
											
											echo $this->Html->link('<i class="fa fa-group"></i>', ['controller' => 'crstudentevents', 'action' => 'groups',$datarecord->slug], [ 'escape' => false, 'title' => 'Groups', 'class'=>'btn btn-primary btn-xs']);
											
											// to show all events registered by this school - with all students
											echo $this->Html->link('<i class="fa fa-puzzle-piece"></i>', ['controller' => 'conventionregistrationstudents', 'action' => 'events',$datarecord->slug], [ 'escape' => false, 'title' => 'View Events Registered By This School', 'class'=>'btn btn-primary btn-xs']);
										}
									}
									
									if($datarecord->Users['user_type'] == 'Teacher_Parent' || $datarecord->Users['user_type'] == 'Judge')
									{
										//echo $this->Html->link('<i class="fa fa-puzzle-piece"></i>', ['controller' => 'conventionregistrations', 'action' => 'judgeevents',$datarecord->slug], [ 'escape' => false, 'title' => 'Judge Selected Events', 'class'=>'btn btn-primary btn-xs']);
										
										//echo '<a href="#info'.$datarecord->id.'" rel="facebox" title="View" class="btn btn-info btn-xs eyee"><i class="fa fa-puzzle-piece"></i></a>';
										
										echo $this->Html->link('<i class="fa fa-puzzle-piece"></i>', ['controller' => 'conventionregistrations', 'action' => 'judgeregevents', $datarecord->slug], [ 'escape' => false, 'title' => 'Events', 'class' => 'btn btn-primary btn-xs']);
										
										if($datarecord->status == 2)
										{
											echo $this->Html->link('<i class="fa fa-check"></i>', ['controller' => 'conventionregistrations', 'action' => 'approvejudgeregistration', $datarecord->slug], [ 'escape' => false, 'title' => 'Approve', 'class' => 'btn btn-primary btn-xs', 'confirm' => 'Are you sure you want to approve this registration?']);
												
											echo $this->Html->link('<i class="fa fa-times"></i>', ['controller' => 'conventionregistrations', 'action' => 'declinejudgeregistration', $datarecord->slug], [ 'escape' => false, 'title' => 'Reject', 'class' => 'btn btn-warning btn-xs', 'confirm' => 'Are you sure you want to reject this registration?']);
										}
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