<?php
/* $Revision: 1.12 $ */
/*SelectOrderItems_IntoCart.inc

This snippet is used to enter order line items into the cart object:

Used only in: SelectOrderItems.php

The reason that it is in this seperate file is because it is used within a loop to get kitset
items into the cart as well as plain vanilla items outside of the kitset loop
*/

$AlreadyOnThisOrder =0;

if (count($_SESSION['Items']->LineItems)>0){

	foreach ($_SESSION['Items']->LineItems AS $OrderItem) {

	/* do a loop round the items on the order to see that the item
	is not already on this order */

		if (strcasecmp($OrderItem->StockID, $NewItem)==0) {
			$AlreadyOnThisOrder = 1;
			echo '<B>' . _('Warning') . ':</B> ' . _('the part') . ' ' . $NewItem . ' ' . _('is already on this order') . ' - ' . _('the system will not allow the same item on the order more than once') . '. ' . _('However you can change the quantity ordered of the existing line if necessary');
       		}
	} /* end of the foreach loop to look for preexisting items of the same code */
}

if ($AlreadyOnThisOrder!=1){

    $sql = "SELECT StockMaster.Description,
    		StockMaster.StockID,
		StockMaster.Units,
		StockMaster.Volume,
		StockMaster.KGS,
		(Materialcost+Labourcost+Overheadcost) AS StandardCost,
		LocStock.Quantity,
		StockMaster.MBflag,
		StockMaster.DiscountCategory,
		StockMaster.DecimalPlaces,
		StockMaster.Discontinued
	FROM StockMaster INNER JOIN LocStock
	ON StockMaster.StockID=LocStock.StockID
	WHERE LocStock.LocCode='" . $_SESSION['Items']->Location . "'
	AND  StockMaster.StockID = '". $NewItem . "'";

    $ErrMsg = _('The details for') . ' ' . $NewItem . ' ' . _('could not be retrieved because');
    $DbgMsg = '<BR>' . _('The SQL used to retrieve the pricing details but failed was');
    $result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

    if (DB_num_rows($result1)==0){

		prnMsg(_('The item code') . ' ' . $NewItem  . ' '  . _('could not be found in the database') . ' - ' . _('it has not been added to the order'),'warn',_('Item Does Not Exist'));

    } elseif ($myrow = DB_fetch_array($result1)){

    		if ($myrow['Discontinued']==1){
			prnMsg(_('The item') . ' ' . $NewItem . ' ' . _('could not be added to the order because it has been flagged as obsolete'),'error',_('Obsolete Item'));

		} elseif (($AllowSalesOfZeroCostItems == false
			AND $myrow['StandardCost']>0
			AND ($myrow['MBFlag']=='B'
			OR $myrow['MBFlag']=='M'))
		OR ($AllowSalesOfZeroCostItems == false
			AND ($myrow['MBFlag']='A'
			OR $myrow['MBFlag']=='D'
			OR $myrow['MBFlag']=='K'))
		OR $AllowSalesOfZeroCostItems==true) {

		/*these checks above ensure that the item has a cost if the
		config.php variable AllowSalesOfZeroCostItems is set to false */

		   	if ($_SESSION['ExistingOrder']!=0){
				$UpdateDB = 'Yes';
			} else {
				$UpdateDB = 'No';
			}

   			if ($_SESSION['Items']->add_to_cart ($NewItem,
							$NewItemQty,
							$myrow['Description'],
							GetPrice ($NewItem, $_SESSION['Items']->DebtorNo,$_SESSION['Items']->Branch, &$db),
							0,
							$myrow['Units'],
							$myrow['Volume'],
							$myrow['KGS'],
							$myrow['Quantity'],
							$myrow['MBflag'],
							NULL, /*Actual Dispatch Date */
							0, /*Qty Invoiced */
							$myrow['DiscountCategory'],
							0, /*Controlled - dont care */
							0, /*serialised - dont care */
							$myrow['DecimalPlaces'],
							'', /*Narrative - none yet */
							$UpdateDB
							)==1){

		      		$_SESSION['Items']->LineItems[$NewItem]->StandardCost = $myrow['StandardCost'];
			}
             } else {
			echo '<BR><FONT COLOR=RED SIZE=3><B>' . _('Warning') . ': </FONT></B>' . _('the item code') . ' ' . $NewItem . ' ' . _('does not have a cost set up and order entry is set up to prohibit sales of items with no cost data entered');
	     }
   }

} /* end of if not already on the order */

?>
