<?php
/* $Revision: 1.3 $ */
/*ProcessSerialItems.php takes the posted variables and adds to the SerialItems array
 in either the cartclass->LineItems->SerialItems or the POClass->LineItems->SerialItems */

/********************************************
        Added KEYED Entry values
********************************************/
if ( isset($_POST['AddBatches']) && $_POST['AddBatches']!='') {

	for ($i=0;$i < 10;$i++){
		if(strlen($_POST['SerialNo' . $i])>0){
			if ($ItemMustExist){
				$ExistingBundleQty = ValidBundleRef($StockID, $LocationOut, $_POST['SerialNo' . $i]);
				if ($ExistingBundleQty >0){
					$AddThisBundle = true;
					/*If the user enters a duplicate serial number the later one over-writes
					the first entered one - no warning given though ? */
					if ($_POST['Qty' . $i] > $ExistingBundleQty){
						if ($LineItem->Serialised ==1){
							echo '<BR>';
							prnMsg ( $_POST['SerialNo' . $i] . ' ' . 
								 _('has already been sold'),'warning' );
							$AddThisBundle = false;
						} elseif ($ExistingBundleQty==0) { /* and its a batch */
							echo '<BR>';
							prnMsg ( _('There is none of') . ' '. $_POST['SerialNo' . $i] . 
								' '. _('remaining').'.', 'warn');
							$AddThisBundle = false;
						} else {
							echo '<BR>';
						 	prnMsg (  _('There is only'). ' ' . $ExistingBundleQty . 
							' '._('of') . ' ' . $_POST['SerialNo' . $i] . ' '. _('remaining') . '. ' . _('The entered quantity will be reduced to the remaining amount left of this batch/bundle/roll'),'warn');
							$_POST['Qty' . $i] = $ExistingBundleQty;
							$AddThisBundle = true;
						}
					}
					if ($AddThisBundle==true){
						$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem ($_POST['SerialNo' . $i], 
									 ($InOutModifier>0?1:-1) * $_POST['Qty' . $i]);
					}
				} /*end if ExistingBundleQty >0 */
				else {
        	                        echo '<BR>';
	                                prnMsg( $_POST['SerialNo'. $i] . ' ' ._('not available') . '...' , '', 'Notice' );
					unset($_POST['SerialNo' . $i]);
				}
			} // end of ItemMustExist
			else {
                        	$LineItem->SerialItems[$_POST['SerialNo' . $i]] = new SerialItem ($_POST['SerialNo' . $i],
                                                       ($InOutModifier>0?1:-1) * $_POST['Qty' . $i]);
			}
		} /* end if posted Serialno . i is not blank */

	} /* end of the loop aroung the form input fields */

	for ($i=0;$i < count($_POST['Bundles']);$i++){ /*there is an entry in the multi select list box */
		if ($LineItem->Serialised==1){	/*only if the item is serialised */
			$LineItem->SerialItems[$_POST['Bundles'][$i]] = new SerialItem ($_POST['Bundles'][$i],  ($InOutModifier>0?1:-1) );
		} else {
			list($SerialNo, $Qty) = explode ('/|/', $_POST['Bundles'][$i]);
                        if ($Qty != 0) {
				$LineItem->SerialItems[$SerialNo] = 
					new SerialItem ($SerialNo,  $Qty*($InOutModifier>0?1:-1) );
			}
		}
	}

} /*end if the user hit the enter button on Keyed Entry */
 
 /********************************************
   Add a Sequence of Items and save entries
 ********************************************/
if ( isset($_POST['AddSequence']) && $_POST['AddSequence']!='') {
	// do some quick validation
	$BeginNo =  $_POST['BeginNo'];
	$EndNo   = $_POST['EndNo'];
	if ($BeginNo > $EndNo){
		prnMsg( _('To Add Items Sequentially, the Begin Number must be less than the End Number'), 'error');
	} else {
		$sql = "SELECT serialno FROM stockserialitems 
			WHERE serialno BETWEEN '". $BeginNo . "' AND '". $EndNo . "' 
			AND stockid = '". $StockID."' AND loccode='". $LocationOut . "'";
		echo $sql;
                $SeqItems = DB_query($sql,$db);

                while ($myrow=db_fetch_array($SeqItems)) {
			
			$LineItem->SerialItems[$myrow['serialno']] = new SerialItem ($myrow['serialno'], ($InOutModifier>0?1:-1) );

			//force it to Keyed entry for cleanup & manual verification
			$_POST['EntryType'] = 'KEYED';

		}
	}//end of is valid request

} /* end of input by Sequence Number */

