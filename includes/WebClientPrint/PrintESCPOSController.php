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

        // $cmds = "x1B@0x1Ba0x010x1B!0x38Blink by Kapal-Laut0x0A0x1B!0x01     0x0A0x1B!0x38SPG END OF SHIFT REPORT0x0A0x1B!0x000x0A0x1Ba0x01Sunday 8 September 2024 15:070x0ASPG Code: 2310x0AShop Code: K20x0A0x0A0x0AK2-0103757-B             Returned Goods: 675,0000x0AK2-0230777-A                Credit Card: 490,0000x0AK2-0230779-A                Credit Card: 885,0000x0AK2-0056745-C                       Cash: 195,0000x0AK2-0103762-B                       Cash: 750,0000x0AK2-0230794-A              Credit Card: 1,120,0000x0AK2-0230795-A                Credit Card: 225,0000x0AK2-0230796-A                Credit Card: 520,0000x0AK2-0230797-A              Credit Card: 1,300,0000x0AK2-0056747-C                       Cash: 195,0000x0AK2-0230798-A                Credit Card: 425,0000x0AK2-0230799-A              Credit Card: 1,120,0000x0AK2-0230802-A              Credit Card: 1,475,0000x0AK2-0230819-A                Credit Card: 325,0000x0A0x0A# Invoices: 14             Total Cash: 1,140,0000x0A0x1Ba0x02Total Credit Card: 7,885,0000x0ATotal Returned Goods: 675,0000x0ATotal Voucher/Discounts: 00x0A0x1B!0x38Total include returns/vouchers: 9,700,0000x0ATotal Personal Sales SPG: 9,025,0000x0A0x1B!0x000x0A0x1Ba0x000x0A0x0A0x0A0x1D0x560x410x00";

        // $identifier = getFileName("impact");

        // Check if the file exists to avoid errors
        if ($identifier) {
            
            $cmds = file_get_contents('wcpcache/'.$identifier.'.pos');
        } else {
            $cmds .= "x1B@0x1Ba0x010x1B!0x38Blink by Kapal-Laut0x0A0x1B!0x01";
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
