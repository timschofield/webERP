<?php
/* $Revision: 1.1 $ */
$title = "Specifiy Dispatched Controlled Items";
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include("includes/DefineCartClass.php");
include("includes/DefineSerialItems.php");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");
include("includes/session.inc");
include("includes/header.inc");

if (isset($_GET['StockID'])){
	$StockID = $_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID = $_POST['StockID'];
} else {
	echo "<CENTER><A HREF='" . $rootpath . "/ConfirmDispatch_Invoice.php?" . SID . "'>Select a line Item to Receive</A><br>";
	prnMsg("This page can only be opened if a Line Item on a Sales Order has been selected. Please do that first.<BR>", "error");
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


if ($_POST['AddBatches']=='Enter'){

	for ($i=0;$i < 10;$i++){
		if($_POST['SerialNo' . $i] != ""){
			$ExistingBundleQty = ValidBundleRef($StockID, $_SESSION['Items']->Location, $_POST['SerialNo' . $i]);
			if ($ExistingBundleQty >0){
				$AddThisBundle = true;
				/*If the user enters a duplicate serial number the later one over-writes
				the first entered one - no warning given though ? */
				if ($_POST['Qty' . $i] > $ExistingBundleQty){
					if ($LineItem->Serialised ==1){
						echo "<BR>" . $_POST['SerialNo' . $i] . " has already been sold";
						$AddThisBundle = false;
					} elseif ($ExistingBundleQty==0) { /* and its a batch */
						echo "<BR>There is none of " . $_POST['SerialNo' . $i] . " left.";
						$AddThisBundle = false;
					} else {
					 	echo "<BR>There is only " . $ExistingBundleQty . " of " . $_POST['SerialNo' . $i] . " left. The entered quantity will be reduced to the remaining amount left of this batch/bundle/roll";
						$_POST['Qty' . $i] = $ExistingBundleQty;
						$AddThisBundle = true;
					}
				}
				if ($AddThisBundle==true){
					$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem ($_POST['SerialNo' . $i], $_POST['Qty' . $i]);
				}
			} /*end if ExistingBundleQty >0 */
		} /* end if posted Serialno . i is not blank */
		if (strlen($_POST['Bundles'][$i])>0 AND $LineItem->Serialised==1){
		/*so the item is serialised and there is an entry in the multi select list box */
			$LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem ($_POST['Bundles'][$i], 1);
		}
	} /* end of the loop aroung the form input fields */

} /*end if the user hit the enter button */

if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}

echo "<CENTER><FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

echo "<INPUT TYPE=HIDDEN NAME='StockID' VALUE=$StockID>";

echo "<br><a href='$rootpath/ConfirmDispatch_Invoice.php?" . SID . "'>Back To Confirmation of Dispatch/Invoice</a>";

echo "<br><FONT SIZE=2><B>Dispatch of controlled item " . $LineItem->StockID  . " - " . $LineItem->ItemDescription . " on order " . $_SESSION['Items']->OrderNo . " to " . $_SESSION['Items']->CustomerName . "</B></FONT>";

if ($LineItem->Serialised==1){
	echo "<BR>Read From a file:<input type=file name='ImportFile'><BR>";
}

echo "<CENTER><TABLE>";

if ($LineItem->Serialised==1){
	$tableheader .= "<TR><TD class='tableheader'>Serial No</TD></TR>";
} else {
	$tableheader = "<TR><TD class='tableheader'>Batch/Roll/Bundle#</TD><TD class='tableheader'>Quantity</TD></TR>";
}

echo $tableheader;
$TotalDispatched = 0; /*Variable to accumulate total quantity received */

$RowCounter =0;
/*Display the batches already entered with quantities if not serialised */
foreach ($LineItem->SerialItems as $Bundle){

	if ($RowCounter == 18){
		echo $tableheader;
		$RowCounter =0;
	} else {
		$RowCounter++;
	}

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	echo "<TD>" . $Bundle->BundleRef . "</TD>";

	if ($LineItem->Serialised==0){
		echo "<TD>" . $Bundle->BundleQty . "</TD>";
	}

	echo "<TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $Bundle->BundleRef . "&StockID=" . $StockID . "'>Delete</A></TD></TR>";
	$TotalDispatched += $Bundle->BundleQty;
}

$_SESSION['Items']->LineItems[$LineItem->StockID]->QtyDispatched = $TotalDispatched;

/*Display the totals and rule off before allowing new entries */
if ($LineItem->Serialised==1){
	echo "<TR><TD ALIGN=RIGHT><B>Total Quantity: " . number_format($TotalDispatched,$LineItem->DecimalPlaces) . "</B></TD></TR>";
	echo "<TR><TD><HR></TD></TR>";
} else {
	echo "<TR><TD ALIGN=RIGHT><B>Total Received:</B></TD><TD ALIGN=RIGHT><B>" . number_format($TotalDispatched,$LineItem->DecimalPlaces) . "</B></TD></TR>";
	echo "<TR><TD COLSPAN=2><HR></TD></TR>";
}

echo "</TABLE>";

echo "<TABLE><TR><TD>";
	echo "<TABLE>"; /*nested table */

/*Now allow new entries in text input boxes */
for ($i=0;$i < 10;$i++){

	if (strlen($_POST['Bundles'][$i])>0 AND $LineItem->Serialised==0){
	/*if the item is controlled not serialised - batch quantity required so just enter bundle refs
	into the form for entry of quantites manually */
		echo "<TR><td><input type=text name='SerialNo" . $i ."' size=21  maxlength=20 Value='" . $_POST['Bundles'][$i] . "'></td>";
	} else {
		echo "<TR><td><input type=text name='SerialNo" . $i ."' size=21  maxlength=20></td>";
	}
	if ($LineItem->Serialised==1){
		echo "<input type=hidden name='Qty" . $i ."' Value=1></TR>";
	} else {
		echo "<TD><input type=text name='Qty" . $i ."' size=11  maxlength=10></TR>";
	}
}

	echo "</table>"; /*end of nested table */
echo "</TD><TD><SELECT Name=Bundles[] multiple>";

$sql = "SELECT SerialNo, Quantity FROM StockSerialItems WHERE StockID='" . $StockID . "' AND LocCode ='" . $_SESSION['Items']->Location . "' AND Quantity > 0";

 $Bundles = DB_query($sql,$db, "Could not retrieve the serial numbers for $StockID");

 $id=0;

 while ($myrow=DB_fetch_array($Bundles,$db)){

	echo "<OPTION VALUE=" . $myrow["SerialNo"] . ">" . $myrow["SerialNo"];
	if ($LineItem->Serialised==0){
		echo " - " . $myrow['Quantity'];
	}
}

echo "</SELECT></TD></TR></TABLE>";

echo "<br><INPUT TYPE=SUBMIT NAME='AddBatches' VALUE='Enter'><BR>";





/* Jesse file stuff
if ($AddType=="FILE"){

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

