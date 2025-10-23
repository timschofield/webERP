<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_GET['WO'])) {
	$SelectedWO = $_GET['WO'];
} elseif (isset($_POST['WO'])){
	$SelectedWO = $_POST['WO'];
} else {
	unset($SelectedWO);
}
if (isset($_GET['StockID'])) {
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
} else {
	unset($StockID);
}

if (isset($_GET['LabelItem'])) {
	$LabelItem = $_GET['LabelItem'];
} elseif (isset($_POST['LabelItem'])){
	$LabelItem = $_POST['LabelItem'];
} else {
	unset($LabelItem);
}
if (isset($_GET['LabelDesc'])) {
	$LabelDesc = $_GET['LabelDesc'];
} elseif (isset($_POST['LabelDesc'])){
	$LabelDesc = $_POST['LabelDesc'];
} else {
	unset($LabelDesc);
}
if (isset($_GET['LabelLot'])) {
	$LabelLot = $_GET['LabelLot'];
} elseif (isset($_POST['LabelLot'])){
	$LabelLot = $_POST['LabelLot'];
} else {
	unset($LabelLot);
}
if (isset($_GET['NoOfBoxes'])) {
	$NoOfBoxes = $_GET['NoOfBoxes'];
} elseif (isset($_POST['NoOfBoxes'])){
	$NoOfBoxes = $_POST['NoOfBoxes'];
} else {
	unset($NoOfBoxes);
}
if (isset($_GET['LabelsPerBox'])) {
	$LabelsPerBox = $_GET['LabelsPerBox'];
} elseif (isset($_POST['LabelsPerBox'])){
	$LabelsPerBox = $_POST['LabelsPerBox'];
} else {
	unset($LabelsPerBox);
}
if (isset($_GET['QtyPerBox'])) {
	$QtyPerBox = $_GET['QtyPerBox'];
} elseif (isset($_POST['QtyPerBox'])){
	$QtyPerBox = $_POST['QtyPerBox'];
} else {
	unset($QtyPerBox);
}
if (isset($_GET['LeftOverQty'])) {
	$LeftOverQty = $_GET['LeftOverQty'];
} elseif (isset($_POST['LeftOverQty'])){
	$LeftOverQty = $_POST['LeftOverQty'];
} else {
	unset($LeftOverQty);
}

if (isset($_POST['PrintLabels']) && ($_POST['PrintOrEmail'] == 'Print' || $ViewingOnly == 1)) {
	$MakePDFThenDisplayIt = true;
	$MakePDFThenEmailIt = false;
} elseif (isset($_POST['PrintLabels']) && $_POST['PrintOrEmail'] == 'Email' && isset($_POST['EmailTo'])) {
	$MakePDFThenEmailIt = true;
	$MakePDFThenDisplayIt = false;
}

