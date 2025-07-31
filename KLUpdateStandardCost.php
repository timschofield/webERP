<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
$Title = _('KL Update Standard Cost for an item');
include('includes/header.php');
include('includes/KLBoards.php');

//Get Out if we have no StockId or NewCost
If (!isset($_GET['StockId']) OR $_GET['StockId']==''){
	prnMsg( _('We need an item code to change the standrd cost') , 'error');
	include('includes/footer.php');
	exit();
}
If (!isset($_GET['NewCost']) OR $_GET['NewCost']==''){
	prnMsg( _('We need anew standard cost to apply to the item ') . $_GET['StockId'] , 'error');
	include('includes/footer.php');
	exit();
}
$SQL = "SELECT materialcost,
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
$OldResult = DB_query($SQL,$ErrMsg);
$OldRow = DB_fetch_array($OldResult);
DB_free_result($OldResult);

$OldCost = $OldRow['materialcost'] + $OldRow['labourcost'] + $OldRow['overheadcost'];
$NewCost = $_GET['NewCost'];

$Result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $_GET['StockId'] . "'");
$MyRow = DB_fetch_row($Result);
if (DB_num_rows($Result)==0) {
	prnMsg (_('The entered item code does not exist'),'error',_('Non-existent Item'));
} elseif ($OldCost != $NewCost){
	ChangeItemStandardCost( $_GET['StockId'], $NewCost, $OldCost, $OldRow['totalqoh']);
	prnMsg (_('Standard Cost of ') . $_GET['StockId'] . ' changed from ' . locale_number_format($OldCost,0) . ' to ' . locale_number_format($NewCost,0),'success');
}

include('includes/footer.php');
