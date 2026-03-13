<?php
$headerImg 		= HTTP_PATH.'/webroot/img/front/certificates/'.$certificateTheme['header_image'];
$footerImg 		= HTTP_PATH.'/webroot/img/front/certificates/'.$certificateTheme['footer_image'];
$signatureImg 	= HTTP_PATH.'/webroot/img/front/certificates/signature.png';
$borderColor 	= $certificateTheme['border_color'];
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
		<title><?php echo $arrCertData['student_name']; ?></title>
		<!-- Bootstrap -->
		<link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
	</head>
	<body style="padding:0px margin:0px;margin-left:1%;margin-right:1%;font-family:Arial,sans-serif,'Open Sans'; font-weight: 500; width: 98%; border: 1px solid <?php echo $borderColor; ?>;">
		<div class="container" style="max-width:100%;margin:0px auto;background-color:#fff;padding:0px; border:1px solid <?php echo $borderColor; ?>;">
			<div class="maincontainer" style="max-width: 100%;background:#fff;margin:0px auto; border: 1px solid <?php echo $borderColor; ?>;">
				<div class="header" style="width:100%;display:block;padding: 0px 0px 0px;">
					<img src="<?php echo $headerImg; ?>" style="width: 100%;">
				</div>
				<div class="contentpart" style="width:100%;text-align:center;">
					<div style="position:relative;top:-100px;right:12%;font-size:14px;font-weight:bold;text-align:right;"><?php echo $arrCertData['convention_name']; ?></div>
					<p class="simpletextt" style="font-family:arial;font-size: 14px;margin-top: -20px;padding: 0px;">
						Accelerate Educational Ministries takes pleasure in presenting this award to
					</p>
					<div><i style="font-size: 30px;padding: 10px 0px 5px;display: block; font-weight:500;font-family:Pinyon Script,cursive;"><?php echo $arrCertData['student_name']; ?></i></div>
					<span style="font-size:14px; display:block;width:100%;">from</span>
					<div><i style="font-size: 24px;padding: 10px 0px 5px;display: block;font-family: Pinyon Script, cursive;"><?php echo $arrCertData['school_name']; ?></i></div>
					<span style="font-size:12px; width: 100%; display: block; padding-top: 10px;
						letter-spacing:0.5px;">for memorising the book of</span>
					<div style="margin-bottom:-110px;">
						<h5 style="font-size:30px;font-style:italic;letter-spacing:0.8px;width:100%;display:block;margin:10px 0px 30px;
							font-weight:500;font-family:Pinyon Script,cursive;"><?php echo $arrCertData['book_names']; ?></h5>
					</div>
					<div style="width: 110px;position:relative;top:120px;left:180px;">
						<img src="<?php echo $signatureImg; ?>" style="width: 60px;">
						<b style="position:absolute;top:43px;left:10px;font-size:10px;">Slabbert Pretorius</b>
						<p style="position:absolute;top:57px;left:3px;font-size:9px;padding:0px;margin:0px;">MANAGING DIRECTOR</p>
						<span style="position:absolute;top:69px;left:-30px;font-size:10px;width:200px;">Southern Cross Educational Enterprises Ltd.</span>
					</div>
					<div style="position:relative;width:240px;left:39%;font-size:12px;font-weight:bold;font-style:italic;top:40px;">
						A word fitly spoken is like apples of gold <br>
						in pictures of silver<br>
						<?php echo $arrCertData['book_names']; ?> 25:11
					</div>
				</div>
				<div class="footer" style="width:100%;display:inline-block;padding:0px;box-sizing:border-box;">
					<img src="<?php echo $footerImg; ?>" style="width:100%;">&nbsp;
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