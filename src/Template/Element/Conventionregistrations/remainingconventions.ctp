<!-- To show remaining conventions -->
<?php
$hasRemainingConventions = method_exists($remainingconventions, 'isEmpty') ? !$remainingconventions->isEmpty() : !empty($remainingconventions);
if ($hasRemainingConventions) {
?> 
    <div class="panel-body">
        <section id="no-more-tables" class="lstng-section">
            <div class="tbl-resp-listing">
                <table class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging" style="width:40%;">Convention</th>
                            <th class="sorting_paging" style="width:30%;">Season Year</th>
                            <th class="sorting_paging" style="width:30%;">Register Now</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($remainingconventions as $datarecord) { ?>
                            <?php //pr($datarecord); exit;?> 
                            <tr>
                                <td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
                                <td data-title="Season Year"><?php echo $datarecord->season_year;?></td>
                                <td data-title="Register Now">
								<?php
							echo $this->Html->link('Register', ['controller' => 'conventionregistrations', 'action' => 'registerfornewconvention', $datarecord->Conventions['slug'],$datarecord->season_id], [ 'escape' => false, 'title' => 'Register Now', 'class' => 'btn btn-primary', 'confirm' => 'Are you sure you want to register for this convention?']);
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
    <div class="admin_no_record">There are no open convention registrations available at the moment.</div>
<?php
}
?>
