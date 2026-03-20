<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$convseasons->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create('Conventions', ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Seasons List</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'conventions', 'action'=>'seasons', $slug, $separator]));
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('id', '# DB ID'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('season_year', 'Season Year'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('registration_start_date', 'Registration Start Date'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('registration_end_date', 'Registration End Date'); ?></th>
							
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('student_registration_fees', 'Student registration ('.CURR.')'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('non_competitor_registration_fees', 'Non-competitor registration ('.CURR.')'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('non_affiliate_registration_fees', 'Non-affiliate registration ('.CURR.')'); ?></th>
                            <th class="sorting_paging"><?php echo $this->Paginator->sort('supervisor_registration_fees', 'Supervisor registration ('.CURR.')'); ?></th>
                            <th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($convseasons as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="# DB ID"><?php echo $datarecord->id;?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Registration Start Date"><?php echo date('M d, Y', strtotime($datarecord->registration_start_date)); ?></td>
                                <td data-title="Registration End Date"><?php echo date('M d, Y', strtotime($datarecord->registration_end_date)); ?></td>
								
								<td data-title="Student registration"><?php echo number_format($datarecord->student_registration_fees,2);?></td>
								<td data-title="Non-competitor registration"><?php echo number_format($datarecord->non_competitor_registration_fees,2);?></td>
								<td data-title="Non-affiliate registration"><?php echo number_format($datarecord->non_affiliate_registration_fees,2);?></td>
								<td data-title="Supervisor registration"><?php echo number_format($datarecord->supervisor_registration_fees,2);?></td>
								
                                <td data-title="Action">
									
									<?php
									echo $this->Html->link('<i class="fa fa-pencil"></i>', ['controller' => 'conventions', 'action' => 'changeprices',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Change Prices', 'class'=>'btn btn-info btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-puzzle-piece"></i>', ['controller' => 'conventions', 'action' => 'events',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Manage Events', 'class'=>'btn btn-info btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-circle"></i>', ['controller' => 'results', 'action' => 'points',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Division Points', 'class'=>'btn btn-warning btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-bullseye"></i>', ['controller' => 'results', 'action' => 'overallpoints',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Overall Points', 'class'=>'btn btn-warning btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-star"></i>', ['controller' => 'conventions', 'action' => 'scriptureawardslist',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Scripture Award', 'class'=>'btn btn-warning btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-trophy"></i>', ['controller' => 'results', 'action' => 'divisionwinners',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Division Winners', 'class'=>'btn btn-warning btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-heart"></i>', ['controller' => 'heartevents', 'action' => 'listheartevents',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Events of the heart students', 'class'=>'btn btn-primary btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-gavel"></i>', ['controller' => 'conventions', 'action' => 'brokenrecordcertificate',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Broken record certificate', 'class'=>'btn btn-primary btn-xs']);

                                    $eventSubmissionsOpen = true;
                                    if (isset($eventSubmissionOpenMap[$datarecord->id])) {
                                        $eventSubmissionsOpen = $eventSubmissionOpenMap[$datarecord->id];
                                    }

                                    if ($eventSubmissionsOpen) {
                                        echo $this->Html->link('<i class="fa fa-lock"></i>', ['controller' => 'conventions', 'action' => 'closeeventsubmissions', $datarecord->slug, $slug], [ 'escape' => false, 'title' => 'Close Event Submissions', 'class'=>'btn btn-warning btn-xs', 'confirm' => 'Are you sure you want to close event submissions for this season?']);
                                    } else {
                                        echo $this->Html->link('<i class="fa fa-unlock"></i>', ['controller' => 'conventions', 'action' => 'openeventsubmissions', $datarecord->slug, $slug], [ 'escape' => false, 'title' => 'Open Event Submissions', 'class'=>'btn btn-success btn-xs', 'confirm' => 'Are you sure you want to open event submissions for this season?']);
                                    }
									
									echo '<br />';
									echo '<br />';
									
									echo $this->Html->link('<i class="fa fa-user-secret"></i>', ['controller' => 'conventions', 'action' => 'judges',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Judges List', 'class'=>'btn btn-info btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-mortar-board"></i>', ['controller' => 'results', 'action' => 'overallpositions',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Results List', 'class'=>'btn btn-info btn-xs']);
									
									if($datarecord->results_release == 0)
									{
										echo $this->Html->link('<i class="fa fa-eye"></i>', ['controller' => 'conventions', 'action' => 'seasonresultrelease',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Release Result', 'class'=>'btn btn-primary btn-xs', 'confirm' => 'Are you sure you want to release results ?']);
									}
									else
									{
										echo $this->Html->link('<i class="fa fa-eye-slash"></i>', ['controller' => 'conventions', 'action' => 'seasonresultreleasestop',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Stop Release Result', 'class'=>'btn btn-primary btn-xs', 'confirm' => 'Are you sure you want to stop release results ?']);
									}
									
									echo $this->Html->link('<i class="fa fa-registered"></i>', ['controller' => 'conventions', 'action' => 'roomevents',$datarecord->slug], [ 'escape' => false, 'title' => 'Room Events', 'class'=>'btn btn-primary btn-xs']);
									
									echo $this->Html->link('<i class="fa fa-clock-o"></i>', ['controller' => 'schedulings', 'action' => 'precheck',$datarecord->slug], [ 'escape' => false, 'title' => 'Scheduling Pre-check', 'class'=>'btn btn-primary btn-xs']);
									
                                    echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'conventions', 'action' => 'deleteconventionsseason',$datarecord->slug,$slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-danger btn-xs action-list delete-list', 'confirm' => 'Are you sure you want to Delete ?']);
									
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
    <div class="admin_no_record">Sorry, no record found.</div>
<?php }
?>