<?php

//################## PRINTING STUFF ##################### 
if($_SESSION['HTTPS_Only'] == 1){
	$Protocol = "https";
}else{
	$Protocol = "http";
}
$PrintScript = $Protocol . "://$_SERVER[HTTP_HOST]" . $RootPath . "/PrintPOSFile.php";
echo '<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>';
include 'includes/WebClientPrint/WebClientPrint.php';
use Neodynamic\SDK\Web\WebClientPrint;
//Specify the ABSOLUTE URL to the php file that will create the ClientPrintJob object
echo WebClientPrint::createScript($PrintScript);
//################## PRINTING STUFF #####################   

?>
