<?php
/* $Revision: 1.2 $ */
$title = "Receive Controlled Items";
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include("includes/DefinePOClass.php");
include("includes/DefineSerialItems.php");
include("includes/session.inc");
include("includes/header.inc");

if (!isset($_SESSION['PO'])) {
	/* This page can only be called with a purchase order number for receiving*/
	echo "<CENTER><A HREF='" . $rootpath . "/PO_SelectPurchOrder.php?" . SID . "'>Select a purchase order to receive</A></CENTER><br>";
	prnMsg("<BR>This page can only be opened if a purchase order and line item has been selected. Please do that first.<BR>","error");
	include( "includes/footer.inc");
	exit;

}

if ($_GET['LineNo']>0){
	$LineNo = $_GET["LineNo"];
} else if ($_POST['LineNo']>0){
	$LineNo = $_POST["LineNo"];
} else {
	echo "<CENTER><A HREF='" . $rootpath . "/GoodsReceived.php?" . SID . "'>Select a line Item to Receive</A></CENTER>";
	prnMsg("<BR>This page can only be opened if a Line Item on a PO has been selected. Please do that first.<BR>", "error");
	include( "includes/footer.inc");
	exit;
}

$LineItem = &$_SESSION['PO']->LineItems[$LineNo];

if ($LineItem->Controlled !=1 ){ /*This page only relavent for controlled items */

	echo "<CENTER><A HREF='" . $rootpath . "/GoodsReceived.php?" . SID . "'>Back to the Purchase Order</A></CENTER>";
	prnMsg("<BR>Notice - the line being recevied must be controlled as defined in the item defintion", "error");
	include( "includes/footer.inc");
	exit;
}

if ($_POST['AddBatches']=='Enter'){

	for ($i=0;$i < 10;$i++){
		if($_POST['SerialNo' . $i] != ""){
			/*If the user enters a duplicate serial number the later one over-writes
			the first entered one - no warning given though ? */
			$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem ($_POST['SerialNo' . $i], $_POST['Qty' . $i]);

		}
	}
}

if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}

echo "<CENTER><FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

echo "<INPUT TYPE=HIDDEN NAME='LineNo' VALUE=$LineNo>";

echo "<br><a href='$rootpath/GoodsReceived.php?" . SID . "'>Back To Purchase Order # " . $_SESSION['PO']->OrderNo . "</a>";

echo "<br><FONT SIZE=2><B>Receive controlled item " . $LineItem->StockID  . " - " . $LineItem->ItemDescription . " on order " . $_SESSION['PO']->OrderNo . " from " . $_SESSION['PO']->SupplierName . "</B></FONT>";


include ("includes/InputSerialItems.php");


/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
$_SESSION['PO']->LineItems[$LineItem->LineNo]->ReceiveQty = $TotalQuantity;

echo "</TR></table><br><INPUT TYPE=SUBMIT NAME='AddBatches' VALUE='Enter'><BR>";




/* Not sure how any of this works - but would be good to allow file input of serial numbers
if (isset($_POST['ImportFile'])){
// do some inits & error checks...
	if (!isset($_SESSION['CurImportFile'])){
		$_SESSION['CurImportFile'] = "";
		$LineItem->SerialItemsValid=false;
	}
	if ($_FILES['ImportFile']['name'] == "" && $_SESSION['CurImportFile'] == ""){
		echo "Please Choose a file and then click 'Change' to upload a file for import";
		include( "includes/footer.inc");
		exit;
	}
	if ($_FILES['ImportFile']['error'] != "" && !isset($_SESSION['CurImportFile'])){
		echo "There was a problem with the uploaded file. We received:<br>".
			 "Name:".$_FILES['ImportFile']['name']."<br>".
			 "Size:".number_format($_FILES['ImportFile']['size']/1024,2)."kb<br>".
			 "Type:".$_FILES['ImportFile']['type']."<br>";
		echo "<br>Error	was".$_FILES['ImportFile']['error']."<br>";
		$LineItem->SerialItemsValid=false;
		include( "includes/footer.inc");  exit;
	} elseif ($_FILES['ImportFile']['name']!=""){
  		$tmpfile = $PO->OrderNo."_".$LineItem->StockID."_".$LineNo."PO-RcvGoods";
		if (!move_uploaded_file($_FILES['ImportFile']['tmp_name'],$tmpfile)){
			pErrMsg("<br />Error Moving temporary file!!! Please check your configuration");
			$LineItem->SerialItemsValid=false;
			include( "includes/footer.inc");  exit;
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

		while (!feof($handle) && $i < $RecvQty) {
			$contents = fgets($handle, 4096);
			$valid = ($LineItem->SerialItems[$contents] = new SerialItem($contents,1));

			if (!$valid) {
				$invalid_imports++;
				prnErrMsg("Invalid line #".($i+1).": [$contents]");
			}
			$i++;
		}
		if ($invalid_imports==0) {
			$LineItem->SerialItemsValid=true;
		}
		fclose($handle);
}

*******************************************
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
<tr><td colspan=2><?=$invalid_imports?> out of <?=$LineItem->ReceiveQty?> records are invalid.</td></tr>
</table>
<?
$filename = $_SESSION['CurImportFile']['tmp_name'];
?>
<br>
<input type=submit name=ValidateFile value=ValidateFile>
<p>1st 10 Lines of File....
<hr width=15%>
<pre>
<?=$contents?>
...
</pre>


<?

 end of blocked out file stuff of Jesse's yet to get going */

echo "</FORM>";
include( "includes/footer.inc");
exit;
?>

