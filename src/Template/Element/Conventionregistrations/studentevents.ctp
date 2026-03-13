<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if ($studentList) { ?>

    <div class="panel-body">
        
        <?php echo $this->Form->create('Conventionregistrationstudents', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            

            <div class="tbl-resp-listing">
                <table id="cr_students_list" class="table table-striped table-bordered" style="width:100%">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Season Year</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Gender</th>
                            <th class="sorting_paging">Age</th>
                            <th class="sorting_paging">Events(s)</th>
							<th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach ($studentList as $datarecord)
						{
						?>
                            
                            <tr>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Student"><?php echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];?> </td>
								<td data-title="Gender"><?php echo $datarecord->Students['gender']; ?></td>
								<td data-title="Age"><?php echo $datarecord->season_year - $datarecord->Students['birth_year']; ?></td>
                                 
								<td data-title="Events(s)">
								<?php
								if($datarecord->event_ids)
									echo count(explode(",",$datarecord->event_ids));
								else
									echo 0;
								?>
								</td>
								
								
								<td data-title="Action">
                                    <?php
									echo $this->Html->link('<i class="fa fa-puzzle-piece"></i>', ['controller' => 'conventionregistrations', 'action' => 'managestudentevents',$datarecord->slug], [ 'escape' => false, 'title' => 'Manage Student Events', 'class'=>'']);
									
									if($userDetails->user_type == "School")
									{
										echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventionregistrations', 'action' => 'removestudentevent',$datarecord->slug], [ 'escape' => false, 'title' => 'Remove all events of this student', 'class'=>'', 'confirm' => 'Are you sure you want to remove all events of this student ?']);
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
    <div class="admin_no_record">No student event found.</div>
<?php }
?>

<script>
$(document).ready(function() {
$('#cr_students_list').dataTable({
    //"bPaginate": false,
    "bLengthChange": false,
	"pageLength": 50,
	order: [[1, 'asc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#cr_students_list').dataTable.search(this.value).draw();
    }); */
});
</script>
<?php echo $this->element("jquery_datatable_code"); ?>
