<?php echo $this->Flash->render() ?>
<?php echo $this->Form->create('Rooms', ['type' => 'file', 'id'=>'importRoomForm']); ?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4 class="panel-title">Import Global Rooms from Excel</h4>
    </div>
    <div class="panel-body">
        <div class="alert alert-info">
            <strong>Instructions:</strong>
            <ul>
                <li>Upload an Excel or CSV file with room data</li>
                <li>Required column: <strong>Room Name</strong></li>
                <li>Optional column: <strong>Description</strong></li>
                <li>Duplicate room names will be skipped</li>
            </ul>
        </div>

        <div class="form-group">
            <label for="import_file">Select Excel File (.xlsx, .xls, or .csv) *</label>
            <?php echo $this->Form->control('import_file', ['label' => false, 'type' => 'file', 'class' => 'form-control', 'accept' => '.xlsx,.xls,.csv', 'required' => true]); ?>
            <small class="form-text text-muted">Supported formats: .xlsx, .xls, .csv</small>
        </div>

        <div class="form-group">
            <?php echo $this->Form->button('Import Rooms', ['type' => 'submit', 'class' => 'btn btn-success']); ?>
            <?php echo $this->Html->link('Cancel', ['controller'=>'rooms', 'action'=>'index'], ['class'=>'btn btn-default']); ?>
        </div>

        <hr>

        <h5>Example Excel Format:</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Room Name</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Main Hall</td>
                    <td>Seats 500, air-conditioned</td>
                </tr>
                <tr>
                    <td>Auditorium A</td>
                    <td>East wing, stage equipped</td>
                </tr>
                <tr>
                    <td>Studio 1</td>
                    <td>First floor</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php echo $this->Form->end(); ?>
