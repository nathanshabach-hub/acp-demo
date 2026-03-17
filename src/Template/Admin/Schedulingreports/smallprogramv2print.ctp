<style>
body { font-family: Arial, sans-serif; font-size: 9pt; margin: 10px; }
.sp2-brand { display: flex; align-items: center; gap: 14px; margin-bottom: 12px; }
.sp2-brand-logo { max-height: 72px; max-width: 180px; width: auto; height: auto; display: block; }
.sp2-brand-copy { min-width: 0; }
.sp2-wrap-custom-note { margin: 0 0 14px; padding: 9px 11px; background: #f8f8f8; border-left: 4px solid <?php echo h($smallProgramCustomization['secondary_color']); ?>; color: #333; }
.sp2-day-header {
    background: <?php echo h($smallProgramCustomization['primary_color']); ?>;
    color: #fff;
    padding: 6px 12px;
    margin-top: 18px;
    font-size: 1.05em;
    font-weight: bold;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.sp2-day-header .sp2-date { font-weight: normal; font-size: 0.88em; margin-left: 10px; opacity: 0.85; }
.sp2-session-header {
    background: <?php echo h($smallProgramCustomization['secondary_color']); ?>;
    color: #fff;
    padding: 4px 10px;
    font-size: 0.88em;
    font-weight: bold;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.sp2-lunch {
    background: #f5f0d8;
    border: 1px solid #e0d080;
    text-align: center;
    padding: 5px;
    font-weight: bold;
    font-size: 0.88em;
    color: #7a6000;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th {
    background: <?php echo h($smallProgramCustomization['table_header_color']); ?>;
    border: 1px solid #aaccee;
    padding: 4px 6px;
    text-align: center;
    font-size: 0.82em;
    color: #1a3a5c;
    word-wrap: break-word;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
td { border: 1px solid #ccd9e8; padding: 3px 5px; vertical-align: top; word-wrap: break-word; font-size: 0.82em; }
.sp2-event { margin-bottom: 2px; }
.sp2-empty-cell { background: #f8f8f8; }
h2 { font-size: 1.1em; margin-bottom: 4px; }
<?php if (!empty($smallProgramCustomization['custom_css'])): ?>
<?php echo $smallProgramCustomization['custom_css']; ?>
<?php endif; ?>
</style>
<script type="text/javascript">
<!--
window.print();
//-->
</script>

<?php
    $reportTitle = !empty($smallProgramCustomization['report_title']) ? $smallProgramCustomization['report_title'] : 'Small Program';
    $reportSubtitle = !empty($smallProgramCustomization['report_subtitle']) ? $smallProgramCustomization['report_subtitle'] : $conventionSD->Conventions['name'] . ' - ' . $conventionSD->season_year;
?>
<div class="sp2-brand">
    <?php if(!empty($smallProgramCustomization['logo_path'])): ?>
    <img class="sp2-brand-logo" src="<?php echo h($smallProgramCustomization['logo_path']); ?>" alt="<?php echo h(!empty($smallProgramCustomization['logo_alt_text']) ? $smallProgramCustomization['logo_alt_text'] : $reportTitle); ?>">
    <?php endif; ?>
    <div class="sp2-brand-copy">
        <h2><?php echo h($reportTitle); ?> &mdash; <?php echo h($reportSubtitle); ?></h2>
        <?php if(!empty($smallProgramCustomization['intro_note'])): ?>
        <div class="sp2-wrap-custom-note"><?php echo nl2br(h($smallProgramCustomization['intro_note'])); ?></div>
        <?php endif; ?>
    </div>
</div>

<?php if(empty($dayData)): ?>
<p><i>No scheduled events found.</i></p>
<?php else: ?>

<?php foreach($dayOrder as $day):
    $dd = $dayData[$day];
    $morningRooms   = array_keys($dd['morning']);
    $afternoonRooms = array_keys($dd['afternoon']);
    $allRooms = array_values(array_unique(array_merge($morningRooms, $afternoonRooms)));
    if (empty($allRooms)) continue;
?>

<div class="sp2-day-header">
    <?php echo strtoupper(h($day)); ?>
    <?php if(!empty($dd['date'])): ?>
    <span class="sp2-date"><?php echo h($dd['date']); ?></span>
    <?php endif; ?>
</div>

<?php if (!empty($dd['morning'])): ?>
<div class="sp2-session-header"><?php echo !empty($dd['morningRange']) ? h($dd['morningRange']) : 'Morning'; ?> &mdash; <?php echo h($smallProgramCustomization['morning_label']); ?></div>
<table>
    <thead>
        <tr><?php foreach($allRooms as $rn): ?><th><?php echo h($rn); ?></th><?php endforeach; ?></tr>
    </thead>
    <tbody>
        <tr>
            <?php foreach($allRooms as $rn): ?>
            <td class="<?php echo empty($dd['morning'][$rn]) ? 'sp2-empty-cell' : ''; ?>">
                <?php if(!empty($dd['morning'][$rn])): ?>
                    <?php foreach($dd['morning'][$rn] as $ev): ?>
                    <div class="sp2-event">
                        <?php echo h($ev['event_name']); ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
        </tr>
    </tbody>
</table>
<?php endif; ?>

<div class="sp2-lunch"><?php echo h($smallProgramCustomization['lunch_label']); ?> &nbsp; <?php echo h($lunchStart); ?> &ndash; <?php echo h($lunchEnd); ?></div>

<?php if (!empty($dd['afternoon'])): ?>
<div class="sp2-session-header"><?php echo !empty($dd['afternoonRange']) ? h($dd['afternoonRange']) : 'Afternoon'; ?> &mdash; <?php echo h($smallProgramCustomization['afternoon_label']); ?></div>
<table>
    <thead>
        <tr><?php foreach($allRooms as $rn): ?><th><?php echo h($rn); ?></th><?php endforeach; ?></tr>
    </thead>
    <tbody>
        <tr>
            <?php foreach($allRooms as $rn): ?>
            <td class="<?php echo empty($dd['afternoon'][$rn]) ? 'sp2-empty-cell' : ''; ?>">
                <?php if(!empty($dd['afternoon'][$rn])): ?>
                    <?php foreach($dd['afternoon'][$rn] as $ev): ?>
                    <div class="sp2-event">
                        <?php echo h($ev['event_name']); ?>
                    </div>
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

<?php if(!empty($smallProgramCustomization['footer_note'])): ?>
<div class="sp2-wrap-custom-note" style="margin-top:14px;"><?php echo nl2br(h($smallProgramCustomization['footer_note'])); ?></div>
<?php endif; ?>
