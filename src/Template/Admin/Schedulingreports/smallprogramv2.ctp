<style>
.sp2-wrap { font-family: Arial, sans-serif; }
.sp2-day-header {
    background: #1a3a5c;
    color: #fff;
    padding: 10px 16px;
    margin-top: 28px;
    border-radius: 4px 4px 0 0;
    font-size: 1.15em;
    font-weight: bold;
    letter-spacing: 0.5px;
}
.sp2-day-header .sp2-date { font-weight: normal; font-size: 0.88em; margin-left: 12px; opacity: 0.85; }
.sp2-session-header {
    background: #2e6da4;
    color: #fff;
    padding: 5px 12px;
    font-size: 0.92em;
    font-weight: bold;
}
.sp2-lunch {
    background: #f5f0d8;
    border: 1px solid #e0d080;
    text-align: center;
    padding: 7px;
    font-weight: bold;
    font-size: 0.95em;
    color: #7a6000;
    margin: 0;
}
.sp2-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.82em;
    table-layout: fixed;
}
.sp2-table th {
    background: #ddeeff;
    border: 1px solid #aaccee;
    padding: 5px 7px;
    text-align: center;
    font-size: 0.88em;
    color: #1a3a5c;
    word-wrap: break-word;
}
.sp2-table td {
    border: 1px solid #ccd9e8;
    padding: 4px 6px;
    vertical-align: top;
    word-wrap: break-word;
}
.sp2-table td:empty { background: #f9f9f9; }
.sp2-event { font-size: 0.85em; margin-bottom: 3px; color: #222; }
.sp2-event .sp2-time { color: #888; font-size: 0.78em; white-space: nowrap; }
.sp2-no-events { color: #aaa; font-size: 0.8em; text-align: center; padding: 8px; background: #fafafa; border: 1px solid #eee; }
.sp2-empty-cell { background: #f8f8f8; }
.sp2-tbd-wrap { margin-top: 28px; border: 1px solid #e6d9a8; }
.sp2-tbd-header { background: #fff5cf; color: #6d5a00; font-weight: bold; padding: 8px 12px; border-bottom: 1px solid #e6d9a8; }
.sp2-tbd-list { margin: 0; padding: 10px 24px; }
.sp2-tbd-list li { margin: 3px 0; }
@media print {
    .sp2-day-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .sp2-session-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .sp2-lunch { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .sp2-table th { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .no-print { display: none !important; }
    .sp2-wrap { font-size: 9pt; }
}
</style>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Small Program &ndash; <?php echo h($conventionSD->Conventions['name']); ?>
        &nbsp;<small><?php echo h($conventionSD->season_year); ?></small>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Conventions', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Seasons', ['controller'=>'conventions', 'action'=>'seasons',$conventionSD->Conventions['slug']], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Reports', ['controller'=>'schedulings', 'action'=>'reports',$convention_season_slug], ['escape'=>false]);?></li>
          <li class="active">Small Program (PDF Style)</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <div style="padding:10px 15px;" class="no-print">
                <?php echo $this->Html->link('<i class="fa fa-print"></i> Print', ['controller'=>'schedulingreports', 'action'=>'smallprogramv2print',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']);?>
                &nbsp;
                <?php echo $this->Html->link('<i class="fa fa-list"></i> List View', ['controller'=>'schedulingreports', 'action'=>'smallprogram',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-info']);?>
                &nbsp;
                <?php echo $this->Html->link('Back', ['controller'=>'schedulings', 'action'=>'reports',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-warning']);?>
            </div>

            <div class="box-body sp2-wrap">

              <?php if(empty($dayData)): ?>
                <p><i>No scheduled events found.</i></p>
              <?php else: ?>

              <?php foreach($dayOrder as $day):
                  $dd = $dayData[$day];
                  // Collect all rooms that appear in either session, preserving order
                  $morningRooms   = array_keys($dd['morning']);
                  $afternoonRooms = array_keys($dd['afternoon']);
                  $allRooms = array_values(array_unique(array_merge($morningRooms, $afternoonRooms)));
                  $colCount = count($allRooms);
                  if ($colCount === 0) continue;
              ?>

              <!-- DAY HEADER -->
              <div class="sp2-day-header">
                  <?php echo strtoupper(h($day)); ?>
                  <?php if(!empty($dd['date'])): ?>
                  <span class="sp2-date"><?php echo h($dd['date']); ?></span>
                  <?php endif; ?>
              </div>

              <?php if (!empty($dd['morning'])): ?>
              <!-- MORNING SESSION -->
              <div class="sp2-session-header">
                  <?php echo !empty($dd['morningRange']) ? h($dd['morningRange']) : 'Morning'; ?> &mdash; Convention Events
              </div>
              <table class="sp2-table">
                  <thead>
                      <tr>
                          <?php foreach($allRooms as $rn): ?>
                          <th><?php echo h($rn); ?></th>
                          <?php endforeach; ?>
                      </tr>
                  </thead>
                  <tbody>
                      <tr>
                          <?php foreach($allRooms as $rn): ?>
                          <td class="<?php echo empty($dd['morning'][$rn]) ? 'sp2-empty-cell' : ''; ?>">
                              <?php if(!empty($dd['morning'][$rn])): ?>
                                  <?php foreach($dd['morning'][$rn] as $ev): ?>
                                  <div class="sp2-event"><?php echo h($ev); ?></div>
                                  <?php endforeach; ?>
                              <?php endif; ?>
                          </td>
                          <?php endforeach; ?>
                      </tr>
                  </tbody>
              </table>
              <?php endif; ?>

              <!-- LUNCH BREAK -->
              <div class="sp2-lunch">
                  LUNCH &nbsp; <?php echo h($lunchStart); ?> &ndash; <?php echo h($lunchEnd); ?>
              </div>

              <?php if (!empty($dd['afternoon'])): ?>
              <!-- AFTERNOON SESSION -->
              <div class="sp2-session-header">
                  <?php echo !empty($dd['afternoonRange']) ? h($dd['afternoonRange']) : 'Afternoon'; ?> &mdash; Convention Events
              </div>
              <table class="sp2-table">
                  <thead>
                      <tr>
                          <?php foreach($allRooms as $rn): ?>
                          <th><?php echo h($rn); ?></th>
                          <?php endforeach; ?>
                      </tr>
                  </thead>
                  <tbody>
                      <tr>
                          <?php foreach($allRooms as $rn): ?>
                          <td class="<?php echo empty($dd['afternoon'][$rn]) ? 'sp2-empty-cell' : ''; ?>">
                              <?php if(!empty($dd['afternoon'][$rn])): ?>
                                  <?php foreach($dd['afternoon'][$rn] as $ev): ?>
                                  <div class="sp2-event"><?php echo h($ev); ?></div>
                                  <?php endforeach; ?>
                              <?php endif; ?>
                          </td>
                          <?php endforeach; ?>
                      </tr>
                  </tbody>
              </table>
              <?php endif; ?>

              <?php endforeach; ?>
              <?php endif; ?>

                            <?php if(!empty($unscheduledEvents)): ?>
                            <div class="sp2-tbd-wrap">
                                <div class="sp2-tbd-header">Unscheduled Events (TBD)</div>
                                <ul class="sp2-tbd-list">
                                    <?php foreach($unscheduledEvents as $ue): ?>
                                    <li>
                                        <?php echo h($ue['event_name']); ?>
                                        <?php if(!empty($ue['event_id_number'])): ?>
                                        (<?php echo h($ue['event_id_number']); ?>)
                                        <?php endif; ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <?php endif; ?>

            </div><!-- /.box-body -->
        </div>
    </section>
</div>
