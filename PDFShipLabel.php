<?php

/* Prints a ship label using DomPDF */

require(__DIR__ . '/includes/session.php');
require_once __DIR__ . '/vendor/autoload.php'; // Make sure DomPDF is installed via composer

use Dompdf\Dompdf;
use Dompdf\Options;

if (isset($_GET['ORD'])) {
	$SelectedORD = $_GET['ORD'];
} elseif (isset($_POST['ORD'])) {
	$SelectedORD = $_POST['ORD'];
} else {
	unset($SelectedORD);
}

if (isset($_GET['StockID'])) {
	$StockId = $_GET['StockID'];
} elseif (isset($_POST['StockID'])) {
	$StockId = $_POST['StockID'];
} else {
	unset($StockId);
}

if (isset($_GET['LabelItem'])) {
	$LabelItem = $_GET['LabelItem'];
} elseif (isset($_POST['LabelItem'])) {
	$LabelItem = $_POST['LabelItem'];
} else {
	unset($LabelItem);
}

if (isset($_GET['LabelDesc'])) {
	$LabelDesc = $_GET['LabelDesc'];
} elseif (isset($_POST['LabelDesc'])) {
	$LabelDesc = $_POST['LabelDesc'];
} else {
	unset($LabelDesc);
}

if (isset($_GET['LabelLot'])) {
	$LabelLot = $_GET['LabelLot'];
} elseif (isset($_POST['LabelLot'])) {
	$LabelLot = $_POST['LabelLot'];
} else {
	unset($LabelLot);
}

if (isset($_GET['NoOfBoxes'])) {
	$NoOfBoxes = $_GET['NoOfBoxes'];
} elseif (isset($_POST['NoOfBoxes'])) {
	$NoOfBoxes = $_POST['NoOfBoxes'];
} else {
	unset($NoOfBoxes);
}

if (isset($_GET['LabelsPerBox'])) {
	$LabelsPerBox = $_GET['LabelsPerBox'];
} elseif (isset($_POST['LabelsPerBox'])) {
	$LabelsPerBox = $_POST['LabelsPerBox'];
} else {
	unset($LabelsPerBox);
}

if (isset($_GET['QtyPerBox'])) {
	$QtyPerBox = $_GET['QtyPerBox'];
} elseif (isset($_POST['QtyPerBox'])) {
	$QtyPerBox = $_POST['QtyPerBox'];
} else {
	unset($QtyPerBox);
}

if (isset($_GET['LeftOverQty'])) {
	$LeftOverQty = $_GET['LeftOverQty'];
} elseif (isset($_POST['LeftOverQty'])) {
	$LeftOverQty = $_POST['LeftOverQty'];
} else {
	unset($LeftOverQty);
}

if (isset($_GET['Type'])) {
	$Type = $_GET['Type'];
} elseif (isset($_POST['Type'])) {
	$Type = $_POST['Type'];
} else {
	unset($Type);
}

/* If we are previewing the order then we dont want to email it */
if ($SelectedORD == 'Preview') {
	$_POST['PrintOrEmail'] = 'Print';
	$MakePDFThenDisplayIt = true;
}

if (isset($_POST['PrintOrEmail']) and $_POST['PrintOrEmail'] == 'Print') {
	$MakePDFThenDisplayIt = true;
	$MakePDFThenEmailIt = false;
} elseif (isset($_POST['PrintOrEmail']) and $_POST['PrintOrEmail'] == 'Email' and isset($_POST['EmailTo'])) {
	$MakePDFThenEmailIt = true;
	$MakePDFThenDisplayIt = false;
} else {
	$MakePDFThenEmailIt = false;
	$MakePDFThenDisplayIt = true;
}

