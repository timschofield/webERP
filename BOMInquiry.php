<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Costed Bill Of Material');
$ViewTopic = 'Manufacturing';
$BookMark = '';
include('includes/header.php');

if (isset($_GET['StockID'])){
	$StockID =trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID =trim(mb_strtoupper($_POST['StockID']));
}

if (!isset($_POST['StockID'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		<div class="page_help_text">
			'. __('Select a manufactured part') . ' (' . __('or Assembly or Kit part') . ') ' . __('to view the costed bill of materials') . '
			<br />' . __('Parts must be defined in the stock item entry') . '/' . __('modification screen as manufactured') . ', ' . __('kits or assemblies to be available for construction of a bill of material') . '
		</div>
		<fieldset>
			<legend>', __('Report Criteria'), '</legend>
		<field>
			<label for="Keywords">' . __('Enter text extracts in the') . ' <b>' . __('description') . '</b>:</label>
			<input tabindex="1" type="text" autofocus="autofocus" name="Keywords" size="20" maxlength="25" />
		</field>
			<b>' . __('OR') . ' </b>
		<field>
			<label for="StockCode">' . __('Enter extract of the') . ' <b>' . __('Stock Code') . '</b>:</label>
			<input tabindex="2" type="text" name="StockCode" size="15" maxlength="20" />
		</field>
		</fieldset>
		<div class="centre">
			<input tabindex="3" type="submit" name="Search" value="' . __('Search Now') . '" />
		</div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
}

if (isset($_POST['Search'])){
	// Work around to auto select
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$_POST['StockCode']='%';
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg( __('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
	}
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		prnMsg( __('At least one stock description keyword or an extract of a stock code must be entered for the search'), 'info' );
	} else {
		if (mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.mbflag,
							SUM(locstock.quantity) as totalonhand
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
					WHERE stockmaster.description " . LIKE . "'" . $SearchString . "'
					AND (stockmaster.mbflag='M'
						OR stockmaster.mbflag='K'
						OR stockmaster.mbflag='A'
						OR stockmaster.mbflag='G')
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.mbflag
					ORDER BY stockmaster.stockid";

		} elseif (mb_strlen($_POST['StockCode'])>0){
			$SQL = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.mbflag,
							sum(locstock.quantity) as totalonhand
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
					WHERE stockmaster.stockid " . LIKE  . "'%" . $_POST['StockCode'] . "%'
					AND (stockmaster.mbflag='M'
						OR stockmaster.mbflag='K'
						OR stockmaster.mbflag='G'
						OR stockmaster.mbflag='A')
					GROUP BY stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.mbflag
					ORDER BY stockmaster.stockid";

		}

		$ErrMsg = __('The SQL to find the parts selected failed with the message');
		$Result = DB_query($SQL, $ErrMsg);

	} //one of keywords or StockCode was more than a zero length string
} //end of if search

if (isset($_POST['Search'])
	AND isset($Result)
	AND !isset($SelectedParent)) {

	echo '<table class="selection">
			<tr>
				<th>' . __('Code') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('On Hand') . '</th>
				<th>' . __('Units') . '</th>
			</tr>';

	$j = 1;

	while ($MyRow=DB_fetch_array($Result)) {
		if ($MyRow['mbflag']=='A' OR $MyRow['mbflag']=='K'){
			$StockOnHand = 'N/A';
		} else {
			$StockOnHand = locale_number_format($MyRow['totalonhand'],2);
		}
		$TabIndex=$j+4;
		echo'<tr class="striped_row">
				<td><input tabindex="' .$TabIndex . '" type="submit" name="StockID" value="', $MyRow['stockid'], '" /></td>
		        <td>', $MyRow['description'], '</td>
				<td class="number">', $StockOnHand, '</td>
				<td>', $MyRow['units'], '</td>
			</tr>';
		$j++;
//end of page full new headings if
	}
//end of while loop

	echo '</table>';
}
if (!isset($_POST['StockID'])) {
    echo '</form>';
}

if (isset($StockID) and $StockID!=""){

	$Result = DB_query("SELECT description,
								units,
								labourcost,
								overheadcost
						FROM stockmaster
						WHERE stockid='" . $StockID  . "'");
	$MyRow = DB_fetch_array($Result);
	$ParentLabourCost = $MyRow['labourcost'];
	$ParentOverheadCost = $MyRow['overheadcost'];

	$SQL = "SELECT bom.parent,
					bom.component,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.actualcost as standardcost,
					bom.quantity,
					bom.quantity * (stockmaster.actualcost) AS componentcost
			FROM bom INNER JOIN stockmaster
			ON bom.component = stockmaster.stockid
			WHERE bom.parent = '" . $StockID . "'
            AND bom.effectiveafter <= CURRENT_DATE
            AND bom.effectiveto > CURRENT_DATE";

	$ErrMsg = __('The bill of material could not be retrieved because');
	$BOMResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($BOMResult)==0){
		prnMsg(__('The bill of material for this part is not set up') . ' - ' . __('there are no components defined for it'),'warn');
	} else {
		echo '<a class="toplink" href="'.$RootPath.'/BOMInquiry.php">' . __('Select another BOM') . '</a>';
		echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title.'
			</p>';

		echo '<table class="selection">';
		echo '<tr>
				<th colspan="5">
					<b>' . $MyRow[0] . ' : ' . __('per') . ' ' . $MyRow[1] . '</b>
				</th>
			</tr>
			<tr>
				<th>' . __('Component') . '</th>
				<th>' . __('Description') . '</th>
				<th>' . __('Quantity') . '</th>
				<th>' . __('Unit Cost') . '</th>
				<th>' . __('Total Cost') . '</th>
			</tr>';

		$j = 1;

		$TotalCost = 0;

		while ($MyRow=DB_fetch_array($BOMResult)) {

			$ComponentLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['component'] . '">' . $MyRow['component'] . '</a>';

			/* Component Code  Description  Quantity Std Cost  Total Cost */
			echo '<tr class="striped_row">
					<td>', $ComponentLink, '</td>
					<td>', $MyRow['description'], '</td>
					<td class="number">', locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']), '</td>
					<td class="number">', locale_number_format($MyRow['standardcost'],$_SESSION['CompanyRecord']['decimalplaces'] + 2), '</td>
					<td class="number">', locale_number_format($MyRow['componentcost'],$_SESSION['CompanyRecord']['decimalplaces'] + 2), '</td>
				</tr>';

			$TotalCost += $MyRow['componentcost'];

		}

		$TotalCost += $ParentLabourCost;
		echo '<tr class="total_row">
				<td colspan="4" class="number"><b>' . __('Labour Cost') . '</b></td>
				<td class="number"><b>' . locale_number_format($ParentLabourCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
			</tr>';
		$TotalCost += $ParentOverheadCost;
		echo '<tr class="total_row">
				<td colspan="4" class="number"><b>' . __('Overhead Cost') . '</b></td>
				<td class="number"><b>' . locale_number_format($ParentOverheadCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
			</tr>';

		echo '<tr class="total_row">
				<td colspan="4" class="number"><b>' . __('Total Cost') . '</b></td>
				<td class="number"><b>' . locale_number_format($TotalCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
			</tr>';

		echo '</table>';
	}
} else { //no stock item entered
	prnMsg(__('Enter a stock item code below') . ', ' . __('to view the costed bill of material for'),'info');
}

include('includes/footer.php');
