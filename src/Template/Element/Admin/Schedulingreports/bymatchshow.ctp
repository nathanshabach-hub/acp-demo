<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!empty($matchRows)) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">
                <h4>All Scheduled Matches and Performances</h4>
                </div>
            </div>

            <div class="tbl-resp-listing">
                <table id="report_by_match" class="table table-bordered table-striped table-condensed cf">

                    <tr>
                        <th class="sorting_paging" width="10%">Day</th>
                        <th class="sorting_paging" width="10%">Start</th>
                        <th class="sorting_paging" width="10%">Finish</th>
                        <th class="sorting_paging" width="20%">Location</th>
                        <th class="sorting_paging" width="20%">Event</th>
                        <th class="sorting_paging" width="30%">Match</th>
                    </tr>
                    <?php foreach ($matchRows as $row) { ?>
                        <tr>
                            <td data-title="Day"><?php echo $row['day']; ?></td>
                            <td data-title="Start"><?php echo $row['start']; ?></td>
                            <td data-title="Finish"><?php echo $row['finish']; ?></td>
                            <td data-title="Location"><?php echo $row['location']; ?></td>
                            <td data-title="Event"><?php echo $row['event']; ?><?php if (!empty($row['event_id_number'])) { ?> (<?php echo $row['event_id_number']; ?>)<?php } ?></td>
                            <td data-title="Match"><?php echo $row['match']; ?></td>
                        </tr>
                    <?php } ?>

                </table>
            </div>
            <div class="pagebreakafter"></div>


        </section>



    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>
