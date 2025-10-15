<?php
require(__DIR__ . '/includes/session.php');

$Title = __('TEST ESCPOS FILE');
include('includes/header.php');

include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLPOSGeneral.php');
include('includes/KLESCPOSCommands.php');
include('includes/WebClientPrint/WebClientPrint.php');
use Neodynamic\SDK\Web\WebClientPrint;

$TextToPrint = "x1B@0x1Ba0x010x1B!0x38Blink Fashion Jewellery0x0A0x1B!0x01     0x0A0x1B!0x38SPG END OF SHIFT REPORT0x0A0x1B!0x000x0A0x1Ba0x01Sunday 8 September 2024 15:070x0ASPG Code: 2310x0AShop Code: K20x0A0x0A0x0AK2-0103757-B             Returned Goods: 675,0000x0AK2-0230777-A                Credit Card: 490,0000x0AK2-0230779-A                Credit Card: 885,0000x0AK2-0056745-C                       Cash: 195,0000x0AK2-0103762-B                       Cash: 750,0000x0AK2-0230794-A              Credit Card: 1,120,0000x0AK2-0230795-A                Credit Card: 225,0000x0AK2-0230796-A                Credit Card: 520,0000x0AK2-0230797-A              Credit Card: 1,300,0000x0AK2-0056747-C                       Cash: 195,0000x0AK2-0230798-A                Credit Card: 425,0000x0AK2-0230799-A              Credit Card: 1,120,0000x0AK2-0230802-A              Credit Card: 1,475,0000x0AK2-0230819-A                Credit Card: 325,0000x0A0x0A# Invoices: 14             Total Cash: 1,140,0000x0A0x1Ba0x02Total Credit Card: 7,885,0000x0ATotal Returned Goods: 675,0000x0ATotal Voucher/Discounts: 00x0A0x1B!0x38Total include returns/vouchers: 9,700,0000x0ATotal Personal Sales SPG: 9,025,0000x0A0x1B!0x000x0A0x1Ba0x000x0A0x0A0x0A0x1D0x560x410x00";

$identifier=GetPOSIdentifier();
$FileName = GetFilenameFromPOSIdentifier($identifier);  
file_put_contents($FileName, $TextToPrint);
$TextActionToPrint = 'Print the Daily SPG End Of Shift Test';

include('includes/KLSilentPrinting.php');


include('includes/footer.php');
