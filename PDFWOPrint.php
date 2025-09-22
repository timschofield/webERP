<?php

// Converted to use DomPDF for PDF generation

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;
use BarcodePack\qrCode;

include('includes/SQL_CommonFunctions.php');

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

if (isset($_GET['PrintLabels'])) {
	$PrintLabels = $_GET['PrintLabels'];
} elseif (isset($_POST['PrintLabels'])){
	$PrintLabels = $_POST['PrintLabels'];
} else {
	unset($PrintLabels);
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
if (isset($_GET['PrintLabels'])) {
	$PrintLabels = $_GET['PrintLabels'];
} elseif (isset($_POST['PrintLabels'])){
	$PrintLabels = $_POST['PrintLabels'];
} else {
	$PrintLabels="Yes";
}
if (isset($_GET['ViewingOnly'])) {
	$ViewingOnly = $_GET['ViewingOnly'];
} elseif (isset($_POST['ViewingOnly'])) {
	$ViewingOnly = $_POST['ViewingOnly'];
} else {
	$ViewingOnly = 1;
}
if (isset($_GET['EmailTo'])) {
	$EmailTo = $_GET['EmailTo'];
} elseif (isset($_POST['EmailTo'])) {
	$EmailTo = $_POST['EmailTo'];
} else {
	$EmailTo = '';
}
if (isset($_GET['LabelLot'])) {
	$LabelLot = $_GET['LabelLot'];
} elseif (isset($_POST['LabelLot'])) {
	$LabelLot = $_POST['LabelLot'];
} else {
	$LabelLot = '';
}

if (!isset($_GET['WO']) and !isset($_POST['WO'])) {
	$Title = __('Select a Work Order');
	include('includes/header.php');
	echo '<div class="centre">';
	prnMsg(__('Select a Work Order Number to Print before calling this page'), 'error');
	echo '<table class="table_index">
			<tr>
				<td class="menu_group_item">
					<li><a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select Work Order') . '</a></li>
				</td>
			</tr>
		</table>
	</div>';
	include('includes/footer.php');
	exit();
}
if (isset($_GET['WO'])) {
	$SelectedWO = $_GET['WO'];
}
elseif (isset($_POST['WO'])) {
	$SelectedWO = $_POST['WO'];
}
$Title = __('Print Work Order Number') . ' ' . $SelectedWO;
if (isset($_POST['PrintOrEmail']) and isset($_POST['EmailTo'])) {
	if ($_POST['PrintOrEmail'] == 'Email' and !IsEmailAddress($_POST['EmailTo'])) {
		include('includes/header.php');
		prnMsg(__('The email address entered does not appear to be valid. No emails have been sent.'), 'warn');
		include('includes/footer.php');
		exit();
	}
}

if (isset($_POST['DoIt']) and ($_POST['PrintOrEmail'] == 'Print' or $ViewingOnly == 1)) {
	$MakePDFThenDisplayIt = true;
	$MakePDFThenEmailIt = false;
} elseif (isset($_POST['DoIt']) and $_POST['PrintOrEmail'] == 'Email' and isset($_POST['EmailTo'])) {
	$MakePDFThenEmailIt = true;
	$MakePDFThenDisplayIt = false;
}

if (isset($SelectedWO) and $SelectedWO != '' and $SelectedWO > 0) {
	/*retrieve the order details from the database to print */
	$ErrMsg = __('There was a problem retrieving the Work order header details for Order Number') . ' ' . $SelectedWO . ' ' . __('from the database');
	$SQL = "SELECT workorders.wo,
							 workorders.loccode,
							 locations.locationname,
							 locations.deladd1,
							 locations.deladd2,
							 locations.deladd3,
							 locations.deladd4,
							 locations.deladd5,
							 locations.deladd6,
							 workorders.requiredby,
							 workorders.startdate,
							 workorders.closed,
							 stockmaster.description,
							 stockmaster.decimalplaces,
							 stockmaster.units,
							 stockmaster.controlled,
							 woitems.stockid,
							 woitems.qtyreqd,
							 woitems.qtyrecd,
							 woitems.comments,
							 woitems.nextlotsnref
						FROM workorders
						INNER JOIN locations
							ON workorders.loccode=locations.loccode
						INNER JOIN woitems
							ON workorders.wo=woitems.wo
						INNER JOIN locationusers
							ON locationusers.loccode=locations.loccode
							AND locationusers.userid='" .  $_SESSION['UserID'] . "'
							AND locationusers.canview=1
						INNER JOIN stockmaster
							ON woitems.stockid=stockmaster.stockid
						WHERE woitems.stockid='" . $StockID . "'
							AND woitems.wo ='" . $SelectedWO . "'";
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		/*There is no order header returned */
		$Title = __('Print Work Order Error');
		include('includes/header.php');
		echo '<div class="centre">';
		prnMsg(__('Unable to Locate Work Order Number') . ' : ' . $SelectedWO . ' ', 'error');
		echo '<table class="table_index">
				<tr>
					<td class="menu_group_item">
						<li><a href="' . $RootPath . '/SelectWorkOrder.php">' . __('Select Work Order') . '</a></li>
					</td>
				</tr>
			</table>
		</div>';
		include('includes/footer.php');
		exit();
	} elseif (DB_num_rows($Result) == 1) {
		/*There is only one order header returned  (as it should be!)*/
		$WOHeader = DB_fetch_array($Result);
		if ($WOHeader['controlled']==1) {
			$SQL = "SELECT serialno
							FROM woserialnos
							WHERE woserialnos.stockid='" . $StockID . "'
							AND woserialnos.wo ='" . $SelectedWO . "'";
			$Result = DB_query($SQL, $ErrMsg);
			if (DB_num_rows($Result) > 0) {
				$SerialNoArray=DB_fetch_array($Result);
				$SerialNo=$SerialNoArray[0];
			}
			else {
				$SerialNo=$WOHeader['nextlotsnref'];
			}
		} //controlled
		if ($WOHeader['comments'] == null) {
			$WOHeader['comments'] = '';
		}
		$PackQty=0;
		$SQL = "SELECT value
				FROM stockitemproperties
				INNER JOIN stockcatproperties
				ON stockcatproperties.stkcatpropid=stockitemproperties.stkcatpropid
				WHERE stockid='" . $StockID . "'
				AND label='PackQty'";
		$Result = DB_query($SQL, $ErrMsg);
		$PackQtyArray=DB_fetch_array($Result);
		if (DB_num_rows($Result) == 0) {
			$PackQty = 1;
		} else {
			$PackQty=$PackQtyArray['value'];
			if ($PackQty==0) {
				$PackQty=1;
			}
		}
	} // 1 valid record
} //if there is a valid order number

