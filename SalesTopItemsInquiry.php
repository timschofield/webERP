<?php

/* $Id: SalesTopItemsInquiry.php 4261 2010-12-22 15:56:50Z  $*/

include('includes/session.inc');
$title = _('Sales Category Report');
include('includes/header.inc');
include('includes/DefineCartClass.php');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Sales Report') . '" alt="" />' . ' ' . _('Sales Category Report') . '</p>';
echo '<div class="page_help_text">' . _('Select the parameters for the report') . '</div><br>';

if (!isset($_POST['DateRange'])){
	/* then assume report is for This Month - maybe wrong to do this but hey better than reporting an error?*/
	$_POST['DateRange']='ThisMonth';
}

echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table cellpadding=2 class="selection">
		<tr><td valign=top>
		<table>';
	
echo '<tr><th colspan="2" class="centre">' . _('Date Selection') . '</th>
		</tr>
	<tr>
		<td>' . _('Custom Range') . ':</td>
		<td><input type="radio" name="DateRange" value="Custom" ';
if ($_POST['DateRange']=='Custom'){
	echo 'checked';
}
echo	'></td>
		</tr>
	<tr>
		<td>' . _('This Week') . ':</td>
		<td><input type="radio" name="DateRange" value="ThisWeek" ';
if ($_POST['DateRange']=='ThisWeek'){
	echo 'checked';
}
echo	'></td>
		</tr>
	<tr>
		<td>' . _('This Month') . ':</td>
		<td><input type="radio" name="DateRange" value="ThisMonth" ';
if ($_POST['DateRange']=='ThisMonth'){
	echo 'checked';
}
echo	'></td>
		</tr>
	<tr>
		<td>' . _('This Quarter') . ':</td>
		<td><input type="radio" name="DateRange" value="ThisQuarter" ';
if ($_POST['DateRange']=='ThisQuarter'){
	echo 'checked';
}
echo	'></td>
		</tr>';
if ($_POST['DateRange']=='Custom'){
	echo '<tr>
			<td>' . _('Date From') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" value="' . $_POST['FromDate'] . '" /></td>
			</tr>';
	echo '<tr>
			<td>' . _('Date To') . ':</td>
			<td><input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
			</tr>';
}
echo '</table></td>
		<td valign=top>
		<table>'; //new sub table to set parameters for order of display


if (!isset($_POST['OrderBy'])){ //default to order by net sales
	$_POST['OrderBy']='NetSales';
}
echo '<tr><th colspan="2" class="centre">' . _('Display') . '</th>
		</tr>
	<tr>
		<td>' . _('Order By Net Sales') . ':</td>
		<td><input type="radio" name="OrderBy" value="NetSales" ';
if ($_POST['OrderBy']=='NetSales'){
	echo 'checked';
}
echo	'></td>
		</tr>
		<tr>
		<td>' . _('Order By Quantity') . ':</td>
		<td><input type="radio" name="OrderBy" value="Quantity" ';
if ($_POST['OrderBy']=='Quantity'){
	echo 'checked';
}
if (!isset($_POST['NoToDisplay'])){
	$_POST['NoToDisplay']=20;
}
echo	'></td>
		</tr>
		<tr>
		<td>' . _('Number to Display') . ':</td>
		<td><input type="text class="number" name="NoToDisplay" size="4" maxlength="4" value="' . $_POST['NoToDisplay'] .'" ></td>
		</tr>
	</table>
	</td></tr>
	</table>';


echo '<br /><div class="centre"><input tabindex=4 type=submit name="ShowSales" value="' . _('Show Sales') . '">';
echo '</form></div>';
echo '<br />';

if ($_POST['DateRange']=='Custom' AND !isset($_POST['FromDate']) AND !isset($_POST['ToDate'])){
	//Don't run the report until custom dates entered
	unset($_POST['ShowSales']);
}

