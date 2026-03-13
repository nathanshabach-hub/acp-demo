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
$this->Conventionregistrationstudents = TableRegistry::get('Conventionregistrationstudents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if ($allEventsArr) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventionregistrations', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
               

            <div class="tbl-resp-listing">
                <table id="convention_registraions" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Event Number</th>
							<th class="sorting_paging">Event DB #</th>
                            <th class="sorting_paging">Event Name</th>
                            <th class="sorting_paging">Student(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
						for($cntrE=0;$cntrE<count($allEventsArr);$cntrE++)
						{ 
							// to get event details
							$eventD = $this->Events->find()->where(['Events.id' => $allEventsArr[$cntrE]])->first();
							
							// to get all strudents for this event
							$studentArr = array();
							
							$contStudents = array();
							$contStudents[] = "(Conventionregistrationstudents.conventionregistration_id = '".$CRDetails->id."')";
							$contStudents[] = "(Conventionregistrationstudents.event_ids = '".$eventD->id."' OR 
											Conventionregistrationstudents.event_ids LIKE '".$eventD->id.",%' OR 
											Conventionregistrationstudents.event_ids LIKE '%,".$eventD->id.",%' OR 
											Conventionregistrationstudents.event_ids LIKE '%,".$eventD->id."')";
							$eventStudents = $this->Conventionregistrationstudents->find()->where($contStudents)->contain(['Students'])->all();
							//echo '<pre>';print_r($eventStudents);exit;
							foreach($eventStudents as $eventStudent)
							{
								$stName = '';
								$stName = $eventStudent->Students['first_name'];
								if(!empty($eventStudent->Students['middle_name']))
								{
									$stName .= ' '.$eventStudent->Students['middle_name'];
								}
								if(!empty($eventStudent->Students['last_name']))
								{
									$stName .= ' '.$eventStudent->Students['last_name'];
								}
								$studentArr[] = $stName;
							};
						?>
                            <tr>
                                <td data-title="Event Number"><?php echo $eventD->event_id_number;?></td>
								<td data-title="Event DB #"><?php echo $eventD->id;?></td>
                                <td data-title="Event Name"><?php echo $eventD->event_name;?></td>
                                <td data-title="Student(s)"><?php if(count($studentArr)) { echo implode(", ",$studentArr); }?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        
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





<script>
$(document).ready(function() {
$('#convention_registraions').dataTable({
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