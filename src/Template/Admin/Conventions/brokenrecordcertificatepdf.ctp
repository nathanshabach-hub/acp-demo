<?php
// certificate theme
$headerImg 		= HTTP_PATH.'/webroot/img/front/certificates/header_broken_record.png';
$footerImg 		= HTTP_PATH.'/webroot/img/front/certificates/footer_broken_record.png';
$signatureImg 	= HTTP_PATH.'/webroot/img/front/certificates/signature.png';
$borderColor 	= '#fff';
?>

<script type="text/javascript">
<!--
window.print();
//-->
</script>

<!DOCTYPE html>
<html lang="en" class="h-100">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
		<title>Broken Record Certificate :: <?php echo $arrCertData['convention_name']; ?> - <?php echo $arrCertData['season_year']; ?> For <?php echo $arrCertData['student_name']; ?></title>
		<!-- Bootstrap -->
		<link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
	</head>
	<body style="padding: 0px; margin:0px;margin-left:1%;margin-right: 1%;font-family:Arial,sans-serif,'Open Sans';font-weight:500;width: 98%; border: 0px solid #06402B;">
		<div class="container" style="max-width:100%;margin:0px auto;background-color: #fff; padding: 1px; border:0px solid #06402B;">
			<div class="maincontainer" style="max-width: 100%;background: #fff;margin:0px auto;border:1px solid #06402B;">
				<div class="header" style="width: 100%;display: block;padding: 0px 0px 0px;">
					<img src="<?php echo $headerImg; ?>" style="width: 100%;">
				</div>
				<div class="contentpart" style="width: 100%; text-align:center;">
					<p class="simpletextt" style="font-family: arial;font-size: 14px;    margin-top: -30px; padding: 0px;">
						Accelerate Educational Ministries 
					</p>
					<p class="simpletextt" style="font-family: arial;font-size: 14px;    margin-top: 0px; padding: 0px; margin-bottom: 0px;">
						in affiliation with 
					</p>
					<div><i style="font-size: 30px;
						padding: 5px 0px 5px;
						display: block; font-weight: 500; font-family: arial;">Accelerate Christian Education <span>®</span></i></div>
					<span style="font-size: 14px; display: block; width: 100%;">takes pleasure in presenting this Broken Record Award to</span>
					<div><i style="    font-size: 24px;
						padding: 5px 0px 5px;
						display: block; font-family: Pinyon Script, cursive;"><?php echo $arrCertData['student_name']; ?></i></div>
					<span style="font-size: 12px; width: 100%; display: block; padding-top: 5px;
						letter-spacing: 0.5px;"> of</span>
					<div style="margin-bottom: -20px;" >
						<h5 style="font-size: 20px;
							font-style: italic;
							letter-spacing: 0.8px; width: 100%; display: block;    margin: 10px 0px 10px;
							font-weight: 500; font-family: Pinyon Script, cursive;"><?php echo $arrCertData['school_name']; ?></h5>
					</div>
					<span style="font-size: 12px; width: 100%; display: block; padding-top: 10px;
						letter-spacing: 0.5px;"> For breaking the <?php echo $arrCertData['event_name']; ?> record at</span>
					<div style="margin-bottom: -110px;" >
						<h5 style="font-size: 30px;
							font-style: italic;
							letter-spacing: 0.8px; width: 100%;display: block;margin: 10px 0px 30px;
							font-weight: 500; font-family: Pinyon Script, cursive;"><?php echo $arrCertData['convention_name']; ?> <?php echo $arrCertData['season_year']; ?></h5>
					</div>
					<div style="    width: 110px;
						position: relative;
						top: 80px;
						left: 200px;">
						<img src="<?php echo $signatureImg; ?>" style="width: 60px;">
						<b style="position: absolute;
							top: 43px;
							left: 10px;
							font-size: 10px;">Slabbert Pretorius</b>
						<p style="    position: absolute;
							top: 57px;
							left: 3px;
							font-size: 9px;
							padding: 0px;
							margin: 0px;">MANAGING DIRECTOR</p>
						<span style="position: absolute;
							top: 69px;
							left: -30px;
							font-size: 10px;
							width: 200px;">Southern Cross Educational Enterprises Ltd.</span>
					</div>
					<div style="    position: relative;
						width: 280px;
						left: 120px;
						font-size: 12px;
						font-weight: bold; font-style: italic;
						top: 130px;">
						And whatsoever you do, do it heartily, as to the<br>
						Lord, and not unto men" Colossians 3:23
					</div>
				</div>
				<div class="footer" style="width: 100%;display: inline-block;padding: 10px;box-sizing: border-box;">
					<img src="<?php echo $footerImg; ?>" style="width: 100%;">&nbsp;
				</div>
			</div>
		</div>
	</body>
	<style>
		.pinyon-script-regular {
		font-family: "Pinyon Script", cursive;
		font-weight: 400;
		font-style: normal;
		}
		@page {
		size: A4 landscape;
		margin:0cm;
		}
	</style>
</html>