/* Load the relevant xml file */
if (isset($MakePDFThenDisplayIt) or isset($MakePDFThenEmailIt)) {

	$HTML = '<html><head><style>
		body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
		.table { border-collapse: collapse; width: 100%;margin-bottom:10px; }
		.table th, .table td { border: 1px solid #000; padding: 4px; }
		.header { font-weight: bold; font-size: 16px; text-align: center; margin-bottom: 20px; }
		</style></head><body>';

	$HTML .= '<html>
				<head>';
	$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	$HTML .= '<img class="logo" src=' . $_SESSION['LogoFile'] . ' /><br />';

	$HTML .= '<table>
				<tr>
					<td style="width:50%;background:transparent">';
	$HTML .= '<div class="centre" id="ReportHeader">';
	$HTML .= $_SESSION['CompanyRecord']['coyname'] . '<br />';
	$HTML .= $_SESSION['CompanyRecord']['regoffice1'] . '<br />';
	$HTML .= $_SESSION['CompanyRecord']['regoffice2'] . '<br />';
	$HTML .= $_SESSION['CompanyRecord']['regoffice3'] . '<br />';
	$HTML .= $_SESSION['CompanyRecord']['regoffice4'] . '<br />';
	$HTML .= $_SESSION['CompanyRecord']['regoffice5'] . '<br />';
	$HTML .= $_SESSION['CompanyRecord']['regoffice6'] . '<br />';
	$HTML .= __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />';
	$HTML .= '</div>';
	$HTML .= '</td>';
	$HTML .= '<td style="width:50%;background:transparent"><div class="centre" id="ReportHeader" style="float:right">';
	$HTML .= __('Produced At') . ':<br />';
	$HTML .= $WOHeader['locationname'] . '<br />';
	$HTML .= $WOHeader['deladd1'] . '<br />';
	$HTML .= $WOHeader['deladd2'] . '<br />';
	$HTML .= $WOHeader['deladd3'] . '<br />';
	$HTML .= $WOHeader['deladd4'] . '<br />';
	$HTML .= $WOHeader['deladd5'] . '<br />';
	$HTML .= $WOHeader['deladd6'] . '<br />';
	$HTML .= '</div></td></tr></table>';
	$HTML .= '<div class="header">' . __('Work Order Number') . ' ' . htmlspecialchars($SelectedWO) . '</div>';
	$HTML .= '<table class="table"><tr>
				<th colspan="7"><h4>' . __('Work Order Details') . '</h4></th>
			</tr><tr>
				<th>' . __('Item Number') . '</th>
				<th>' . __('Item Description') . '</th>
				<th>' . __('Lot') . '</th>
				<th>' . __('Required By') . '</th>
				<th>' . __('Qty Required') . '</th>
				<th>' . __('Qty Received') . '</th>
				<th>' . __('Packing Qty') . '</th>
			</tr>
			<tr>
				<td>' . $StockID . '</td>
				<td>' . $WOHeader['description'] . '</td>
				<td>' . $SerialNo . '</td>
				<td>' . ConvertSQLDate($WOHeader['requiredby']) . '</td>
				<td class="number">' . $WOHeader['qtyreqd'] . '</td>
				<td class="number">' . $WOHeader['qtyrecd'] . '</td>
				<td class="number">' . $PackQty . '</td>
			</tr>
			</table>';
	$HTML .= '<table class="table">';
	$HTML .= '<tr>
				<th colspan="6"><h4>' . __('Material Requirements for this Work Order') . '</h4></th>
			</tr>
			<tr>
				<th>' . __('Action') . '</th>
				<th>' . __('Item') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Qty Required') . '</th>
				<th>' . __('Issued') . '</th>
				<th>' . __('Units') . '</th>
			</tr>';

		$IssuedAlreadyRow = array();
		$ErrMsg = __('There was a problem retrieving the line details for order number') . ' ' . $SelectedWO . ' ' . __('from the database');
		$RequirmentsResult = DB_query("SELECT worequirements.stockid,
										stockmaster.description,
										stockmaster.decimalplaces,
										autoissue,
										qtypu,
										controlled,
										units
									FROM worequirements INNER JOIN stockmaster
									ON worequirements.stockid=stockmaster.stockid
									WHERE wo='" . $SelectedWO . "'
									AND worequirements.parentstockid='" . $StockID . "'");
		$IssuedAlreadyResult = DB_query("SELECT stockid,
											SUM(-qty) AS total
										FROM stockmoves
										WHERE stockmoves.type=28
										AND reference='".$SelectedWO."'
										GROUP BY stockid");
		while ($IssuedRow = DB_fetch_array($IssuedAlreadyResult)){
			$IssuedAlreadyRow[$IssuedRow['stockid']] = $IssuedRow['total'];
		}

	$i=0;
	$WOLine=array();
	while ($RequirementsRow = DB_fetch_array($RequirmentsResult)){
		if ($RequirementsRow['autoissue']==0){
			$WOLine[$i]['action']='Manual Issue';
		} else {
			$WOLine[$i]['action']='Auto Issue';
		}
		if (isset($IssuedAlreadyRow[$RequirementsRow['stockid']])){
			$Issued = $IssuedAlreadyRow[$RequirementsRow['stockid']];
			unset($IssuedAlreadyRow[$RequirementsRow['stockid']]);
		}else{
			$Issued = 0;
		}
		$WOLine[$i]['item'] = $RequirementsRow['stockid'];
		$WOLine[$i]['description'] = $RequirementsRow['description'];
		$WOLine[$i]['controlled'] = $RequirementsRow['controlled'];
		$WOLine[$i]['qtyreqd'] = $WOHeader['qtyreqd']*$RequirementsRow['qtypu'];
		$WOLine[$i]['issued'] = $Issued  ;
		$WOLine[$i]['decimalplaces'] = $RequirementsRow['decimalplaces'];
		$WOLine[$i]['units'] = $RequirementsRow['units'];
		$i+=1;
	}

	foreach ($WOLine as $line) {
		$HTML .= '<tr>
			<td>' . htmlspecialchars($line['action']) . '</td>
			<td>' . htmlspecialchars($line['item']) . '</td>
			<td>' . htmlspecialchars($line['description']) . '</td>
			<td class="number">' . locale_number_format($line['qtyreqd'], $line['decimalplaces']) . '</td>
			<td class="number">' . locale_number_format($line['issued'], $line['decimalplaces']) . '</td>
			<td>' . htmlspecialchars($line['units']) . '</td>
		</tr>';
	}
	$HTML .= '</table>';
	$HTML .= '<br/><b>' . __('Comments') . ':</b><br/>' . nl2br(htmlspecialchars($WOHeader['comments'])) . '<br/>';

	// Generate QR code for https://weberp.org via barcodepack
	ob_start();
	$qr = new qrCode($RootPath.'/WorkOrderIssue.php?WO='.$SelectedWO.'&StockID='.$StockID,5);
	/// @todo there seems to be double work done here... $imageData is set but never used?
	$qr->draw();  // This outputs PNG image directly
	$imageData = ob_get_contents();
	ob_end_clean();
	imagepng(($qr->draw()),$_SESSION['part_pics_dir'] . '/qr.png');
	$qrImgTag = '<div style="margin-right:18px 0;"><img style="width:192px" style="margin-top:80px" src="' .$_SESSION['part_pics_dir'] . '/qr.png" alt="QR Code">';
	ob_start();
	$qr = new qrCode($StockID,7);
	$qr->draw();  // This outputs PNG image directly
	$imageData = ob_get_contents();
	ob_end_clean();
	imagepng(($qr->draw()),$_SESSION['part_pics_dir'] . '/qr1.png');
	$qrImgTag .= '<img style="width:192px" style="margin-bottom:-10px" src="' .$_SESSION['part_pics_dir'] . '/qr1.png" alt="QR Code">';
	ob_start();
	$qr = new qrCode($RootPath.'/WorkOrderReceive.php?WO='.$SelectedWO.'&StockID='.$StockID,5);
	$qr->draw();  // This outputs PNG image directly
	$imageData = ob_get_contents();
	ob_end_clean();
	imagepng(($qr->draw()),$_SESSION['part_pics_dir'] . '/qr2.png');
	$qrImgTag .= '<img style="width:192px" style="margin-top:80px" src="' .$_SESSION['part_pics_dir'] . '/qr2.png" alt="QR Code"></div>';
	// Insert QR code here
	$HTML .= $qrImgTag;

	$HTML .= '<br/><b>' . __('Date') . ':</b> ______________ &nbsp;&nbsp; <b>' . __('Signed for') . ':</b> ____________________________________';

	// If you want QR codes, generate them as PNG and embed <img src="data:image/png;base64,..." />
	// Use a library like endroid/qr-code or similar for QR code generation

	$HTML .= '</body></html>';

	if ($MakePDFThenDisplayIt) {
		// Stream PDF to browser
		$FileName = $_SESSION['DatabaseName'] . '_WorkOrder_' . $SelectedWO . '_' . date('Y-m-d') . '.pdf';
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($FileName, array(
			"Attachment" => false
		));
		exit();
	} else {
		// Save PDF to file and send email
		$pdfOutput = $dompdf->output();
		$PDFFileName = $_SESSION['reports_dir'] . '/' . $_SESSION['DatabaseName'] . '_WorkOrder_' . $SelectedWO . '_' . date('Y-m-d') . '.pdf';
		file_put_contents($PDFFileName, $pdfOutput);

		$Success = SendEmailFromWebERP(
			$_SESSION['CompanyRecord']['email'],
			array($_POST['EmailTo'] => ''),
			__('Work Order Number') . ' ' . $SelectedWO,
			__('Please Process this Work order number') . ' ' . $SelectedWO,
			array($PDFFileName)
		);

		/// @todo should we delete the generated report?

		include('includes/header.php');
		if ($Success == 1) {
			prnMsg(__('Work Order') . ' ' . $SelectedWO . ' ' . __('has been emailed to') . ' ' . $_POST['EmailTo'] . ' ' . __('as directed'), 'success');
		} else {
			prnMsg(__('Emailing Work order') . ' ' . $SelectedWO . ' ' . __('to') . ' ' . $_POST['EmailTo'] . ' ' . __('failed'), 'error');
		}
		include('includes/footer.php');
	}
} //isset($MakePDFThenDisplayIt) or isset($MakePDFThenEmailIt)

/* There was enough info to either print or email the Work order */
else {
	/**
	/*the user has just gone into the page need to ask the question whether to print the order or email it */
	include('includes/header.php');

	if (!isset($LabelItem)) {
		$SQL = "SELECT workorders.wo,
						stockmaster.description,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.controlled,
						woitems.stockid,
						woitems.qtyreqd,
						woitems.nextlotsnref
						FROM workorders INNER JOIN woitems
						ON workorders.wo=woitems.wo
						INNER JOIN stockmaster
						ON woitems.stockid=stockmaster.stockid
						WHERE woitems.stockid='" . $StockID . "'
						AND woitems.wo ='" . $SelectedWO . "'";

		$Result = DB_query($SQL, $ErrMsg);
		$Labels = DB_fetch_array($Result);
		$LabelItem=$Labels['stockid'];
		$LabelDesc=$Labels['description'];
		$QtyPerBox=0;
		$SQL = "SELECT value
				FROM stockitemproperties
				INNER JOIN stockcatproperties
				ON stockcatproperties.stkcatpropid=stockitemproperties.stkcatpropid
				WHERE stockid='" . $StockID . "'
				AND label='PackQty'";
		$Result = DB_query($SQL, $ErrMsg);
		$PackQtyArray=DB_fetch_array($Result);
		if (DB_num_rows($Result) == 0) {
			$QtyPerBox = 1;
		} else {
			$QtyPerBox=$PackQtyArray['value'];
			if ($QtyPerBox==0) {
				$QtyPerBox=1;
			}
		}
		$NoOfBoxes=(int)($Labels['qtyreqd'] / $QtyPerBox);
		$LeftOverQty=$Labels['qtyreqd'] % $QtyPerBox;
		$LabelsPerBox=1;
		$QtyPerBox=locale_number_format($QtyPerBox, $Labels['decimalplaces']);
		$LeftOverQty=locale_number_format($LeftOverQty, $Labels['decimalplaces']);
		if ($Labels['controlled']==1) {
			$SQL = "SELECT serialno
							FROM woserialnos
							WHERE woserialnos.stockid='" . $StockID . "'
							AND woserialnos.wo ='" . $SelectedWO . "'";
			$Result = DB_query($SQL, $ErrMsg);
			if (DB_num_rows($Result) > 0) {
				$SerialNoArray=DB_fetch_array($Result);
				$LabelLot=$SerialNoArray[0];
			}
			else {
				$LabelLot=$WOHeader['nextlotsnref'];
			}
		} //controlled
	} //not set yet
	echo '<p class="page_title_text" ><img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/printer.png" title="' . __('Print') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if ($ViewingOnly == 1) {
		echo '<input type="hidden" name="ViewingOnly" value="1" />';
	} //$ViewingOnly == 1
	echo '<input type="hidden" name="WO" value="' . $SelectedWO . '" />';
	echo '<input type="hidden" name="StockID" value="' . $StockID . '" />';
	echo '<Fieldset>
			<legend>', __('Order Printiing Options'), '</legend>
			<field>
				<label for="PrintOrEmail">' . __('Print or Email the Order') . '</label>
				<select name="PrintOrEmail">';

	if (!isset($_POST['PrintOrEmail'])) {
		$_POST['PrintOrEmail'] = 'Print';
	}
	if ($ViewingOnly != 0) {
		echo '<option selected="selected" value="Print">' . __('Print') . '</option>';
	}
	else {
		if ($_POST['PrintOrEmail'] == 'Print') {
			echo '<option selected="selected" value="Print">' . __('Print') . '</option>';
			echo '<option value="Email">' . __('Email') . '</option>';
		} else {
			echo '<option value="Print">' . __('Print') . '</option>';
			echo '<option selected="selected" value="Email">' . __('Email') . '</option>';
		}
	}
	echo '</select>
		</field>';
	echo '<field>
			<label for="PrintLabels">' . __('Print Labels') . ':</label>
			<select name="PrintLabels" >';
	if ($PrintLabels=="Yes") {
		echo '<option value="Yes" selected>' . __('Yes') . '</option>';
		echo '<option value="No">' . __('No') . '</option>';
	}
	else {
		echo '<option value="Yes" >' . __('Yes') . '</option>';
		echo '<option value="No" selected>' . __('No') . '</option>';
	}
	echo '</select>';

	if ($_POST['PrintOrEmail'] == 'Email') {
		$ErrMsg = __('There was a problem retrieving the contact details for the location');

		$SQL = "SELECT workorders.wo,
						workorders.loccode,
						locations.email
						FROM workorders INNER JOIN locations
						ON workorders.loccode=locations.loccode
						INNER JOIN woitems
						ON workorders.wo=woitems.wo
						WHERE woitems.stockid='" . $StockID . "'
						AND woitems.wo ='" . $SelectedWO . "'";
		$ContactsResult = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($ContactsResult) > 0) {
			echo '<field><td>' . __('Email to') . ':</td><td><input name="EmailTo" value="';
			while ($ContactDetails = DB_fetch_array($ContactsResult)) {
				if (mb_strlen($ContactDetails['email']) > 2 and mb_strpos($ContactDetails['email'], '@') > 0) {
					echo $ContactDetails['email'];
				}
			}
			echo '"/></field></fieldset>';
		}

	} else {
		echo '</fieldset>';
	}
	echo '<div class="centre">
			<input type="submit" name="DoIt" value="' . __('Paperwork') . '" />
		</div>
	</form>';

	if ($PrintLabels=="Yes") {
		echo '<form action="' . $RootPath . '/PDFFGLabel.php" method="post" target="_blank">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		if ($ViewingOnly == 1) {
			echo '<input type="hidden" name="ViewingOnly" value="1" />';
		} //$ViewingOnly == 1
		echo '<input type="hidden" name="WO" value="' . $SelectedWO . '" />';
		echo '<input type="hidden" name="StockID" value="' . $StockID . '" />';
		echo '<input type="hidden" name="EmailTo" value="' . $EmailTo . '" />';
		echo '<input type="hidden" name="PrintOrEmail" value="' . $_POST['PrintOrEmail'] . '" />';
		echo '<fieldset>
				<legend>', __('Label Print Options'), '</legend>
				<field>
					<label for="LabelItem">' . __('Label Item') . ':</label>
					<input name="LabelItem" value="' .$LabelItem.'"/>
				</field>
				<field>
					<label for="LabelDesc">' . __('Label Description') . ':</label>
					<input name="LabelDesc" value="' .$LabelDesc.'"/>
				</field>
				<field>
					<label for="LabelLot">' . __('Label Lot') . ':</label>
					<input name="LabelLot" value="' .$LabelLot.'"/>
				</field>
				<field>
					<label for="NoOfBoxes">' . __('No of Full Packages') . ':</label>
					<input name="NoOfBoxes" class="integer" value="' .$NoOfBoxes.'"/>
				</field>
				<field>
					<label for="LabelsPerBox">' . __('Labels/Package') . ':</label>
					<input name="LabelsPerBox" class="integer" value="' .$LabelsPerBox.'"/>
				</field>
				<field>
					<label for="QtyPerBox">' . __('Weight/Package') . ':</label>
					<input name="QtyPerBox" class="number" value="' .$QtyPerBox. '"/>
				</field>
				<field>
					<label for="LeftOverQty">' . __('LeftOver Qty') . ':</label>
					<input name="LeftOverQty" class="number" value="' .$LeftOverQty.'"/>
				</field>
				<field>
					<label for="PrintOrEmail">' . __('Print or Email the Order') . '</label>
					<select name="PrintOrEmail">';

		if (!isset($_POST['PrintOrEmail'])) {
			$_POST['PrintOrEmail'] = 'Print';
		}
		if ($ViewingOnly != 0) {
			echo '<option selected="selected" value="Print">' . __('Print') . '</option>';
		}
		else {
			if ($_POST['PrintOrEmail'] == 'Print') {
				echo '<option selected="selected" value="Print">' . __('Print') . '</option>';
				echo '<option value="Email">' . __('Email') . '</option>';
			} else {
				echo '<option value="Print">' . __('Print') . '</option>';
				echo '<option selected="selected" value="Email">' . __('Email') . '</option>';
			}
		}
		echo '</select>
			</field>';
		$SQL = "SELECT workorders.wo,
						workorders.loccode,
						locations.email
						FROM workorders INNER JOIN locations
						ON workorders.loccode=locations.loccode
						INNER JOIN woitems
						ON workorders.wo=woitems.wo
						WHERE woitems.stockid='" . $StockID . "'
						AND woitems.wo ='" . $SelectedWO . "'";
		$ContactsResult = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($ContactsResult) > 0) {
			echo '<field><label for="EmailTo">' . __('Email to') . ':</label><input name="EmailTo" value="';
			while ($ContactDetails = DB_fetch_array($ContactsResult)) {
				if (mb_strlen($ContactDetails['email']) > 2 and mb_strpos($ContactDetails['email'], '@') > 0) {
					echo $ContactDetails['email'];
				}
			}
			echo '"/></field></fieldset>';
		}
		else {
			echo '</fieldset>';
		}
		echo '<div class="centre">
				<input type="submit" name="PrintLabels" value="' . __('Labels') . '" />
			</div>
			</form>';
	}
	include('includes/footer.php');
}
