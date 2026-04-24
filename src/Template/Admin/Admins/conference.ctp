<div class="content-wrapper">
    <section class="content-header">
        <h1>Conference Registrations</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo HTTP_PATH;?>/admin/admins/dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Conference</li>
        </ol>
    </section>

    <section class="content">

        <!-- Create Conference Year -->
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Create Conference Year</h3>
                    </div>
                    <div class="box-body">
                        <form method="post" action="/admin/admins/conference" class="form-inline">
                            <div class="form-group">
                                <label for="year" class="sr-only">Year</label>
                                <input type="text" name="year" id="year" class="form-control" placeholder="e.g. 2025" maxlength="10" required>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-left:10px;">
                                <i class="fa fa-plus"></i> Create Year
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conference Years Accordion -->
        <div class="row">
            <div class="col-xs-12">
                <?php if (empty($yearData)): ?>
                    <div class="box">
                        <div class="box-body text-center">
                            <p>No conference years created yet. Create one above to get started.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="box-group" id="conference-accordion">
                        <?php foreach ($yearData as $idx => $yd): ?>
                            <?php $cy = $yd['year']; $regs = $yd['registrations']; $count = $yd['count']; ?>
                            <div class="panel box box-primary">
                                <div class="box-header with-border accordion-toggle" style="cursor:pointer;" data-target="#collapse-year-<?php echo $cy->id; ?>">
                                    <h4 class="box-title">
                                        <i class="fa fa-calendar"></i>&nbsp;
                                        Conference <?php echo h($cy->year); ?>
                                        <span class="badge bg-blue" style="margin-left:10px;"><?php echo $count; ?> registration<?php echo $count != 1 ? 's' : ''; ?></span>
                                        <i class="fa fa-chevron-down pull-right accordion-chevron"></i>
                                    </h4>
                                </div>
                                <div id="collapse-year-<?php echo $cy->id; ?>" class="accordion-body" style="<?php echo $idx === 0 ? '' : 'display:none;'; ?>">
                                    <div class="box-body">
                                        <a href="/admin/admins/printconferencetags/<?php echo $cy->id; ?>" target="_blank" class="btn btn-default btn-print-tags">
                                            <i class="fa fa-print"></i> Print Name Tags
                                        </a>
                                        <button type="button" class="btn btn-danger" onclick="if(confirm('Are you sure you want to delete Conference <?php echo h($cy->year); ?> and all its registrations?')) { window.location.href='/admin/admins/conference?delete_year=<?php echo $cy->id; ?>'; }">
                                            <i class="fa fa-trash"></i> Delete Year
                                        </button>
                                    </div>
                                    <div class="box-body table-responsive no-padding">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>School</th>
                                                    <th>Supervisor</th>
                                                    <th>Date Registered</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php if ($count > 0): ?>
                                                <?php $i = 1; foreach ($regs as $reg): ?>
                                                <tr>
                                                    <td><?php echo $i++; ?></td>
                                                    <td><?php echo isset($reg->Schools) ? h(trim($reg->Schools->first_name . ' ' . $reg->Schools->last_name)) : 'N/A'; ?></td>
                                                    <td><?php echo isset($reg->Supervisors) ? h(trim($reg->Supervisors->first_name . ' ' . $reg->Supervisors->last_name)) : 'N/A'; ?></td>
                                                    <td><?php echo $reg->created ? $reg->created->format('d M Y, h:i A') : ''; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="4" class="text-center">No registrations for this year.</td>
                                                </tr>
                                            <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </section>
</div>

<style>
.accordion-chevron {
    transition: transform 0.3s ease;
}
.accordion-toggle.collapsed .accordion-chevron {
    transform: rotate(-90deg);
}
</style>

<script>
$(document).ready(function() {
    // Set initial chevron state
    $('.accordion-toggle').each(function() {
        var target = $($(this).data('target'));
        if (!target.is(':visible')) {
            $(this).addClass('collapsed');
        }
    });

    $('.accordion-toggle').on('click', function() {
        var target = $($(this).data('target'));
        var allBodies = $('#conference-accordion .accordion-body');
        var allToggles = $('#conference-accordion .accordion-toggle');

        if (target.is(':visible')) {
            target.slideUp(200);
            $(this).addClass('collapsed');
        } else {
            allBodies.slideUp(200);
            allToggles.addClass('collapsed');
            target.slideDown(200);
            $(this).removeClass('collapsed');
        }
    });
});

</script>
