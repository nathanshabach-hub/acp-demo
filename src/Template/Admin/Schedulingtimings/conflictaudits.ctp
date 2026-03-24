<?php
$convention_slug = isset($convention_slug) ? $convention_slug : '';
$convention_season_slug = isset($convention_season_slug) ? $convention_season_slug : '';
$conflictAuditRows = isset($conflictAuditRows) && is_array($conflictAuditRows) ? $conflictAuditRows : [];
?>

<section class="content-header">
  <h1>
	Conflict Audit Trail
  </h1>
  <ol class="breadcrumb">
	<li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
	<li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
	<li><?php echo $this->Html->link('<i class="fa fa-bullhorn"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
	<li><?php echo $this->Html->link('Schedule Categories', ['controller'=>'schedulings', 'action'=>'schedulecategory', $convention_season_slug], ['escape'=>false]);?></li>
	<li class="active">Conflict Audit Trail</li>
  </ol>
</section>

<section class="content">
  <div class="box box-primary">
	<div class="box-header with-border">
	  <h3 class="box-title">Conflict Scan Runs</h3>
	  <div class="box-tools pull-right">
		<?php
		  echo $this->Html->link('Export CSV', ['controller' => 'schedulingtimings', 'action' => 'exportconflictaudits', $convention_season_slug], ['class' => 'btn btn-sm btn-success']);
		  echo '&nbsp;';
		  echo $this->Html->link('Back To Schedule Categories', ['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug], ['class' => 'btn btn-sm btn-default']);
		?>
	  </div>
	</div>
	<div class="box-body">
	  <?php if (!empty($conflictAuditRows)) { ?>
		<div class="table-responsive">
		  <table class="table table-bordered table-striped table-condensed">
			<thead>
			  <tr>
				<th>Run ID</th>
				<th>Created</th>
				<th>Source</th>
				<th>Conflict Users</th>
				<th>Conflict Group Rows</th>
				<th>Conflict Timing Rows</th>
				<th>Season</th>
				<th>Notes</th>
			  </tr>
			</thead>
			<tbody>
			  <?php foreach ($conflictAuditRows as $row) { ?>
				<tr>
				  <td><?php echo (int)$row['id']; ?></td>
				  <td><?php echo h($row['created']); ?></td>
				  <td><?php echo h($row['trigger_source']); ?></td>
				  <td><?php echo (int)$row['conflict_user_count']; ?></td>
				  <td><?php echo (int)$row['conflict_group_row_count']; ?></td>
				  <td><?php echo (int)$row['conflict_timing_row_count']; ?></td>
				  <td><?php echo h($row['season_year']); ?></td>
				  <td><?php echo !empty($row['notes']) ? h($row['notes']) : '-'; ?></td>
				</tr>
			  <?php } ?>
			</tbody>
		  </table>
		</div>
	  <?php } else { ?>
		<div class="alert alert-info" style="margin-bottom:0;">No conflict audit runs recorded yet for this season.</div>
	  <?php } ?>
	</div>
  </div>
</section>
