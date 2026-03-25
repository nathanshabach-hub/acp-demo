<?php echo $this->Html->script('highcharts/highcharts.js'); ?>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/offline-exporting.js"></script>

<div class="content-wrapper">
    <section class="content-header">
        <h1><?php echo isset($chartTitle) ? h($chartTitle) : 'Dashboard Chart'; ?></h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><?php echo $this->Html->link('Dashboard', ['controller' => 'Admins', 'action' => 'dashboard']); ?></li>
            <li class="active">Chart View</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo isset($chartTitle) ? h($chartTitle) : 'Chart'; ?></h3>
                        <div class="box-tools pull-right">
                            <?php echo $this->Html->link('Back to Dashboard', ['controller' => 'Admins', 'action' => 'dashboard'], ['class' => 'btn btn-default btn-sm']); ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (!empty($emptyMessage)): ?>
                            <div class="alert alert-info"><?php echo h($emptyMessage); ?></div>
                        <?php endif; ?>

                        <div style="margin-bottom: 12px;">
                            <button type="button" id="download-png" class="btn btn-success btn-sm">Download PNG</button>
                            <button type="button" id="download-jpeg" class="btn btn-primary btn-sm">Download JPEG</button>
                            <button type="button" id="download-svg" class="btn btn-warning btn-sm">Download SVG</button>
                            <button type="button" id="download-pdf" class="btn btn-danger btn-sm">Download PDF</button>
                            <button type="button" id="download-csv" class="btn btn-default btn-sm">Download CSV</button>
                        </div>

                        <div id="chart-fullscreen" style="height: 70vh; min-height: 540px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function () {
    if (typeof Highcharts === 'undefined') {
        $('#chart-fullscreen').html('<p style="color:#999;padding:20px;">Chart library failed to load. Please refresh the page.</p>');
        return;
    }

    var chartOptions = <?php echo isset($chartOptionsJson) ? $chartOptionsJson : '{}'; ?>;
    if (!chartOptions || typeof chartOptions !== 'object') {
        chartOptions = {};
    }

    chartOptions.exporting = {
        enabled: true,
        fallbackToExportServer: false
    };

    var chart = Highcharts.chart('chart-fullscreen', chartOptions);

    function exportChart(type) {
        var exportOptions = {
            type: type,
            filename: '<?php echo !empty($chartKey) ? h($chartKey) : 'dashboard-chart'; ?>'
        };

        if (chart && typeof chart.exportChartLocal === 'function') {
            chart.exportChartLocal(exportOptions);
            return;
        }

        if (chart && typeof chart.exportChart === 'function') {
            chart.exportChart(exportOptions);
        }
    }

    $('#download-png').on('click', function () { exportChart('image/png'); });
    $('#download-jpeg').on('click', function () { exportChart('image/jpeg'); });
    $('#download-svg').on('click', function () { exportChart('image/svg+xml'); });
    $('#download-pdf').on('click', function () { exportChart('application/pdf'); });
    $('#download-csv').on('click', function () {
        if (chart && typeof chart.downloadCSV === 'function') {
            chart.downloadCSV();
        }
    });
});
</script>
