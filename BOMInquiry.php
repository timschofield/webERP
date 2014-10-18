<?php

/* $Id: BOMInquiry.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Costed Bill Of Material');
include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID =trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID =trim(mb_strtoupper($_POST['StockID']));
}

if (!isset($_POST['StockID'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
        <div>
		<br />
		<div class="page_help_text">
			'. _('Select a manufactured part') . ' (' . _('or Assembly or Kit part') . ') ' . _('to view the costed bill of materials') . '
			<br />' . _('Parts must be defined in the stock item entry') . '/' . _('modification screen as manufactured') . ', ' . _('kits or assemblies to be available for construction of a bill of material') . '
		</div>
		<br />
		<table class="selection">
		<tr>
			<td>' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</td>
			<td><input tabindex="1" type="text" autofocus="autofocus" name="Keywords" size="20" maxlength="25" /></td>
			<td><b>' . _('OR') . '</b></td>
			<td>' . _('Enter extract of the') . ' <b>' . _('Stock Code') . '</b>:</td>
			<td><input tabindex="2" type="text" name="StockCode" size="15" maxlength="20" /></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input tabindex="3" type="submit" name="Search" value="' . _('Search Now') . '" />
		</div>
		<br />
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
}

if (isset($_POST['Search'])){
	// Work around to auto select
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$_POST['StockCode']='%';
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg( _('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
	}
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		prnMsg( _('At least one stock description keyword or an extract of a stock code must be entered for the search'), 'info' );
	} else {
		if (mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.mbflag,
							SUM(locstock.quantity) as totalonhand
					FROM stockmaster INNER JOIN locstock
					ON stockmaster.stockid = locstock.stockid
					WHERE stockmaster.description " . LIKE . " '$SearchString'
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
			$sql = "SELECT stockmaster.stockid,
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

		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$result = DB_query($sql,$db,$ErrMsg);

	} //one of keywords or StockCode was more than a zero length string
} //end of if search

if (isset($_POST['Search'])
	AND isset($result)
	AND !isset($SelectedParent)) {

	echo '<br />
			<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('On Hand') . '</th>
						<th>' . _('Units') . '</th>
					</tr>';

	echo $TableHeader;

	$j = 1;
	$k = 0; //row colour counter
	while ($myrow=DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		if ($myrow['mbflag']=='A' OR $myrow['mbflag']=='K'){
			$StockOnHand = 'N/A';
		} else {
			$StockOnHand = locale_number_format($myrow['totalonhand'],2);
		}
		$tabindex=$j+4;
		printf('<td><input tabindex="' .$tabindex . '" type="submit" name="StockID" value="%s" /></td>
		        <td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>',
				$myrow['stockid'],
				$myrow['description'],
				$StockOnHand,
				$myrow['units'] );
		$j++;
//end of page full new headings if
	}
//end of while loop

	echo '</table><br />';
}
if (!isset($_POST['StockID'])) {
    echo '</div>
          </form>';
}

if (isset($StockID) and $StockID!=""){

	$result = DB_query("SELECT description,
								units,
								labourcost,
								overheadcost
						FROM stockmaster
						WHERE stockid='" . $StockID  . "'",
						$db);
	$myrow = DB_fetch_array($result);
	$ParentLabourCost = $myrow['labourcost'];
	$ParentOverheadCost = $myrow['overheadcost'];

	$sql = "SELECT bom.parent,
					bom.component,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.materialcost+ stockmaster.labourcost+stockmaster.overheadcost as standardcost,
					bom.quantity,
					bom.quantity * (stockmaster.materialcost+ stockmaster.labourcost+ stockmaster.overheadcost) AS componentcost
			FROM bom INNER JOIN stockmaster
			ON bom.component = stockmaster.stockid
			WHERE bom.parent = '" . $StockID . "'
			AND bom.effectiveafter < '" . Date('Y-m-d') . "'
			AND (bom.effectiveto > '" . Date('Y-m-d') . "' 
			 OR bom.effectiveto='0000-00-00')";
			 
	$ErrMsg = _('The bill of material could not be retrieved because');
	$BOMResult = DB_query ($sql,$db,$ErrMsg);

	if (DB_num_rows($BOMResult)==0){
		prnMsg(_('The bill of material for this part is not set up') . ' - ' . _('there are no components defined for it'),'warn');
	} else {
		echo '<a href="'.$RootPath.'/index.php">' . _('Return to Main Menu') . '</a>';
		echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
				</p>
				<br />';

		echo '<table class="selection">';
		echo '<tr>
				<th colspan="5">
					<div class="centre"><b>' . $myrow[0] . ' : ' . _('per') . ' ' . $myrow[1] . '</b>
					</div></th>
			</tr>';
		$TableHeader = '<tr>
							<th>' . _('Component') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('Quantity') . '</th>
							<th>' . _('Unit Cost') . '</th>
							<th>' . _('Total Cost') . '</th>
						</tr>';
		echo $TableHeader;

		$j = 1;
		$k=0; //row colour counter

		$TotalCost = 0;

		while ($myrow=DB_fetch_array($BOMResult)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			$ComponentLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['component'] . '">' . $myrow['component'] . '</a>';

			/* Component Code  Description  Quantity Std Cost  Total Cost */
			printf('<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%.2f</td>
					<td class="number">%.2f</td>
					</tr>',
					$ComponentLink,
					$myrow['description'],
					locale_number_format($myrow['quantity'],$myrow['decimalplaces']),
					$myrow['standardcost'],
					$myrow['componentcost']);

			$TotalCost += $myrow['componentcost'];

			$j++;
		}

		$TotalCost += $ParentLabourCost;
		echo '<tr>
			<td colspan="4" class="number"><b>' . _('Labour Cost') . '</b></td>
			<td class="number"><b>' . locale_number_format($ParentLabourCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td></tr>';
		$TotalCost += $ParentOverheadCost;
		echo '<tr><td colspan="4" class="number"><b>' . _('Overhead Cost') . '</b></td>
			<td class="number"><b>' . locale_number_format($ParentOverheadCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td></tr>';

		echo '<tr>
				<td colspan="4" class="number"><b>' . _('Total Cost') . '</b></td>
				<td class="number"><b>' . locale_number_format($TotalCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
			</tr>';

		echo '</table>';
	}
} else { //no stock item entered
	prnMsg(_('Enter a stock item code above') . ', ' . _('to view the costed bill of material for'),'info');
}

include('includes/footer.inc');
?>