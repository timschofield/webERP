<?php
/* $Id: Add_SerialItemsOut.php 5768 2012-12-20 08:38:22Z daintree $*/
/*ProcessSerialItems.php takes the posted variables and adds to the SerialItems array
 in either the cartclass->LineItems->SerialItems or the POClass->LineItems->SerialItems */

if (isset($_POST['AddBatches'])){

	if (isset($_POST['TotalBundles'])){
		$loop_max = $_POST['TotalBundles'];
	} else {
		$loop_max = 10;
	}
	for ($i=0;$i < $loop_max;$i++){
	if(isset($_POST['SerialNo' . $i]) and mb_strlen($_POST['SerialNo' . $i])>0){
			$ExistingBundleQty = ValidBundleRef($StockID, $LocationOut, $_POST['SerialNo' . $i]);
			if ($ExistingBundleQty >0){
				$AddThisBundle = true;
				/*If the user enters a duplicate serial number the later one over-writes
				the first entered one - no warning given though ? */
				if (filter_number_format($_POST['Qty' . $i]) > $ExistingBundleQty){
					if ($LineItem->Serialised ==1){
						echo '<br />' . $_POST['SerialNo' . $i] . " " . _('has already been sold');
						$AddThisBundle = false;
					} elseif ($ExistingBundleQty==0) { /* and its a batch */
						echo '<br />' . _('There is none of') . ' ' . $_POST['SerialNo' . $i] . ' ' . _('left');
						$AddThisBundle = false;
					} else {
					 	echo '<br />' . _('There is only') . ' ' . $ExistingBundleQty . ' ' . _('of') . ' ' . $_POST['SerialNo' . $i] . ' ' . _('remaining') . '. ' . _('The entered quantity will be reduced to the remaining amount left of this batch/bundle/roll');
						$_POST['Qty' . $i] = $ExistingBundleQty;
						$AddThisBundle = true;
					}
				}
				if ($AddThisBundle==true
					AND filter_number_format($_POST['Qty' . $i])>0){

					$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem ($_POST['SerialNo' . $i],
																					filter_number_format($_POST['Qty' . $i]),
																					$_POST['ExpiryDate' . $i]);
				}
			} /*end if ExistingBundleQty >0 */
		} /* end if posted Serialno . i is not blank */

	} /* end of the loop aroung the form input fields */

//	for ($i=0;$i < count($_POST['Bundles']);$i++){ /*there is an entry in the multi select list box */
//		if ($LineItem->Serialised==1){	/*only if the item is serialised */
//			$LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem ($_POST['Bundles'][$i], 1);
//		}
//	}


} /*end if the user hit the enter button */

if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}

if (isset($_GET['DELETEALL'])){
	$LineItem->SerialItems=array();
}

?>
