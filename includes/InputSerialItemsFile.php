<?php
/* $Revision: 1.1 $ */
/*Input Serial Items - used for inputing serial numbers or batch/roll/bundle references
for controlled items - used in:
- ConfirmDispatchControlledInvoice.php
- GoodsReceivedControlled.php
- StockAdjustments.php
- StockTransfers.php
- CreditItemsControlled.php

*/

//we start with a batch or serial no header and need to display something for verification...
global $tableheader;
global $LineItem;
if (isset($_GET['LineNo'])){
	$LineNo = $_GET['LineNo'];
} elseif (isset($_POST['LineNo'])){
	$LineNo = $_POST['LineNo'];
}

echo "<DIV Align=Center>";
echo "<TABLE>";
echo $tableheader;

$TotalQuantity = 0; /*Variable to accumulate total quantity received */
$RowCounter =0;

/*Display the batches already entered with quantities if not serialised */
foreach ($LineItem->SerialItems as $Bundle){

	$RowCounter++;
	//only show 1st 10 lines
	if ($RowCounter < 10){
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		echo "<TD>" . $Bundle->BundleRef . "</TD>";

		if ($LineItem->Serialised==0){
			echo "<TD ALIGN=RIGHT>" . number_format($Bundle->BundleQty, $LineItem->DecimalPlaces) . "</TD>";
		}
	}

	$TotalQuantity += $Bundle->BundleQty;
}



/*Display the totals and rule off before allowing new entries */
if ($LineItem->Serialised==1){
	echo "<TR><TD ALIGN=RIGHT><B>Total Quantity: " . number_format($TotalQuantity,$LineItem->DecimalPlaces) . "</B></TD></TR>";
	echo "<TR><TD><HR></TD></TR>";
} else {
	echo "<TR><TD ALIGN=RIGHT><B>Total Quantity:</B></TD><TD ALIGN=RIGHT><B>" . number_format($TotalQuantity,$LineItem->DecimalPlaces) . "</B></TD></TR>";
	echo "<TR><TD COLSPAN=2><HR></TD></TR>";
}

echo "</TABLE><HR>";
//echo "<TABLE><TR><TD>";

//DISPLAY FILE INFO
// do some inits & error checks...
        if (!isset($_SESSION['CurImportFile'])){
                $_SESSION['CurImportFile'] = "";
                $LineItem->SerialItemsValid=false;
        }
        if ($_FILES['ImportFile']['name'] == "" && $_SESSION['CurImportFile'] == ""){
                $msg = "Please Choose a file and then click 'Set Entry Type' to upload a file for import";
		prnMsg($msg);
                $LineItem->SerialItemsValid=false;
		include("includes/footer.inc");
		exit();
        }
        if ($_FILES['ImportFile']['error'] != "" && !isset($_SESSION['CurImportFile'])){
                echo "There was a problem with the uploaded file. We received:<br>".
                         "Name:".$_FILES['ImportFile']['name']."<br>".
                         "Size:".number_format($_FILES['ImportFile']['size']/1024,2)."kb<br>".
                         "Type:".$_FILES['ImportFile']['type']."<br>";
                echo "<br>Error was".$_FILES['ImportFile']['error']."<br>";
                $LineItem->SerialItemsValid=false;
                endWEBERP();
        } elseif ($_FILES['ImportFile']['name']!=""){
                //User has uploaded importfile. reset items, then just 'get hold' of it for later.

                //foreach($_FILES['ImportFile'] as $k=>$v){
                //      $_SESSION['CurImportFile'][$k] = $v;
                //}
                $LineItem->SerialItems=array();
                $LineItem->SerialItemsValid=false;
	        $_SESSION['CurImportFile'] = $_FILES['ImportFile'];
		$_SESSION['CurImportFile']['tmp_name'] = $LineItem->StockID."_".$LineNo."PO-RcvGoods";
                if (!move_uploaded_file($_FILES['ImportFile']['tmp_name'],$_SESSION['CurImportFile']['tmp_name'])){
                        pErrMsg("<br />Error Moving temporary file!!! Please check your configuration");
                        $LineItem->SerialItemsValid=false;
			include("includes/footer.inc");
			exit;
                }

                if ($_FILES['ImportFile']['name']!=""){
                        echo "Successfully received:<br>";
                }
        } elseif (isset($_SESSION['CurImportFile']) && !isset($_POST['ValidateFile'])) {
                //file exists, some action performed...
                echo "Working with:<br>";
        }


/********************************************
  Display file info for visual verification
********************************************/
	echo "<TABLE>";
	echo "<tr><td>Name:</td><td>".$_SESSION['CurImportFile']['name']."</td></tr>
        <tr><td>Size:</td><td>" . number_format($_SESSION['CurImportFile']['size']/1024,4) . "kb</td></tr>
        <tr><td>Type:</td><td>" . $_SESSION['CurImportFile']['type'] . "</td></tr>
        <tr><td>TmpName:</td><td>" . $_SESSION['CurImportFile']['tmp_name'] . "</td></tr>
       <tr><td>Status:</td><td>" . ($LineItem->SerialItemsValid?getMsg("Valid","success"):getMsg("Invalid","error")) . "</td></tr>
       </TABLE>";
        $invalid_imports." out of ".$TotalLines." records are invalid.<br>";
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

	echo "<br>
        <form ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID ."'  method=post>
        <input type=submit name=ValidateFile value=ValidateFile>
        <input type=hidden name=LineNo value=" . $LineNo . ">
        <input type=hidden name=EntryType value=" . $EntryType .">
        </form>
        <p>1st 10 Lines of File....
        <hr width=15%>

	<pre>";

	echo $contents;

	echo "</pre>";

} else {
        //Otherwise we have all valid records. show the first (100)  for visual verification.
	echo "Below are the 1st 100 records as parsed<hr width=20%>";
	foreach($LineItem->SerialItems as $SItem){
		echo $SItem->BundleRef."<br>";
		$i++;
		if ($i == 100) {
			break;
                }
	}
}

?>