$FormDesign = simplexml_load_file($PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/ShippingLabel.xml');

// Set up DomPDF options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Enable for images/logos
$dompdf = new Dompdf($options);

$HTMLLabels = '';
$HTMLLabels .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';

if ($SelectedORD == 'Preview') {
	$NoOfLabels = 1;
	$MyArray[1]['deliverto'] = str_pad('', 30, 'x');
	$MyArray[1]['deladd1'] = str_pad('', 30, 'x');
	$MyArray[1]['deladd2'] = str_pad('', 50, 'x');
	$MyArray[1]['deladd3'] = str_pad('', 50, 'x');
	$MyArray[1]['deladd4'] = str_pad('', 50, 'x');
	$MyArray[1]['deladd5'] = str_pad('', 50, 'x');
	$MyArray[1]['deladd6'] = str_pad('', 50, 'x');
	$MyArray[1]['contactphone'] = '+1 987-654-3210';
	$MyArray[1]['customerref'] = str_pad('', 30, 'x');
	$MyArray[1]['stockid'] = str_pad('', 30, 'x');
	$MyArray[1]['custitem'] = str_pad('', 30, 'x');
} else {
	if ($Type == "Sales") {
		$OrderHeaderSQL = "SELECT debtorsmaster.name as deliverto,
								salesorderdetails.quantity as shipqty,
								salesorderdetails.stkcode as stockid,
								salesorders.branchcode,
								salesorders.customerref,
								salesorders.deliverto,
								salesorders.deladd1,
								salesorders.deladd2,
								salesorders.deladd3,
								salesorders.deladd4,
								salesorders.deladd5,
								salesorders.deladd6,
								salesorders.contactphone,
								stockmaster.decimalplaces,
								custitem.cust_part,
								custitem.cust_description
						FROM salesorderdetails
						INNER JOIN stockmaster
							ON stockmaster.stockid = stkcode
						INNER JOIN salesorders
							ON salesorders.orderno=salesorderdetails.orderno
						INNER JOIN debtorsmaster
							ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN currencies
							ON debtorsmaster.currcode=currencies.currabrev
						LEFT OUTER JOIN custitem
							ON custitem.debtorno=debtorsmaster.debtorno
							AND custitem.stockid=salesorderdetails.stkcode
						WHERE salesorders.orderno = '" . $SelectedORD . "'";
	} else {
		$OrderHeaderSQL = "SELECT loctransfers.reference as customerref,
								loctransfers.stockid,
								stockmaster.description,
								loctransfers.shipqty,
								loctransfers.shiploc,
								locations.locationname as shiplocname,
								loctransfers.recloc,
								locationsrec.contact as deliverto,
								locationsrec.deladd1,
								locationsrec.deladd2,
								locationsrec.deladd3,
								locationsrec.deladd4,
								locationsrec.deladd5,
								locationsrec.deladd6,
								locationsrec.tel as contactphone,
								stockmaster.decimalplaces
							FROM loctransfers
							INNER JOIN stockmaster
								ON loctransfers.stockid=stockmaster.stockid
							INNER JOIN locations
								ON loctransfers.shiploc=locations.loccode
							INNER JOIN locations AS locationsrec
								ON loctransfers.recloc = locationsrec.loccode
							WHERE loctransfers.reference='" . $SelectedORD . "'";
	}

	$ErrMsg = __('The order cannot be retrieved because');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg);

	if (DB_num_rows($GetOrdHdrResult) > 0) {
		$BoxNumber = 1;
		$LabelsPerBox = 1;
		$NoOfLabels = 1;
		while ($MyRow = DB_fetch_array($GetOrdHdrResult)) {
			$i = 1;
			$QtyPerBox = 0;
			$SQL = "SELECT value
					FROM stockitemproperties
					INNER JOIN stockcatproperties
						ON stockcatproperties.stkcatpropid=stockitemproperties.stkcatpropid
					WHERE stockid='" . $MyRow['stockid'] . "'
						AND label='PackQty'";
			$Result = DB_query($SQL, $ErrMsg);
			if (DB_num_rows($Result) > 0) {
				$PackQtyArray = DB_fetch_array($Result);
				$QtyPerBox = $PackQtyArray['value'];
				if ($QtyPerBox == 0) {
					$QtyPerBox = 1;
				}
			} else {
				$QtyPerBox = 1;
			}
			$NoOfBoxes = (int) ($MyRow['shipqty'] / $QtyPerBox);
			$LeftOverQty = $MyRow['shipqty'] % $QtyPerBox;
			$NoOfLabelsLine = $LabelsPerBox * $NoOfBoxes;
			$QtyPerBox = locale_number_format($QtyPerBox, $MyRow['decimalplaces']);
			$LeftOverQty = locale_number_format($LeftOverQty, $MyRow['decimalplaces']);

			while ($i <= $NoOfLabelsLine) {
				$MyArray[$NoOfLabels]['deliverto'] = $MyRow['deliverto'];
				$MyArray[$NoOfLabels]['deladd1'] = $MyRow['deladd1'];
				$MyArray[$NoOfLabels]['deladd2'] = $MyRow['deladd2'];
				$MyArray[$NoOfLabels]['deladd3'] = $MyRow['deladd3'];
				$MyArray[$NoOfLabels]['deladd4'] = $MyRow['deladd4'];
				$MyArray[$NoOfLabels]['deladd5'] = $MyRow['deladd5'];
				$MyArray[$NoOfLabels]['deladd6'] = $MyRow['deladd6'];
				$MyArray[$NoOfLabels]['customerref'] = $MyRow['customerref'];
				$MyArray[$NoOfLabels]['stockid'] = $MyRow['stockid'];
				$MyArray[$NoOfLabels]['custitem'] = $MyRow['cust_part'] . ' ' . $MyRow['cust_description'];
				++$i;
				++$NoOfLabels;
			}
			if ($LeftOverQty > 0) {
				$j = 1;
				while ($j <= $LabelsPerBox) {
					$MyArray[$NoOfLabels]['deliverto'] = $MyRow['deliverto'];
					$MyArray[$NoOfLabels]['deladd1'] = $MyRow['deladd1'];
					$MyArray[$NoOfLabels]['deladd2'] = $MyRow['deladd2'];
					$MyArray[$NoOfLabels]['deladd3'] = $MyRow['deladd3'];
					$MyArray[$NoOfLabels]['deladd4'] = $MyRow['deladd4'];
					$MyArray[$NoOfLabels]['deladd5'] = $MyRow['deladd5'];
					$MyArray[$NoOfLabels]['deladd6'] = $MyRow['deladd6'];
					$MyArray[$NoOfLabels]['customerref'] = $MyRow['customerref'];
					$MyArray[$NoOfLabels]['stockid'] = $MyRow['stockid'];
					$MyArray[$NoOfLabels]['custitem'] = $MyRow['cust_part'] . ' ' . $MyRow['cust_description'];
					++$i;
					++$j;
				}
			}
		}
	}
}