if (isset($_POST['ShowSales'])){
	$InputError=0; //assume no input errors now test for errors
	if ($_POST['DateRange']=='Custom'){
		if (!Is_Date($_POST['FromDate'])){
			$InputError = 1;
			prnMsg(_('The date entered for the from date is not in the appropriate format. Dates must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		}
		if (!Is_Date($_POST['ToDate'])){
			$InputError = 1;
			prnMsg(_('The date entered for the to date is not in the appropriate format. Dates must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
		}
		if (Date1GreaterThanDate2($_POST['FromDate'],$_POST['ToDate'])){
			$InputError = 1;
			prnMsg(_('The from date is expected to be a date prior to the to date. Please review the selected date range'),'error');
		}
	}
	switch ($_POST['DateRange']) {
		case 'ThisWeek':
			$FromDate = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y')));
			$ToDate = date('Y-m-d');
			break;
		case 'ThisMonth':
			$FromDate = date('Y-m-d',mktime(0,0,0,date('m'),1,date('Y')));
			$ToDate = date('Y-m-d');
			break;
		case 'ThisQuarter':
			switch (date('m')) {
				case 1:
				case 2:
				case 3:
					$QuarterStartMonth=1;
					break;
				case 4:
				case 5:
				case 6:
					$QuarterStartMonth=4;
					break;
				case 7:
				case 8:
				case 9:
					$QuarterStartMonth=7;
					break;
				default:
					$QuarterStartMonth=10;
			}
			$FromDate = date('Y-m-d',mktime(0,0,0,$QuarterStartMonth,1,date('Y')));
			$ToDate = date('Y-m-d');
			break;
		case 'Custom':
			$FromDate = FormatDateForSQL($_POST['FromDate']);
			$ToDate = FormatDateForSQL($_POST['ToDate']);
	}
	$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockcategory.categorydescription,
					SUM(CASE WHEN stockmoves.type=10 
							OR stockmoves.type=11 THEN 
							 -qty
							ELSE 0 END) as salesquantity,
					SUM(CASE WHEN stockmoves.type=10 THEN 
							price*(1-discountpercent)* -qty
							ELSE 0 END) as salesvalue,
					SUM(CASE WHEN stockmoves.type=11 THEN 
							price*(1-discountpercent)* (-qty)
							ELSE 0 END) as returnvalue,
					SUM(CASE WHEN stockmoves.type=11 
								OR stockmoves.type=10 THEN 
							price*(1-discountpercent)* (-qty)
							ELSE 0 END) as netsalesvalue,
					SUM((standardcost * -qty)) as cost
			FROM stockmoves INNER JOIN stockmaster
			ON stockmoves.stockid=stockmaster.stockid 
			INNER JOIN stockcategory 
			ON stockmaster.categoryid=stockcategory.categoryid 
			WHERE (stockmoves.type=10 or stockmoves.type=11)
			AND show_on_inv_crds =1
			AND trandate>='" . $FromDate . "'
			AND trandate<='" . $ToDate . "'
			GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockcategory.categorydescription ";
	
	if ($_POST['OrderBy']=='NetSales'){
		$sql .= " ORDER BY netsalesvalue DESC ";
	} else {
		$sql .= " ORDER BY salesquantity DESC ";
	}
	if (is_numeric($_POST['NoToDisplay'])){
		if ($_POST['NoToDisplay'] > 0){
			$sql .= " LIMIT " . $_POST['NoToDisplay'];
		}
	}
	
	$ErrMsg = _('The sales data could not be retrieved because') . ' - ' . DB_error_msg($db);
	$SalesResult = DB_query($sql,$db,$ErrMsg);

	
	echo '<table cellpadding=2 class="selection">';
	
	echo'<tr>
		<th>' . _('Rank') . '</th>
		<th>' . _('Item') . '</th>
		<th>' . _('Category') . '</th>
		<th>' . _('Sales Value') . '</th>
		<th>' . _('Refunds') . '</th>
		<th>' . _('Net Sales') . '</th>
		<th>' . _('Sales') .'<br />' . _('Quantity') . '</th>
		</tr>';
	
	$CumulativeTotalSales = 0;
	$CumulativeTotalRefunds = 0;
	$CumulativeTotalNetSales = 0;
	$CumulativeTotalQuantity = 0;
	$i=1;
	$k=0;
	while ($SalesRow=DB_fetch_array($SalesResult)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
				
		echo '<td>' . $i . '</td>
				<td>' . $SalesRow['stockid'] . ' - ' . $SalesRow['description'] . '</td>
				<td>' . $SalesRow['categorydescription'] . '</td>
				<td class="number">' . number_format($SalesRow['salesvalue'],2) . '</td>
				<td class="number">' . number_format($SalesRow['returnvalue'],2) . '</td>
				<td class="number">' . number_format($SalesRow['netsalesvalue'],2) . '</td>
				<td class="number">' . $SalesRow['salesquantity'] . '</td>
				</tr>';
		$i++;
		
		$CumulativeTotalSales += $SalesRow['salesvalue'];
		$CumulativeTotalRefunds += $SalesRow['returnvalue'];
		$CumulativeTotalNetSales += ($SalesRow['salesvalue']+$SalesRow['returnvalue']);
		$CumulativeTotalQuantity += $SalesRow['salesquantity'];

	} //loop around category sales for the period
	
	if ($k==1){
		echo '<tr class="EvenTableRows"><td colspan="8"><hr></td></tr>';
		echo '<tr class="OddTableRows">';
	} else {
		echo '<tr class="OddTableRows"><td colspan="8"><hr></td></tr>';
		echo '<tr class="EvenTableRows">';
	}
	echo '<td class="number" colspan=3>' . _('GRAND Total') . '</td>
		<td class="number">' . number_format($CumulativeTotalSales,2) . '</td>
		<td class="number">' . number_format($CumulativeTotalRefunds,2) . '</td>
		<td class="number">' . number_format($CumulativeTotalNetSales,2) . '</td>
		<td class="number">' . $CumulativeTotalQuantity . '</td>
		</tr>';
	
	echo '</table>';

} //end of if user hit show sales
include('includes/footer.inc');
?>