<?php
use Cake\ORM\TableRegistry;
$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$events->isEmpty()) { ?> 
    <div class="panel-body">
        
        <?php echo $this->Form->create('Groups', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

            <div class="tbl-resp-listing">
                <table id="group_events_table" class="table table-striped table-bordered" style="width:100%">
					<thead>
						<tr>
							<th class="sorting_paging">Event Number</th>
							<th class="sorting_paging">Event Name</th>
							<th class="sorting_paging">Group Event?</th>
							<th class="sorting_paging">Min</th>
							<th class="sorting_paging">Max</th>
							<th class="sorting_paging">Total Students</th>
							<th class="sorting_paging">Students Not Grouped</th>
							<th class="sorting_paging"><i class=" fa fa-gavel"></i> Action</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($events as $datarecord) { ?>
						<tr>
						<td data-title="Event Number"><?php echo $datarecord->event_id_number;?></td>
						<td data-title="Event Name"><?php echo $datarecord->event_name;?></td>
						<td data-title="Group Event?"><?php echo ($datarecord->group_event_yes_no == 1) ? "Yes" : "No"; ?></td>
						<td data-title="Min"><?php echo $datarecord->min_no;?></td>
						<td data-title="Max"><?php echo $datarecord->max_no;?></td>
						<td data-title="Total Students">
						<?php
						$condTS = array();
						$condTS[] = "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."')";
						$condTS[] = "(Crstudentevents.convention_id = '".$conventionRegD->convention_id."')";
						$condTS[] = "(Crstudentevents.season_id = '".$conventionRegD->season_id."')";
						$condTS[] = "(Crstudentevents.season_year = '".$conventionRegD->season_year."')";
						$condTS[] = "(Crstudentevents.event_id = '".$datarecord->id."')";
						
						$totalStudentsEvent = $this->Crstudentevents->find()->where($condTS)->count();
						echo $totalStudentsEvent;
						?>
						</td>
						
						<td data-title="Students Not Grouped">
						<?php
						$condTS[] = "(Crstudentevents.group_name = '' OR Crstudentevents.group_name IS NULL)";
						$studentNotGrouped = $this->Crstudentevents->find()->where($condTS)->count();
						
						if($datarecord->group_event_yes_no == 1)
						{
							echo $studentNotGrouped;
						}
						else
						{
							echo '';
						}
						?>
						</td>
						
						<td data-title="Action">
							
							<?php
							if($userDetails->user_type == "School")
							{
								// to check if its a group event and min/max students are in range..
								//.. then create group list icon
								
								if($datarecord->group_event_yes_no == 1)
								{
									if($totalStudentsEvent >= $datarecord->min_no)
									{
										echo $this->Html->link('<i class="fa fa-list"></i>', ['controller' => 'groups', 'action' => 'eventgroups',$datarecord->slug], [ 'escape' => false, 'title' => 'Event groups', 'class'=>'']);
									}
									else
									{
										echo '<i title="Min/max students criteria does not match. Add/remove more students in this event then you can create groups." class="fa fa-question-circle"></i>';
									}
									
									// to check that if all students assigned to a group or not
									
									if($studentNotGrouped >0)
									{
										echo '<i title="Some of the students might not assigned to groups." class="fa fa-info-circle" style="color:red;"></i>';
									}
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
<?php echo $this->element("jquery_datatable_code"); ?>





