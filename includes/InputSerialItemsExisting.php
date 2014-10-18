<?php

/* $Id: InputSerialItemsExisting.php 6512 2013-12-23 06:46:46Z exsonqu $*/

/**
If the User has selected Keyed Entry, show them this special select list...
it is just in the way if they are doing file imports
it also would not be applicable in a PO and possible other situations...
**/
if ($_POST['EntryType'] == 'KEYED'){
        /*Also a multi select box for adding bundles to the dispatch without keying */
     $sql = "SELECT serialno, quantity
			FROM stockserialitems
			WHERE stockid='" . $StockID . "'
			AND loccode ='" . $LocationOut."'
			AND quantity > 0";

	$ErrMsg = '<br />' .  _('Could not retrieve the items for'). ' ' . $StockID;
    $Bundles = DB_query($sql,$db, $ErrMsg );
	echo '<table class="selection"><tr>';
	if (DB_num_rows($Bundles)>0){
		$AllSerials=array();

		foreach ($LineItem->SerialItems as $Itm){
			$AllSerials[$Itm->BundleRef] = $Itm->BundleQty;
		}

		echo '<td valign="top"><b>' .  _('Select Existing Items'). '</b><br />';

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier=' . $identifier . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="LineNo" value="' . $LineNo . '">
			<input type="hidden" name="StockID" value="' . $StockID . '">
			<input type="hidden" name="EntryType" value="KEYED">
			<input type="hidden" name="identifier" value="' . $identifier . '">
			<input type="hidden" name="EditControlled" value="true">
			<select name=Bundles[] multiple="multiple">';

		$id=0;
		$ItemsAvailable=0;
		while ($myrow=DB_fetch_array($Bundles,$db)){
			if ($LineItem->Serialised==1){
				if ( !array_key_exists($myrow['serialno'], $AllSerials) ){
					echo '<option value="' . $myrow['serialno'] . '">' . $myrow['serialno'] . '</option>';
					$ItemsAvailable++;
				}
			} else {
				
				if ( !array_key_exists($myrow['serialno'], $AllSerials)  OR
					($myrow['quantity'] - $AllSerials[$myrow['serialno']] >= 0) ) {
					//Use the $InOutModifier to ajust the negative or postive direction of the quantity. Otherwise the calculated quantity is wrong.
					$RecvQty = $myrow['quantity'] - $InOutModifier*$AllSerials[$myrow['serialno']];
					echo '<option value="' . $myrow['serialno'] . '/|/'. $RecvQty .'">' . $myrow['serialno'].' - ' . _('Qty left'). ': ' . $RecvQty . '</option>';
					$ItemsAvailable += $RecvQty;
				}
			}
		}
		echo '</select>
			<br />';
		echo '<br /><div class="centre"><input type="submit" name="AddBatches" value="'. _('Enter'). '"></div>
			<br />';
		echo '</form>';
		echo $ItemsAvailable . ' ' . _('items available');
		echo '</td>';
	} else {
		echo '<td>' .  prnMsg( _('There does not appear to be any of') . ' ' . $StockID . ' ' . _('left in'). ' '. $LocationOut , 'warn') . '</td>';
	}
	echo '</tr></table>';
}
