<?php
/* $Revision: 1.6 $ */
$title = 'Receive Controlled Items';
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/DefinePOClass.php');
include('includes/DefineSerialItems.php');
include('includes/session.inc');
include('includes/header.inc');

if (!isset($_SESSION['PO'])) {
	/* This page can only be called with a purchase order number for receiving*/
	echo '<CENTER><A HREF="' . $rootpath . '/PO_SelectPurchOrder.php?' . SID . '">'. 
		_('Select a purchase order to receive'). '</A></CENTER><br>';
	prnMsg('<BR>'. _('This page can only be opened if a purchase order and line item has been selected. Please do that first').'.<BR>','error');
	include('includes/footer.inc');
	exit;
}

if ($_GET['LineNo']>0){
	$LineNo = $_GET['LineNo'];
} else if ($_POST['LineNo']>0){
	$LineNo = $_POST['LineNo'];
} else {
	echo '<CENTER><A HREF="' . $rootpath . '/GoodsReceived.php?' . SID . '">'. 
		_('Select a line Item to Receive').'</A></CENTER>';
	prnMsg('<BR>'. _('This page can only be opened if a Line Item on a PO has been selected. Please do that first').
		'.<BR>', 'error');
	include( 'includes/footer.inc');
	exit;
}

global $LineItem;
$LineItem = &$_SESSION['PO']->LineItems[$LineNo];

if ($LineItem->Controlled !=1 ){ /*This page only relavent for controlled items */

	echo '<CENTER><A HREF="' . $rootpath . '/GoodsReceived.php?' . SID . '">'.
		_('Back to the Purchase Order'). '</A></CENTER>';
	prnMsg('<BR>'. _('Notice - the line being recevied must be controlled as defined in the item defintion'), 'error');
	include('includes/footer.inc');
	exit;
}

/********************************************
	Added KEYED Entry values
********************************************/
if ($_POST['AddBatches']=='Enter'){

	for ($i=0;$i < 10;$i++){
		if($_POST['SerialNo' . $i] != ''){
			/*If the user enters a duplicate serial number the later one over-writes
			the first entered one - no warning given though ? */
			$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem ($_POST['SerialNo' . $i], $_POST['Qty' . $i]);

		}
	}
}

/********************************************
  Validate an uploaded FILE and save entries
********************************************/
$valid = true;
if (isset($_SESSION['CurImportFile']) && isset($_POST['ValidateFile'])){

                $filename = $_SESSION['CurImportFile']['tmp_name'];
                $handle = fopen($filename, "r");
                $TotalLines=0;
		$LineItem->SerialItemsValid=false;
                while (!feof($handle)) {
                        $contents = trim(fgets($handle, 4096));
                        //$valid = $LineItem->SerialItems[$i]->importFileLineItem($contents);
                        $pieces  = explode(",",$contents);
                        if ($LineItem->Serialised == 1){
                        //for Serialised items, we are expecting the line to contain either just the serial no
                        //OR a comma delimited file w/ the serial no FIRST
                                if($pieces[0] != ""){
                                /*If the user enters a duplicate serial number the later one over-writes
                                the first entered one - no warning given though ? */
                                        $LineItem->SerialItems[$pieces[0]] = new SerialItem ($pieces[0],  1 );
                                } else {
                                        if ($pieces[0] != "") $valid = false;
                                }
                        } else {
                        //for controlled only items, we must receive: BatchID, Qty in a comma delimited  file

                                if($pieces[0] != "" && $pieces[1] != "" && is_numeric($pieces[1]) && $pieces[1] > 0 ){
                                /*If the user enters a duplicate batch number the later one over-writes
                                the first entered one - no warning given though ? */
                                        $LineItem->SerialItems[$pieces[0]] = new SerialItem ($pieces[0],  $pieces[1] );
                                } else {
                                        if ($pieces[0] != "") $valid = false;
                                }
                        }
                        $TotalLines++;
                        if (!$valid) $invalid_imports++;
                }//while (file)
                if ($invalid_imports==0) $LineItem->SerialItemsValid=true;
                fclose($handle);
}

/********************************************
  Process Remove actions
********************************************/
if (isset($_GET['DELETEALL'])){
	$RemAll = $_GET['DELETEALL'];
} else {
	$RemAll = "NO";
}

if ($RemAll == "YES"){
	unset($LineItem->SerialItems);
	$LineItem->SerialItems=array();
}

if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}

/********************************************
  Get the page going....
********************************************/
echo '<CENTER>';

echo '<BR><A HREF="'.$rootpath.'/GoodsReceived.php?' . SID . '">'. _('Back To Purchase Order #'). ' '. $_SESSION['PO']->OrderNo . '</a>';

echo '<BR><FONT SIZE=2><B>'. _('Receive controlled item'). ' '. $LineItem->StockID  . ' - ' . $LineItem->ItemDescription . 
	' ' . _('on order ') . ' ' . $_SESSION['PO']->OrderNo . ' from ' . $_SESSION['PO']->SupplierName . '</B></FONT>';

include ('includes/InputSerialItems.php');

echo '</TR></TABLE>';

echo '<BR><INPUT TYPE=SUBMIT NAME=\'AddBatches\' VALUE=\'Enter\'><BR>';
echo '</CENTER>';
/*TotalQuantity set inside this include file from the sum of the bundles
of the item selected for dispatch */
$_SESSION['PO']->LineItems[$LineItem->LineNo]->ReceiveQty = $TotalQuantity;

include( "includes/footer.inc");
?>