/********************************************
  Validate an uploaded FILE and save entries
********************************************/
$valid = true;
if ($_POST['EntryType']=='FILE' && isset($_POST['ValidateFile'])){

                $filename = $_SESSION['CurImportFile']['tmp_name'];
                $handle = fopen($filename, 'r');
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
                                        //$LineItem->SerialItems[$pieces[0]] = new SerialItem ($pieces[0],  1 );
					$NewSerialNo = $pieces[0];
					$NewQty = ($InOutModifier>0?1:-1);
                                } else {
                                        $valid = false;
                                }
                        } else {
                        //for controlled only items, we must receive: BatchID, Qty in a comma delimited  file

                                if($pieces[0] != "" && $pieces[1] != "" && is_numeric($pieces[1]) && $pieces[1] > 0 ){
                                /*If the user enters a duplicate batch number the later one over-writes
                                the first entered one - no warning given though ? */
                                        //$LineItem->SerialItems[$pieces[0]] = new SerialItem ($pieces[0],  $pieces[1] );
                                        $NewSerialNo = $pieces[0];
                                        $NewQty = ($InOutModifier>0?1:-1) * $pieces[1];
                                } else {
                                        $valid = false;
                                }
                        }
                        $TotalLines++;
			if ($ItemMustExist){
	                        $ExistingBundleQty = ValidBundleRef($StockID, $LocationOut, $NewSerialNo);
	                        if ($ExistingBundleQty >0){
                                $AddThisBundle = true;
        	                        /*If the user enters a duplicate serial number the later one over-writes
                	                the first entered one - no warning given though ? */
                        	        if ($NewQty > $ExistingBundleQty){
                                	        if ($LineItem->Serialised ==1){
	                                                echo '<BR>' . $NewSerialNo . ' '. _('has already been sold'). '.';
        	                                        $AddThisBundle = false;
                		                        } elseif ($ExistingBundleQty==0) { /* and its a batch */
                                        	        echo '<BR>' . _('There is none of'). ' '. $NewSerialNo . ' '. 
								_('remaining') .'.';
                                	                $AddThisBundle = false;
	                                        } else {
        	                                        echo '<BR>'. _('There is only') . ' ' . $ExistingBundleQty . ' '. 
								_('of') . ' ' . $NewSerialNo . ' ' . _('remaining') . '. '. _('The entered quantity will be reduced to the remaining amount left of this batch/bundle/roll');
                	                                $NewQty = $ExistingBundleQty;
                        	                        $AddThisBundle = true;
                                	        }
	                                }
        	                        if ($AddThisBundle==true){
                	                        $LineItem->SerialItems[$NewSerialNo] = new SerialItem ($NewSerialNo, $NewQty);
                        	        }
	                        } /*end if ExistingBundleQty >0 */
				else {
					echo '<BR>';
					prnMsg( $NewSerialNo . ' ' . _('not available') . '...' ,'', 'Notice' );
				}
        	                if (!$valid) $invalid_imports++;
			// of MustExist
			} else {
				$LineItem->SerialItems[$NewSerialNo] = new SerialItem ($NewSerialNo, $NewQty);
			}
                }//while (file)
                if ($invalid_imports==0){
			$LineItem->SerialItemsValid=true;
			$_SESSION['CurImportFile']['Processed']=true;
		}
                fclose($handle);
                //we've saved the info we need from the file, so get rid of it
}

/********************************************
  Process Remove actions
********************************************/
if (isset($_GET['DELETEALL'])){
        $RemAll = $_GET['DELETEALL'];
} else {
        $RemAll = 'NO';
}

if ($RemAll == 'YES'){
        unset($LineItem->SerialItems);
        $LineItem->SerialItems=array();
	unset($_SESSION['CurImportFile']);
}

if (isset($_GET['Delete'])){
        unset($LineItem->SerialItems[$_GET['Delete']]);
}
?>
