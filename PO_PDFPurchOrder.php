<?php

require(__DIR__ . '/includes/session.php');
require 'vendor/autoload.php'; // DomPDF

use Dompdf\Dompdf;

include('includes/SQL_CommonFunctions.php');
include('includes/DefinePOClass.php');

if (!isset($_GET['OrderNo']) AND !isset($_POST['OrderNo'])) {
	$Title = __('Select a purchase order');
	include('includes/header.php');
	echo '<div class="centre"><br /><br /><br />';
	prnMsg(__('Select a Purchase Order Number to Print before calling this page'), 'error');
	echo '<br />
				<br />
				<br />
				<table class="table_index">
					<tr><td class="menu_group_item">
						<li><a href="' . $RootPath . '/PO_SelectOSPurchOrder.php">' . __('Outstanding Purchase Orders') . '</a></li>
						<li><a href="' . $RootPath . '/PO_SelectPurchOrder.php">' . __('Purchase Order Inquiry') . '</a></li>
						</td>
					</tr></table>
				</div>
				<br />
				<br />
				<br />';
	include('includes/footer.php');
	exit();
}

if (isset($_GET['OrderNo'])) {
	$OrderNo = $_GET['OrderNo'];
}
elseif (isset($_POST['OrderNo'])) {
	$OrderNo = $_POST['OrderNo'];
}
$Title = __('Print Purchase Order Number') . ' ' . $OrderNo;

if (isset($_POST['PrintOrEmail']) AND isset($_POST['EmailTo'])) {
	if ($_POST['PrintOrEmail'] == 'Email' AND !IsEmailAddress($_POST['EmailTo'])) {
		include('includes/header.php');
		prnMsg(__('The email address entered does not appear to be valid. No emails have been sent.'), 'warn');
		include('includes/footer.php');
		exit();
	}
}

$ViewingOnly = 0;

if (isset($_GET['ViewingOnly']) AND $_GET['ViewingOnly'] != '') {
	$ViewingOnly = $_GET['ViewingOnly'];
}
elseif (isset($_POST['ViewingOnly']) AND $_POST['ViewingOnly'] != '') {
	$ViewingOnly = $_POST['ViewingOnly'];
}

if (isset($_POST['DoIt']) AND ($_POST['PrintOrEmail'] == 'Print' OR $ViewingOnly == 1)) {
	$MakePDFThenDisplayIt = true;
	$MakePDFThenEmailIt = false;
} elseif (isset($_POST['DoIt']) AND $_POST['PrintOrEmail'] == 'Email' AND isset($_POST['EmailTo'])) {
	$MakePDFThenEmailIt = true;
	$MakePDFThenDisplayIt = false;
}

$POHeader = array();
if (isset($OrderNo) AND $OrderNo != '' AND $OrderNo > 0) {
	$ErrMsg = __('There was a problem retrieving the purchase order header details for Order Number') . ' ' . $OrderNo . ' ' . __('from the database');
	$SQL = "SELECT purchorders.supplierno,
					suppliers.suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					purchorders.comments,
					purchorders.orddate,
					purchorders.rate,
					purchorders.dateprinted,
					purchorders.deladd1,
					purchorders.deladd2,
					purchorders.deladd3,
					purchorders.deladd4,
					purchorders.deladd5,
					purchorders.deladd6,
					purchorders.allowprint,
					purchorders.requisitionno,
					www_users.realname as initiator,
					purchorders.paymentterms,
					suppliers.currcode,
					purchorders.status,
					purchorders.stat_comment,
					paymentterms.terms,
					currencies.decimalplaces AS currdecimalplaces
				FROM purchorders INNER JOIN suppliers
					ON purchorders.supplierno = suppliers.supplierid
				INNER JOIN currencies
					ON suppliers.currcode=currencies.currabrev
				INNER JOIN paymentterms
					ON purchorders.paymentterms=paymentterms.termsindicator
				INNER JOIN www_users
					ON purchorders.initiator=www_users.userid
				INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE purchorders.orderno='" . $OrderNo . "'";
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result) == 0) {
		$Title = __('Print Purchase Order Error');
		include('includes/header.php');
		echo '<div class="centre"><br /><br /><br />';
		prnMsg(__('Unable to Locate Purchase Order Number') . ' : ' . $OrderNo . ' ', 'error');
		echo '<br />
			<br />
			<br />
			<table class="table_index">
				<tr><td class="menu_group_item">
				<li><a href="' . $RootPath . '/PO_SelectOSPurchOrder.php">' . __('Outstanding Purchase Orders') . '</a></li>
				<li><a href="' . $RootPath . '/PO_SelectPurchOrder.php">' . __('Purchase Order Inquiry') . '</a></li>
				</td>
				</tr>
			</table>
			</div><br /><br /><br />';
		include('includes/footer.php');
		exit();
	} elseif (DB_num_rows($Result) == 1) {
		$POHeader = DB_fetch_array($Result);

		if ($POHeader['status'] != 'Authorised' AND $POHeader['status'] != 'Printed') {
			include('includes/header.php');
			prnMsg(__('Purchase orders can only be printed once they have been authorised') . '. ' . __('This order is currently at a status of') . ' ' . __($POHeader['status']), 'warn');
			include('includes/footer.php');
			exit();
		}

		if ($ViewingOnly == 0) {
			if ($POHeader['allowprint'] == 0) {
				$Title = __('Purchase Order Already Printed');
				include('includes/header.php');
				echo '<p>';
				prnMsg(__('Purchase Order Number') . ' ' . $OrderNo . ' ' . __('has previously been printed') . '. ' . __('It was printed on') . ' ' . ConvertSQLDate($POHeader['dateprinted']) . '<br />' . __('To allow a reprint, you must modify the order and enable printing again.'), 'warn');
				echo '<div class="centre">
						<li><a href="' . $RootPath . '/PO_PDFPurchOrder.php?OrderNo=' . $OrderNo . '&ViewingOnly=1">' . __('Print This Order as a Copy') . '</a>
						<li><a href="' . $RootPath . '/PO_Header.php?ModifyOrderNumber=' . $OrderNo . '">' . __('Modify the order to allow a real reprint') . '</a>
						<li><a href="' . $RootPath . '/PO_SelectPurchOrder.php">' . __('Select another order') . '</a>
						<li><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a></div>';
				include('includes/footer.php');
				exit();
			}
		}
	}
}

