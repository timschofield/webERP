<?php

/* $Id$*/

/**
If the User has selected Keyed Entry, show them this special select list...
it is just in the way if they are doing file imports
it also would not be applicable in a PO and possible other situations...
**/
if ($_POST['EntryType'] == 'KEYED'){
        /*Also a multi select box for adding bundles to the dispatch without keying */
        $sql = "SELECT serialno, quantity
			FROM stockserialitems
			WHERE stockid='" . $StockID . "' AND loccode ='" .
		$LocationOut."' AND quantity > 0";
	//echo $sql;

	$ErrMsg = '<br />'. _('Could not retrieve the items for'). ' ' . $StockID;
        $Bundles = DB_query($sql,$db, $ErrMsg );
	echo '<table class=selection><tr>';
        if (DB_num_rows($Bundles)>0){
                $AllSerials=array();

		foreach ($LineItem->SerialItems as $Itm){
			$AllSerials[$Itm->BundleRef] = $Itm->BundleQty;
		}

		echo '<td valign="top"><b>'. _('Select Existing Items'). '</b><br />';

		echo '<form action="' . $_SERVER['PHP_SELF'] . '?=' . SID . '" method="POST">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        echo '<input type=hidden name=LineNo value="' . $LineNo . '">
                        <input type=hidden name=StockID value="' . $StockID . '">
                        <input type=hidden name=EntryType value="KEYED">
			<input type=hidden name=EditControlled value="true">
			<select Name=Bundles[] multiple>';

                $id=0;
		$ItemsAvailable=0;
                while ($myrow=DB_fetch_array($Bundles,$db)){
			if ($LineItem->Serialised==1){
				if ( !array_key_exists($myrow['serialno'], $AllSerials) ){
	                        	echo '<option value="' . $myrow['serialno'] . '">' . $myrow['serialno'].'</option>';
					$ItemsAvailable++;
				}
                        } else {
                               if ( !array_key_exists($myrow['serialno'], $AllSerials)  ||
					($myrow['quantity'] - $AllSerials[$myrow['serialno']] >= 0) ) {
					$RecvQty = $myrow['quantity'] - $AllSerials[$myrow['serialno']];
                                        echo '<OPTION VALUE="' . $myrow['serialno'] . '/|/'. $RecvQty .'">' .
						$myrow['serialno'].' - ' . _('Qty left'). ': ' . $RecvQty . '</OPTION>';
					$ItemsAvailable += $RecvQty;
                                }
			}
                }
                echo '</select><br>';
		echo '<br><div class=centre><input type=submit name="AddBatches" value="'. _('Enter'). '"></div><br />';
		echo '</form>';
		echo $ItemsAvailable . ' ' . _('items available');
		echo '</td>';
        } else {
		echo '<td>'. prnMsg( _('There does not appear to be any of') . ' ' . $StockID . ' ' . _('left in'). ' '. $LocationOut , 'warn') . '</td>';
	}

        echo '</tr></table>';
}