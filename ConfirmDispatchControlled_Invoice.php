<?php
/* $Revision: 1.2 $ */
$title = "Specifiy Dispatched Controlled Items";
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include("includes/DefineCartClass.php");
include("includes/DefineSerialItems.php");
include("includes/session.inc");
include("includes/header.inc");

if (isset($_GET['StockID'])){
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
} else {
	echo "<CENTER><A HREF='" . $rootpath . "/ConfirmDispatch_Invoice.php?" . SID . "'>Select a line Item to Receive</A><br>";
	echo "<BR><B>Error:</B>This page can only be opened if a Line Item on a Sales Order has been selected. Please do that first.<BR>";
	echo "</CENTER>";
	include("includes/footer.inc");
	exit;
}

if (!isset($_SESSION['Items']) OR !isset($_SESSION['ProcessingOrder'])) {
	/* This page can only be called with a sales order number to invoice */
	echo "<CENTER><A HREF='" . $rootpath . "/SelectSalesOrder.php?" . SID . "'>Select a sales order to invoice</A><<br>";
	prnMsg("This page can only be opened if a purchase order and line item has been selected. Please do that first.<BR>","error");
	echo "</CENTER>";
	include("includes/footer.inc");
	exit;
}


/*Save some typing by referring to the line item class object in short form */
$LineItem = &$_SESSION['Items']->LineItems[$StockID];

//Make sure this item is really controlled
if ( $LineItem->Controlled != 1 ){
	echo "<CENTER><A HREF='" . $rootpath . "/ConfirmDispatch_Invoice.php?" . SID . "'>Back to The Sales Order</A></CENTER>";
	prnMsg("<BR>Notice - The line item must be defined as controlled to require input of the batch numbers or serial numbers being sold","error");
	include("includes/footer.inc");
	exit;
}


include ("includes/Add_SerialItemsOut.php");



echo "<CENTER><FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

echo "<INPUT TYPE=HIDDEN NAME='StockID' VALUE=$StockID>";

echo "<br><a href='$rootpath/ConfirmDispatch_Invoice.php?" . SID . "'>Back To Confirmation of Dispatch/Invoice</a>";

echo "<br><FONT SIZE=2><B>Dispatch of controlled item " . $LineItem->StockID  . " - " . $LineItem->ItemDescription . " on order " . $_SESSION['Items']->OrderNo . " to " . $_SESSION['Items']->CustomerName . "</B></FONT>";



include ("includes/InputSerialItems.php");


/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
$_SESSION['Items']->LineItems[$LineItem->StockID]->QtyDispatched = $TotalQuantity;

/*Also a multi select box for adding bundles to the dispatch without keying */

$sql = "SELECT SerialNo, Quantity FROM StockSerialItems WHERE StockID='" . $StockID . "' AND LocCode ='" . $_SESSION['Items']->Location . "' AND Quantity > 0";