$HTML = '<html><head><style>
		body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
		.table { border-collapse: collapse; width: 100%;margin-bottom:10px; }
		.table th, .table td { border: 1px solid #000; padding: 4px; }
		.header { font-weight: bold; font-size: 16px; text-align: center; margin-bottom: 20px; }
		</style></head><body>';
$pageBreak = '<div style="page-break-after: always;"></div>';
$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';

$i = 1;
$NoOfLabels = $NoOfBoxes * $LabelsPerBox;
$BoxNumber = 1;
while ($i <= $NoOfLabels) {
	$MyArray[$i]['itemcode'] = $LabelItem;
	$MyArray[$i]['itemdescription'] = $LabelDesc;
	$MyArray[$i]['serialno'] = $LabelLot;
	$MyArray[$i]['weight'] = $QtyPerBox;
	$MyArray[$i]['box'] = $BoxNumber;
	if ($i % $LabelsPerBox == 0) {
		$BoxNumber += 1;
	}
	$i++;
}
if ($LeftOverQty > 0) {
	$j = 1;
	while ($j <= $LabelsPerBox) {
		$MyArray[$i]['itemcode'] = $LabelItem;
		$MyArray[$i]['itemdescription'] = $LabelDesc;
		$MyArray[$i]['serialno'] = $LabelLot;
		$MyArray[$i]['weight'] = $LeftOverQty;
		$MyArray[$i]['box'] = $BoxNumber;
		if ($i % $LabelsPerBox == 0) {
			$BoxNumber += 1;
		}
		$i++;
		$j++;
		$NoOfLabels++;
	}
}

if ($NoOfLabels > 0) {
	for ($i=1; $i<=$NoOfLabels; $i++) {
		$HTML .= '<div><img class="logo" src=' . $_SESSION['LogoFile'] . ' /></div>';
		$MyRow = $MyArray[$i];
		$SQL = "SELECT stockmaster.controlled, stockmaster.units FROM stockmaster WHERE stockid ='" . $MyRow['itemcode'] . "'";
		$CheckControlledResult = DB_query($SQL, '<br />' . __('Could not determine if the item was controlled or not because') . ' ');
		$ControlledRow = DB_fetch_row($CheckControlledResult);
		// Build HTML for each label
		$HTML .= '
		<div style="width: 100%; font-family: Arial, sans-serif; border: 1px solid #000; margin-bottom: 24px; padding: 12px;">
			<div style="text-align: center;">
			</div>
			<div style="margin-top: 10px;">
				<strong>' . $_SESSION['CompanyRecord']['regoffice1'] . '</strong><br />
				' . $_SESSION['CompanyRecord']['regoffice2'] . '<br />
				' . $_SESSION['CompanyRecord']['regoffice3'] . '<br />
				' . __('Tel') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br />
				' . $_SESSION['CompanyRecord']['regoffice4'] . '<br />
			</div>
			<hr>
			<div style="margin-top: 12px;">
				<strong>Item:</strong> ' . $MyArray[$i]['itemcode'] . '<br />
				<strong>Description:</strong> ' . $MyArray[$i]['itemdescription'] . '<br />
				<strong>Weight (' . htmlspecialchars($ControlledRow[1]) . '):</strong> ' . $MyArray[$i]['weight'] . '<br />
				<strong>Box:</strong> ' . $MyArray[$i]['box'] . '<br />';
		if ($ControlledRow[0] == 1) {
			$HTML .= '<strong>Lot:</strong> ' . $MyArray[$i]['serialno'] . '<br />';
		}
		$HTML .= '
			</div>
		</div>';
		if ($i < $NoOfLabels) {
			$HTML .= $pageBreak;
		}
	}

	if ($MakePDFThenDisplayIt) {
		// Stream PDF to browser
		$FileName = $_SESSION['DatabaseName'] . '_FGLabel_WO-'  . $SelectedWO . '_' . date('Y-m-d') . '.pdf';
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($FileName, array(
			"Attachment" => false
		));
	} else {
		$pdfFilename = $_SESSION['DatabaseName'] . '_FGLABEL_WO-' . $SelectedWO . '_' . date('Y-m-d') . '.pdf';
		$dompdf->loadHtml($HTML);
		$dompdf->setPaper(strtolower($PaperSize), 'portrait');
		$dompdf->render();
		file_put_contents($_SESSION['reports_dir'] . '/' . $pdfFilename, $dompdf->output());

		$Success = SendEmailFromWebERP(
			$_SESSION['CompanyRecord']['email'],
			array($_POST['EmailTo'] => ''),
			__('Work Order Number') . ' ' . $SelectedWO,
			__('Please Process this Work order number') . ' ' . $SelectedWO,
			$_SESSION['reports_dir'] . '/' . $pdfFilename
		);

		$Title = __('Email a Work Order');
		include('includes/header.php');
		echo '<div class="centre"><br /><br /><br />';
		if ($Success == 1) {
			prnMsg(__('Work Order') . ' ' . $SelectedWO . ' ' . __('has been emailed to') . ' ' . $_POST['EmailTo'] . ' ' . __('as directed'), 'success');
		} else {
			prnMsg(__('Emailing Work order') . ' ' . $SelectedWO . ' ' . __('to') . ' ' . $_POST['EmailTo'] . ' ' . __('failed'), 'error');
		}
		include('includes/footer.php');
	}
} else {
	$Title = __('Label Error');
	include('includes/header.php');
	prnMsg(__('There were no labels to print'),'warn');
	echo '<br /><a href="'.$RootPath.'/index.php">' .  __('Back to the menu') . '</a>';
	include('includes/footer.php');
}