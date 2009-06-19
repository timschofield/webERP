<?php

/* $Revision: 1.20 $ */

$PageSecurity = 9; /*viewing possible with inquiries but not mods */

$UpdateSecurity =10;

include('includes/session.inc');
$title = _('Stock Cost Update');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID =trim(strtoupper($_POST['StockID']));
}

echo "<a href='" . $rootpath . '/SelectProduct.php?' . SID . "'>" . _('Back to Items') . '</a><br>';

if (isset($_POST['UpdateData'])){


    $sql = "SELECT  materialcost,
                    labourcost,
                    overheadcost,
                    mbflag,
                    sum(quantity) as totalqoh
                FROM stockmaster INNER JOIN locstock
                    ON stockmaster.stockid=locstock.stockid
                WHERE stockmaster.stockid='".$StockID."'
                GROUP BY description,
                    units,
                    lastcost,
                    actualcost,
                    materialcost,
                    labourcost,
                    overheadcost,
                    mbflag";
    $ErrMsg = _('The entered item code does not exist');
    $oldresult = DB_query($sql,$db,$ErrMsg);
    $oldrow = DB_fetch_array($oldresult);
    $_POST['QOH'] = $oldrow['totalqoh'];
    $_POST['OldMaterialCost'] = $oldrow['materialcost'];
    if ($oldrow['mbflag']=='M') {
        $_POST['OldLabourCost'] = $oldrow['labourcost'];
        $_POST['OldOverheadCost'] = $oldrow['overheadcost'];
    } else {
        $_POST['OldLabourCost'] = 0;
        $_POST['OldOverheadCost'] = 0;
        $_POST['LabourCost'] = 0;
        $_POST['OverheadCost'] = 0;
    }
    DB_free_result($oldresult);

 	$OldCost =$_POST['OldMaterialCost'] + $_POST['OldLabourCost'] + $_POST['OldOverheadCost'];
   	$NewCost =$_POST['MaterialCost'] + $_POST['LabourCost'] + $_POST['OverheadCost'];

	$result = DB_query("SELECT * FROM stockmaster WHERE stockid='$StockID'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		prnMsg (_('The entered item code does not exist'),'error',_('Non-existent Item'));
	} elseif ($OldCost != $NewCost){

	$Result = DB_Txn_Begin($db);
	ItemCostUpdateGL($db, $StockID, $NewCost, $OldCost, $_POST['QOH']);


		$SQL = "UPDATE stockmaster SET
					materialcost=" . $_POST['MaterialCost'] . ",
					labourcost=" . $_POST['LabourCost'] . ",
					overheadcost=" . $_POST['OverheadCost'] . ",
					lastcost=" . $OldCost . "
			WHERE stockid='" . $StockID . "'";

		$ErrMsg = _('The cost details for the stock item could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$Result = DB_Txn_Commit($db);
		UpdateCost($db, $StockID); //Update any affected BOMs

   	}
}

$ErrMsg = _('The cost details for the stock item could not be retrieved because');
$DbgMsg = _('The SQL that failed was');

$result = DB_query("SELECT description,
								units,
								lastcost,
								actualcost,
								materialcost,
								labourcost,
								overheadcost,
								mbflag,
								stocktype,
								sum(quantity) as totalqoh
							FROM stockmaster INNER JOIN locstock
								ON stockmaster.stockid=locstock.stockid
								INNER JOIN stockcategory
								ON stockmaster.categoryid = stockcategory.categoryid
							WHERE stockmaster.stockid='" . $StockID . "'
							GROUP BY description,
								units,
								lastcost,
								actualcost,
								materialcost,
								labourcost,
								overheadcost,
								mbflag,
								stocktype",
							$db,$ErrMsg,$DbgMsg);


$myrow = DB_fetch_array($result);
echo "<br><div class='centre'><font color=BLUE size=3><b>" . $StockID . " - " . $myrow['description'] . '</b> - ' . _('Total Quantity On Hand') . ': ' . $myrow['totalqoh'] . " " . $myrow['units'] ."</font>";

echo "<form action='" . $_SERVER['PHP_SELF'] . "?". SID ."' method=post>";
echo _('Item Code') . ":<input type=text name='StockID' value='$StockID' 1 maxlength=20>";

echo " <input type=submit name='Show' VALUE='" . _('Show Cost Details') . "'></div><hr>";

if (($myrow['mbflag']=='D' AND $myrow['stocktype'] != 'L')
	OR $myrow['mbflag']=='A'
	OR $myrow['mbflag']=='K'){
    echo '</form>'; // Close the form
   if ($myrow['mbflag']=='D'){
        echo "<br>$StockID " . _('is a service item');
   } else if ($myrow['mbflag']=='A'){
        echo "<br>$StockID " . _('is an assembly part');
   } else if ($myrow['mbflag']=='K'){
        echo "<br>$StockID " . _('is a kit set part');
   }
   prnMsg(_('Cost information cannot be modified for kits assemblies or service items') . '. ' . _('Please select a different part'),'warn');
   include('includes/footer.inc');
   exit;
}

echo '<input type=hidden name=OldMaterialCost VALUE=' . $myrow['materialcost'] .'>';
echo '<input type=hidden name=OldLabourCost VALUE=' . $myrow['labourcost'] .'>';
echo '<input type=hidden name=OldOverheadCost VALUE=' . $myrow['overheadcost'] .">";
echo '<input type=hidden name=QOH VALUE=' . $myrow['totalqoh'] .'>';

echo '<table cellpadding=2 BORDER=2>';
echo '<tr><td>' . _('Last Cost') .':</td><td class=number>' . number_format($myrow['lastcost'],2) . '</td></tr>';
if (! in_array($UpdateSecurity,$_SESSION['AllowedPageSecurityTokens']) OR !isset($UpdateSecurity)){
	echo '<tr><td>' . _('Cost') . ':</td><td class=number>' . number_format($myrow['materialcost']+$myrow['labourcost']+$myrow['overheadcost'],2) . '</td></tr></table>';
} else {

	if ($myrow['mbflag']=='M'){
		echo '<input type=hidden name="MaterialCost" VALUE=' . $myrow['materialcost'] . '>';
		echo '<tr><td>' . _('Standard Material Cost Per Unit') .':</td><td class=number>' . number_format($myrow['materialcost'],4) . '</td></tr>';
		echo '<tr><td>' . _('Standard Labour Cost Per Unit') . ':</td><td><input type=TEXT class="number" name=LabourCost VALUE=' . $myrow['labourcost'] . '></td></tr>';
		echo '<tr><td>' . _('Standard Overhead Cost Per Unit') . ':</td><td><input type=TEXT class="number" name=OverheadCost VALUE=' . $myrow['overheadcost'] . '></td></tr>';
	} elseif ($myrow['mbflag']=='B' OR  $myrow['mbflag']=='D') {
		echo '<tr><td>' . _('Standard Cost') .':</td><td><input type=TEXT class="number" name="MaterialCost" VALUE=' . $myrow['materialcost'] . '></td></tr>';
	} else 	{
		echo '<input type=hidden name=LabourCost VALUE=0>';
		echo '<input type=hidden name=OverheadCost VALUE=0>';
	}
    echo "</table><div class='centre'><input type=submit name='UpdateData' VALUE='" . _('Update') . "'><hr>";
}
if ($myrow['mbflag']!='D'){
	echo "<a href='$rootpath/StockStatus.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Status') . '</a>';
	echo "<br><a href='$rootpath/StockMovements.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Movements') . '</a>';
	echo "<br><a href='$rootpath/StockUsage.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Usage')  .'</a>';
	echo "<br><a href='$rootpath/SelectSalesOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Search Outstanding Sales Orders') . '</a>';
	echo "<br><a href='$rootpath/SelectCompletedOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Search Completed Sales Orders') . '</a>';
}
echo '</form></div>';
include('includes/footer.inc');
?>