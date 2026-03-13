<style>
  @media print {
    .page-break-after {
      page-break-after: always;
    }
  }
  .topn {display:none;}
  body { font-size: 11pt; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
  th, td { border: 1px solid #ccc; padding: 4px 6px; }
  th { background-color: #eee; }
  h4 { margin-top: 15px; margin-bottom: 4px; }
</style>
<script type="text/javascript">
<!--
window.print();
//-->
</script>

<h2><?php echo h($conventionSD->Conventions['name']); ?> &mdash; <?php echo h($conventionSD->season_year); ?> &mdash; Small Program</h2>

<?php if(!empty($programByDay)) { ?>
<?php foreach($programByDay as $dayName => $events) { ?>

<h4><strong><?php echo h($dayName); ?><?php if(!empty($dayDates[$dayName])): ?> <span style="font-weight:normal;font-size:0.9em;"><?php echo h($dayDates[$dayName]); ?></span><?php endif; ?></strong></h4>
<table>
    <thead>
        <tr>
            <th style="width:12%;">Start</th>
            <th style="width:12%;">Finish</th>
            <th style="width:40%;">Event</th>
            <th style="width:36%;">Room / Location</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($events as $ev) { ?>
    <tr>
        <td><?php echo h($ev['start_time']); ?></td>
        <td><?php echo h($ev['end_time']); ?></td>
        <td><?php echo h($ev['event_name']); ?></td>
        <td><?php echo h($ev['room_name']); ?></td>
    </tr>
    <?php } ?>
    </tbody>
</table>
<?php } ?>
<?php } else { ?>
<p><i>No scheduled events found for this convention season.</i></p>
<?php } ?>
