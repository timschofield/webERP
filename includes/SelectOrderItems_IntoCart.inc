<?php
/* $Revision: 1.4 $ */
/*SelectOrderItems_IntoCart.inc

This snippet is used to enter order line items into the cart object:

Used only in: SelectOrderItems.php

The reason that it is in this seperate file is because it is used within a loop to get kitset
items into the cart as well as plain vanilla items outside of the kitset loop
just reduces a double up in the typing and if it does need to be modified - it is all in one place */

$AlreadyOnThisOrder =0;

if (count($_SESSION['Items']->LineItems)>0){

	foreach ($_SESSION['Items']->LineItems AS $OrderItem) {

	/* do a loop round the items on the order to see that the item
	is not already on this order */

		if (strcasecmp($OrderItem->StockID, $NewItem)==0) {
			$AlreadyOnThisOrder = 1;
			echo "<B>Warning:</B> the part " . $NewItem . " is already on this order - the system will not allow the same item on the order more than once. However, you can change the quantity ordered of the existing line if necessary";
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
		StockMaster.DecimalPlaces
	FROM StockMaster INNER JOIN LocStock
	ON StockMaster.StockID=LocStock.StockID
	WHERE LocStock.LocCode='" . $_SESSION['Items']->Location . "'
	AND  StockMaster.StockID = '". $NewItem . "'";

    $ErrMsg = "The details for " . $NewItem . " could not be retrieved because";
    $DbgMsg = "<BR>The SQL used to retrieve the pricing details but failed was:<BR>$sql";
    $result1 = DB_query($sql,$db,$ErrMsg,$DbgMsg);

   if ($myrow = DB_fetch_array($result1)){

   		if ($_SESSION['Items']->add_to_cart ($NewItem,
							$NewItemQty,
							$myrow["Description"],
							GetPrice ($NewItem, $_SESSION['Items']->DebtorNo,$_SESSION['Items']->Branch, &$db),
							0,
							$myrow["Units"],
							$myrow["Volume"],
							$myrow["KGS"],
							$myrow["Quantity"],
							$myrow["MBflag"],
							NULL, /*Actual Dispatch Date */
							0, /*Qty Invoiced */
							$myrow["DiscountCategory"],
							0, /*Controlled - dont care */
							0, /*serialised - dont care */
							$myrow['DecimalPlaces'],
							'Yes' /*Do please update the DB */
							)==1){

		      $_SESSION['Items']->LineItems[$NewItem]->StandardCost = $myrow["StandardCost"];
             }
   } else {
             echo "<FONT COLOR=RED SIZE=4>The part code '" . $NewItem . "' cannot be added to the order because the item cannot be found in the database.</FONT><P>";
   }

} /* end of if not already on the order */

?>
