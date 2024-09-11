<?php

include 'WebClientPrint.php';

use Neodynamic\SDK\Web\WebClientPrint;
use Neodynamic\SDK\Web\DefaultPrinter;
use Neodynamic\SDK\Web\InstalledPrinter;
use Neodynamic\SDK\Web\ClientPrintJob;

// Process request
// Generate ClientPrintJob? only if clientPrint param is in the query string
$urlParts = parse_url($_SERVER['REQUEST_URI']);

if (isset($urlParts['query'])) {
    $rawQuery = $urlParts['query'];
    parse_str($rawQuery, $qs);
    if (isset($qs[WebClientPrint::CLIENT_PRINT_JOB])) {

        $useDefaultPrinter = ($qs['useDefaultPrinter'] === 'checked');
        $printerName = urldecode($qs['printerName']);

        //Create ESC/POS commands for sample receipt
        $esc = '0x1B'; //ESC byte in hex notation
        $newLine = '0x0A'; //LF byte in hex notation
        
        $cmds = '';
        $cmds = $esc . "@"; //Initializes the printer (ESC @)
        $cmds .= $esc . '!' . '0x38'; //Emphasized + Double-height + Double-width mode selected (ESC ! (8 + 16 + 32)) 56 dec => 38 hex
        $cmds .= 'BEST DEAL STORES'; //text to print
        $cmds .= $newLine . $newLine;
        $cmds .= $esc . '!' . '0x00'; //Character font A selected (ESC ! 0)
        $cmds .= 'COOKIES                   5.00'; 
        $cmds .= $newLine;
        $cmds .= 'MILK 65 Fl oz             3.78';
        $cmds .= $newLine . $newLine;
        $cmds .= 'SUBTOTAL                  8.78';
        $cmds .= $newLine;
        $cmds .= 'TAX 5%                    0.44';
        $cmds .= $newLine;
        $cmds .= 'TOTAL                     9.22';
        $cmds .= $newLine;
        $cmds .= 'CASH TEND                10.00';
        $cmds .= $newLine;
        $cmds .= 'CASH DUE                  0.78';
        $cmds .= $newLine . $newLine;
        $cmds .= $esc . '!' . '0x18'; //Emphasized + Double-height mode selected (ESC ! (16 + 8)) 24 dec => 18 hex
        $cmds .= '# ITEMS SOLD 2';
        $cmds .= $esc . '!' . '0x00'; //Character font A selected (ESC ! 0)
        $cmds .= $newLine . $newLine;
        $cmds .= '11/03/13  19:53:17';
		
		$cmds = "0x1B@0x1Ba0x010x1B!0x38Kapal-Laut0x0A0x1B!0x08Your Essential Jewellery0x0A0x1B!0x01Jl. Danau Tamblingan 69  Sanur Bali Indonesia0x0A0x1B!0x000x0A0x1Ba0x01TEST ONLY - THIS IS NOT A VALID INVOICE0x0A0x0A0x1Ba0x00Invoice: SA-0004718-B              Order: 6320710x0AMonday 12 August 2024 15:48             SPG: 9990x0A0x0A0x0A1 QSAN61 Ring                            225,0000x0A0x0A0x0A0x1Ba0x020x1B!0x38Total: Rp. 225,0000x1B!0x000x0A0x0A0x1Ba0x020x1B!0x38# Items: 10x1B!0x000x0A0x0A0x1B!0x010x1Ba0x00This invoice is the only valid proof of purchase. Keep it. No refund. Exchange within 7 days with this original invoice, packaging and goods in perfect and unused conditions. We reserve the right to refuse any exchange. Warranty only valid with this original invoice (bank statement is not valid).For more information on our terms and conditions, promotions, shop locations, job opportunities, news and warranty terms and conditions check our online shop. PT. Sungai Mutiara Hitam Jl. Kesambi No 1 Kerobokan, Bali NPWP:50.789.872.4-906.0000x0A0x0A0x1B!0x380x1Ba0x01kapal-laut.com0x0A0x1B!0x000x1B!0x080x1Ba0x010x0AFollow us on0x0AFacebook: KapalLautBali0x0AInstagram: @KapalLautBali0x0A0x1B!0x000x0A0x1Ba0x01TEST ONLY - THIS IS NOT A VALID INVOICE0x0A0x0A0x1B!0x380x1Ba0x01CUSTOMER COPY0x0A0x0A0x0A0x0A0x1D0x560x410x000x1B@0x1Ba0x010x1B!0x38Kapal-Laut0x0A0x1B!0x08Your Essential Jewellery0x0A0x1B!0x01Jl. Danau Tamblingan 69  Sanur Bali Indonesia0x0A0x1B!0x000x0A0x1Ba0x01TEST ONLY - THIS IS NOT A VALID INVOICE0x0A0x0A0x1Ba0x00Invoice: SA-0004718-B              Order: 6320710x0AMonday 12 August 2024 15:48             SPG: 9990x0A0x0A0x0A1 QSAN61 Ring                            225,0000x0A0x0A0x0A0x1Ba0x020x1B!0x38Total: Rp. 225,0000x1B!0x000x0A0x0A0x1Ba0x020x1B!0x38# Items: 10x1B!0x000x0A0x1B!0x000x0APaid Cash: 225,0000x0A0x1B!0x080x1Ba0x000x0APackaging included0x1B!0x000x0A0x1B!0x000x0A0x1Ba0x01TEST ONLY - THIS IS NOT A VALID INVOICE0x0A0x0A0x1B!0x380x1Ba0x01SHOP COPY0x0A0x0A0x0A0x0A0x1D0x560x410x00";

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