// Compose HTML for each label
if (isset($NoOfLabels) && $NoOfLabels > 0) {
	for ($i = 1; $i < $NoOfLabels; $i++) {
		$companyAddress = $_SESSION['CompanyRecord']['regoffice1'];
		$companyAddress2 = $_SESSION['CompanyRecord']['regoffice2'] ?? '';
		$companyAddress3 = $_SESSION['CompanyRecord']['regoffice3'] ?? '';
		$companyAddress4 = $_SESSION['CompanyRecord']['regoffice4'] ?? '';
		$companyAddress5 = $_SESSION['CompanyRecord']['regoffice5'] ?? '';
		$companyAddress6 = $_SESSION['CompanyRecord']['regoffice6'] ?? '';

		$HTMLLabels .= '
		<div style="page-break-after: always; border: 1px solid #333; padding: 12px; font-family: Arial, sans-serif;">
			<table style="width:100%; border:none;">
				<tr>
					<td style="width:40%; vertical-align:top;">';
		$HTMLLabels .= '<img class="logo" src="' . $_SESSION['LogoFile'] . '" /><br />';

		$HTMLLabels .= '<div style="font-weight:bold; margin-top:8px;">' . __('Ship From') . ':</div>
						<div>' . htmlentities($_SESSION['CompanyRecord']['coyname']) . '</div>
						<div>' . htmlentities($companyAddress) . '</div>
						' . ($companyAddress2 ? '<div>' . htmlentities($companyAddress2) . '</div>' : '') . '
						' . ($companyAddress3 ? '<div>' . htmlentities($companyAddress3) . '</div>' : '') . '
						' . ($companyAddress4 ? '<div>' . htmlentities($companyAddress4) . '</div>' : '') . '
						' . ($companyAddress5 ? '<div>' . htmlentities($companyAddress5) . '</div>' : '') . '
						' . ($companyAddress6 ? '<div>' . htmlentities($companyAddress6) . '</div>' : '') . '
					</td>
					<td style="width:60%; vertical-align:top;">
						<div style="font-weight:bold;">' . __('Ship To') . ':</div>
						<div>' . htmlentities($MyArray[$i]['deliverto']) . '</div>
						<div>' . htmlentities($MyArray[$i]['deladd1']) . '</div>
						' . ($MyArray[$i]['deladd2'] ? '<div>' . htmlentities($MyArray[$i]['deladd2']) . '</div>' : '') . '
						' . ($MyArray[$i]['deladd3'] ? '<div>' . htmlentities($MyArray[$i]['deladd3']) . '</div>' : '') . '
						' . ($MyArray[$i]['deladd4'] ? '<div>' . htmlentities($MyArray[$i]['deladd4']) . '</div>' : '') . '
						' . ($MyArray[$i]['deladd5'] ? '<div>' . htmlentities($MyArray[$i]['deladd5']) . '</div>' : '') . '
						' . ($MyArray[$i]['deladd6'] ? '<div>' . htmlentities($MyArray[$i]['deladd6']) . '</div>' : '') . '
					</td>
				</tr>
			</table>
			<hr>
			<div><strong>' . __('Order') . ':</strong> ' . htmlentities($MyArray[$i]['customerref']) . '</div>
			<div><strong>' . __('Item') . ':</strong> ' . htmlentities($MyArray[$i]['stockid']) . '</div>
			<div><strong>' . __('Customer Item') . ':</strong> ' . htmlentities($MyArray[$i]['custitem']) . '</div>
		</div>
		';
	}

	$dompdf->loadHtml($HTMLLabels);

	// Optionally set paper size/orientation from $FormDesign
	$paperSize = isset($FormDesign->PaperSize) ? (string)$FormDesign->PaperSize : 'A4';
	$orientation = 'portrait';
	$dompdf->setPaper($paperSize, $orientation);

	$dompdf->render();

	$PDFFileName = $_SESSION['DatabaseName'] . '_FGLABEL_' . $SelectedORD . '_' . date('Y-m-d') . '.pdf';

	if ($MakePDFThenDisplayIt) {
		// Stream to browser
		$dompdf->stream($PDFFileName, ['Attachment' => false]);
		exit;
	} else {
		// Save to file and email
		$output = $dompdf->output();
		$tmpFile = sys_get_temp_dir() . '/' . $PDFFileName;
		file_put_contents($tmpFile, $output);

		$Success = SendEmailFromWebERP(
			$_SESSION['CompanyRecord']['email'],
			[$_POST['EmailTo'] => ''],
			__('Work Order Number') . ' ' . $SelectedORD,
			__('Please Process this Work order number') . ' ' . $SelectedORD,
			[$tmpFile]
		);

		$Title = __('Email a Work Order');
		include('includes/header.php');
		if ($Success == 1) {
			prnMsg(__('Work Order') . ' ' . $SelectedORD . ' ' . __('has been emailed to') . ' ' . $_POST['EmailTo'] . ' ' . __('as directed'), 'success');
		} else {
			prnMsg(__('Emailing Work order') . ' ' . $SelectedORD . ' ' . __('to') . ' ' . $_POST['EmailTo'] . ' ' . __('failed'), 'error');
		}
		unlink($tmpFile);
		include('includes/footer.php');
	}
} else {
	$Title = __('Label Error');
	include('includes/header.php');
	prnMsg(__('There were no labels to print'), 'warn');
	echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
	include('includes/footer.php');
}