<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Location Time Allocation - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Location Time Allocation</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Available vs Required Time Per Room</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

			<div style="padding:10px 15px 5px 15px; text-align:right;">
				
				<?php echo $this->Html->link('<i class="fa fa-download"></i> Download CSV', ['controller'=>'schedulingreports', 'action'=>'exportcsv',$convention_season_slug,'locationtimeallocation'], ['escape'=>false, 'class'=>'btn btn-success']);?>
				
				<?php echo $this->Html->link('Back', ['controller'=>'schedulings', 'action'=>'reports',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-warning']);?>
				
			</div>

            <div class="box-body">
			
			<div class="alert alert-info">
				<strong>Daily Available Minutes:</strong> <?php echo $dailyMinutes; ?> minutes per day per room
				(based on scheduled operating hours minus lunch break).
				Rooms with restrictions on certain days have proportionally fewer available minutes.
			</div>
			
			<div class="table-responsive">
                <table class="table table-bordered table-condensed">
                    <thead>
						<tr>
							<th>Room / Location</th>
							<th style="text-align:center;">Available (mins)</th>
							<th style="text-align:center;">Required (mins)</th>
							<th style="text-align:center;">Utilisation</th>
							<th style="text-align:center;">Events Scheduled</th>
							<th style="text-align:center;">Status</th>
						</tr>
                    </thead>
                    <tbody>
					<?php if(!empty($roomData)) { ?>
					<?php foreach($roomData as $ri => $row) {
						$isOver = ($row['required_minutes'] > $row['available_minutes']);
						$rowStyle = $isOver ? 'background-color:#f2dede;' : 'background-color:#dff0d8;';
						$statusLabel = $isOver
							? '<span class="label label-danger">Over Capacity</span>'
							: '<span class="label label-success">OK</span>';
						$utilPct = $row['available_minutes'] > 0
							? round(($row['required_minutes'] / $row['available_minutes']) * 100, 1)
							: 0;
						$collapseId = 'evbreak_'.$ri;
					?>
					<tr style="<?php echo $rowStyle; ?>">
						<td>
							<?php echo h($row['room_name']); ?>
							<?php if ($isOver && !empty($row['events'])): ?>
							<br><button type="button" onclick="var d=document.getElementById('<?php echo $collapseId; ?>');d.style.display=(d.style.display==='none'||d.style.display==='')?'block':'none';" class="btn btn-xs btn-link" style="padding:0;font-size:0.82em;"><i class="fa fa-list"></i> Show events</button>
							<?php endif; ?>
						</td>
						<td style="text-align:center;"><?php echo $row['available_minutes']; ?></td>
						<td style="text-align:center;"><?php echo $row['required_minutes']; ?></td>
						<td style="text-align:center;"><?php echo $utilPct; ?>%</td>
						<td style="text-align:center;"><?php echo $row['event_count']; ?></td>
						<td style="text-align:center;"><?php echo $statusLabel; ?></td>
					</tr>
					<?php if ($isOver && !empty($row['events'])): ?>
					<tr style="background-color:#fdf2f2;">
						<td colspan="6" style="padding:0;">
							<div id="<?php echo $collapseId; ?>">
								<table class="table table-condensed" style="margin:0;font-size:0.85em;">
									<thead><tr style="background:#e8c8c8;"><th>Event</th><th style="text-align:center;">Matches</th><th style="text-align:center;">Minutes Used</th></tr></thead>
									<tbody>
									<?php foreach($row['events'] as $ev): ?>
									<tr>
										<td><?php echo h($ev['event_name']); ?></td>
										<td style="text-align:center;"><?php echo $ev['count']; ?></td>
										<td style="text-align:center;"><?php echo round($ev['minutes']); ?></td>
									</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							</div>
						</td>
					</tr>
					<?php endif; ?>
					<?php } ?>
					<?php } else { ?>
					<tr>
						<td colspan="6"><i>No room data found for this convention season.</i></td>
					</tr>
					<?php } ?>
                    </tbody>
                </table>
			</div>
			
            </div>
        </div>
    </section>
  </div>
