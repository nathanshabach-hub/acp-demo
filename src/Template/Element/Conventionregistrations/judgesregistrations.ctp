<?php if (!$conventionregistrations->isEmpty()) { ?> 
    <div class="panel-body">
        <section id="no-more-tables" class="lstng-section">
            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging" style="width:30%;">Convention</th>
                            <th class="sorting_paging" style="width:10%;">Season Year</th>
                            <th class="sorting_paging" style="width:20%;">Registration Date</th>
                            <th class="sorting_paging" style="width:10%;">Events</th>
                            <th class="sorting_paging" style="width:30%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conventionregistrations as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Created"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td data-title="Events">
								<?php
								echo $this->Html->link('<i class="fa fa-puzzle-piece"></i>', ['controller' => 'conventionregistrations', 'action' => 'judgeevents',$datarecord->slug], [ 'escape' => false, 'title' => 'View Selected Events', 'class'=>'']);
								?>
								</td>
                                <td data-title="Status">
								<?php
								if($datarecord->status == 0)
									echo 'Declined';
								else
								if($datarecord->status == 1)
									echo 'Approved';
								else
								if($datarecord->status == 2)
									echo 'Pending From Admin';
								?>
								</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    
    </div>
<?php
}
else
{
?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">You have not yet registered for any convention in this season <?php echo $seasonD->season_year; ?>.</div>
<?php
}
?>

