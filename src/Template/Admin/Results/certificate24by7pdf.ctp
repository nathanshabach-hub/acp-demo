<?php
// certificate theme
$headerImg 		= HTTP_PATH.'/webroot/img/front/certificates/header_24by7_certificate_portrait.png';
$footerImg 		= HTTP_PATH.'/webroot/img/front/certificates/footer_24by7_certificate_portrait.png';
//$signatureImg 	= HTTP_PATH.'/webroot/img/front/certificates/signature.png';
$borderColor 	= '#5d0b0d';
?>
<!DOCTYPE html>
<html lang="en" class="h-100">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
		<title>Certificate 24/7 - <?php echo $arrCertData['student_name']; ?></title>
		<!-- Bootstrap -->
		<link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
	</head>
	<body style="padding: 0px;font-family: Arial, sans-serif, 'Open Sans'; font-weight: 500; width: 98%; outline: 1px solid <?php echo $borderColor; ?>;box-sizing: border-box;">
		<div class="container" style="max-width:100%;margin: 0px auto; background-color: #fff; padding: 0px; ">
			<div class="maincontainer" style="max-width: 100%;background: #fff;margin: 0px auto; ">
				<div class="header" style="width: 100%;display: block;padding: 0px 0px 0px;">
					<img src="<?php echo $headerImg; ?>" style="width: 100%;">
				</div>
				<div class="contentpart" style="width: 100%; text-align: center;">
					<p class="simpletextt" style="font-family: arial;     font-size:25px;    margin-top: 0px; padding: 0px;">
						Accelerate Educational Ministries 
					</p>
					<i class="simpletextt" style="font-family: arial;font-size: 14px;    margin-top: 0px; padding:0px; margin-bottom:0px;">
					in affiliation with </i>
					<div><i style="font-size: 30px;padding: 20px 0px 5px;display: block; font-weight: 500; font-family: arial;">Accelerate Christian Education <span style="font-size: 20px;position: relative;top: -10px;right: 5px;">®</span></i></div>
					<span style="padding-top:20px; font-size: 14px; display: block; width: 100%; font-style: italic;">takes pleasure in presenting this Award to</span>
					
					<!--<div style="height: 425px;">ddddd</div>-->
					
					<div><i style="font-size: 34px;padding: 20px 0px 5px;display: block;font-family: Pinyon Script, cursive;height:25px;"><?php echo $arrCertData['student_name']; ?></i></div>
					
					<div><i style="font-size:20px;padding: 30px 0px 5px;display: block;font-family: Pinyon Script, cursive;height: 315px;"><?php echo $arrCertData['school_name']; ?></i></div>
					
					
					
					<div style="font-size: 20px; padding-bottom: 20px;font-style: italic;"><b>1</b> <span><b>L</b>ife</span> <b>24</b> hours a day <b>7</b> days a week <b>4</b> <span><b>C</b>hrist</span></div>
					<div style="width: 100%; display: block;">
						<div style="width:25%; float:left;font-style: italic;">
							<span>Commitment</span>
							<p style="margin: 0px;">jeremiah 24:7</p>
						</div>
						<div style="width:25%; float: left;font-style: italic;">
							<span>Worship</span>
							<p style="margin: 0px;">Psalm 24:7</p>
						</div>
						<div style="width:25%; float: left;font-style: italic;">
							<span>Obedience</span>
							<p style="margin:0px;">Exodus 24:7</p>
						</div>
						<div style="width:25%;float:left;font-style: italic;">
							<span>Wisdom</span>
							<p style="margin: 0px;">Proverbs 24:7</p>
						</div>
					</div>
				</div>
				<div class="footer" style="width: 100%;display: inline-block;padding: 0px;box-sizing: border-box;">
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
		size: A4 portrait;
		}
	</style>
</html>
<script type="text/javascript">
<!--
window.print();
//-->
</script>