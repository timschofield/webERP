<?php
/* $Revision: 1.5 $ */
/* Definition of the cart class
this class can hold all the information for:

i)   a sales order
ii)  an invoice
iii) a credit note

*/

Class Cart {

	var $LineItems; /*array of objects of class LineDetails using the product id as the pointer */
	var $total; /*total cost of the items ordered */
	var $totalVolume;
	var $totalWeight;
	var $ItemsOrdered; /*no of different line items ordered */
	var $DeliveryDate;
	var $DefaultSalesType;
	var $SalesTypeName;
	var $DefaultCurrency;
	var $DeliverTo;
	var $DelAdd1;
	var $DelAdd2;
	var $DelAdd3;
	var $DelAdd4;
	var $PhoneNo;
	var $Email;
	var $CustRef;
	var $Comments;
	var $Location;

	var $DebtorNo;
	var $CustomerName;
	var $Orig_OrderDate;
	var $Branch;
	var $TransID;
	var $ShipVia;
	var $FreightCost;

	function Cart(){
	/*Constructor function initialises a new shopping cart */
		$this->LineItems = array();
		$this->total=0;
		$this->ItemsOrdered=0;
		$this->DefaltSalesType="";
	}

	function add_to_cart($StockID, $Qty, $Descr, $Price, $Disc, $UOM, $Volume, $Weight,$QOHatLoc=0, $MBflag='B', $ActDispatchDate=NULL, $QtyInvoiced=0, $DiscCat=''){

		if (isset($StockID) AND $StockID!="" AND $Qty>0 AND isset($Qty)){

			if ($Price<0){ /*madness check - use a credit note to give money away!*/
				$Price=0;
			}

			$this->LineItems[$StockID] = new LineDetails($StockID, $Descr, $Qty, $Price, $Disc, $UOM, $Volume, $Weight, $QOHatLoc, $MBflag, $ActDispatchDate, $QtyInvoiced, $DiscCat);
			$this->ItemsOrdered++;

			if ($_SESSION['ExistingOrder']!=0 AND !isset($_GET['ModifyOrderNumber'])){
				/*ExistingOrder !=0 set means that an order is selected or created for entry
				of items - ExistingOrder is set to 0 in scripts that should not allow
				adding items to the order - New orders have line items added at the time of
				committing the order to the DB in DeliveryDetails.php
				 GET['ModifyOrderNumber'] is only set when the items are first
				being retrieved from the DB - dont want to add them again - would return
				errors anyway */

				echo "<BR>ExistingOrder = " . $_SESSION['ExistingOrder'] . " and ModifyOrderNumber = " . $_GET['ModifyOrderNumber'];

				global $db;
				$result = DB_query("INSERT INTO SalesOrderDetails (OrderNo, StkCode, Quantity, UnitPrice, DiscountPercent) VALUES(" . $_SESSION['ExistingOrder'] . ", '" . $StockID ."'," . $Qty . ", " . $Price . ", " . $Disc . ")" , $db , "<BR>The order line for " . $StockID . " could not be inserted");
			}

			Return 1;
		}
		Return 0;
	}

	function update_cart_item($UpdateItem, $Qty, $Price, $Disc){

		if ($Qty>0){
			$this->LineItems[$UpdateItem]->Quantity = $Qty;
		}
		$this->LineItems[$UpdateItem]->Price = $Price;
		$this->LineItems[$UpdateItem]->DiscountPercent = $Disc;

		if ($_SESSION['ExistingOrder']!=0){
			global $db;
			$result = DB_query("UPDATE SalesOrderDetails SET Quantity=" . $Qty . ", UnitPrice=" . $Price . ", DiscountPercent=" . $Disc . " WHERE OrderNo=" . $_SESSION['ExistingOrder'] . " AND StkCode='" . $UpdateItem ."'" , $db , "<BR>The order line for " . $UpdateItem . " could not be updated");
		}
	}

	function remove_from_cart(&$StockID){
		if (isset($StockID)){
			unset($this->LineItems[$StockID]);
			$this->ItemsOrdered--;
		}
		if ($_SESSION['ExistingOrder']!=0){
			global $db;
			$result = DB_query("DELETE FROM SalesOrderDetails WHERE OrderNo=" . $_SESSION['ExistingOrder'] . " AND StkCode='" . $StockID ."'",$db,"The order line for " . $StockID . " couold not be deleted");
		}
	}

	function Get_StockID_List(){
		/* Makes a comma seperated list of the stock items ordered
		for use in SQL expressions */

		$StockID_List="";
		foreach ($this->LineItems as $StockItem) {
			$StockID_List .= ",'" . $StockItem->StockID . "'";
		}

		return substr($StockID_List, 1);

	}

	function Any_Already_Delivered(){
		/* Checks if there have been deliveries of line items */

		foreach ($this->LineItems as $StockItem) {
			if ($StockItem->QtyInv !=0){
				return 1;
			}
		}

		return 0;

	}

	function Some_Already_Delivered($StockID){
		/* Checks if there have been deliveries of a specific line item */

		if ($this->LineItems[$StockID]->QtyInv !=0){
			return 1;
		}
		return 0;
	}
} /* end of class defintion */

Class LineDetails {
	Var $StockID;
	Var $ItemDescription;
	Var $Quantity;
	Var $Price;
	Var $DiscountPercent;
	Var $Units;
	Var $Volume;
	Var $Weight;
	Var $ActDispDate;
	Var $QtyInv;
	Var $QtyDispatched;
	Var $StandardCost;
	Var $QOHatLoc;
	Var $MBflag;
	Var $DiscCat; /* Discount Category of the item if any */
	Var $TaxRate;
	
	function LineDetails ($StockItem, $Descr, $Qty, $Prc, $DiscPercent, $UOM, $Volume, $Weight, $QOHatLoc, $MBflag, $ActDispatchDate, $QtyInvoiced, $DiscCat){

/* Constructor function to add a new LineDetail object with passed params */

		$this->StockID =$StockItem;
		$this->ItemDescription = $Descr;
		$this->Quantity = $Qty;
		$this->Price = $Prc;
		$this->DiscountPercent = $DiscPercent;
		$this->Units = $UOM;
		$this->Volume = $Volume;
		$this->Weight = $Weight;
		$this->ActDispDate = $ActDispatchDate;
		$this->QtyInv = $QtyInvoiced;
		$this->QtyDispatched = $Qty - $QtyInvoiced;
		$this->QOHatLoc = $QOHatLoc;
		$this->MBflag = $MBflag;
		$this->DiscCat = $DiscCat;

	}
}

?>
