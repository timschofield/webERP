<?php
/* $Revision: 1.2 $ */
/*ProcessSerialItems.php takes the posted variables and adds to the SerialItems array
 in either the cartclass->LineItems->SerialItems or the POClass->LineItems->SerialItems */


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

	} /* end of the loop aroung the form input fields */

	for ($i=0;$i < count($_POST['Bundles']);$i++){ /*there is an entry in the multi select list box */
		if ($LineItem->Serialised==1){	/*only if the item is serialised */
			$LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem ($_POST['Bundles'][$i], 1);
		} else {
			$LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem ($_POST['Bundles'][$i], 0);
		}
	}


} /*end if the user hit the enter button */

if (isset($_GET['Delete'])){
	unset($LineItem->SerialItems[$_GET['Delete']]);
}

?>
