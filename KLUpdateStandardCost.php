<?php

include ('includes/session.inc');
include('includes/SQL_CommonFunctions.inc');
$Title = _('Kapal-Laut. Update Standard Cost for an item');
include('includes/header.inc');

//Get Out if we have no StockId or NewCost
If (!isset($_GET['StockId']) OR $_GET['StockId']==''){
	prnMsg( _('We need an item code to change the standrd cost') , 'error');
	include('includes/footer.inc');
	exit;
}
If (!isset($_GET['NewCost']) OR $_GET['NewCost']==''){
	prnMsg( _('We need anew standard cost to apply to the item ') . $_GET['StockId'] , 'error');
	include('includes/footer.inc');
	exit;
}
$sql = "SELECT materialcost,
				labourcost,
				overheadcost,
				mbflag,
				sum(quantity) as totalqoh
		FROM stockmaster INNER JOIN locstock
		ON stockmaster.stockid=locstock.stockid
		WHERE stockmaster.stockid='".$_GET['StockId']."'
		GROUP BY description,
				units,
				lastcost,
				actualcost,
				materialcost,
				labourcost,
				overheadcost,
				mbflag";
$ErrMsg = _('The entered item code does not exist');
$OldResult = DB_query($sql,$ErrMsg);
$OldRow = DB_fetch_array($OldResult);
DB_free_result($OldResult);

$OldCost = $OldRow['materialcost'] + $OldRow['labourcost'] + $OldRow['overheadcost'];
$NewCost = $_GET['NewCost'];

$result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_GET['StockId'] . "'",$db);
$myrow = DB_fetch_row($result);
if (DB_num_rows($result)==0) {
	prnMsg (_('The entered item code does not exist'),'error',_('Non-existent Item'));
} elseif ($OldCost != $NewCost){

	$Result = DB_Txn_Begin();
	ItemCostUpdateGL($db, $_GET['StockId'], $NewCost, $OldCost, $OldRow['totalqoh']);

	$SQL = "UPDATE stockmaster SET	materialcost='" . $NewCost . "',
									labourcost='" . 0 . "',
									overheadcost='" . 0 . "',
									lastcost='" . $OldCost . "',
									lastcostupdate ='" . Date('Y-m-d')."'
							WHERE stockid='" . $_GET['StockId'] . "'";

	$ErrMsg = _('The cost details for the stock item could not be updated because');
	$DbgMsg = _('The SQL that failed was');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	$Result = DB_Txn_Commit();
	UpdateCost($db, $_GET['StockId']); //Update any affected BOMs
	prnMsg (_('Standard Cost of ') . $_GET['StockId'] . ' changed from ' . locale_number_format($OldCost,0) . ' to ' . locale_number_format($NewCost,0),'success');
}

include('includes/footer.inc');

?>