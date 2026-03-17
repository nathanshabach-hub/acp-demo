<div class="content-wrapper">
    <section class="content-header">
      <h1>
            Overflow Reallocation - <?php echo $conventionSD->Conventions['name']; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('View Scheduling', ['controller'=>'schedulingtimings', 'action'=>'viewscheduling', $convention_season_slug, $scheduling_category], ['escape'=>false]);?></li>
          <li class="active">Overflow Reallocation</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search" style="display:nones;">
                <div class="add_new_record">
                <?php
                echo $this->Html->link('<< Back To Schedule View', ['controller'=>'schedulingtimings', 'action'=>'viewscheduling', $convention_season_slug, $scheduling_category], ['escape'=>false, 'class'=>'btn btn-default']);
                echo '&nbsp;&nbsp;';
                echo $this->Html->link('<< View/Start Scheduling', ['controller'=>'schedulings', 'action'=>'schedulecategory', $convention_season_slug], ['escape'=>false, 'class'=>'btn btn-success']);
                echo '&nbsp;&nbsp;';
                echo $this->Html->link('Auto-Assign Top Fit', ['controller'=>'schedulingtimings', 'action'=>'autoassignoverflow', $convention_season_slug, $scheduling_category, '?' => ['days' => $selectedDays, 'rooms' => $selectedRoomIds]], ['escape'=>false, 'class'=>'btn btn-warning', 'confirm' => 'Auto-assign will place Friday/Saturday/Sunday overflow events into the first valid Monday-Thursday slots. Continue?']);
                ?>
                </div>
            </div>

            <div class="m_content" id="listID">
                <div class="panel-body">
                    <div class="alert alert-info" style="margin-bottom:12px;">
                        <strong>Overflow Status</strong><br>
                        <span><strong>Currently on Fri-Sun:</strong> <?php echo (int)$weekendOverflowCount; ?></span>
                        &nbsp;|&nbsp;
                        <span><strong>Currently unplaced:</strong> <?php echo (int)$unplacedCount; ?></span>
                        &nbsp;|&nbsp;
                        <span><strong>Moved back after last auto-assign:</strong> <?php echo !empty($lastAutoAssign) ? (int)$lastAutoAssign['assigned'] : 0; ?></span>
                        <?php if (!empty($lastAutoAssign)) { ?>
                            &nbsp;|&nbsp;
                            <span><strong>Last run:</strong> <?php echo h($lastAutoAssign['created']); ?></span>
                        <?php } ?>
                    </div>

                    <p><strong>Allowed days for reassignment:</strong> Monday to Thursday only.</p>
                    <div class="well" style="margin-bottom:15px;">
                        <?php echo $this->Form->create(null, ['type' => 'get', 'url' => ['controller' => 'schedulingtimings', 'action' => 'overflowallocator', $convention_season_slug, $scheduling_category]]); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <label><strong>Days To Use</strong></label><br>
                                <?php foreach ($allowedDays as $dayName) { ?>
                                    <label style="margin-right:10px; font-weight:normal;">
                                        <input type="checkbox" name="days[]" value="<?php echo h($dayName); ?>" <?php echo in_array($dayName, $selectedDays, true) ? 'checked' : ''; ?>>
                                        <?php echo h($dayName); ?>
                                    </label>
                                <?php } ?>
                            </div>
                            <div class="col-md-6">
                                <label><strong>Rooms To Use</strong></label>
                                <select name="rooms[]" class="form-control" multiple style="height:120px;">
                                    <?php foreach ($allRoomMap as $roomId => $roomName) { ?>
                                        <option value="<?php echo (int)$roomId; ?>" <?php echo in_array((int)$roomId, $selectedRoomIds, true) ? 'selected' : ''; ?>><?php echo h($roomName); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div style="margin-top:10px;">
                            <?php echo $this->Form->button('Apply Filters', ['class' => 'btn btn-primary']); ?>
                            &nbsp;
                            <?php echo $this->Html->link('Reset Filters', ['controller' => 'schedulingtimings', 'action' => 'overflowallocator', $convention_season_slug, $scheduling_category], ['class' => 'btn btn-default']); ?>
                        </div>
                        <?php echo $this->Form->end(); ?>
                    </div>
                    <p><strong>How to use:</strong> Choose a suggested slot for each overflow event. Suggestions only show room-free slots and also filter out obvious user clashes.</p>

                    <?php if (!empty($overflowRows)) { ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Current Status</th>
                                        <th>Event</th>
                                        <th>Match</th>
                                        <th>Duration</th>
                                        <th>Suggested Empty Slots (Mon-Thu)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($overflowRows as $idx => $rowData) {
                                    $timing = $rowData['timing'];
                                    $suggestions = $rowData['suggestions'];
                                ?>
                                    <tr>
                                        <td><?php echo ($idx + 1); ?> (ID <?php echo $timing->id; ?>)</td>
                                        <td>
                                            <?php echo !empty($timing->day) ? h($timing->day) : '(Unscheduled)'; ?>
                                            <?php if (!empty($timing->start_time) && !empty($timing->finish_time)) { ?>
                                                <br><?php echo date('h:i A', strtotime($timing->start_time)); ?> - <?php echo date('h:i A', strtotime($timing->finish_time)); ?>
                                            <?php } ?>
                                            <?php if (!empty($timing->Conventionrooms['room_name'])) { ?>
                                                <br>Room: <?php echo h($timing->Conventionrooms['room_name']); ?>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <?php echo h($timing->Events['event_name']); ?>
                                            (<?php echo h($timing->Events['event_id_number']); ?>)
                                        </td>
                                        <td>
                                            <?php
                                            $matchName = '';
                                            if (!empty($timing->Users['first_name'])) {
                                                $matchName = trim($timing->Users['first_name'].' '.$timing->Users['middle_name'].' '.$timing->Users['last_name']);
                                            }
                                            echo !empty($matchName) ? h($matchName) : '-';
                                            ?>
                                        </td>
                                        <td><?php echo (int)$rowData['duration_minutes']; ?> mins</td>
                                        <td>
                                            <?php if (!empty($suggestions)) { ?>
                                                <table class="table table-condensed table-bordered" style="margin-bottom:0;">
                                                    <thead>
                                                        <tr>
                                                            <th>Day</th>
                                                            <th>Room</th>
                                                            <th>Start</th>
                                                            <th>Finish</th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php foreach ($suggestions as $slot) { ?>
                                                        <tr>
                                                            <td><?php echo h($slot['day']); ?></td>
                                                            <td><?php echo h($slot['room_name']); ?></td>
                                                            <td><?php echo date('h:i A', strtotime($slot['start_time'])); ?></td>
                                                            <td><?php echo date('h:i A', strtotime($slot['finish_time'])); ?></td>
                                                            <td>
                                                                <?php echo $this->Form->create(null, ['url' => ['controller' => 'schedulingtimings', 'action' => 'overflowallocator', $convention_season_slug, $scheduling_category, '?' => ['days' => $selectedDays, 'rooms' => $selectedRoomIds]], 'style' => 'display:inline;']); ?>
                                                                <?php echo $this->Form->hidden('Overflow.timing_id', ['value' => $timing->id]); ?>
                                                                <?php echo $this->Form->hidden('Overflow.room_id', ['value' => $slot['room_id']]); ?>
                                                                <?php echo $this->Form->hidden('Overflow.day', ['value' => $slot['day']]); ?>
                                                                <?php echo $this->Form->hidden('Overflow.start_time', ['value' => $slot['start_time']]); ?>
                                                                <?php echo $this->Form->hidden('Overflow.finish_time', ['value' => $slot['finish_time']]); ?>
                                                                <?php echo $this->Form->button('Assign', ['class' => 'btn btn-xs btn-primary']); ?>
                                                                <?php echo $this->Form->end(); ?>
                                                            </td>
                                                        </tr>
                                                    <?php } ?>
                                                    </tbody>
                                                </table>
                                            <?php } else { ?>
                                                <span class="text-danger">No free room slot found before Thursday with current constraints.</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else { ?>
                        <div class="alert alert-success">No overflow events found for this category. Everything is within Monday-Thursday.</div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>
