<?php

include('includes/session.php');
use Dompdf\Dompdf;

$Title = _('Sell Through Support Claims Report');

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);
	$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
				<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . _('Reorder Level Report') . '<br />
					' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . _('Low GP Sales Between') . ' ' . $_POST['FromDate'] . ' ' . _('and') . ' ' . $_POST['ToDate'] . '<br />
				</div>';

	$Title = _('Sell Through Support Claim') . ' - ' . _('Problem Report');

	if (! Is_Date($_POST['FromDate']) OR ! Is_Date($_POST['ToDate'])){
		include('includes/header.php');
		prnMsg(_('The dates entered must be in the format') . ' '  . $_SESSION['DefaultDateFormat'],'error');
		include('includes/footer.php');
		exit();
	}

	  /*Now figure out the data to report for the category range under review */
	$SQL = "SELECT sellthroughsupport.supplierno,
					suppliers.suppname,
					suppliers.currcode,
					currencies.decimalplaces as currdecimalplaces,
					stockmaster.stockid,
					stockmaster.decimalplaces,
					stockmaster.description,
					stockmoves.transno,
					stockmoves.trandate,
					systypes.typename,
					stockmoves.qty,
					stockmoves.debtorno,
					debtorsmaster.name,
					stockmoves.price*(1-stockmoves.discountpercent) as sellingprice,
					purchdata.price as fxcost,
					sellthroughsupport.rebatepercent,
					sellthroughsupport.rebateamount
				FROM stockmaster INNER JOIN stockmoves
					ON stockmaster.stockid=stockmoves.stockid
				INNER JOIN systypes
					ON stockmoves.type=systypes.typeid
				INNER JOIN debtorsmaster
					ON stockmoves.debtorno=debtorsmaster.debtorno
				INNER JOIN purchdata
					ON purchdata.stockid = stockmaster.stockid
				INNER JOIN suppliers
					ON suppliers.supplierid = purchdata.supplierno
				INNER JOIN sellthroughsupport
					ON sellthroughsupport.supplierno=suppliers.supplierid
				INNER JOIN currencies
					ON currencies.currabrev=suppliers.currcode
				WHERE stockmoves.trandate >= '" . FormatDateForSQL($_POST['FromDate']) . "'
				AND stockmoves.trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'
				AND sellthroughsupport.effectivefrom <= stockmoves.trandate
				AND sellthroughsupport.effectiveto >= stockmoves.trandate
				AND (stockmoves.type=10 OR stockmoves.type=11)
				AND (sellthroughsupport.stockid=stockmoves.stockid OR sellthroughsupport.categoryid=stockmaster.categoryid)
				AND (sellthroughsupport.debtorno=stockmoves.debtorno OR sellthroughsupport.debtorno='')
				ORDER BY sellthroughsupport.supplierno,
					stockmaster.stockid";

	$ClaimsResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {

	  include('includes/header.php');
		prnMsg(_('The sell through support items to claim could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug==1){
		  echo '<br />' . $SQL;
		}
		include('includes/footer.php');
		exit();
	}

	if (DB_num_rows($ClaimsResult) == 0) {

		include('includes/header.php');
		prnMsg(_('No sell through support items retrieved'), 'warn');
		echo '<br /><a href="'  . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	$HTML .= '<table>';

	$HTML .= '<tr>
				<th>' . _('Transaction') . '</th>
				<th>' . _('Item') . '</th>
				<th>' . _('Customer') . '</th>
				<th>' . _('Sell Price') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Claim') . '</th>
			</tr>';

	$SupplierClaimTotal = 0;
	while ($SellThroRow = DB_fetch_array($ClaimsResult)){

		$CurrDecimalPlaces = $SellThroRow['currdecimalplaces'];
		$Supplier = $SellThroRow['suppname'];
		$CurrCode = $SellThroRow['currcode'];
		if (isset($Supplier) and $SellThroRow['suppname']!=$Supplier){
			$LeftOvers = $pdf->addTextWrap($Left_Margin+2,$YPos,250,$FontSize,$SellThroRow['suppname']);
			if ($SupplierClaimTotal > 0) {
				$HTML .= '<tr>
							<td colspan="3"></td>
							<td colspan="2">' . $Supplier . ' ' . _('Total Claim:') . ' (' . $CurrCode . ')' . '</td>
							<td class="number">' . locale_number_format($SupplierClaimTotal,$CurrDecimalPlaces) . '</td>
						</tr>';
			}
		}
		$DisplaySellingPrice = locale_number_format($SellThroRow['sellingprice'],$_SESSION['CompanyRecord']['decimalplaces']);
		$ClaimAmount = (($SellThroRow['fxcost']*$SellThroRow['rebatepercent']) + $SellThroRow['rebateamount']) * -$SellThroRow['qty'];
		$SupplierClaimTotal += $ClaimAmount;
		$HTML .= '<tr>
					<td>' . $SellThroRow['typename'] . '-' . $SellThroRow['transno'] . '</td>
					<td>' . $SellThroRow['stockid']. '-' . $SellThroRow['description'] . '</td>
					<td>' . $SellThroRow['name'] . '</td>
					<td>' . $DisplaySellingPrice . '</td>
					<td class="number">' . locale_number_format(-$SellThroRow['qty']) . '</td>
					<td class="number">' . locale_number_format($ClaimAmount,$CurrDecimalPlaces) . '</td>
				</tr>';

	} /*end sell through support claims while loop */

	if ($SupplierClaimTotal > 0) {

		$HTML .= '<tr>
					<td colspan="3"></td>
					<td colspan="2">' . $Supplier . ' ' . _('Total Claim:') . ' (' . $CurrCode . ')' . '</td>
					<td class="number">' . locale_number_format($SupplierClaimTotal,$CurrDecimalPlaces) . '</td>
				</tr>';

	}

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
			</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_SellThroughSupportClaim_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = _('Sales With Low GP');
		include ('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/sales.png" title="' . _('Sales With Low G') . '" alt="" />' . ' ' . _('Sales With Low G') . '
			</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

} else {

	$ViewTopic = 'Sales';
	$BookMark = '';

	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' '
		. _('Sell Through Support Claims Report') . '</p>';

	if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate'])) {

	/*if $FromDate is not set then show a form to allow input */
		$_POST['FromDate']=Date('Y-m-d');
		$_POST['ToDate']=Date('Y-m-d');
		$_POST['GPMin']=0;
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<fieldset>
				<legend>', _('Report Criteria'), '</legend>
				<field>
					<label for="FromDate">' . _('Sales Made From') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</label>
					<input type="date" name="FromDate" size="11" maxlength="10" value="' . $_POST['FromDate'] . '" />
				</field>
				<field>
					<label for="ToDate">' . _('Sales Made To') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</label>
					<input type="date" name="ToDate" size="11" maxlength="10" value="' . $_POST['ToDate'] . '" />
				</field>
			</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . _('Print Low GP PDF') . '" />
				<input type="submit" name="View" title="View" value="' . _('View Low GP Report') . '" />
			</div>';
		echo '</form>';
	}
	include('includes/footer.php');

} /*end of else not PrintPDF */
