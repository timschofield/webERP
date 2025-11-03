<?php

// Shows supply and demand for a part as determined by MRP

require(__DIR__ . '/includes/session.php');

// Use DomPDF for PDF generation
use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

if (isset($_POST['Select'])) {
	$_POST['Part']=$_POST['Select'];
	$_POST['PrintPDF']='Yes';
}

if (isset($_POST['PrintPDF']) && $_POST['Part'] != '') {

	// Load mrprequirements into $Requirements array
	$SQL = "SELECT mrprequirements.*,
				TRUNCATE(((TO_DAYS(daterequired) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
				TO_DAYS(daterequired) - TO_DAYS(CURRENT_DATE) AS datediff
			FROM mrprequirements
			WHERE part = '" . $_POST['Part'] ."'
			ORDER BY daterequired,whererequired";

	$ErrMsg = __('The MRP calculation must be run before this report will have any output. MRP requires set up of many parameters, including, EOQ, lead times, minimums, bills of materials, demand types, etc.');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_error_no() != 0) {
		$Errors = 1;
	}

	if (DB_num_rows($Result) == 0) {
		$Errors = 1;
		$Title = __('Print MRP Report Warning');
		include('includes/header.php');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	$Requirements = array();
	$WeeklyReq = array_fill(0, 28, 0);
	$PastDueReq = 0;
	$FutureReq = 0;
	$GrossReq = 0;

	while ($MyRow=DB_fetch_array($Result)) {
		array_push($Requirements,$MyRow);
		$GrossReq += $MyRow['quantity'];
		if ($MyRow['datediff'] < 0) {
			$PastDueReq += $MyRow['quantity'];
		} elseif ($MyRow['weekindex'] > 27) {
			$FutureReq += $MyRow['quantity'];
		} else {
			$WeeklyReq[$MyRow['weekindex']] += $MyRow['quantity'];
		}
	}

	// Load mrpsupplies into $Supplies array
	$SQL = "SELECT mrpsupplies.*,
				   TRUNCATE(((TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
				   TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE) AS datediff
			 FROM mrpsupplies
			 WHERE part = '" . $_POST['Part'] . "'
			 ORDER BY mrpdate";
	$Result = DB_query($SQL);
	if (DB_error_no() !=0) {
		$Errors = 1;
	}
	$Supplies = array();
	$WeeklySup = array_fill(0, 28, 0);
	$PastDueSup = 0;
	$FutureSup = 0;
	$QOH = 0; // Get quantity on Hand to display
	$OpenOrd = 0;
	while ($MyRow=DB_fetch_array($Result)) {
		if ($MyRow['ordertype'] == 'QOH') {
			$QOH += $MyRow['supplyquantity'];
		} else {
			$OpenOrd += $MyRow['supplyquantity'];
			if ($MyRow['datediff'] < 0) {
				$PastDueSup += $MyRow['supplyquantity'];
			} elseif ($MyRow['weekindex'] > 27) {
				$FutureSup += $MyRow['supplyquantity'];
			} else {
				$WeeklySup[$MyRow['weekindex']] += $MyRow['supplyquantity'];
			}
		}
		array_push($Supplies,$MyRow);
	}

	// Load planned orders
	$SQL = "SELECT mrpplannedorders.*,
				   TRUNCATE(((TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
				   TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE) AS datediff
				FROM mrpplannedorders WHERE part = '" . $_POST['Part'] . "' ORDER BY mrpdate";
	$Result = DB_query($SQL,'','',false);
	if (DB_error_no() !=0) {
		$Errors = 1;
	}

	$WeeklyPlan = array_fill(0, 28, 0);
	$PastDuePlan = 0;
	$FuturePlan = 0;
	while ($MyRow=DB_fetch_array($Result)) {
		array_push($Supplies,$MyRow);
		if ($MyRow['datediff'] < 0) {
			$PastDuePlan += $MyRow['supplyquantity'];
		} elseif ($MyRow['weekindex'] > 27) {
			$FuturePlan += $MyRow['supplyquantity'];
		} else {
			$WeeklyPlan[$MyRow['weekindex']] += $MyRow['supplyquantity'];
		}
	}

	foreach ($Supplies as $key => $Row) {
		$MRPDate[$key] = $Row['mrpdate'];
	}

	if (isset($Errors)) {
		$Title = __('MRP Report') . ' - ' . __('Problem Report');
		include('includes/header.php');
		prnMsg( __('The MRP Report could not be retrieved'), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	if (count($Supplies)) {
		array_multisort($MRPDate, SORT_ASC, $Supplies);
	}

	// Get and display part information
	$SQL = "SELECT levels.*,
				   stockmaster.description,
				   stockmaster.lastcost,
				   stockmaster.decimalplaces,
				   stockmaster.mbflag
				   FROM levels
			LEFT JOIN stockmaster
			ON levels.part = stockmaster.stockid
			WHERE part = '" . $_POST['Part'] . "'";
	$Result = DB_query($SQL,'','',false);
	$MyRow=DB_fetch_array($Result);

	// Calculate fields for projected available weekly buckets
	$PlannedAccum = array();
	$PastDueAvail = ($QOH + $PastDueSup + $PastDuePlan) - $PastDueReq;
	$WeeklyAvail = array();
	$WeeklyAvail[0] = ($PastDueAvail + $WeeklySup[0] + $WeeklyPlan[0]) - $WeeklyReq[0];
	$PlannedAccum[0] = $PastDuePlan + $WeeklyPlan[0];
	for ($i = 1; $i < 28; $i++) {
		$WeeklyAvail[$i] = ($WeeklyAvail[$i - 1] + $WeeklySup[$i] + $WeeklyPlan[$i]) - $WeeklyReq[$i];
		$PlannedAccum[$i] = $PlannedAccum[$i-1] + $WeeklyPlan[$i];
	}
	$FutureAvail = ($WeeklyAvail[27] + $FutureSup + $FuturePlan) - $FutureReq;
	$FuturePlannedaccum = $PlannedAccum[27] + $FuturePlan;

	// Prepare the HTML content
	$HTML = '';
	$HTML .= '<html>
	<head>
		<link href="css/reports.css" rel="stylesheet" type="text/css" />
		<style>
			body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
			table { border-collapse: collapse; width: 100%; margin-bottom: 18px; }
			th, td { border: 1px solid #666; padding: 4px 6px; font-size: 11px; }
			th { background: #e0e8f8; }
			.section { font-weight: bold; background: #eee; }
			.header-table td { border: none; }
		</style>
	</head>
	<body>
		<h2 style="text-align: center;">'.htmlspecialchars($_SESSION['CompanyRecord']['coyname']).'</h2>
		<h3 style="text-align: center;">MRP Report</h3>
		<table class="header-table">
			<tr>
				<td><b>Part:</b></td>
				<td>'.htmlspecialchars($MyRow['part']).'</td>
				<td><b>EOQ:</b></td>
				<td class="number">'.locale_number_format($MyRow['eoq'],$MyRow['decimalplaces']).'</td>
				<td><b>On Hand:</b></td>
				<td class="number">'.locale_number_format($QOH,$MyRow['decimalplaces']).'</td>
			</tr>
			<tr>
				<td><b>Description:</b></td>
				<td colspan="3">'.html_entity_decode($MyRow['description']).'</td>
				<td><b>On Order:</b></td>
				<td class="number">'.locale_number_format($OpenOrd,$MyRow['decimalplaces']).'</td>
			</tr>
			<tr>
				<td><b>M/B:</b></td>
				<td>'.htmlspecialchars($MyRow['mbflag']).'</td>
				<td><b>Shrinkage:</b></td>
				<td class="number">'.locale_number_format($MyRow['shrinkfactor'],$MyRow['decimalplaces']).'</td>
				<td><b>Gross Req:</b></td>
				<td class="number">'.locale_number_format($GrossReq,$MyRow['decimalplaces']).'</td>
			</tr>
			<tr>
				<td><b>Lead Time:</b></td>
				<td>'.htmlspecialchars($MyRow['leadtime']).'</td>
				<td><b>Last Cost:</b></td>
				<td class="number">'.locale_number_format($MyRow['lastcost'],2).'</td>
				<td></td>
				<td></td>
			</tr>
		</table>';

	// Weekly Buckets Table
	$HTML .= '<table>
		<tr>
			<th></th>';
	$Dateformat = $_SESSION['DefaultDateFormat'];
	$Today = date($Dateformat);
	$HTML .= '<th>Past Due</th>';
	for ($i=0; $i<9; $i++) {
		$HTML .= '<th>'.htmlspecialchars(DateAdd($Today,'w',$i)).'</th>';
	}
	$HTML .= '</tr>
		<tr><td class="section">Gross Reqts</td><td class="number">'.locale_number_format($PastDueReq,0).'</td>';
	for ($i=0; $i<9; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklyReq[$i],0).'</td>';
	$HTML .= '</tr>
		<tr><td class="section">Open Order</td><td class="number">'.locale_number_format($PastDueSup,0).'</td>';
	for ($i=0; $i<9; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklySup[$i],0).'</td>';
	$HTML .= '</tr>
		<tr><td class="section">Planned</td><td class="number">'.locale_number_format($PastDuePlan,0).'</td>';
	for ($i=0; $i<9; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklyPlan[$i],0).'</td>';
	$HTML .= '</tr>
		<tr><td class="section">Proj Avail</td><td class="number">'.locale_number_format($PastDueAvail,0).'</td>';
	for ($i=0; $i<9; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklyAvail[$i],0).'</td>';
	$HTML .= '</tr>
		<tr><td class="section">Planned Acc</td><td class="number">'.locale_number_format($PastDuePlan,0).'</td>';
	for ($i=0; $i<9; $i++) $HTML .= '<td class="number">'.locale_number_format($PlannedAccum[$i],0).'</td>';
	$HTML .= '</tr></table>';

	$HTML .= '<table>
		<tr>
			<th></th>';
	for ($i=9; $i<19; $i++) {
		$HTML .= '<th>'.htmlspecialchars(DateAdd($Today,'w',$i)).'</th>';
	}
	$HTML .= '</tr>
		<tr><td class="section">Gross Reqts</td>';
	for ($i=9; $i<19; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklyReq[$i],0).'</td>';
	$HTML .= '</tr>
		<tr><td class="section">Open Order</td>';
	for ($i=9; $i<19; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklySup[$i],0).'</td>';
	$HTML .= '</tr>
		<tr><td class="section">Planned</td>';
	for ($i=9; $i<19; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklyPlan[$i],0).'</td>';
	$HTML .= '</tr>
		<tr><td class="section">Proj Avail</td>';
	for ($i=9; $i<19; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklyAvail[$i],0).'</td>';
	$HTML .= '</tr>
		<tr><td class="section">Planned Acc</td>';
	for ($i=9; $i<19; $i++) $HTML .= '<td class="number">'.locale_number_format($PlannedAccum[$i],0).'</td>';
	$HTML .= '</tr></table>';

	$HTML .= '<table>
		<tr>
			<th></th>';
	for ($i=19; $i<28; $i++) {
		$HTML .= '<th>'.htmlspecialchars(DateAdd($Today,'w',$i)).'</th>';
	}
	$HTML .= '<th>Future</th></tr>
		<tr><td class="section">Gross Reqts</td>';
	for ($i=19; $i<28; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklyReq[$i],0).'</td>';
	$HTML .= '<td class="number">'.locale_number_format($FutureReq,0).'</td></tr>
		<tr><td class="section">Open Order</td>';
	for ($i=19; $i<28; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklySup[$i],0).'</td>';
	$HTML .= '<td class="number">'.locale_number_format($FutureSup,0).'</td></tr>
		<tr><td class="section">Planned</td>';
	for ($i=19; $i<28; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklyPlan[$i],0).'</td>';
	$HTML .= '<td class="number">'.locale_number_format($FuturePlan,0).'</td></tr>
		<tr><td class="section">Proj Avail</td>';
	for ($i=19; $i<28; $i++) $HTML .= '<td class="number">'.locale_number_format($WeeklyAvail[$i],0).'</td>';
	$HTML .= '<td class="number">'.locale_number_format($FutureAvail,0).'</td></tr>
		<tr><td class="section">Planned Acc</td>';
	for ($i=19; $i<28; $i++) $HTML .= '<td class="number">'.locale_number_format($PlannedAccum[$i],0).'</td>';
	$HTML .= '<td class="number">'.locale_number_format($FuturePlannedaccum,0).'</td></tr>
	</table>';

	// Demand/Supply details
	$HTML .= '<h4>Demand / Supply Details</h4>
	<table>
		<tr>
			<th colspan="5">' . _('Demand') . '</th>
			<th colspan="6">' . _('Supply') . '</th>
		</tr>
		<tr>
			<th>Dem Type</th>
			<th>Where Required</th>
			<th>Order</th>
			<th>Quantity</th>
			<th>Due Date</th>
			<th>Order No.</th>
			<th>Sup Type</th>
			<th>For</th>
			<th>Quantity</th>
			<th>Due Date</th>
			<th>MRP Date</th>
		</tr>';

	$i = 0;
	while ((isset($Supplies[$i]) && mb_strlen($Supplies[$i]['part']) > 1)
		|| (isset($Requirements[$i]) && mb_strlen($Requirements[$i]['part']) > 1)) {

		$HTML .= '<tr>';
		// Demand
		if (isset($Requirements[$i]['part']) && mb_strlen($Requirements[$i]['part']) > 1) {
			$FormatedReqDueDate = ConvertSQLDate($Requirements[$i]['daterequired']);
			$HTML .= '<td>' . htmlspecialchars($Requirements[$i]['mrpdemandtype']) . '</td>
				<td>' . htmlspecialchars($Requirements[$i]['whererequired']) . '</td>
				<td>' . htmlspecialchars($Requirements[$i]['orderno']) . '</td>
				<td class="number">' . locale_number_format($Requirements[$i]['quantity'],$MyRow['decimalplaces']) . '</td>
				<td>' . htmlspecialchars($FormatedReqDueDate) . '</td>';
		} else {
			$HTML .= '<td colspan="5"></td>';
		}
		// Supply
		if (isset($Supplies[$i]) && mb_strlen($Supplies[$i]['part']) > 1) {
			$SupType = $Supplies[$i]['ordertype'];
			if ($SupType == 'QOH' || $SupType == 'PO' || $SupType == 'WO') {
				$DisplayType = $SupType;
				$ForType = ' ';
			} else {
				$DisplayType = 'Planned';
				$ForType = $SupType;
			}
			$FormatedSupDueDate = ConvertSQLDate($Supplies[$i]['duedate']);
			$FormatedSupMRPDate = ConvertSQLDate($Supplies[$i]['mrpdate']);
			$OrderNo = ($SupType == 'QOH' OR $SupType == 'REORD') ? ' ' : $Supplies[$i]['orderno'];
			$HTML .= '<td>' . htmlspecialchars($OrderNo) . '</td>
				<td>' . htmlspecialchars($DisplayType) . '</td>
				<td>' . htmlspecialchars($ForType) . '</td>
				<td class="number">' . locale_number_format($Supplies[$i]['supplyquantity'],$MyRow['decimalplaces']) . '</td>
				<td>' . htmlspecialchars($FormatedSupDueDate) . '</td>
				<td>' . htmlspecialchars($FormatedSupMRPDate) . '</td>';
		} else {
			$HTML .= '<td colspan="6"></td>';
		}
		$HTML .= '</tr>';
		$i++;
	}
	$HTML .= '</table>
	</body></html>';

	// Generate PDF with DomPDF
	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);
	$DomPDF->setPaper($_SESSION['PageSize'], 'landscape');
	$DomPDF->render();
	$DomPDF->stream($_SESSION['DatabaseName'] . '_MRPReport_' . date('Y-m-d') . '.pdf', array("Attachment" => false));

} else { /*The option to print PDF was not hit so display form */

	$Title=__('MRP Report');
	$ViewTopic = 'MRP';
	$BookMark = '';
	include('includes/header.php');

	if (isset($_POST['PrintPDF'])) {
		prnMsg(__('This report shows the MRP calculation for a specific item - a part code must be selected'),'warn');
	}
	// Always show the search facilities
	$SQL = "SELECT categoryid,
					categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	if (DB_num_rows($Result1) == 0) {
		echo '<p class="bad">' . __('Problem Report') . ':<br />' . __('There are no stock categories currently defined please use the link below to set them up');
		echo '<a href="' . $RootPath . '/StockCategories.php">' . __('Define Stock Categories') . '</a>';
		exit();
	}

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Search for Inventory Items') . '</p>
		<fieldset>
			<legend>', __('Search Criteria'), '</legend>
			<field>
				<label for="StockCat">' . __('In Stock Category') . ':</label>
				<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = '';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	} else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	echo '<field>
			<label for="Keywords">' . __('Enter partial') . '<b> ' . __('Description') . '</b>:</label>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" autofocus="autofocus" name="Keywords" size="20" maxlength="25" />';
	}
	echo '</field>';

	echo '<field>
			<label for="StockCode">' . '<b>' . __('OR') . ' </b>' . __('Enter partial') . ' <b>' . __('Stock Code') . '</b>:</label>';
	if (isset($_POST['StockCode'])) {
		echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="StockCode" size="15" maxlength="18" />';
	}
	echo '</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Search" value="' . __('Search Now') . '" />
		</div>
		</form>';
	if (!isset($_POST['Search'])) {
		include('includes/footer.php');
	}

} /*end of else not PrintPDF */
// query for list of record(s)
if (isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	$_POST['Search']='Search';
}
if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg( __('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.description " . LIKE . " '".$SearchString."'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND description " . LIKE . " '".$SearchString."'
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		}
	} elseif (isset($_POST['StockCode'])) {
		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					sum(locstock.quantity) as qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		}
	} elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		}
	}
	$ErrMsg = __('No stock items were returned by the SQL because');
	$SearchResult = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(__('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if (isset($SearchResult) AND !isset($_POST['Select'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($SearchResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre">
					<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . __('of') . ' ' . $ListPageMax . ' ' . __('pages') . '. ' . __('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				} else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go" value="' . __('Go') . '" />
				<input type="submit" name="Previous" value="' . __('Previous') . '" />
				<input type="submit" name="Next" value="' . __('Next') . '" />
				<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'" />
				<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'" />
				<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'" />
				</div>';
		}
		echo '<table class="selection">';
		$Tableheader = '<tr>
							<th>' . __('Code') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . __('Total Qty On Hand') . '</th>
							<th>' . __('Units') . '</th>
							<th>' . __('Stock Status') . '</th>
						</tr>';
		echo $Tableheader;
		$j = 1;
		$RowIndex = 0;
		if (DB_num_rows($SearchResult) <> 0) {
			DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($MyRow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($MyRow['mbflag'] == 'D') {
				$QOH = 'N/A';
			} else {
				$QOH = locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']);
			}
			echo '<tr class="striped_row">
				<td><input type="submit" name="Select" value="'.$MyRow['stockid']. '" /></td>
				<td>' . $MyRow['description'] . '</td>
				<td class="number">' . $QOH . '</td>
				<td>' . $MyRow['units'] . '</td>
				<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] .'">' . __('View') . '</a></td>
				</tr>';
			$j++;
			if ($j == 20 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $Tableheader;
			}
			$RowIndex = $RowIndex + 1;
			//end of page full new headings if
		}
		//end of while loop
		echo '</table>
			</form>';
	}

	include('includes/footer.php');
}
/* end display list if there is more than one record */

function PrintHeader($pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin) {

	$LineHeight=12;
	/*PDF page header for MRP Report */
	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$LineHeight;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,__('MRP Report'));
	$pdf->addTextWrap($Page_Width-$Right_Margin-110,$YPos,160,$FontSize,__('Printed') . ': ' .
		 date($_SESSION['DefaultDateFormat']) . '   ' . __('Page') . ' ' . $PageNumber,'left');

	$YPos -=(2*$LineHeight);

	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$FontSize=8;
	$YPos =$YPos - (2*$LineHeight);
	$PageNumber++;

} // End of PrintHeader function
