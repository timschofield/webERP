<?php
/* $Revision: 1.1 $ */

include("includes/DefineSerialItems.php");

class StockTransfer {

	var $StockID;
	Var $StockLocationFrom;
	Var $StockLocationTo; /*Used in stock transfers only */
	Var $TranDate; /*Used in stock transfers only */
	var $Controlled;
	var $Serialised;
	var $ItemDescription;
	Var $PartUnit;
	Var $StandardCost;
	Var $DecimalPlaces;
	Var $Quantity;
	var $SerialItems; /*array to hold controlled items*/

	//Constructor
	function StockTransfer(){
		$this->SerialItems = array();
		$Quantity =0;
	}
}


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


/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['Transfer'];

//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo "<CENTER><A HREF='" . $rootpath . "/StockTransfer.php?" . SID . "NewTransfer=Yes'>Enter A Stock Transfer</A></CENTER>";
	prnMsg("<BR>Notice - The transferred item must be defined as controlled to require input of the batch numbers or serial numbers being transferred","error");
	include("includes/footer.inc");
	exit;
}



include("includes/Add_SerialItemsOut.php");


echo "<CENTER><FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";


echo "<br><a href='$rootpath/StockTransfers.php?"  . SID . "'>Back To Transfer Screen</A>";

echo "<br><FONT SIZE=2><B>Transfer of controlled item " . $LineItem->StockID  . " - " . $LineItem->ItemDescription . "</B></FONT>";


include ("includes/InputSerialItems.php");


/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for adjusting */
$_SESSION['Transfer']->Quantity = $TotalQuantity;

/*Also a multi select box for adding bundles to the Transfer without keying */

$sql = "SELECT SerialNo, Quantity FROM StockSerialItems WHERE StockID='" . $LineItem->StockID . "' AND LocCode ='" . $_SESSION['Transfer']->StockLocation . "' AND Quantity > 0";

$Bundles = DB_query($sql,$db, "<BR>Could not retrieve the items for " . $LineItem->StockID);

if (DB_num_rows($Bundles)>0){

	echo "<TD VALIGN=TOP><B>Adjust Existing Items</B><BR>";
	echo "<SELECT Name=Bundles[] multiple>";

	$id=0;

	while ($myrow=DB_fetch_array($Bundles,$db)){

		echo "<OPTION VALUE=" . $myrow["SerialNo"] . ">" . $myrow["SerialNo"];
		if ($LineItem->Serialised==0){
			echo " - " . $myrow['Quantity'];
		}
	}

	echo "</SELECT></TD>";
}

echo "</TR></TABLE>";

echo "<br><INPUT TYPE=SUBMIT NAME='AddBatches' VALUE='Enter'><BR>";

include("includes/footer.inc");
exit;


?>

