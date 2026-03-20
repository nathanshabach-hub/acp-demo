<?php
use Cake\ORM\TableRegistry;
$this->Users = TableRegistry::getTableLocator()->get('Users');
$this->Events = TableRegistry::getTableLocator()->get('Events');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (count($finalSchoolsList)) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Divisions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
				<b>Scripture Award Certificates</b>
            </div>   

            <div class="tbl-resp-listing">
                <table id="convention_events" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">School</th>
                            <th class="sorting_paging">Event</th>
							<th class="sorting_paging">Certificate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($finalSchoolsList as $school_id)
						{	
							// to get school details
							$schoolD 		= $this->Users->find()->where(['Users.id' => $school_id])->first();
							
							$schoolEvents = $finalSchoolsEventsList[$school_id];
							foreach ($schoolEvents as $event_id)
							{
								// to get event details
								$eventD 		= $this->Events->find()->where(['Events.id' => $event_id])->first();
							?>
							<tr>
                                <td data-title="School"><?php echo $schoolD->first_name;?> (#<?php echo $schoolD->id;?>)</td>
                                <td data-title="Event"><?php echo $eventD->event_name;?> (<?php echo $eventD->event_id_number;?>)</td>
                                <td data-title="Certificate">
								<?php
								echo $this->Html->link('<i class="fa fa-file-pdf-o"></i>', ['controller' => 'conventionregistrationstudents', 'action' => 'scriptureawardpdf',$slug_convention_season,$schoolD->slug,$eventD->slug], [ 'escape' => false, 'title' => 'Generate Scripture Award Certificate', 'target'=>'_blank']);
								?>
								</td>
                            </tr>
							<?php
							}
							
						}
						?>
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