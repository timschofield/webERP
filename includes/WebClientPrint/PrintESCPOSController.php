<?php
include 'WebClientPrint.php';

use Neodynamic\SDK\Web\WebClientPrint;
use Neodynamic\SDK\Web\DefaultPrinter;
use Neodynamic\SDK\Web\InstalledPrinter;
use Neodynamic\SDK\Web\ClientPrintJob;

// Process request
// Generate ClientPrintJob? only if clientPrint param is in the query string
$urlParts = parse_url($_SERVER['REQUEST_URI']);
// function getFileName($n) {
//     return isset($_COOKIE[$n]) ? $_COOKIE[$n] : null;
// }


if (isset($urlParts['query'])) {
    $rawQuery = $urlParts['query'];
    parse_str($rawQuery, $qs);
    if (isset($qs[WebClientPrint::CLIENT_PRINT_JOB])) {

        $useDefaultPrinter = ($qs['useDefaultPrinter'] === 'checked');
        $printerName = urldecode($qs['printerName']);

        $useDefaultPrinter = ($qs['useDefaultPrinter'] === '1');
        $printerName = urldecode($qs['printerName']);
        $identifier = isset($qs['identifier']) ? $qs['identifier'] : null;
        //Create ESC/POS commands for sample receipt
        $esc = '0x1B'; //ESC byte in hex notation
        $newLine = '0x0A'; //LF byte in hex notation

        // Check if the file exists to avoid errors
        if ($identifier) {
            $cmds = file_get_contents('wcpcache/'.$identifier.'.pos');
        } else {
            // Handle the error if the file does not exist
            die("Error: The file $identifier does not exist.");
        }

		//Create a ClientPrintJob obj that will be processed at the client side by the WCPP
		$cpj = new ClientPrintJob();
		//set ESCPOS commands to print...
		$cpj->printerCommands = $cmds;
        $cpj->formatHexValues = true;
		
		if ($useDefaultPrinter || $printerName === 'null') {
			$cpj->clientPrinter = new DefaultPrinter();
		} else {
			$cpj->clientPrinter = new InstalledPrinter($printerName);
		}

		//Send ClientPrintJob back to the client
		ob_start();
		ob_clean();
		header('Content-type: application/octet-stream');
		echo $cpj->sendToClient();
		ob_end_flush();
		exit();
        
    }
}
