<!DOCTYPE html>
<html>
<head>
    <title>Nametags - Conference<?php echo isset($conferenceYear) && $conferenceYear ? ' ' . h($conferenceYear->year) : ''; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
    <style>
        .name-card {
            border: 1px solid #ccc;
            text-align: center;
            padding: 10px;
            margin-bottom:60px; position: relative; 
        }
        .name-card h4 {
            font-weight: bold;
            color: #3d3dc2;
            margin-top: 5px;    padding-bottom: 15px;
        }
        .name-card p {padding-bottom: 10px;
            margin: 0;
            color: #1b1464;    font-size: 14px;
            font-style: italic;
        }
        .name-card .signature {
            margin-top: 10px;
            height: 30px;
        }
        .pb-2{padding-bottom:15px;}
        .name-card img {
            position: absolute;
			width: 30px;
			bottom: 10px;
			right: 5px;
		}
		.col-xs-4{padding:0px 5px;}
		.container {
				width: 95%; margin-top:20px;
		}
		
		@media print {
            .page-break {
			page-break-after: always;
			break-after: page;
		}

 
		}
</style>
</head>
<body onload="window.print()">
<div class="container">
    <div class="row">
		<?php
        $counter = 0;
		foreach($nametags as $reg)
		{
            if ($counter % 12 == 0) {
				echo '<div style="height: 10mm;"></div>';
			}

            $supervisorName = isset($reg->Supervisors) ? trim($reg->Supervisors->first_name . ' ' . $reg->Supervisors->last_name) : 'N/A';
            $schoolName = isset($reg->Schools) ? trim($reg->Schools->first_name . ' ' . $reg->Schools->last_name) : 'N/A';
            $yearLabel = isset($conferenceYear) && $conferenceYear ? $conferenceYear->year : '';
		?>
        <div class="col-xs-4">
            <div class="name-card">
                <h4><?php echo h($supervisorName); ?></h4>
                <p class="pb-2"><?php echo h($schoolName); ?></p>
                <p>Conference<?php echo $yearLabel ? '<br>' . h($yearLabel) : ''; ?></p>
				<?php echo $this->Html->image('front/scce_logo_tags.jpg', array("width" => 100)); ?>
            </div>
        </div>
		<?php
			$counter++;
			if ($counter % 12 == 0) {
				echo '<div class="page-break spacer-after-break"></br></div>';
			}
		}
		?>
    </div>
</div>
</body>
</html>
