<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Room Allocation: <?php echo h($allocation->name); ?>
        <?php if($allocation->description): ?><small><?php echo h($allocation->description); ?></small><?php endif; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span>', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Room Events', ['controller'=>'conventions', 'action'=>'roomevents',$slug_convention_season], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Room Allocations', ['controller'=>'roomallocations', 'action'=>'index',$slug_convention_season], ['escape'=>false]);?></li>
          <li class="active"><?php echo h($allocation->name); ?></li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <!-- Action buttons -->
            <div style="padding:10px 15px 0;">
                <?php echo $this->Html->link('<i class="fa fa-pencil"></i> Edit Name/Description', ['controller'=>'roomallocations', 'action'=>'edit',$slug_convention_season,$allocation->id], ['escape'=>false, 'class'=>'btn btn-default']);?>
                &nbsp;
                <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> Back to All Allocations', ['controller'=>'roomallocations', 'action'=>'index',$slug_convention_season], ['escape'=>false, 'class'=>'btn btn-default canlcel_le']);?>
            </div>

            <div class="m_content" style="padding:15px;">

                <!-- Add Room Section -->
                <div class="panel panel-default" style="max-width:500px;margin-bottom:25px;">
                    <div class="panel-heading"><strong><i class="fa fa-plus"></i> Add a Room to this Allocation</strong></div>
                    <div class="panel-body">
                        <?php if(empty($availableRooms)): ?>
                            <p class="text-muted">All convention rooms have already been added to this allocation.</p>
                        <?php else: ?>
                            <?php echo $this->Form->create(null, ['url'=>['controller'=>'roomallocations','action'=>'view',$slug_convention_season,$allocation->id], 'method'=>'post']); ?>
                            <div class="form-group">
                                <label>Select Room</label>
                                <?php echo $this->Form->select('RoomallocationRooms.conventionroom_id', $availableRooms, ['empty'=>'-- Select a Room --', 'class'=>'form-control']); ?>
                            </div>
                            <div>
                                <?php echo $this->Form->button('<i class="fa fa-plus"></i> Add Room', ['class'=>'btn btn-primary', 'escape'=>false]); ?>
                            </div>
                            <?php echo $this->Form->end(); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Assigned Rooms -->
                <h4>Rooms in this Allocation (<?php echo count($assignedRooms); ?>)</h4>
                <?php if(count($assignedRooms) == 0): ?>
                    <div class="alert alert-warning">No rooms have been added to this allocation yet. Use the form above to add rooms.</div>
                <?php else: ?>
                <table class="table table-bordered table-hover" style="max-width:700px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Room Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $cnt = 1; foreach($assignedRooms as $ar): ?>
                        <tr>
                            <td><?php echo $cnt++; ?></td>
                            <td><?php echo h($ar->Conventionrooms['room_name']); ?></td>
                            <td>
                                <?php echo $this->Form->postLink('<i class="fa fa-trash"></i> Remove', ['controller'=>'roomallocations', 'action'=>'removeroom',$slug_convention_season,$ar->id], ['escape'=>false, 'class'=>'btn btn-sm btn-danger', 'confirm'=>'Remove "'.h($ar->Conventionrooms['room_name']).'" from this allocation?']); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>

            </div>
        </div>
    </section>
</div>
