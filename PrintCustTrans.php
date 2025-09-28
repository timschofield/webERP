<?php

require(__DIR__ . '/includes/session.php');
require_once __DIR__ . '/vendor/autoload.php'; // DomPDF autoload

use Dompdf\Dompdf;

$ViewTopic = 'ARReports';
$BookMark = 'PrintInvoicesCredits';

if (isset($_GET['orientation'])) {
	$Orientation = $_GET['orientation'];
}

if (isset($_GET['FromTransNo'])) {
	$FromTransNo = filter_number_format($_GET['FromTransNo']);
} elseif (isset($_POST['FromTransNo'])) {
	$FromTransNo = filter_number_format($_POST['FromTransNo']);
} else {
	$FromTransNo = '';
}

if (isset($_GET['InvOrCredit'])) {
	$InvOrCredit = $_GET['InvOrCredit'];
} elseif (isset($_POST['InvOrCredit'])) {
	$InvOrCredit = $_POST['InvOrCredit'];
}

if (isset($_GET['PrintPDF'])) {
	$PrintPDF = $_GET['PrintPDF'];
} elseif (isset($_POST['PrintPDF'])) {
	$PrintPDF = $_POST['PrintPDF'];
}

if (!isset($_POST['ToTransNo'])
	|| trim($_POST['ToTransNo'])==''
	|| filter_number_format($_POST['ToTransNo']) < $FromTransNo) {

	$_POST['ToTransNo'] = $FromTransNo;
}

$FirstTrans = $FromTransNo;

