<?php

class GenericStockItem {

	var $ModuleName="GenericStockItem";
	var $StockItemId;
	var $StockID;
	var $BundleRef;
	var $BundleQty;
	var $StockMoveNo;
	var $Valid=false;
	var $ValidationMsg;

	var $_PO_Header = "<table><tr><td class=tableheader>#</td><td class=tableheader>Reference No</td><td>Quantity</td></tr>";
	var $_PO_Footer =	"</table>";

	//Constructor
	function GenericStockItem(){

	}

	function viewLineItem($Seq){

		$html = "<tr><td>";
		if (!$this->Valid){
			$html .= markRequired();
		}
		$html .= "$Seq</td>".$this->viewLineItemHTML($Seq)."</tr>\n";
		return $html;
	}

	//Shows HTML view of item for individual display
	function viewLineItemHTML($Seq){

		return "<td>". $this->BundleRef."</td>";
	}

	//Shows HTML form to edit individual item
	function editLineItem($Seq){

		$html = "<tr><td>";
		if (!$this->Valid){ $html .= markRequired();}
		$html .= "$Seq</td>".
				$this->editLineItemHTML($Seq).
				"</tr>\n";
		return $html;

	}

	function editLineItemHTML($Seq){

		return "<td><input type=text size=20 name=\"SERIAL_".$Seq."\" value=\"".$this->BundleRef."\"></td>";

	}

	//validates data in line items from the edit HTML form for this item
	function validate($APP){
		switch($APP){
			case "PO":
				if ($this->BundleRef == "" || (strlen($this->BundleRef) < 4)) {
					 $this->Valid=false;
					 $this->ValidationMsg = "Serial No must be longer than 4 chars";
				} else {
					$this->Valid=true;
					$this->ValidationMsg = "ok";
				}
				break; //PO
			case "SO":
				global $db;
				$SQL = "SELECT StockSerialItems.StockItemId, StockMoves.StkMoveNo, StockMoves.Type FROM StockSerialItems INNER  JOIN StockMoves ON StockSerialItems.StkMoveNo = StockMoves.StkMoveNo WHERE StockSerialItems.SerialNo =  '".$this->BundleRef."'";
				//check it & return
			   	$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The StockItems's availability cannot be retrieved because:";
			   	$DbgMsg = "<BR>The following SQL to check StockItem availability was used:";
			   	$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, false);
			   	if (DB_num_rows($Result) == 0){
			   		$this->Valid = false;
			   		$this->ValidationMsg = "Item never inventoried";
			   	} else {
			   		$myrow = DB_fetch_row($Result);

			   		if ( TypeInInventory($myrow[2]) ){
			   			//only make data available if we are happy with it
			   			$this->StockItemId = $myrow[0];
			   			$this->StockMoveNo = $myrow[1];
			   			$this->Valid = true;
			   			$this->ValidationMsg = "ok";
			   		} else {
			   			$this->Valid = false;
			   			$this->ValidationMsg = "Item exists, but has been sold, removed, etc.";
			   		}
			   	}
			   	DB_free_result($Result);
			   	break; //SO
		}
		return $this->Valid;

	}//validate

	//imports line items from the edit HTML form for this item
	function importFormLineItem($Seq, $APP){

		$this->BundleRef = trim( initPvar("SERIAL_".$Seq) );
		$this->BundleQty = trim( initPvar("QTY_".$Seg) );

		return $this->validate($APP);
	}

	function importFileLineItem($line, $APP){

		$line = explode(" ",$line,2);
		if (isset($line[0]) ){
			$this->BundleRef = trim($line[0]);
		}
		return $this->validate($APP);
	}

	//all functionality to add an Item...
	//StockItem (this object) should have been created via import functions so it has been validated correctly
	//StockMoveNo must be passed - any modules overriding this must implement that so the Generic Parent (this code) gets it.
	function add_item($NewStkMoveNo, $SQLTrans){
		$this->StockMoveNo = $NewStkMoveNo;
		//there may be several Inserts/Updates necessary based on the Module..
		//make sure we can accomodate.
		$stmts = array();
		$stmts = $this->add_item_sql();
		global $db;
		foreach($stmts as $SQL){
			$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock Item records could not be inserted because at Item #$cnt :";
			$DbgMsg = "<BR>The following SQL to insert the Stock Item record was used:";
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, $SQLTrans);
		}//foreach stmt

		return true;

	}//add_item

	//sql insert statements - must return an array of sql statements! That should be coming from parent anyway.
	function add_item_sql(){
		$stmts = array();
		$stmts[] = "INSERT INTO StockSerialItems (StkMoveNo, SerialNo) VALUES (".$this->StockMoveNo.",'".$this->BundleRef."')";
		return $stmts;
	}//add_item_sql


	//all functionality to perform a StockMove on an existing Item.
	//StockItem (this object) should have been created via import functions so it has been validated correctly
	function move_item($NewStkMoveNo, $SQLTrans){
		if ($NewStkMoveNo == "" || !is_numeric($NewStkMoveNo)){
			prnErrMsg("StockItem->move_item() must be called with the new StockMoveNo. DB Tran should fail");
			//can't return else DB Tran would not fail....
		}
		if (!is_numeric($this->StockMoveNo) || !is_numeric($this->StockItemId) ){
			prnErrMsg("Improperly configured StockItemId or StockMove No. DB Tran should fail");
			//can't return else DB Tran would not fail....
		}
		global $db;
		//Update StockItem w/ the new StockMove No
		$SQL = "UPDATE StockSerialItems set StockSerailItems.StkMoveNo=$NewStkMoveNo WHERE StockSerialItems.StockItemId = ".$this->StockItemId;
		$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock Serial Item records could not be updated because: ";
		$DbgMsg = "<BR>The following SQL to insert the Stock Serial Item record was used:";
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, $SQLTrans);

		//Create a StockItem move to track history for each Item.
		$SQL = "INSERT INTO StockItemMoves (FromStkMoveNo, ToStkMoveNo, StockItemId) VALUES ".
			"(".$this->StockMoveNo.", ".$NewStkMoveNo.", ".$this->StockItemId.")";
		$ErrMsg = "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock Item Move records could not be inserted because: ";
		$DbgMsg = "<BR>The following SQL to insert the StockItemMove record was used:";
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, $SQLTrans);

		return true;
	}//move_item



}//class StockItemModule
?>
