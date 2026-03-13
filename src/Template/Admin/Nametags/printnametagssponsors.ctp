<!DOCTYPE html>
<html>
<head>
    <title>Nametags - Sponsors</title>
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
        <!-- Example loop start -->
        <!-- Assume 'cards' is an array of objects with 'name' field -->

        <!-- Pseudocode-like structure below, adapt to actual server-side loop -->
        <!-- For example, in Blade: @foreach ($cards as $card) -->
        
        <!-- BEGIN LOOP -->
		<?php
        $counter = 0;
		foreach($nametags as $datarecord)
		{
            // Add top space on first card or after every page break
			if ($counter % 12 == 0) {
				echo '<div style="height: 10mm;"></div>'; // Adds visible space at the top of the page
			}
		?>
        <div class="col-xs-4">
            <div class="name-card">
                <!-- Replace this condition in real code -->
                <!-- if card has data -->
                <h4><?php echo $datarecord->Teachers['first_name'].' '.$datarecord->Teachers['last_name']; ?></h4>
                <p class="pb-2"><?php echo $datarecord->Users['first_name']; ?></p>
                <p><?php echo $convSeasD->Conventions['name']; ?><br><?php echo $convSeasD->season_year; ?></p>
				<?php echo $this->Html->image('front/scce_logo_tags.jpg',array("width"=>100)); ?>
            </div>
        </div>
		<?php
			$counter++;
			// Insert page break after every 12 cards
			if ($counter % 12 == 0) {
				echo '<div class="page-break spacer-after-break"></br></div>';
			}
		}
		?>
        <!-- END LOOP -->

        <!-- In real logic, fill blank cards if total is not multiple of 3 -->
    </div>
</div>
</body>
</html>
