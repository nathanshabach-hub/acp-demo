<?php 
require_once(BASE_PATH . DS . 'vendor/tecnickcom' . DS . 'tcpdf' . DS . 'tcpdf.php');


// Create a new TCPDF instance
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Southern Cross Educational Enterprises Ltd.');
$pdf->SetTitle('Sample PDF using TCPDF');
$pdf->SetSubject('SCEE Certificate');
$pdf->SetKeywords('SCEE, Certificate');

$pdf->SetPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();



// to collect data
//Student Name, School Name, Convention Name & year

$headerImg 		= HTTP_PATH.'/webroot/img/front/certificates/'.$certificateTheme['header_image'];
$footerImg 		= HTTP_PATH.'/webroot/img/front/certificates/'.$certificateTheme['footer_image'];
$signatureImg 	= HTTP_PATH.'/webroot/img/front/certificates/signature.png';

// Set some content to display
//$html = '<h1>Hello, World!</h1><p>This is a sample PDF generated using TCPDF in PHP.</p>';
$html = '
			<!DOCTYPE html>
				<html lang="en" class="h-100">
					<head>
						<meta charset="utf-8">
						<meta http-equiv="X-UA-Compatible" content="IE=edge">
						<meta name="viewport" content="width=device-width, initial-scale=1">
						<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
						<!-- Bootstrap -->
					</head>
					<body style="padding: 0px; margin: 0px;font-family: Arial, sans-serif,\'Open Sans\'; font-weight: 500;">
						<div class="container" style="max-width: 600px;  margin: 0px auto; background-color: #fff; padding: 10px; ">
							<div class="maincontainer" style="max-width: 600px;background: #fff;margin: 0px auto; border: 1px solid #fbf0c2;">
								<div class="header" style="width: 100%;display: block;padding: 0px 0px 0px;">
									<img src="'.$headerImg.'" style="width: 100%;">
								</div>
								<div class="contentpart" style="width: 100%; text-align: center;">
									<div style="position: relative;top: -62px;right: -160px;font-size: 12px; font-weight: bold;">Online Student Convention 2023        </div>
									<p class="simpletextt" style="font-family: arial;font-size: 14px;    margin-top: 5px;">
										Accelerate Educational Ministries takes pleasure in presenting this award to
									</p>
									<div><i style="font-size: 25px;
										padding: 0px 0px 10px;
										display: block; font-weight: 500;">Jedaiah Waqa</i></div>
									<span style="font-size: 14px; display: block; width: 100%;">from</span>
									<div><i style="font-size: 20px;
										padding: 10px 0px 10px;
										display: block;">Jedaiah Waqa</i></div>
									<span style="font-size: 12px; width: 100%; display: block; padding-top: 20px;
										letter-spacing: 0.5px;">for memorising the book of</span>
									<div style="margin-bottom: -110px;" >
										<h5 style="font-size: 20px;
											font-style: italic;
											letter-spacing: 0.8px; width: 100%; display: block;    margin: 20px 0px 50px;
											font-weight: 500;">Proverbs</h5>
									</div>
									<div style="    width: 110px;
										position: relative;
										top: 68px;
										left: 98px;">
										<img src="'.$signatureImg.'" style="width: 60px;">
										<b style="position: absolute;
											top: 45px;
											left: 10px;
											font-size: 10px;">Slabbert Pretorius</b>
										<p style="    position: absolute;
											top: 57px;
											left: 3px;
											font-size: 9px;
											padding: 0px;
											margin: 0px;">MANAGING DIRECTOR</p>
										<span style="position: absolute;
											top: 67px;
											left: -30px;
											font-size: 8px;
											width: 170px;">Southern Cross Educational Enterprises Ltd.</span>
									</div>
									<div style="position: relative;
										width: 240px;
										left: 170px;
										font-size: 9px;
										font-weight: bold; font-style: italic;
										top: 39px;">
										A word fitly spoken is like apples of gold <br>
										in pictures of silver<br>
										Proverbs 25:11
									</div>
								</div>
								<div class="footer" style="width: 100%;display: inline-block;padding: 0px;box-sizing: border-box;">
									<img src="'.$footerImg.'" style="width: 100%;">
								</div>
							</div>
						</div>
					</body>
					<style>
					</style>
				</html>
';

// Print the content onto the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('sample.pdf', 'I'); // 'D' forces download, 'I' opens inline in the browser

// Exit the script
exit;

?>

Redirecting....