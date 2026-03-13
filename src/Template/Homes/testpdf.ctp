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

// Add a page
$pdf->AddPage();

// Set some content to display
$html = '<h1>Hello, World!</h1><p>This is a sample PDF generated using TCPDF in PHP.</p>';

// Print the content onto the PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('sample.pdf', 'I'); // 'D' forces download, 'I' opens inline in the browser

// Exit the script
exit;

?>

Redirecting....