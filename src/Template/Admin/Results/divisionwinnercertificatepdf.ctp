<?php
use Cake\ORM\TableRegistry;
$this->Conventionregistrationstudents = TableRegistry::get('Conventionregistrationstudents');
$this->Eventsubmissions = TableRegistry::get('Eventsubmissions');
$this->Books = TableRegistry::get('Books');

// certificate theme
$headerImg 		= HTTP_PATH.'/webroot/img/front/certificates/header_division_certificate_portrait.png';
$footerImg 		= HTTP_PATH.'/webroot/img/front/certificates/footer_division_certificate_portrait.png';
//$signatureImg 	= HTTP_PATH.'/webroot/img/front/certificates/signature.png';
$borderColor 	= '#1a98d5';
?>

<!DOCTYPE html>
<html lang="en" class="h-100">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<link rel="shortcut icon" type="image/x-icon" href="images/favicon.png">
		<title>Division Winner - <?php echo $arrCertData['student_name']; ?></title>
		<!-- Bootstrap -->
		<link href="https://fonts.googleapis.com/css2?family=Pinyon+Script&display=swap" rel="stylesheet">
	</head>
	<body style="padding: 0px;font-family: Arial, sans-serif, 'Open Sans'; font-weight: 500; width: 98%; outline: 1px solid <?php echo $borderColor; ?>;box-sizing: border-box;">
		<div class="container" style="max-width: 100%;  margin: 0px auto; background-color: #fff; padding: 0px; ">
			<div class="maincontainer" style="max-width: 100%;background: #fff;margin: 0px auto; ">
				<div class="header" style="width: 100%;display: block;padding: 0px 0px 0px;">
					<img src="<?php echo $headerImg; ?>" style="width: 100%;">
				</div>
				<div class="contentpart" style="width:100%;text-align:center;">
					<p class="simpletextt" style="font-family: arial;font-size:25px;margin-top: 0px; padding: 0px;">
						Accelerate Educational Ministries 
					</p>
					<i class="simpletextt" style="font-family: arial;font-size: 14px;    margin-top: 0px; padding:0px; margin-bottom:0px;">
					in affiliation with </i>
					<div><span style="font-size:30px;padding:20px 0px 5px;display: block; font-weight: 500; font-family: arial; ">Accelerated Christian Education <span style="font-size: 20px;position: relative;top:-10px;right:5px;">®</span></span></div>
					<span style="padding-top:20px;font-size:14px;display:block;width:100%; font-style: italic;">takes pleasure in presenting this Divisional Certificate to</span>
					
					<div><i style="font-size:34px;padding:20px 0px 5px;display:block;font-family: Pinyon Script, cursive;"><?php echo $arrCertData['student_name']; ?></i></div>
					
					<div><i style="font-size:20px;padding:20px 0px 5px;display:block;font-family: Pinyon Script, cursive;"><?php echo $arrCertData['school_name']; ?></i></div>
					
					<div style="padding:20px 0px;font-style:italic;">For being the overall point-scorer in the </div>
					
					<div><i style="font-size:44px;padding:1px 0px 5px;display:block;font-family: Pinyon Script, cursive;"><?php echo $arrCertData['division_name']; ?></i></div>
					
					<div><i style="font-size:15px;padding:208px 0px 5px;display:block;font-family: Pinyon Script, cursive;">&nbsp;</i></div>
					
					<div style="width: 100%;
						display: block;
						font-size: 20px;
						position: relative;
						bottom: -100px;
						left: -200px;
						font-style: italic;">
						<span>"And whatsover you do, do it <br> heartily, as to the lord, and not unto <br> men" Colossians 3:23</span>
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