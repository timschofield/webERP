<?php
/* $Revision: 1.2 $ */

include("includes/DefineSerialItems.php");
include("includes/DefineStockTransfers.php");

$title = "Transfer Controlled Items";
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */

include("includes/session.inc");
include("includes/header.inc");


if (!isset($_SESSION['Transfer'])) {
	/* This page can only be called when a stock Transfer is pending */
	echo "<CENTER><A HREF='" . $rootpath . "/StockTransfer.php?" . SID . "NewTransfer=Yes'>Enter A Stock Transfer</A><<br>";
	prnMsg("This page can only be opened if a stock Transfer for a controlled item has been entered<BR>","error");
	echo "</CENTER>";
	include("includes/footer.inc");
	exit;
}


if (isset($_GET['TransferItem'])){
	$TransferItem = $_GET['TransferItem'];
	$_SESSION['TransferItem'] = $_GET['TransferItem'];
} elseif (isset($_SESSION['TransferItem'])){
	$TransferItem = $_SESSION['TransferItem'];
}


/*Save some typing by referring to the line item class object in short form */
if (isset($TransferItem)){ /*we are in a bulk transfer */

	$LineItem = &$_SESSION['Transfer']->TransferItem[$TransferItem];

} else { /*we are in an individual transfer */
	$LineItem = &$_SESSION['Transfer'];
}

//Make sure this item is really controlled
if ($LineItem->Controlled != 1 ){
	if (isset($TransferItem)){
		echo "<CENTER><A HREF='" . $rootpath . "/StockLocTransferReceive.php?" . SID . ">Receive A Stock Transfer</A></CENTER>";
	} else {
		echo "<CENTER><A HREF='" . $rootpath . "/StockTransfers.php?" . SID . "NewTransfer=Yes'>Enter A Stock Transfer</A></CENTER>";
	}
	prnMsg("<BR>Notice - The transferred item must be defined as controlled to require input of the batch numbers or serial numbers being transferred","error");
	include("includes/footer.inc");
	exit;
}


$LocationOut = $_SESSION['Transfer']->StockLocationFrom;
$StockID = $LineItem->StockID;
include("includes/Add_SerialItemsOut.php");


echo "<CENTER><FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

if (isset($TransferItem)){
	echo "<br><a href='$rootpath/StockLocTransferReceive.php?"  . SID . "'>Back To Transfer Screen</A>";
} else {
	echo "<br><a href='$rootpath/StockTransfers.php?"  . SID . "'>Back To Transfer Screen</A>";
}

echo "<br><FONT SIZE=2><B>Transfer of controlled item " . $LineItem->StockID  . " - " . $LineItem->ItemDescription . "</B></FONT>";


include ("includes/InputSerialItems.php");


/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for adjusting */
$LineItem->Quantity = $TotalQuantity;

/*Also a multi select box for adding bundles to the Transfer without keying */

$sql = "SELECT SerialNo, Quantity FROM StockSerialItems WHERE StockID='" . $LineItem->StockID . "' AND LocCode ='" . $_SESSION['Transfer']->StockLocationFrom . "' AND Quantity > 0";

$Bundles = DB_query($sql,$db, "<BR>Could not retrieve the items for " . $LineItem->StockID);

if (DB_num_rows($Bundles)>0){


	echo "<TD VALIGN=TOP>";

	if ($LineItem->Serialised==1){
		echo "<B>Transfer Existing Serial Numbers</B><BR>";
		echo "<SELECT Name=Bundles[] multiple>";
	} else {
		echo "<B>Existing Batches</B><BR>";
	}
	$id=0;

	while ($myrow=DB_fetch_array($Bundles,$db)){
		if ($LineItem->Serialised==1){
			echo "<OPTION VALUE=" . $myrow["SerialNo"] . ">" . $myrow["SerialNo"];
		} else {
			echo $myrow["SerialNo"] . " : " . number_format($myrow['Quantity'],$LineItem->DecimalPlaces) . "<BR>";
		}
	}

	echo "</SELECT></TD>";
}

echo "</TR></TABLE>";

echo "<br><INPUT TYPE=SUBMIT NAME='AddBatches' VALUE='Enter'><BR>";

include("includes/footer.inc");
exit;
?>