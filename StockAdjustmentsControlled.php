<?php
/* $Revision: 1.1 $ */
$title = "Adjusting Controlled Items";
$PageSecurity = 11;

class StockAdjustment {

	var $StockID;
	Var $StockLocation;
	var $Controlled;
	var $Serialised;
	var $ItemDescription;
	Var $PartUnit;
	Var $StandardCost;
	Var $DecimalPlaces;
	Var $Quantity;
	var $SerialItems; /*array to hold controlled items*/

	//Constructor
	function StockAdjustment(){
		$this->SerialItems = array();
		$Quantity =0;
	}
}

/* Session started in header.inc for password checking and authorisation level check */
include("includes/DefineSerialItems.php");
include("includes/session.inc");
include("includes/header.inc");


if (!isset($_SESSION['Adjustment'])) {
	/* This page can only be called when a stock adjustment is pending */
	echo "<CENTER><A HREF='" . $rootpath . "/StockAdjustment.php?" . SID . "NewAdjustment=Yes'>Enter A Stock Adjustment</A><<br>";
	prnMsg("This page can only be opened if a stock adjustment for a controlled item has been entered<BR>","error");
	echo "</CENTER>";
	include("includes/footer.inc");
	exit;
}


/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['Adjustment'];

//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo "<CENTER><A HREF='" . $rootpath . "/StockAdjustment.php?" . SID . "NewAdjustment=Yes'>Enter A Stock Adjustment</A></CENTER>";
	prnMsg("<BR>Notice - The adjusted item must be defined as controlled to require input of the batch numbers or serial numbers being adjusted","error");
	include("includes/footer.inc");
	exit;
}


if ($_POST['AddBatches']=='Enter'){

	for ($i=0;$i < 10;$i++){
		if($_POST['SerialNo' . $i] != ""){
			/*If the user enters a duplicate serial number the later one over-writes
			the first entered one - no warning given though ? */

    			$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem ($_POST['SerialNo' . $i], $_POST['Qty' . $i]);
		} /* end if posted Serialno . i is not blank */
	} /* end of the loop aroung the form input fields */

	for ($i=0;$i < count($_POST['Bundles']);$i++){ /*there is an entry in the multi select list box */
		if ($LineItem->Serialised==1){	/*only if the item is serialised */
			$LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem ($_POST['Bundles'][$i], -1);
		} else {
   			$LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem ($_POST['Bundles'][$i], 0);
		}
	}

} /*end if the user hit the enter button */

if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}



echo "<CENTER><FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";


echo "<br><a href='$rootpath/StockAdjustments.php?"  . SID . "'>Back To Adjustment Screen</A>";

echo "<br><FONT SIZE=2><B>Adjustment of controlled item " . $LineItem->StockID  . " - " . $LineItem->ItemDescription . "</B></FONT>";


include ("includes/InputSerialItems.php");


/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for adjusting */
$_SESSION['Adjustment']->Quantity = $TotalQuantity;

/*Also a multi select box for adding bundles to the adjustment without keying */

$sql = "SELECT SerialNo, Quantity FROM StockSerialItems WHERE StockID='" . $LineItem->StockID . "' AND LocCode ='" . $_SESSION['Adjustment']->StockLocation . "' AND Quantity > 0";

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

