<?php
$convention_slug = isset($convention_slug) ? $convention_slug : '';
$convention_season_slug = isset($convention_season_slug) ? $convention_season_slug : '';
$bulkMoveAuditRows = isset($bulkMoveAuditRows) && is_array($bulkMoveAuditRows) ? $bulkMoveAuditRows : [];
?>

<section class="content-header">
  <h1>
    Bulk Move Audit Trail
  </h1>
  <ol class="breadcrumb">
    <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
    <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
    <li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
    <li><?php echo $this->Html->link('Schedule Categories', ['controller'=>'schedulings', 'action'=>'schedulecategory', $convention_season_slug], ['escape'=>false]);?></li>
    <li class="active">Bulk Move Audit Trail</li>
  </ol>
</section>

<section class="content">
  <div class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">Bulk Move Runs</h3>
      <div class="box-tools pull-right">
        <?php
          echo $this->Html->link('Export CSV', ['controller' => 'schedulingtimings', 'action' => 'exportbulkmoveaudits', $convention_season_slug], ['class' => 'btn btn-sm btn-success']);
          echo '&nbsp;';
          echo $this->Html->link('Back To Scheduling View', ['controller' => 'schedulingtimings', 'action' => 'viewscheduling', $convention_season_slug, 1], ['class' => 'btn btn-sm btn-default']);
        ?>
      </div>
    </div>
    <div class="box-body">
      <?php if (!empty($bulkMoveAuditRows)) { ?>
        <div class="table-responsive">
          <table class="table table-bordered table-striped table-condensed">
            <thead>
              <tr>
                <th>Run ID</th>
                <th>Created</th>
                <th>Category</th>
                <th>Action</th>
                <th>Move Type</th>
                <th>Strategy</th>
                <th>Moved</th>
                <th>Unchanged</th>
                <th>Notes</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bulkMoveAuditRows as $row) { ?>
                <tr>
                  <td><?php echo (int)$row['id']; ?></td>
                  <td><?php echo h($row['created']); ?></td>
                  <td><?php echo isset($row['schedule_category']) ? (int)$row['schedule_category'] : '-'; ?></td>
                  <td><?php echo !empty($row['action_type']) ? h($row['action_type']) : '-'; ?></td>
                  <td><?php echo !empty($row['move_type']) ? h($row['move_type']) : '-'; ?></td>
                  <td><?php echo !empty($row['strategy']) ? h($row['strategy']) : '-'; ?></td>
                  <td><?php echo isset($row['moved_count']) ? (int)$row['moved_count'] : 0; ?></td>
                  <td><?php echo isset($row['unchanged_count']) ? (int)$row['unchanged_count'] : 0; ?></td>
                  <td><?php echo !empty($row['notes']) ? h($row['notes']) : '-'; ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } else { ?>
        <div class="alert alert-info" style="margin-bottom:0;">No bulk move audit runs recorded yet for this season.</div>
      <?php } ?>
    </div>
  </div>
</section>
