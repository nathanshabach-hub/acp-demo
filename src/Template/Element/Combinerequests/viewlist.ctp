<?php
use Cake\ORM\TableRegistry;
$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$combinerequests->isEmpty()) { ?> 
    <div class="panel-body">
        
        <?php echo $this->Form->create('Groups', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

            <div class="tbl-resp-listing">
                <table id="group_events_table" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th class="sorting_paging">Event Number</th>
							<th class="sorting_paging">Event Name</th>
							<th class="sorting_paging">Combined With School</th>
							<th class="sorting_paging">Student Name</th>
							<th class="sorting_paging">Request Date</th>
							<th class="sorting_paging">Status</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($combinerequests as $datarecord) { ?>
						<tr>
						<td data-title="Event Number"><?php echo $datarecord->event_id_number;?></td>
						<td data-title="Event Name"><?php echo $datarecord->Events['event_name'];?></td>
						<td data-title="Combined with"><?php echo $datarecord->Combineduser['first_name'];?></td>
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

<script>
$(document).ready(function() {
$('#group_events_table').dataTable({
    //"bPaginate": false,
    "bLengthChange": false,
	"pageLength": 50,
	order: [[6, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#group_events_table').dataTable.search(this.value).draw();
    }); */
});
</script>

<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
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