if (isset($MakePDFThenDisplayIt) or isset($MakePDFThenEmailIt)) {
	// Build HTML for DomPDF
	$HTML = '<html lang="en-GB"><head><style>
	body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; }
	table { border-collapse: collapse; width: 100%; }
	th, td { border: 1px solid #333; padding: 3px; }
	.header-table td { border: none; }
	.right { text-align: right; }
	</style></head><body>';

	$CompanyAddress = '';
	for ($i = 1; $i < 7; $i++) {
		if ($_SESSION['CompanyRecord']['regoffice' . $i] != '') {
			$CompanyAddress .= $_SESSION['CompanyRecord']['regoffice' . $i] . '<br />';
		}
	}

	$SupplierAddress = '';
	for ($i = 1; $i < 7; $i++) {
		if ($POHeader['address' . $i] != '') {
			$SupplierAddress .= $POHeader['address' . $i] . '<br />';
		}
	}

	$DeliveryAddress = '';
	for ($i = 1; $i < 7; $i++) {
		if ($POHeader['deladd' . $i] != '') {
			$DeliveryAddress .= $POHeader['deladd' . $i] . '<br />';
		}
	}

	$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';

	$HTML .= '<img class="logo" src="' . $_SESSION['LogoFile'] . '" /><br />';
	$HTML .= '<h2>' . __('Purchase Order') . ' #' . htmlspecialchars($OrderNo) . '</h2>';
	$HTML .= '<table class="header-table">
				<tr>
					<td><strong>' . __('From') . ':</strong></td>
					<td><strong>' . __('To') . ':</strong></td>
					<td><strong>' . __('Deliver To') . ':</strong></td>
				</tr>
				<tr>
					<td style="vertical-align:top">' . htmlspecialchars($_SESSION['CompanyRecord']['coyname']) . '<br />' . $CompanyAddress . '</td>
					<td style="vertical-align:top">' . htmlspecialchars($POHeader['suppname']) . '<br />' . $SupplierAddress . '</td>
					<td style="vertical-align:top">' . $DeliveryAddress . '</td>
				</tr>

		<tr>
			<td><strong>' . __('Order Date') . ':</strong></td>
			<td>' . ConvertSQLDate($POHeader['orddate']) . '</td>
			<td></td>
		</tr>
		<tr>
			<td><strong>' . __('Initiator') . ':</strong></td>
			<td>' . $POHeader['initiator'] . '</td>
			<td></td>
		</tr>
		<tr>
			<td><strong>' . __('Payment Terms') . ':</strong></td>
			<td>' . $POHeader['terms'] . '</td>
			<td></td>
		</tr>
		<tr>
			<td><strong>' . __('Comments') . ':</strong></td>
			<td colspan="2">' . htmlspecialchars($POHeader['comments']) . '</td>
		</tr>
	</table>';

	$HTML .= '';
	$HTML .= '<table>
		<thead>
			<tr>
				<th colspan="7"><h3>' . __('Order Details') . '</h3></th>
			</tr>
			<tr>
				<th>' . __('Item Code') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Delivery Date') . '</th>
				<th>' . __('Quantity') . '</th>
				<th>' . __('Unit') . '</th>
				<th>' . __('Price') . '</th>
				<th>' . __('Line Total') . '</th>
			</tr>
		</thead>
		<tbody>';

	$OrderTotal = 0;
		$ErrMsg = __('There was a problem retrieving the line details for order number') . ' ' . $OrderNo . ' ' . __('from the database');
		$SQL = "SELECT itemcode,
						deliverydate,
						itemdescription,
						unitprice,
						suppliersunit,
						quantityord,
						decimalplaces,
						conversionfactor,
						suppliers_partno
				FROM purchorderdetails LEFT JOIN stockmaster
					ON purchorderdetails.itemcode=stockmaster.stockid
				WHERE orderno ='" . $OrderNo . "'
				ORDER BY itemcode";
		$Result = DB_query($SQL);
		$lines = [];
		while ($POLine = DB_fetch_array($Result)) {
			$lines[] = $POLine;
		}

	foreach ($lines as $POLine) {
		$DecimalPlaces = ($POLine['decimalplaces'] !== NULL) ? $POLine['decimalplaces'] : 2;
		$DisplayQty = locale_number_format($POLine['quantityord'] / $POLine['conversionfactor'], $DecimalPlaces);

		if ($_POST['ShowAmounts'] == 'Yes') {
			$DisplayPrice = locale_number_format($POLine['unitprice'] * $POLine['conversionfactor'], $POHeader['currdecimalplaces']);
			$DisplayLineTotal = locale_number_format($POLine['unitprice'] * $POLine['quantityord'], $POHeader['currdecimalplaces']);
		} else {
			$DisplayPrice = '----';
			$DisplayLineTotal = '----';
		}
		$DisplayDelDate = ConvertSQLDate($POLine['deliverydate']);
		$ItemCode = (mb_strlen($POLine['suppliers_partno'])>0) ? $POLine['suppliers_partno'] : $POLine['itemcode'];
		$OrderTotal += ($POLine['unitprice'] * $POLine['quantityord']);

		$HTML .= '<tr>
			<td>' . htmlspecialchars($ItemCode) . '</td>
			<td>' . htmlspecialchars($POLine['itemdescription']) . '</td>
			<td>' . htmlspecialchars($DisplayDelDate) . '</td>
			<td class="right">' . htmlspecialchars($DisplayQty) . '</td>
			<td>' . htmlspecialchars($POLine['suppliersunit']) . '</td>
			<td class="right">' . htmlspecialchars($DisplayPrice) . '</td>
			<td class="right">' . htmlspecialchars($DisplayLineTotal) . '</td>
		</tr>';

	}
	$HTML .= '</tbody></table>';

	if ($_POST['ShowAmounts'] == 'Yes') {
		$DisplayOrderTotal = locale_number_format($OrderTotal, $POHeader['currdecimalplaces']);
	} else {
		$DisplayOrderTotal = '----';
	}

	$HTML .= '<h3>' . __('Order Total - excl tax') . ' ' . htmlspecialchars($POHeader['currcode']) . ': <span class="right">' . $DisplayOrderTotal . '</span></h3>';

	$HTML .= '</body></html>';

	// DomPDF options
	$PdfFileName = $_SESSION['DatabaseName'] . '_PurchaseOrder_' . $OrderNo . '_' . date('Y-m-d') . '.pdf';

	if ($MakePDFThenDisplayIt) {
		// Display PDF in browser
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($PdfFileName, array(
			"Attachment" => false
		));
		exit();
	} else {
		// Save PDF to file and send via email
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);
		// (Optional) set up the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');
		// Render the HTML as PDF
		$dompdf->render();
		// Output the generated PDF to a temporary file
		$output = $dompdf->output();

		$PdfFileName = sys_get_temp_dir() . '/' . $_SESSION['DatabaseName'] . '_InventoryValuation_' . date('Y-m-d') . '.pdf';
		file_put_contents($PdfFileName, $output);

		$From = $_SESSION['CompanyRecord']['email'];
		$To = $_POST['EmailTo'];

		if ($From != '') {
			$Subject = __('Purchase Order Number') . ' ' . $OrderNo;
			$Body = __('Please find herewith Purchase Order From') . ' ' . htmlspecialchars($_SESSION['CompanyRecord']['coyname']);
			$ConfirmationText = __('Please find attached the Reorder level report, generated by user') . ' ' . $_SESSION['UserID'] . ' ' . __('at') . ' ' . Date('Y-m-d H:i:s');
			$EmailSubject = $_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf';
			/// @todo drop this IF - it's handled within SendEmailFromWebERP
			if ($_SESSION['SmtpSetting'] == 0) {
				mail($To, $EmailSubject, $ConfirmationText);
			} else {
				$EmailResult = SendEmailFromWebERP($From, array($To=>''), $Subject, $Body, array($PdfFileName), false);
			}
		}
		unlink($PdfFileName);

		include('includes/header.php');
		if ($EmailResult == 1) {
			echo '<div class="centre"><br /><br /><br />';
			prnMsg(__('Purchase Order') . ' ' . $OrderNo . ' ' . __('has been emailed to') . ' ' . $_POST['EmailTo'] . ' ' . __('as directed'), 'success');
		} else {
			echo '<div class="centre"><br /><br /><br />';
			prnMsg(__('Emailing Purchase order') . ' ' . $OrderNo . ' ' . __('to') . ' ' . $_POST['EmailTo'] . ' ' . __('failed'), 'error');
		}
		include('includes/footer.php');
	}

	if ($ViewingOnly == 0 && $EmailResult == 1) {
		$StatusComment = date($_SESSION['DefaultDateFormat']) . ' - ' . __('Printed by') . ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName'] . '</a><br />' . html_entity_decode($POHeader['stat_comment']);
		$SQL = "UPDATE purchorders SET allowprint = 0,
										dateprinted  = CURRENT_DATE,
										status = 'Printed',
										stat_comment = '" . htmlspecialchars($StatusComment, ENT_QUOTES, 'UTF-8') . "'
				WHERE purchorders.orderno = '" . $OrderNo . "'";
		$Result = DB_query($SQL);
	}
} else {
	// Print/email selection form (unchanged)
	$ViewTopic = 'PurchaseOrdering';
	$BookMark = '';
	include('includes/header.php');
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if ($ViewingOnly == 1) {
		echo '<input type="hidden" name="ViewingOnly" value="1" />';
	}
	echo '<input type="hidden" name="OrderNo" value="' . $OrderNo . '" />';
	echo '<fieldset>
			<legend>', __('Print Options'), '</legend>
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
			<label for="ShowAmounts">' . __('Show Amounts on the Order') . '</label>
			<select name="ShowAmounts">';
	if (!isset($_POST['ShowAmounts'])) {
		$_POST['ShowAmounts'] = 'Yes';
	}
	if ($_POST['ShowAmounts'] == 'Yes') {
		echo '<option selected="selected" value="Yes">' . __('Yes') . '</option>';
		echo '<option value="No">' . __('No') . '</option>';
	} else {
		echo '<option value="Yes">' . __('Yes') . '</option>';
		echo '<option selected="selected" value="No">' . __('No') . '</option>';
	}
	echo '</select>
		</field>';
	if ($_POST['PrintOrEmail'] == 'Email') {
		$ErrMsg = __('There was a problem retrieving the contact details for the supplier');
		$SQL = "SELECT suppliercontacts.contact,
						suppliercontacts.email
				FROM suppliercontacts INNER JOIN purchorders
				ON suppliercontacts.supplierid=purchorders.supplierno
				WHERE purchorders.orderno='" . $OrderNo . "'";
		$ContactsResult = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($ContactsResult) > 0) {
			echo '<field>
					<label for="EmailTo">' . __('Email to') . ':</label>
					<select name="EmailTo">';
			while ($ContactDetails = DB_fetch_array($ContactsResult)) {
				if (mb_strlen($ContactDetails['email']) > 2 and mb_strpos($ContactDetails['email'], '@') > 0) {
					if (isset($_POST['EmailTo']) and $_POST['EmailTo'] == $ContactDetails['email']) {
						echo '<option selected="selected" value="' . $ContactDetails['email'] . '">' . $ContactDetails['Contact'] . ' - ' . $ContactDetails['email'] . '</option>';
					} else {
						echo '<option value="' . $ContactDetails['email'] . '">' . $ContactDetails['contact'] . ' - ' . $ContactDetails['email'] . '</option>';
					}
				}
			}
			echo '</select>
				</field>
			</fieldset>';
		} else {
			echo '</fieldset>';
			prnMsg(__('There are no contacts defined for the supplier of this order') . '. ' . __('You must first set up supplier contacts before emailing an order'), 'error');
		}
	} else {
		echo '</fieldset>';
	}
	echo '<div class="centre">
			<input type="submit" name="DoIt" value="' . __('OK') . '" />
		</div>
	</form>';

	include('includes/footer.php');
}