$Bundles = DB_query($sql,$db, "<BR>Could not retrieve the items for $StockID");
if (DB_num_rows($Bundles)>0){

	echo "<TD VALIGN=TOP><B>Select Existing Items</B><BR>";
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






/* Jesse file stuff

// do some inits & error checks...
	if (!isset($_SESSION['CurImportFile'])){
		$_SESSION['CurImportFile'] = "";
		$LineItem->SerialItemsValid=false;
	}
	if ($_FILES['ImportFile']['name'] == "" && $_SESSION['CurImportFile'] == ""){
		echo "Please Choose a file and then click 'Change' to upload a file for import";
		$LineItem->SerialItemsValid=false;
		include("includes/footer.inc"); exit;
	}
	if ($_FILES['ImportFile']['error'] != "" && !isset($_SESSION['CurImportFile'])){
		echo "There was a problem with the uploaded file. We received:<br>".
			 "Name:".$_FILES['ImportFile']['name']."<br>".
			 "Size:".number_format($_FILES['ImportFile']['size']/1024,2)."kb<br>".
			 "Type:".$_FILES['ImportFile']['type']."<br>";
		echo "<br>Error	was".$_FILES['ImportFile']['error']."<br>";
		$LineItem->SerialItemsValid=false;
		include("includes/footer.inc"); exit;
	} elseif ($_FILES['ImportFile']['name']!=""){
		//User has uploaded importfile. reset items, then just 'get hold' of it for later.

		//foreach($_FILES['ImportFile'] as $k=>$v){
		//	$_SESSION['CurImportFile'][$k] = $v;
		//}
		$LineItem->SerialItems=array();
		$LineItem->SerialItemsValid=false;
		$tmpfile = ini_get("upload_tmp_dir")."/".$SO->OrderNo."_".$LineItem->StockID."_".$LineNo."SO-Dispatch";
		if (!move_uploaded_file($_FILES['ImportFile']['tmp_name'], $tmpfile)){
			pErrMsg("<br />Error Moving temporary file!!! Please check your configuration");
			$LineItem->SerialItemsValid=false;
			include("includes/footer.inc"); exit;
		}
		if ($_FILES['ImportFile']['name']!=""){
			echo "Successfully received:<br>";
		}
		$_SESSION['CurImportFile'] = $_FILES['ImportFile'];
		$_SESSION['CurImportFile']['tmp_name'] = $tmpfile;
	} elseif (isset($_SESSION['CurImportFile']) && !isset($_POST['ValidateFile'])) {
		//file exists, some action performed...
		echo "Working with:<br>";
	}

/********************************************
  Try to Validate an uploaded file.
*******************************************
if (isset($_SESSION['CurImportFile']) && isset($_POST['ValidateFile'])){

		$filename = $_SESSION['CurImportFile']['tmp_name'];
		$handle = fopen($filename, "r");
		$i=0;
		//we are receiving new data. Always force revalidation
		$LineItem->SerialItemsValid = false;
		while (!feof($handle) && $i < $DispatchQty) {
			$contents = fgets($handle, 4096);
			$valid = $LineItem->SerialItems[$i]->importFileLineItem($contents,"SO");
			if (!in_array($LineItem->SerialItems[$i]->SerialNo,$TmpSerials) ){
				$TmpSerials[] = $LineItem->SerialItems[$i]->SerialNo;
			} else {
				$valid = false;
				$LineItem->SerialItems[$i]->ValidationMsg = "Duplicate";
			}
			if (!$valid) {
				$invalid_imports++;
				// [SERIAL] not valid b/c xyz
				$txt = "[".$LineItem->SerialItems[$i]->SerialNo."] ".$LineItem->SerialItems[$i]->ValidationMsg;
				prnErrMsg( $txt , "<br>Invalid line #".($i+1));
			}
			$i++;
		}
		if ($invalid_imports==0) $LineItem->SerialItemsValid=true;
		fclose($handle);

}

/********************************************
  Display file info for visual verification
*******************************************
?>
<table>
<tr><td class=tableheader>Name:</td><td><?=$_SESSION['CurImportFile']['name']?></td></tr>
<tr><td class=tableheader>Size:</td><td><?=number_format($_SESSION['CurImportFile']['size']/1024,4)?> kb</td></tr>
<tr><td class=tableheader>Type:</td><td><?=$_SESSION['CurImportFile']['type']?></td></tr>
<tr><td class=tableheader>Lines:</td><td><?=$i?></td></tr>
<tr><td class=tableheader>TmpName:</td><td><?=$_SESSION['CurImportFile']['tmp_name']?></td></tr>
<tr><td class=tableheader>Status:</td><td><?=($LineItem->SerialItemsValid?getSuccMsg("Valid"):getErrMsg("Invalid"))?></td></tr>
<tr><td colspan=2><?=$invalid_imports?> out of <?=$LineItem->QtyDispatched?> records are invalid.</td></tr>
</table>
<?
$filename = $_SESSION['CurImportFile']['tmp_name'];

if (!$LineItem->SerialItemsValid){
	// IF all items are not valid, show the raw first 10 lines of the file. maybe it will help.

	$handle = fopen($filename, "r");
	$i=0;
	while (!feof($handle) && $i < 10) {
		$contents .= fgets($handle, 4096);
		$i++;
	}
	fclose($handle);
?>
	<br>
	<form ACTION="<?=$_SERVER['PHP_SELF'].SID?>"  method=post>
	<input type=submit name=ValidateFile value=ValidateFile>
	<input type=hidden name=StockId value="<?=$StkId?>">
	<input type=hidden name=AddType value=<?=$AddType?> >
	</form>
	<p>1st 10 Lines of File....
	<hr width=15%>
<pre>
<?=$contents?>
...
</pre>
<?
} else {
	//Otherwise we have all valid records. show the first (100)  for visual verification.

		echo "Below are the 1st 100 records as parsed<hr width=20%>";
		$TmpItem = new $StockClass;
		echo $TmpItem->_PO_Header;
		for ( $i=0; ($i < $LineItem->QtyDispatched && $i < 100); $i++){
			echo $LineItem->SerialItems[$i]->viewLineItem(($i+1));
		}
		echo $TmpItem->_PO_Footer;
		/** sql debugs
                for ( $i=0; ($i < $LineItem->QtyDispatched && $i < 100); $i++){
                        $stmts = $LineItem->SerialItems[$i]->getSqlInserts();
                        foreach($stmts as $SQL){
				echo "[$i]".$SQL."<br>";
			}
                }
		*

} end of Jesse file stuff could this be an include file somewhere ? */


include("includes/footer.inc");
exit;


?>

