<div class="content-wrapper">
    <section class="content-header">
      <h1>
            Post-Schedule Overview - <?php echo $conventionSD->Conventions['name']; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Schedule Categories', ['controller'=>'schedulings', 'action'=>'schedulecategory', $convention_season_slug], ['escape'=>false]);?></li>
          <li class="active">Post-Schedule Overview</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search" style="margin-bottom:10px; padding:10px;">
                <div class="add_new_record">
                <?php
                echo $this->Html->link('<< Back To Schedule Categories', ['controller'=>'schedulings', 'action'=>'schedulecategory', $convention_season_slug], ['escape'=>false, 'class'=>'btn btn-default']);
                ?>
                </div>
            </div>

            <div class="m_content" id="listID">
                <div class="panel-body">


                    <?php if (!empty($overflowTrendRows)) { ?>
                    <div class="panel panel-default" style="margin-bottom:15px;">
                        <div class="panel-heading"><strong>Overflow Trend (Recent Auto-Assign Runs)</strong></div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-condensed" style="margin-bottom:0;">
                                    <thead>
                                        <tr>
                                            <th>When</th>
                                            <th>Source</th>
                                            <th>Category</th>
                                            <th>Before</th>
                                            <th>Assigned</th>
                                            <th>After</th>
                                            <th>Remaining</th>
                                            <th>Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($overflowTrendRows as $trendRow) { ?>
                                        <tr>
                                            <td><?php echo h($trendRow['created']); ?></td>
                                            <td><?php echo h($trendRow['trigger_source']); ?></td>
                                            <td><?php echo $trendRow['schedule_category'] === null ? 'All' : (int)$trendRow['schedule_category']; ?></td>
                                            <td><?php echo (int)$trendRow['overflow_before']; ?></td>
                                            <td><?php echo (int)$trendRow['assigned_count']; ?></td>
                                            <td><?php echo (int)$trendRow['overflow_after']; ?></td>
                                            <td><?php echo (int)$trendRow['remaining_count']; ?></td>
                                            <td><?php echo !empty($trendRow['filter_days']) ? h($trendRow['filter_days']) : '-'; ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if ($totalOverflow === 0) { ?>
                        <div class="alert alert-success">
                            <strong>All events scheduled successfully!</strong> No overflow or unplaced events found. Everything fits within Monday-Thursday.
                        </div>
                        <p>
                            <?php echo $this->Html->link('Continue to Schedule Categories &raquo;', ['controller'=>'schedulings', 'action'=>'schedulecategory', $convention_season_slug], ['escape'=>false, 'class'=>'btn btn-success btn-lg']); ?>
                        </p>
                    <?php } else { ?>

                        <!-- Overflow Summary -->
                        <div class="alert alert-warning">
                            <strong><?php echo (int)$totalOverflow; ?> event(s) could not be placed</strong> within Monday-Thursday. They are either on Friday/Saturday/Sunday or have no room/time assigned.
                        </div>

                        <h4>Overflow by Category</h4>
                        <table class="table table-bordered table-condensed" style="max-width:700px;">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>On Fri-Sun</th>
                                    <th>Unplaced</th>
                                    <th>Total Overflow</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($overflowByCategory as $cat => $info) { ?>
                                <tr class="<?php echo $info['total'] > 0 ? 'danger' : 'success'; ?>">
                                    <td><?php echo h($info['label']); ?></td>
                                    <td><?php echo (int)$info['weekend']; ?></td>
                                    <td><?php echo (int)$info['unplaced']; ?></td>
                                    <td><strong><?php echo (int)$info['total']; ?></strong></td>
                                    <td>
                                        <?php if ($info['total'] > 0) { ?>
                                            <?php echo $this->Html->link('Open Overflow Allocator', ['controller'=>'schedulingtimings', 'action'=>'overflowallocator', $convention_season_slug, $cat], ['escape'=>false, 'class'=>'btn btn-xs btn-warning']); ?>
                                        <?php } else { ?>
                                            <span class="text-success">OK</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>

                            <div class="box-body">
                                <p>Create a new room below and the system will immediately try to fit all overflow events into available slots (including the new room).</p>

                                <div class="well" style="max-width:600px;">
                                    <h4>Existing Rooms (<?php echo count($rooms); ?>)</h4>
                                    <ul style="columns:2; -webkit-columns:2; -moz-columns:2; margin-bottom:15px;">
                                    <?php foreach ($rooms as $room) { ?>
                                        <li><?php echo h($room->room_name); ?></li>
                                    <?php } ?>
                                    </ul>
                                </div>

                                <?php echo $this->Form->create(null, ['url' => ['controller' => 'schedulingtimings', 'action' => 'createroomandreschedule', $convention_season_slug]]); ?>
                                <div class="form-group" style="max-width:400px;">
                                    <label>New Room Name <span style="color:red;">*</span></label>
                                    <?php echo $this->Form->control('NewRoom.room_name', ['label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control', 'placeholder' => 'e.g. Hall B, Room 5, Overflow Room', 'required' => true]); ?>
                                </div>
                                <div class="form-group" style="max-width:400px;">
                                    <label>Short Description (optional)</label>
                                    <?php echo $this->Form->control('NewRoom.short_description', ['label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control', 'placeholder' => 'Short name for reports']); ?>
                                </div>
                                <div style="margin-top:15px;">
                                    <?php echo $this->Form->button('Create Room & Auto-Assign Overflow', ['class' => 'btn btn-success btn-lg', 'onclick' => "return confirm('This will create the new room and immediately try to assign all overflow events. Continue?');"]); ?>
                                </div>
                                <?php echo $this->Form->end(); ?>
                            </div>
                        </div>

                        <hr>

                        <p>
                            <?php echo $this->Html->link('Skip &mdash; Continue to Schedule Categories &raquo;', ['controller'=>'schedulings', 'action'=>'schedulecategory', $convention_season_slug], ['escape'=>false, 'class'=>'btn btn-default btn-lg']); ?>
                        </p>

                    <?php } ?>

                </div>
            </div>
        </div>
    </section>
</div>