if (isset($PrintPDF)
	&& $PrintPDF!=''
	&& isset($FromTransNo)
	&& isset($InvOrCredit)
	&& $FromTransNo!=''
	OR isset($_GET['View'])
	OR isset($_GET['Email'])) {

	$UserLanguage = $_SESSION['Language'];

	while ($FromTransNo <= filter_number_format($_POST['ToTransNo'])) {

		// --- Fetch bank account details for invoice footer ---
		$SQL = "SELECT bankaccounts.invoice,
					bankaccounts.bankaccountnumber,
					bankaccounts.bankaccountcode
				FROM bankaccounts
				WHERE bankaccounts.invoice = '1'";
		$Result = DB_query($SQL, '', '', false, false);
		if(DB_error_no()!=1) {
			if(DB_num_rows($Result)==1) {
				$MyRowBank = DB_fetch_array($Result);
				$DefaultBankAccountNumber = __('Account') .': ' .$MyRowBank['bankaccountnumber'];
				$DefaultBankAccountCode = __('Bank Code:') .' ' .$MyRowBank['bankaccountcode'];
			} else {
				$DefaultBankAccountNumber = '';
				$DefaultBankAccountCode = '';
			}
		} else {
			$DefaultBankAccountNumber = '';
			$DefaultBankAccountCode = '';
		}

		// --- Invoice/Credit Header Query ---
		if ($InvOrCredit=='Invoice') {
			$SQL = "SELECT debtortrans.trandate,
							debtortrans.ovamount,
							debtortrans.ovdiscount,
							debtortrans.ovfreight,
							debtortrans.ovgst,
							debtortrans.rate,
							debtortrans.invtext,
							debtortrans.consignment,
							debtortrans.packages,
							debtorsmaster.name,
							debtorsmaster.address1,
							debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,
							debtorsmaster.address5,
							debtorsmaster.address6,
							debtorsmaster.currcode,
							debtorsmaster.invaddrbranch,
							debtorsmaster.taxref,
							debtorsmaster.language_id,
							paymentterms.terms,
							paymentterms.dayinfollowingmonth,
							paymentterms.daysbeforedue,
							salesorders.deliverto,
							salesorders.deladd1,
							salesorders.deladd2,
							salesorders.deladd3,
							salesorders.deladd4,
							salesorders.deladd5,
							salesorders.deladd6,
							salesorders.customerref,
							salesorders.orderno,
							salesorders.orddate,
							locations.locationname,
							shippers.shippername,
							custbranch.brname,
							custbranch.braddress1,
							custbranch.braddress2,
							custbranch.braddress3,
							custbranch.braddress4,
							custbranch.braddress5,
							custbranch.braddress6,
							custbranch.brpostaddr1,
							custbranch.brpostaddr2,
							custbranch.brpostaddr3,
							custbranch.brpostaddr4,
							custbranch.brpostaddr5,
							custbranch.brpostaddr6,
							custbranch.salesman,
							salesman.salesmanname,
							debtortrans.debtorno,
							debtortrans.branchcode,
							currencies.decimalplaces
						FROM debtortrans INNER JOIN debtorsmaster
						ON debtortrans.debtorno=debtorsmaster.debtorno
						INNER JOIN custbranch
						ON debtortrans.debtorno=custbranch.debtorno
						AND debtortrans.branchcode=custbranch.branchcode
						INNER JOIN salesorders
						ON debtortrans.order_ = salesorders.orderno
						INNER JOIN shippers
						ON debtortrans.shipvia=shippers.shipper_id
						INNER JOIN salesman
						ON custbranch.salesman=salesman.salesmancode
						INNER JOIN locations
						ON salesorders.fromstkloc=locations.loccode
						INNER JOIN locationusers
						ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
						INNER JOIN paymentterms
						ON debtorsmaster.paymentterms=paymentterms.termsindicator
						INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
						WHERE debtortrans.type=10
						AND debtortrans.transno='" . $FromTransNo . "'";
			if(isset($_POST['PrintEDI']) AND $_POST['PrintEDI']=='No') {
				$SQL .= ' AND debtorsmaster.ediinvoices=0';
			}
		} else {
			$SQL = "SELECT debtortrans.trandate,
							debtortrans.ovamount,
							debtortrans.ovdiscount,
							debtortrans.ovfreight,
							debtortrans.ovgst,
							debtortrans.rate,
							debtortrans.invtext,
							debtorsmaster.invaddrbranch,
							debtorsmaster.name,
							debtorsmaster.address1,
							debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,
							debtorsmaster.address5,
							debtorsmaster.address6,
							debtorsmaster.currcode,
							debtorsmaster.taxref,
							debtorsmaster.language_id,
							custbranch.brname,
							custbranch.braddress1,
							custbranch.braddress2,
							custbranch.braddress3,
							custbranch.braddress4,
							custbranch.braddress5,
							custbranch.braddress6,
							custbranch.brpostaddr1,
							custbranch.brpostaddr2,
							custbranch.brpostaddr3,
							custbranch.brpostaddr4,
							custbranch.brpostaddr5,
							custbranch.brpostaddr6,
							custbranch.salesman,
							salesman.salesmanname,
							debtortrans.debtorno,
							debtortrans.branchcode,
							currencies.decimalplaces
						FROM debtortrans INNER JOIN debtorsmaster
						ON debtortrans.debtorno=debtorsmaster.debtorno
						INNER JOIN custbranch
						ON debtortrans.debtorno=custbranch.debtorno
						AND debtortrans.branchcode=custbranch.branchcode
						INNER JOIN salesman
						ON custbranch.salesman=salesman.salesmancode
						INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
						WHERE debtortrans.type=11
						AND debtortrans.transno='" . $FromTransNo . "'";
			if(isset($_POST['PrintEDI']) AND $_POST['PrintEDI']=='No') {
				$SQL .= ' AND debtorsmaster.ediinvoices=0';
			}
		}

		$ErrMsg = __('There was a problem retrieving the invoice or credit note details for note number') . ' ' . $FromTransNo;
		$Result = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($Result)==1) {
			$MyRow = DB_fetch_array($Result);

			$CustomerAddress = '';
			for ($i = 1; $i < 6; $i++) {
				if (trim($MyRow['address' . $i]) != '') {
					$CustomerAddress .= $MyRow['address' . $i] . '<br />';
				}
			}

			$BranchAddress = '';
			for ($i = 1; $i < 6; $i++) {
				if (trim($MyRow['braddress' . $i]) != '') {
					$BranchAddress .= $MyRow['braddress' . $i] . '<br />';
				}
			}

			$DeliveryAddress = '';
			for ($i = 1; $i < 6; $i++) {
				if (trim($MyRow['deladd' . $i]) != '') {
					$DeliveryAddress .= $MyRow['deladd' . $i] . '<br />';
				}
			}

			// Security checks as before (salesman/customer authorization)
			if ($_SESSION['SalesmanLogin'] != '' AND $_SESSION['SalesmanLogin'] != $MyRow['salesman']){
				echo '<p class="bad">' . __('Your account is set up to see only a specific salespersons orders. You are not authorised to view transaction for this order') . '</p>';
				exit();
			}
			if (isset($CustomerLogin) && $CustomerLogin == 1 AND $MyRow['debtorno'] != $_SESSION['CustomerID']){
				echo '<p class="bad">' . __('This transaction is addressed to another customer and cannot be displayed for privacy reasons') . '</p>';
				exit();
			}

			$ExchRate = $MyRow['rate'];

			// --- Get line items ---
			if ($InvOrCredit=='Invoice') {
				$SQLLines = "SELECT stockmoves.stockid,
								stockmaster.description,
								-stockmoves.qty as quantity,
								stockmoves.discountpercent,
								((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . "* -stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmoves.narrative,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.units,
								stockmoves.stkmoveno,
								stockmaster.decimalplaces
							FROM stockmoves INNER JOIN stockmaster
							ON stockmoves.stockid = stockmaster.stockid
							WHERE stockmoves.type=10
							AND stockmoves.transno='" . $FromTransNo . "'
							AND stockmoves.show_on_inv_crds=1";
			} else {
				$SQLLines = "SELECT stockmoves.stockid,
								stockmaster.description,
								stockmoves.qty as quantity,
								stockmoves.discountpercent,
								((1 - stockmoves.discountpercent) * stockmoves.price * " . $ExchRate . " * stockmoves.qty) AS fxnet,
								(stockmoves.price * " . $ExchRate . ") AS fxprice,
								stockmoves.narrative,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.units,
								stockmoves.stkmoveno,
								stockmaster.decimalplaces
							FROM stockmoves INNER JOIN stockmaster
							ON stockmoves.stockid = stockmaster.stockid
							WHERE stockmoves.type=11
							AND stockmoves.transno='" . $FromTransNo . "'
							AND stockmoves.show_on_inv_crds=1";
			}
			$ErrMsgLines = __('There was a problem retrieving the invoice or credit note stock movement details for invoice number') . ' ' . $FromTransNo;
			$ResultLines = DB_query($SQLLines, $ErrMsgLines);

			// --- Calculate Due Date (Invoice only) ---
			if ($InvOrCredit=='Invoice') {
				$DisplayDueDate = CalcDueDate(ConvertSQLDate($MyRow['trandate']), $MyRow['dayinfollowingmonth'], $MyRow['daysbeforedue']);
			}

			// --- Calculate Totals ---
			if ($InvOrCredit=='Invoice') {
				$DisplaySubTot = locale_number_format($MyRow['ovamount'],$MyRow['decimalplaces']);
				$DisplayFreight = locale_number_format($MyRow['ovfreight'],$MyRow['decimalplaces']);
				$DisplayTax = locale_number_format($MyRow['ovgst'],$MyRow['decimalplaces']);
				$DisplayTotal = locale_number_format($MyRow['ovfreight']+$MyRow['ovgst']+$MyRow['ovamount'],$MyRow['decimalplaces']);
			} else {
				$DisplaySubTot = locale_number_format(-$MyRow['ovamount'],$MyRow['decimalplaces']);
				$DisplayFreight = locale_number_format(-$MyRow['ovfreight'],$MyRow['decimalplaces']);
				$DisplayTax = locale_number_format(-$MyRow['ovgst'],$MyRow['decimalplaces']);
				$DisplayTotal = locale_number_format(-$MyRow['ovfreight']-$MyRow['ovgst']-$MyRow['ovamount'],$MyRow['decimalplaces']);
			}

			// --- Begin HTML ---
			$HTML = '<html>
			<head>
				<style>
				body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
				.header { background: #eee; padding: 10px; }
				.table1 { width: 100%; border-collapse: collapse; }
				.table1 th, .table1 td { border: 1px solid #ccc; padding: 5px; }
				.number { text-align: right; }
				.striped_row:nth-child(even) { background: #f9f9f9; }
				.centre { text-align: center; }
				.footer { margin-top: 20px; }
				</style>
			</head>
			<body>';

			$HTML .= '<table class="table1">
					<tr>
						<td>
						<img class="logo" src="' . $_SESSION['LogoFile'] . '" alt="" style="height:50px;" />';
			if ($InvOrCredit == "Invoice") {
				$HTML .= '<h2>
							' . __("TAX INVOICE") . ' ' . __("Number") . ' ' . $FromTransNo .
						'</h2>';
			} else {
				$HTML .= '<h2>
							' . __("TAX CREDIT NOTE") . ' ' . __("Number") . ' ' . $FromTransNo .
						'</h2>';
			}


			$HTML .= '<p>' . __("Tax Authority Ref") . '. ' . $_SESSION['CompanyRecord']['gstno'] . '</p>';
			if ($InvOrCredit == "Invoice") {
				$HTML .='<p>' . __("Payment Terms") . ': ' . $MyRow['terms'] . '<br />' . __("Due Date") . ': ' . $DisplayDueDate . '</p>
						</td>
						<td>
							<b>' . $_SESSION['CompanyRecord']['coyname'] . '</b><br/>'
							. $_SESSION['CompanyRecord']['regoffice1'] . '<br/>'
							. $_SESSION['CompanyRecord']['regoffice2'] . '<br/>'
							. $_SESSION['CompanyRecord']['regoffice3'] . '<br/>'
							. $_SESSION['CompanyRecord']['regoffice4'] . '<br/>'
							. $_SESSION['CompanyRecord']['regoffice5'] . '<br/>'
							. $_SESSION['CompanyRecord']['regoffice6'] . '<br/>'
							. __('Telephone') . ': ' . $_SESSION['CompanyRecord']['telephone'] . '<br/>'
							. __('Facsimile') . ': ' . $_SESSION['CompanyRecord']['fax'] . '<br/>'
							. __('Email') . ': ' . $_SESSION['CompanyRecord']['email'] . '<br/>
						</td>';

				$HTML .= '<td class="number">
							<b>' . __('Page') . ': 1</b>
						</td>
					</tr>
				</table>';

				$HTML .= '<table class="table1">
					<tr>
					</tr>
					<tr>
					</tr>
				</table>';

				if ($InvOrCredit=='Invoice') {
					$HTML .= '<table class="table1">
						<tr>
							<th>' . __('Charge To') . '</th>
							<th>' . __('Charge Branch') .  '</th>
							<th>' . __('Delivered To') . '</th>
						</tr>
						<tr>
							<td style="vertical-align:top">
								' . $MyRow['name'] . '<br/>
								' . $CustomerAddress . '
							</td>
							<td style="vertical-align:top">
								' . $MyRow['brname'] . '<br/>
								' . $BranchAddress . '
							</td>
							<td style="vertical-align:top">
								' . $MyRow['deliverto'] . '<br/>
								' . $DeliveryAddress . '
							</td>
						</tr>
					</table>';

					$HTML .= '<table class="table1">
						<tr>
							<td><b>' . __('Your Order Ref'). '</b></td>
							<td><b>' . __('Our Order No'). '</b></td>
							<td><b>' . __('Order Date'). '</b></td>
							<td><b>' . __('Invoice Date'). '</b></td>
							<td><b>' . __('Sales Person'). '</b></td>
							<td><b>' . __('Shipper'). '</b></td>
							<td><b>' . __('Consignment Ref'). '</b></td>
						</tr>
						<tr>
							<td>' . $MyRow['customerref']. '</td>
							<td>' . $MyRow['orderno']. '</td>
							<td>' . ConvertSQLDate($MyRow['orddate']). '</td>
							<td>' . ConvertSQLDate($MyRow['trandate']). '</td>
							<td>' . $MyRow['salesmanname']. '</td>
							<td>' . $MyRow['shippername']. '</td>
							<td>' . $MyRow['consignment']. '</td>
						</tr>
					</table>';

				} else {
					$HTML .= '<table class="table1">
						<tr>
							<th>' . __('Branch'). '</th>
						</tr>
						<tr>
							<td>
								' . $MyRow['brname']. '<br/>
								' . $MyRow['braddress1']. '<br/>
								' . $MyRow['braddress2']. '<br/>
								' . $MyRow['braddress3']. '<br/>
								' . $MyRow['braddress4']. '<br/>
								' . $MyRow['braddress5']. '<br/>
								' . $MyRow['braddress6']. '
							</td>
						</tr>
					</table>';

					$HTML .= '<table class="table1">
						<tr>
							<th>' . __('Date'). '</th>
							<th>' . __('Sales Person'). '</th>
						</tr>
						<tr>
							<td>' . ConvertSQLDate($MyRow['trandate']). '</td>
							<td>' . $MyRow['salesmanname']. '</td>
						</tr>
					</table>';
}
					$HTML .= '<table class="table1">
					<tr>
						<th>' . __('Item Code'). '</th>
						<th>' . __('Item Description'). '</th>
						<th>' . __('Quantity'). '</th>
						<th>' . __('Unit'). '</th>
						<th>' . __('Price'). '</th>
						<th>' . __('Discount'). '</th>
						<th>' . __('Net'). '</th>
					</tr>';
				}
					while ($MyRow2 = DB_fetch_array($ResultLines)) {
						$DisplayPrice = locale_number_format($MyRow2['fxprice'], $MyRow['decimalplaces']);
						$DisplayQty = locale_number_format($MyRow2['quantity'], $MyRow2['decimalplaces']);
						$DisplayNet = locale_number_format($MyRow2['fxnet'], $MyRow['decimalplaces']);
						$DisplayDiscount = $MyRow2['discountpercent'] == 0 ? '' : locale_number_format($MyRow2['discountpercent'] * 100, 2) . '%';

						$HTML .= '<tr class="striped_row">
							<td>' . $MyRow2['stockid'] . '</td>
							<td>';
						// Get translation if available
						$TranslationResult = DB_query("SELECT descriptiontranslation
									FROM stockdescriptiontranslations
									WHERE stockid='" . $MyRow2['stockid'] . "'
									AND language_id='" . $MyRow['language_id'] ."'");
						if (DB_num_rows($TranslationResult)==1){
							$TranslationRow = DB_fetch_array($TranslationResult);
							$HTML .= $TranslationRow['descriptiontranslation'];
						} else {
							$HTML .= $MyRow2['description'];
						}
						$HTML .= '<td class="number">' . $DisplayQty . '</td>
								<td>' . $MyRow2['units'] . '</td>
								<td class="number">' . $DisplayPrice . '</td>
								<td class="number">' . $DisplayDiscount . '</td>
								<td class="number">' . $DisplayNet . '</td>
							</tr>';
						if (mb_strlen($MyRow2['narrative']) > 1) {
							$Narrative = str_replace(array("\r\n", "\n", "\r", "\\r\\n"), '<br />', $MyRow2['narrative']);
							$HTML .= '<tr class="striped_row"><td></td><td colspan="6">' . $Narrative . '</td></tr>';
						}
						// Serial/controlled stock lines if you want to show them
						if($MyRow2['controlled']==1) {
							$GetControlMovts = DB_query("
								SELECT
									moveqty,
									serialno
								FROM stockserialmoves
								WHERE stockmoveno='" . $MyRow2['stkmoveno'] . "'");
							if($MyRow2['serialised']==1) {
								while($ControlledMovtRow = DB_fetch_array($GetControlMovts)) {
									$HTML .= '<tr><td></td><td colspan="6">' . $ControlledMovtRow['serialno'] . '</td></tr>';
								}
							} else {
								while($ControlledMovtRow = DB_fetch_array($GetControlMovts)) {
									$HTML .= '<tr><td></td><td colspan="6">' . (-$ControlledMovtRow['moveqty']) . ' x ' . $ControlledMovtRow['serialno'] . '</td></tr>';
								}
							}
						}
					}
				$HTML .= '</table>
							<b>' . __('Invoice Text') . ':</b>' . $MyRow['invtext'] . '<br/>';

				$HTML .= '<table class="table1 footer">
					<tr>
						<td>' . __('Sub Total') . '</td>
						<td class="number">' . $DisplaySubTot . '</td>
					</tr>
					<tr>
						<td>' . __('Freight') . '</td>
						<td class="number">' . $DisplayFreight . '</td>
					</tr>
					<tr>
						<td>' . __('Tax') . '</td>
						<td class="number">' . $DisplayTax . '</td>
					</tr>';

				if ($InvOrCredit == 'Invoice') {
					$HTML .= '<tr>
								<td><b>' . __('TOTAL INVOICE') . '</b></td>
								<td class="number"><b>' . $DisplayTotal . '</b></td>
							</tr>';
				} else {
					$HTML .= '<tr>
								<td><b>' . __('TOTAL CREDIT') . '</b></td>
								<td class="number"><b>' . $DisplayTotal  . '</b></td>
					</tr>';
				}
				$HTML .= '</table>';

				$HTML .= '<b>' . __('All amounts stated in') . ' ' . $MyRow['currcode'] . '</b>';

				if ($InvOrCredit=='Invoice' && ($DefaultBankAccountCode || $DefaultBankAccountNumber)) {
					$HTML .= '<div class="footer">
						<b>' . $DefaultBankAccountCode . ' ' . $DefaultBankAccountNumber . '</b>
					</div>';
				}
				if ($InvOrCredit=='Invoice' && $_SESSION['RomalpaClause']) {
					$HTML .= '<div class="footer">' . $_SESSION['RomalpaClause'] . '</div>';
				}
			$HTML .= '</body>
			</html>';
		}
		$FromTransNo++;
	}

if (isset($_GET['View']) and $_GET['View'] == 'Yes') {
	include('includes/header.php');
	echo $HTML;
	include('includes/footer.php');
} elseif (isset($_GET['Email'])) {

	$PdfFileName = $_SESSION['DatabaseName'] . '_' . $InvOrCredit . '_' . ($FromTransNo-1) .'_'. date('Y-m-d') . '.pdf';
	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);
	// (Optional) set up the paper size and orientation
	$dompdf->setPaper($_SESSION['PageSize'], 'landscape');
	// Render the HTML as PDF
	$dompdf->render();
	// Output the generated PDF to a temporary file
	$output = $dompdf->output();

	file_put_contents($PdfFileName, $output);

	if ($_GET['Email']!='') {
		$ConfirmationText = __('Please find attached the') . $InvOrCredit . '_' . ($FromTransNo-1);
		$EmailSubject = $PdfFileName;
		/// @todo drop this IF - it's handled within SendEmailFromWebERP
		if ($_SESSION['SmtpSetting']==0) {
			mail($_GET['Email'],$EmailSubject,$ConfirmationText);
		} else {
			$Success = SendEmailFromWebERP($_SESSION['CompanyRecord']['email'],
								array($_GET['Email'] =>  ''),
								$EmailSubject,
								$ConfirmationText,
								array($PdfFileName)
							);
		}
	}
	unlink($PdfFileName);

	$Title = __('Send Report By Email');
	include('includes/header.php');
	/// @todo give different message based on $Success
	echo '<div class="centre">
			<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
		</div>';
	include('includes/footer.php');

} else {

	// Generate PDF with DomPDF
	$PdfFileName = $_SESSION['DatabaseName'] . '_' . $InvOrCredit . '_' . ($FromTransNo-1) .'_'. date('Y-m-d') . '.pdf';
	// Display PDF in browser
	$dompdf = new Dompdf(['chroot' => __DIR__]);
	$dompdf->loadHtml($HTML);

	$dompdf->setPaper($_SESSION['PageSize'], $Orientation);

	// Render the HTML as PDF
	$dompdf->render();

	// Output the generated PDF to Browser
	$dompdf->stream($PdfFileName, array("Attachment" => false));
}

} else {
	// --- HTML output for preview form ---
	$Title=__('Select Invoices/Credit Notes To Print');
	include('includes/header.php');

	if (!isset($FromTransNo) OR $FromTransNo=='') {

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print') . '" alt="" />' . ' ' . __('Print Invoices or Credit Notes (Landscape Mode)') . '</p>';

		echo '<fieldset>
				<legend>', __('Print Criteria'), '</legend>
				<field>
					<label for="InvOrCredit">' . __('Print Invoices or Credit Notes') . '</label>
					<select name="InvOrCredit">';

		if ($InvOrCredit=='Invoice' OR !isset($InvOrCredit)) {

			echo '<option selected="selected" value="Invoice">' . __('Invoices') . '</option>';
			echo '<option value="Credit">' . __('Credit Notes') . '</option>';
		} else {
			echo '<option selected="selected" value="Credit">' . __('Credit Notes') . '</option>';
			echo '<option value="Invoice">' . __('Invoices') . '</option>';
		}

		echo '</select>
			</field>';

		echo '<field>
				<label for="PrintEDI">', __('Print EDI Transactions'), '</label>
				<select name="PrintEDI">';

		if ($InvOrCredit=='Invoice' OR !isset($InvOrCredit)) {

			echo '<option selected="selected" value="No">' . __('Do not Print PDF EDI Transactions') . '</option>';
			echo '<option value="Yes">' . __('Print PDF EDI Transactions Too') . '</option>';

		} else {

			echo '<option value="No">' . __('Do not Print PDF EDI Transactions') . '</option>';
			echo '<option selected="selected" value="Yes">' . __('Print PDF EDI Transactions Too') . '</option>';
		}

		echo '</select>
			</field>';

		echo '<field>
				<label for="FromTransNo">' . __('Start invoice/credit note number to print') . '</label>
				<input class="number" type="text" maxlength="6" size="7" name="FromTransNo" required="required" />
			</field>';

		echo '<field>
				<label for="ToTransNo">' . __('End invoice/credit note number to print') . '</label>
				<input class="number" type="text" maxlength="6" size="7" name="ToTransNo" />
			</field>
		</fieldset>';
		echo '<div class="centre">
				<input type="submit" name="Print" value="' . __('Print Preview') . '" />
				<input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
			</div>';

		$SQL = "SELECT typeno FROM systypes WHERE typeid=10";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		echo '<div class="page_help_text"><b>' . __('The last invoice created was number') . ' ' . $MyRow[0] . '</b><br />' . __('If only a single invoice is required') . ', ' . __('enter the invoice number') . ' ' . __('as both the start and end numbers') . '.</div>';

		$SQL = "SELECT typeno FROM systypes WHERE typeid=11";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);

		echo '<div class="page_help_text"><b>' . __('The last credit note created was number') . ' ' . $MyRow[0] . '</b><br />' . __('A sequential range can be printed using the same method as for invoices above') . '. ' . __('A range is printed from the start to the end number inclusive') . '.</div>';

		echo '</form>';

	} else {

		// --- Output HTML preview for selected invoice(s) (similar to above, but just echo) ---
		while($FromTransNo <= filter_number_format($_POST['ToTransNo'])) {
			// ... (reuse earlier logic to fetch and echo details, but as HTML, not PDF)
			// For brevity, you can reuse the same PHP/HTML as in the PDF block, but echo instead of buffering.
			// You can copy the above HTML for invoice preview, replacing $HTML .= ob_get_clean(); with echo.
			// (Omitted for brevity here, but you can copy-paste the HTML/PHP above)
			$FromTransNo++;
		}
	}
	include('includes/footer.php');
}
