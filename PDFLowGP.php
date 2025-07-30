<?php

include('includes/session.php');
use Dompdf\Dompdf;

if (isset($_POST['FromDate'])) {$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])) {$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

if (!isset($_POST['FromCat'])  OR $_POST['FromCat']=='') {
	$Title=_('Low Gross Profit Sales');
}
$Debug=0;
if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

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

	$Title = _('Low GP sales') . ' - ' . _('Problem Report');

	if (! Is_Date($_POST['FromDate']) OR ! Is_Date($_POST['ToDate'])){
		include('includes/header.php');
		prnMsg(_('The dates entered must be in the format') . ' '  . $_SESSION['DefaultDateFormat'],'error');
		include('includes/footer.php');
		exit();
	}

	/*Now figure out the data to report for the category range under review */
	$SQL = "SELECT stockmaster.categoryid,
					stockmaster.stockid,
					stockmoves.transno,
					stockmoves.trandate,
					systypes.typename,
					stockmaster.actualcost as unitcost,
					stockmoves.qty,
					stockmoves.debtorno,
					stockmoves.branchcode,
					stockmoves.price*(1-stockmoves.discountpercent) as sellingprice,
					(stockmoves.price*(1-stockmoves.discountpercent)) - (stockmaster.actualcost) AS gp,
					debtorsmaster.name
				FROM stockmaster INNER JOIN stockmoves
					ON stockmaster.stockid=stockmoves.stockid
				INNER JOIN systypes
					ON stockmoves.type=systypes.typeid
				INNER JOIN debtorsmaster
					ON stockmoves.debtorno=debtorsmaster.debtorno
				WHERE stockmoves.trandate >= '" . FormatDateForSQL($_POST['FromDate']) . "'
				AND stockmoves.trandate <= '" . FormatDateForSQL($_POST['ToDate']) . "'
				AND ((stockmoves.price*(1-stockmoves.discountpercent)) - (stockmaster.actualcost))/(stockmoves.price*(1-stockmoves.discountpercent)) <=" . $_POST['GPMin']/100 . "
				ORDER BY stockmaster.stockid";

	$LowGPSalesResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {

		include('includes/header.php');
		prnMsg(_('The low GP items could not be retrieved by the SQL because') . ' - ' . DB_error_msg(),'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug==1){
			echo '<br />' . $SQL;
		}
		include('includes/footer.php');
		exit();
	}

	if (DB_num_rows($LowGPSalesResult) == 0) {

		include('includes/header.php');
		prnMsg(_('No low GP items retrieved'), 'warn');
		echo '<br /><a href="'  . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug==1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit();
	}
	$HTML .= '<table>
				<tr>
					<th>' . _('Trans') . '</th>
					<th>' . _('No') . '</th>
					<th>' . _('Item') . '</th>
					<th>' . _('Customer') . '</th>
					<th>' . _('Sell Price') . '</th>
					<th>' . _('Cost') . '</th>
					<th>' . _('GP') . '</th>
					<th>' . _('GP') . '%</th>
				</tr>';

	$Tot_Val=0;
	$Category = '';
	$CatTot_Val=0;
	while ($LowGPItems = DB_fetch_array($LowGPSalesResult)){

		$DisplayUnitCost = locale_number_format($LowGPItems['unitcost'],$_SESSION['CompanyRecord']['decimalplaces']);
		$DisplaySellingPrice = locale_number_format($LowGPItems['sellingprice'],$_SESSION['CompanyRecord']['decimalplaces']);
		$DisplayGP = locale_number_format($LowGPItems['gp'],$_SESSION['CompanyRecord']['decimalplaces']);
		$DisplayGPPercent = locale_number_format(($LowGPItems['gp']*100)/$LowGPItems['sellingprice'],1);

		$HTML .= '<tr>
					<td>' . $LowGPItems['typename'] . '</td>
					<td>' . $LowGPItems['transno'] . '</td>
					<td>' . $LowGPItems['stockid'] . '</td>
					<td>' . $LowGPItems['name'] . '</td>
					<td class="number">' . $DisplaySellingPrice . '</td>
					<td class="number">' . $DisplayUnitCost . '</td>
					<td class="number">' . $DisplayGP . '</td>
					<td class="number">' . $DisplayGPPercent . '%</td>
				</tr>';

	} /*end low GP items while loop */

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
		$dompdf->stream($_SESSION['DatabaseName'] . '_LowGPSales_' . date('Y-m-d') . '.pdf', array(
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
		. _('Low Gross Profit Report') . '</p>';

	if (!isset($_POST['FromDate']) OR !isset($_POST['ToDate'])) {

	/*if $FromDate is not set then show a form to allow input */
		$_POST['FromDate']=Date('Y-m-d');
		$_POST['ToDate']=Date('Y-m-d');
		$_POST['GPMin']=0;
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<fieldset>
				<legend>', _('Report Criteria'), '</legend>';
		echo '<field>
				<label for="FromDate">' . _('Sales Made From') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</label>
				<input type="date" required="required" autofocus="autofocus" name="FromDate" size="11" maxlength="10" value="' . $_POST['FromDate'] . '" />
			</field>
			<field>
				<label for="ToDate">' . _('Sales Made To') . ' (' . _('in the format') . ' ' . $_SESSION['DefaultDateFormat'] . '):</label>
				<input type="date" required="required" name="ToDate" size="11" maxlength="10" value="' . $_POST['ToDate'] . '" />
			</field>
			<field>
				<label for="GPMin">' . _('Show sales with GP % below') . ':</label>
				<input type="text" class="integer" name="GPMin" maxlength="3" size="3" value="' . $_POST['GPMin'] . '" />
			</field>
			</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . _('Print Low GP PDF') . '" />
				<input type="submit" name="View" title="View" value="' . _('View Low GP Report') . '" />
			</div>
		</form>';
	}
	include('includes/footer.php');

} /*end of else not PrintPDF */
