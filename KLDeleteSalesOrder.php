<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');
$Title = _('KL Delete Sales Order');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/OCOpenCartGeneralFunctions.php');
include('includes/OCOpenCartConnectDB.php');

//Get Out if we don't have the data needed to work with
if (!isset($_GET['OrderNo']) OR $_GET['OrderNo']==''){
	prnMsg( _('We need an order number to delete it') , 'error');
	include('includes/footer.php');
	exit();
}

$Result = DB_Txn_Begin();

// online sale from our website, we must update the status of the order in OpenCart
$OnlineOrderNo = GetOnlineOrderNoFromWeberp($_GET['OrderNo']);
$ReasonChangeStatusId = "webERP --> Expired as no payment received";  
UpdateOpenCartOrderStatus($OnlineOrderNo, OPENCART_ORDER_STATUS_EXPIRED, 1, "", "", $ReasonChangeStatusId);

$SQL = "DELETE FROM salesorderdetails WHERE salesorderdetails.orderno='" . $_GET['OrderNo'] . "'";
$ErrMsg = _('Cannot delete the sales order details because');
$Result = DB_query($SQL,$ErrMsg,'',true);
prnMsg( _('Deleted Sales Order Lines ').  $_GET['OrderNo']);

$SQL = "DELETE FROM salesorders WHERE salesorders.orderno='" . $_GET['OrderNo'] . "'";
$ErrMsg = _('Cannot delete the sales order because');
$Result = DB_query($SQL,$ErrMsg,'',true);
prnMsg( _('Deleted Sales Order Header ').  $_GET['OrderNo']);


$Result = DB_Txn_Commit();

include('includes/footer.php');

