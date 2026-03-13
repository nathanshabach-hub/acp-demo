<?php
use Cake\ORM\TableRegistry;
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
$this->Judgeevaluations = TableRegistry::get('Judgeevaluations');


?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php //if (!$conventionseasonevents->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(NULL, ['id' => 'addresults', 'type' => 'file', 'class' => ' ']); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left"> Total Entries for this event: <?php echo count($resultsPos); ?>
				</div>  
            </div>   

            <div class="tbl-resp-listing">
                <table id="results_table" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Position</th>
							<th class="sorting_paging">Points Obtained</th>
                            <th class="sorting_paging">Edit Position</th>
                            <th class="sorting_paging">School</th>
                            <th class="sorting_paging">Student/Group</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						foreach($resultsPos as $datarecord)
						{
						?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Position">
									<?php echo $datarecord->position;?>
								</td>
								<td data-title="Points Obtained">
									<?php echo $datarecord->points_obtained;?>
								</td>
								
								<td data-title="Edit Position">
									<input type="number" name="result_position_<?php echo $datarecord->id; ?>" id="result_position_<?php echo $datarecord->id; ?>" value="<?php echo $datarecord->position;?>" />
								</td>
								
                                <td data-title="School"><?php echo $datarecord->Users['first_name']; ?></td>
                                <td data-title="Student/Group">
								<?php 
								if($datarecord->student_id>0)
								{
									echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
								}
								else
								if(!empty($datarecord->group_name))
								{
									echo $datarecord->group_name;
								}
								?>
								</td>
                            </tr>
                        <?php } ?>
						
							
                    </tbody>
                </table>
            </div>
        </section>
		
		<button type="submit" class="btn btn-success">Save</button>
		<?php
		if($checkResultsAlready->original_results_modified >0)
		{
			echo '<p></p><p style="color:red">Note: Original results already changed.</p>';
		}
		?>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php //} else { ?> 
<?php //}
?>

<script>
$(document).ready(function() {
$('#results_table').dataTable({
    "bPaginate": false,
    "bInfo": false,
    //"bLengthChange": false,
	//"pageLength": 500000,
	order: [[1, 'desc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#results_table').dataTable.search(this.value).draw();
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