<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Room Allocations - <?php echo h($conventionSD->Conventions['name']); ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span>', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons', ['controller'=>'conventions', 'action'=>'seasons',$conventionSD->Conventions['slug']], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Room Events', ['controller'=>'conventions', 'action'=>'roomevents',$slug_convention_season], ['escape'=>false]);?></li>
          <li class="active">Room Allocations</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <div style="padding:10px 15px 5px;">
                <?php echo $this->Html->link('<i class="fa fa-plus"></i> Add Room Allocation', ['controller'=>'roomallocations', 'action'=>'add',$slug_convention_season], ['escape'=>false, 'class'=>'btn btn-default']);?>
                &nbsp;
                <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> Back to Room Events', ['controller'=>'conventions', 'action'=>'roomevents',$slug_convention_season], ['escape'=>false, 'class'=>'btn btn-default canlcel_le']);?>
            </div>

            <div class="m_content" style="padding:15px;">
                <?php if(empty($allocationList)): ?>
                    <div class="alert alert-info">No room allocations have been created yet. Click <strong>Add Room Allocation</strong> to create one.</div>
                <?php else: ?>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Allocation Name</th>
                            <th>Description</th>
                            <th>Rooms Assigned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $cnt = 1; foreach($allocationList as $item): $alloc = $item['allocation']; $rooms = $item['rooms']; ?>
                        <tr>
                            <td><?php echo $cnt++; ?></td>
                            <td><strong><?php echo h($alloc->name); ?></strong></td>
                            <td><?php echo h($alloc->description); ?></td>
                            <td>
                                <?php if(count($rooms) == 0): ?>
                                    <span class="label label-default">No rooms yet</span>
                                <?php else: ?>
                                    <?php foreach($rooms as $r): ?>
                                        <span class="label label-info" style="display:inline-block;margin:2px;"><?php echo h($r->Conventionrooms['room_name']); ?></span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $this->Html->link('<i class="fa fa-eye"></i> View / Manage', ['controller'=>'roomallocations', 'action'=>'view',$slug_convention_season,$alloc->id], ['escape'=>false, 'class'=>'btn btn-sm btn-info']);?>
                                &nbsp;
                                <?php echo $this->Html->link('<i class="fa fa-pencil"></i> Edit', ['controller'=>'roomallocations', 'action'=>'edit',$slug_convention_season,$alloc->id], ['escape'=>false, 'class'=>'btn btn-sm btn-default']);?>
                                &nbsp;
                                <?php echo $this->Form->postLink('<i class="fa fa-trash"></i> Delete', ['controller'=>'roomallocations', 'action'=>'delete',$slug_convention_season,$alloc->id], ['escape'=>false, 'class'=>'btn btn-sm btn-danger', 'confirm'=>'Delete "'.h($alloc->name).'" and remove all its room assignments?']);?>
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
