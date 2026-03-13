<?php
namespace App\View\Helper;

use Cake\View\Helper;
//require_once(BASE_PATH . DS . 'vendor' . DS . 'PHPExcel/PHPExcel.php');
class PdfHelper  extends Helper                                  //2
{
    var $core;
 
    function PdfHelper() {
        $this->core = new TCPDF();                                  //3
    }
     
}
?>