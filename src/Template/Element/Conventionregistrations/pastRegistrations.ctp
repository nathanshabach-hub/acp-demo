<!-- To show remaining conventions -->
<?php if (!$pastRegistrations->isEmpty()) { ?> 
    <div class="panel-body">
        <section id="no-more-tables" class="lstng-section">
            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging" style="width:40%;">Convention</th>
                            <th class="sorting_paging" style="width:30%;">Season Year</th>
                            <th class="sorting_paging" style="width:30%;">View Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pastRegistrations as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Register Now">
								<?php
								echo $this->Html->link('View Details', ['controller' => 'conventionregistrations', 'action' => 'pastregistrationdetails', $datarecord->slug], [ 'escape' => false, 'title' => 'View Past Registration Details', 'class' => 'btn btn-primary', 'confirm' => 'Are you sure you want to view details of this past registration?']);
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
    <div class="admin_no_record">None of the convention remained to register for season <?php echo $seasonD->season_year; ?>.</div>
<?php
}
?>
