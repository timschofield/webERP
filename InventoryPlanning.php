<?php

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

$ViewTopic = "Inventory";
$BookMark = "PlanningReport";

include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');

if (isset($_POST['PrintPDF']) or isset($_POST['View'])){

      /*Now figure out the inventory data to report for the category range under review
      need QOH, QOO, QDem, Sales Mth -1, Sales Mth -2, Sales Mth -3, Sales Mth -4*/
	if ($_POST['Location']=='All'){
		$SQL = "SELECT stockmaster.categoryid,
						stockmaster.description,
						stockcategory.categorydescription,
						locstock.stockid,
						SUM(locstock.quantity) AS qoh
					FROM locstock
					INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
						stockmaster,
						stockcategory
					WHERE locstock.stockid=stockmaster.stockid
					AND stockmaster.discontinued = 0
					AND stockmaster.categoryid=stockcategory.categoryid
					AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
					AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					GROUP BY stockmaster.categoryid,
						stockmaster.description,
						stockcategory.categorydescription,
						locstock.stockid,
						stockmaster.stockid
					ORDER BY stockmaster.categoryid,
						stockmaster.stockid";
	} else {
		$SQL = "SELECT stockmaster.categoryid,
					locstock.stockid,
					stockmaster.description,
					stockcategory.categorydescription,
					locstock.quantity  AS qoh
				FROM locstock
				INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
					stockmaster,
					stockcategory
				WHERE locstock.stockid=stockmaster.stockid
				AND stockmaster.discontinued = 0
				AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				AND stockmaster.categoryid=stockcategory.categoryid
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
				AND locstock.loccode = '" . $_POST['Location'] . "'
				ORDER BY stockmaster.categoryid,
					stockmaster.stockid";

	}
	$ErrMsg = __('The inventory quantities could not be retrieved');
	$InventoryResult = DB_query($SQL, $ErrMsg);

	$HTML = '';

	if (isset($_POST['PrintPDF']) or isset($_POST['Email'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	if ($_POST['NumberMonthsHolding']>10){
		$NumberMonthsHolding=$_POST['NumberMonthsHolding']-10;
	}
	else{
		$NumberMonthsHolding=$_POST['NumberMonthsHolding'];
	}

	$Period_0_Name = GetMonthText(date('m', mktime(0,0,0,Date('m'),Date('d'),Date('Y'))));
	$Period_1_Name = GetMonthText(date('m', mktime(0,0,0,Date('m')-1,Date('d'),Date('Y'))));
	$Period_2_Name = GetMonthText(date('m', mktime(0,0,0,Date('m')-2,Date('d'),Date('Y'))));
	$Period_3_Name = GetMonthText(date('m', mktime(0,0,0,Date('m')-3,Date('d'),Date('Y'))));
	$Period_4_Name = GetMonthText(date('m', mktime(0,0,0,Date('m')-4,Date('d'),Date('Y'))));
	$Period_5_Name = GetMonthText(date('m', mktime(0,0,0,Date('m')-5,Date('d'),Date('Y'))));

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Inventory Planning Report') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Item') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . $Period_5_Name . ' ' . __('Qty') . '</th>
							<th>' . $Period_4_Name . ' ' . __('Qty') . '</th>
							<th>' . $Period_3_Name . ' ' . __('Qty') . '</th>
							<th>' . $Period_2_Name . ' ' . __('Qty') . '</th>
							<th>' . $Period_1_Name . ' ' . __('Qty') . '</th>
							<th>' . $Period_0_Name . ' ' . __('MTD') . '</th>
							<th>' . $NumberMonthsHolding . ' ' . __('ms stk') . '</th>
							<th>' . __('QOH') . '</th>
							<th>' . __('Cust Ords') . '</th>
							<th>' . __('Splr Ords') . '</th>
							<th>' . __('Sugg Ord') . '</th>
						</tr>
					</thead>
					<tbody>';

	$Category = '';

	$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$Period_1 = $CurrentPeriod -1;
	$Period_2 = $CurrentPeriod -2;
	$Period_3 = $CurrentPeriod -3;
	$Period_4 = $CurrentPeriod -4;
	$Period_5 = $CurrentPeriod -5;

	while ($InventoryPlan = DB_fetch_array($InventoryResult)){

		if ($Category!=$InventoryPlan['categoryid']){
			$HTML .= '<tr>
						<th>' . $InventoryPlan['categoryid'] . ' - ' . $InventoryPlan['categorydescription'] . '</th>
						<th colspan="12"></th>
					</tr>';
			$Category = $InventoryPlan['categoryid'];
		}

		if ($_POST['Location']=='All'){
			$SQL = "SELECT SUM(CASE WHEN prd='" . $CurrentPeriod . "' THEN -qty ELSE 0 END) AS prd0,
				   		SUM(CASE WHEN prd='" . $Period_1 . "' THEN -qty ELSE 0 END) AS prd1,
						SUM(CASE WHEN prd='" . $Period_2 . "' THEN -qty ELSE 0 END) AS prd2,
						SUM(CASE WHEN prd='" . $Period_3 . "' THEN -qty ELSE 0 END) AS prd3,
						SUM(CASE WHEN prd='" . $Period_4 . "' THEN -qty ELSE 0 END) AS prd4,
						SUM(CASE WHEN prd='" . $Period_5 . "' THEN -qty ELSE 0 END) AS prd5
					FROM stockmoves
					INNER JOIN locationusers ON locationusers.loccode=stockmoves.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
					WHERE stockid='" . $InventoryPlan['stockid'] . "'
					AND (type=10 OR type=11)
					AND stockmoves.hidemovt=0";
		} else {
  		   $SQL = "SELECT SUM(CASE WHEN prd='" . $CurrentPeriod . "' THEN -qty ELSE 0 END) AS prd0,
				   		SUM(CASE WHEN prd='" . $Period_1 . "' THEN -qty ELSE 0 END) AS prd1,
						SUM(CASE WHEN prd='" . $Period_2 . "' THEN -qty ELSE 0 END) AS prd2,
						SUM(CASE WHEN prd='" . $Period_3 . "' THEN -qty ELSE 0 END) AS prd3,
						SUM(CASE WHEN prd='" . $Period_4 . "' THEN -qty ELSE 0 END) AS prd4,
						SUM(CASE WHEN prd='" . $Period_5 . "' THEN -qty ELSE 0 END) AS prd5
					FROM stockmoves
					INNER JOIN locationusers ON locationusers.loccode=stockmoves.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
					WHERE stockid='" . $InventoryPlan['stockid'] . "'
					AND stockmoves.loccode ='" . $_POST['Location'] . "'
					AND (stockmoves.type=10 OR stockmoves.type=11)
					AND stockmoves.hidemovt=0";
		}

		$ErrMsg = __('The sales quantities could not be retrieved');
		$SalesResult = DB_query($SQL, $ErrMsg);
		$ListCount = DB_num_rows($SalesResult);
		$SalesRow = DB_fetch_array($SalesResult);

		if ($_POST['Location']=='All'){
			$LocationCode = 'ALL';
		} else {
			$LocationCode = $_POST['Location'];
		}

		// get the demand of the item
		$TotalDemand = GetDemand($InventoryPlan['stockid'], $LocationCode);

		// Get the QOO of the item
		$QOO = GetQuantityOnOrder($InventoryPlan['stockid'], $LocationCode);

		if ($_POST['NumberMonthsHolding']>10){
			$NumberMonths=$_POST['NumberMonthsHolding']-10;
			$MaxMthSales = ($SalesRow['prd1']+$SalesRow['prd2']+$SalesRow['prd3']+$SalesRow['prd4']+$SalesRow['prd5'])/5;
		}
		else{
			$NumberMonths=$_POST['NumberMonthsHolding'];
			$MaxMthSales = max($SalesRow['prd1'], $SalesRow['prd2'], $SalesRow['prd3'], $SalesRow['prd4'], $SalesRow['prd5']);
		}
		$IdealStockHolding = ceil($MaxMthSales * $NumberMonths);

		$SuggestedTopUpOrder = $IdealStockHolding - $InventoryPlan['qoh'] + $TotalDemand - $QOO;
		if ($SuggestedTopUpOrder <=0){
			$TopUpOrder = '   ';
		} else {
			$TopUpOrder = locale_number_format($SuggestedTopUpOrder,0);
		}

		$HTML .= '<tr class="striped_row">
					<td>' . $InventoryPlan['stockid'] . '</td>
					<td>' . $InventoryPlan['description'] . '</td>
					<td class="number">' . locale_number_format($SalesRow['prd5'],0) . '</td>
					<td class="number">' . locale_number_format($SalesRow['prd4'],0) . '</td>
					<td class="number">' . locale_number_format($SalesRow['prd3'],0) . '</td>
					<td class="number">' . locale_number_format($SalesRow['prd2'],0) . '</td>
					<td class="number">' . locale_number_format($SalesRow['prd1'],0) . '</td>
					<td class="number">' . locale_number_format($SalesRow['prd0'],0) . '</td>
					<td class="number">' . locale_number_format($IdealStockHolding,0) . '</td>
					<td class="number">' . locale_number_format($InventoryPlan['qoh'],0) . '</td>
					<td class="number">' . locale_number_format($TotalDemand,0) . '</td>
					<td class="number">' . locale_number_format($QOO,0) . '</td>
					<td class="number">' . $TopUpOrder . '</td>
				</tr>';

	} /*end inventory valn while loop */

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
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
		$dompdf->stream($_SESSION['DatabaseName'] . '_InventoryPlanning_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Inventory Planning Report');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Inventory') . '" alt="" />' . ' ' . __('Inventory Planning Report') . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} elseif  (isset($_POST['Spreadsheet'])) { //send the data to a CSV

 /*Now figure out the inventory data to report for the category range under review
   need QOH, QOO, QDem, Sales Mth -1, Sales Mth -2, Sales Mth -3, Sales Mth -4*/
	if ($_POST['Location']=='All'){
		$SQL = "SELECT stockmaster.categoryid,
						stockmaster.description,
						stockcategory.categorydescription,
						locstock.stockid,
						SUM(locstock.quantity) AS qoh
					FROM locstock
					INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
						stockmaster,
						stockcategory
					WHERE locstock.stockid=stockmaster.stockid
					AND stockmaster.discontinued = 0
					AND stockmaster.categoryid=stockcategory.categoryid
					AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
					AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					GROUP BY stockmaster.categoryid,
						stockmaster.description,
						stockcategory.categorydescription,
						locstock.stockid,
						stockmaster.stockid
					ORDER BY stockmaster.categoryid,
						stockmaster.stockid";
	} else {
		$SQL = "SELECT stockmaster.categoryid,
					locstock.stockid,
					stockmaster.description,
					stockcategory.categorydescription,
					locstock.quantity  AS qoh
				FROM locstock
				INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1,
					stockmaster,
					stockcategory
				WHERE locstock.stockid=stockmaster.stockid
				AND stockmaster.discontinued = 0
				AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				AND stockmaster.categoryid=stockcategory.categoryid
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
				AND locstock.loccode = '" . $_POST['Location'] . "'
				ORDER BY stockmaster.categoryid,
					stockmaster.stockid";
	}
	$InventoryResult = DB_query($SQL);
	$CurrentPeriod = GetPeriod(Date($_SESSION['DefaultDateFormat']));
	$Periods = array();
	for ($i=0;$i<24;$i++) {
		$Periods[$i]['Period'] = $CurrentPeriod - $i;
		$Periods[$i]['Month'] = GetMonthText(Date('m',mktime(0,0,0,Date('m') - $i,Date('d'),Date('Y')))) .  ' ' . Date('Y',mktime(0,0,0,Date('m') - $i,Date('d'),Date('Y')));
	}
	$SQLStarter = "SELECT stockmoves.stockid,";
	for ($i=0;$i<24;$i++) {
		$SQLStarter .= "SUM(CASE WHEN prd='" . $Periods[$i]['Period'] . "' THEN -qty ELSE 0 END) AS prd" . $i . ' ';
		if ($i<23) {
			$SQLStarter .= ', ';
		}
	}
	$SQLStarter .= "FROM stockmoves
					INNER JOIN locationusers ON locationusers.loccode=stockmoves.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
					WHERE (type=10 OR type=11)
					AND stockmoves.hidemovt=0";
	if ($_POST['Location']!='All'){
		$SQLStarter .= " AND stockmoves.loccode ='" . $_POST['Location'] . "'";
	}

	$HTML = '';
	$HTML .= '<tr>
				<th>' . __('Category ID') . '</th>
				<th>' . __('Category Description') .'</th>
				<th>' . __('Stock ID') .'</th>
				<th>' . __('Description') .'</th>
				<th>' . __('QOH') . '</th>';
	for ($i=0;$i<24;$i++) {
		$HTML .= '<th>' . $Periods[$i]['Month'] . '</th>';
	}
	$HTML .= '</tr>';

	$Category ='';

	while ($InventoryPlan = DB_fetch_array($InventoryResult)){

		$SQL = $SQLStarter . " AND stockid='" . $InventoryPlan['stockid'] . "' GROUP BY stockmoves.stockid";
		$SalesResult = DB_query($SQL,__('The stock usage of this item could not be retrieved because'));

		if (DB_num_rows($SalesResult)==0) {
			$HTML .= '<tr>
						<td>' . $InventoryPlan['categoryid'] . '</td>
						<td>' . $InventoryPlan['categorydescription'] . '</td>
						<td>' . $InventoryPlan['stockid'] . '</td>
						<td>' . $InventoryPlan['description'] . '</td>
						<td>' . $InventoryPlan['qoh'] . '</td>
					</tr>';
		} else {
			$SalesRow = DB_fetch_array($SalesResult);
			$HTML .= '<tr>
						<td>' . $InventoryPlan['categoryid'] . '</td>
						<td>' . $InventoryPlan['categorydescription'] . '</td>
						<td>' . $InventoryPlan['stockid'] . '</td>
						<td>' . $InventoryPlan['description'] . '</td>
						<td>' . $InventoryPlan['qoh'] . '</td>';
			for ($i=0;$i<24;$i++) {
				$HTML .= '<td>' . $SalesRow['prd' .$i] . '</td>';
			}
			$CSVListing .= '</tr>';
		}

	}
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

	$File = 'InventoryPlanning-' . Date('Y-m-d'). '.' . 'ods';

	header('Content-Disposition: attachment;filename="' . $File . '"');
	header('Cache-Control: max-age=0');
	$reader = new \PhpOffice\PhpSpreadsheet\Reader\Html();
	$spreadsheet = $reader->loadFromString($HTML);

	$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Ods');
	$writer->save('php://output');

} else { /*The option to print PDF was not hit */

	$Title=__('Inventory Planning Reporting');
	include('includes/header.php');

	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
			<field>
				<label for="Categories">' . __('Select Inventory Categories') . ':</label>
				<select autofocus="autofocus" required="required" minlength="1" name="Categories[]" multiple="multiple">';
	$SQL = 'SELECT categoryid, categorydescription
			FROM stockcategory
			ORDER BY categorydescription';
	$CatResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($CatResult)) {
		if (isset($_POST['Categories']) AND in_array($MyRow['categoryid'], $_POST['Categories'])) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] .'</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Location">' . __('For Inventory in Location') . ':</label>
			<select name="Location">';

	$SQL = "SELECT locations.loccode, locationname FROM locations INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$LocnResult=DB_query($SQL);

	echo '<option value="All">' . __('All Locations') . '</option>';

	while ($MyRow=DB_fetch_array($LocnResult)){
		echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="NumberMonthsHolding">' . __('Stock Planning') . ':</label>
			<select name="NumberMonthsHolding">
				<option selected="selected" value="1">' . __('One Month MAX')  . '</option>
				<option value="1.5">' . __('One Month and a half MAX')  . '</option>
				<option value="2">' . __('Two Months MAX')  . '</option>
				<option value="2.5">' . __('Two Month and a half MAX')  . '</option>
				<option value="3">' . __('Three Months MAX')  . '</option>
				<option value="4">' . __('Four Months MAX')  . '</option>
				<option value="11">' . __('One Month AVG')  . '</option>
				<option value="11.5">' . __('One Month and a half AVG')  . '</option>
				<option value="12">' . __('Two Months AVG')  . '</option>
				<option value="12.5">' . __('Two Month and a half AVG')  . '</option>
				<option value="13">' . __('Three Months AVG')  . '</option>
				<option value="14">' . __('Four Months AVG')  . '</option>
			</select>
		</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
		<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		<input type="submit" name="Spreadsheet" title="Spreadsheet" value="' . __('Spreadsheet') . '" />
	</div>
	</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */
