<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Add Room Allocation - <?php echo h($conventionSD->Conventions['name']); ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span>', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons', ['controller'=>'conventions', 'action'=>'seasons',$conventionSD->Conventions['slug']], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Room Events', ['controller'=>'conventions', 'action'=>'roomevents',$slug_convention_season], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Room Allocations', ['controller'=>'roomallocations', 'action'=>'index',$slug_convention_season], ['escape'=>false]);?></li>
          <li class="active">Add Room Allocation</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <div class="box-body" style="max-width:600px;padding:20px;">
                <?php echo $this->Form->create(null, ['url'=>['controller'=>'roomallocations','action'=>'add',$slug_convention_season], 'method'=>'post']); ?>

                <div class="form-group">
                    <label>Allocation Name <span style="color:red;">*</span></label>
                    <?php echo $this->Form->input('Roomallocations.name', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'placeholder'=>'e.g. Main Hall', 'required'=>true, 'value'=> (isset($this->request->data['Roomallocations']['name']) ? h($this->request->data['Roomallocations']['name']) : '')]); ?>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <?php echo $this->Form->input('Roomallocations.description', ['label'=>false, 'type'=>'text', 'class'=>'form-control', 'placeholder'=>'Optional description', 'value'=> (isset($this->request->data['Roomallocations']['description']) ? h($this->request->data['Roomallocations']['description']) : '')]); ?>
                </div>

                <div style="margin-top:20px;">
                    <?php echo $this->Form->button('<i class="fa fa-save"></i> Save Allocation', ['class'=>'btn btn-primary', 'escape'=>false]); ?>
                    &nbsp;
                    <?php echo $this->Html->link('Cancel', ['controller'=>'roomallocations', 'action'=>'index',$slug_convention_season], ['class'=>'btn btn-default canlcel_le']); ?>
                </div>

                <?php echo $this->Form->end(); ?>
            </div>

        </div>
    </section>
</div>
