<?php

class CellPhoneItem extends GenericStockItem{

	/** From GenericStockItem
	var $StockID;
	var $SerialNo;
	var $StockMoveNo;
	var $Valid=false;
	var $ValidationMsg
	**/

        var $ModuleName="CellPhoneItem";
	var $ESN, $MDN, $CarrierId, $Activated;

	var $_PO_Header = "<table><tr><td class=tableheader>#</td><td class=tableheader>SerialNo</td><td class=tableheader>Activated</td><td class=tableheader>MDN</td><td class=tableheader>Status</td></tr>";
	var $_PO_Footer = "</table>";

	//Constructor
	function CellPhoneItem(){
	
	}
	
	//Shows HTML view of item for individual display
	function viewLineItemHTML($Seq=""){
			
		return parent::viewLineItemHTML($Seq).
			"<td>". $this->Activated."</td>";
			"<td>". $this->MDN."</td>";
			"<td>". $this->ValidationMsg."</td>";

	
	}
	
	function editLineItemHTML($Seq="0"){
		
                return parent::editLineItemHTML($Seq).
                        "<td>". $this->Activated."</td>".
                        "<td>". $this->MDN."</td>".
                        "<td>". $this->ValidationMsg."</td>";
	}
	
	//validates data in line items from the edit HTML form for this item
	function validate($APP){
		switch($APP){
			case "PO":
				$len = strlen($this->SerialNo);
				if ($this->SerialNo == "" || !is_numeric($this->SerialNo) || $len < 10 || $len > 13) {
					 $this->Valid=false;
					 $this->ValidationMsg = "Serial No must be 10-13 digits";
				} else {
					$this->Valid=true;
					$this->ValidationMsg = "ok";
				}
				break; //PO
			case "SO":
				$this->Valid = parent::validate("SO");
			   	break; //SO
		}
		return $this->Valid;

	}//validate
	
	//sql insert statements - must return an array of sql statements! That should be coming from parent anyway.
	function add_item_sql(){
		$stmts = parent::add_item_sql();
		$stmts[] = "insert into StockCellPhones (ESN) values ('".$this->SerialNo."')";
		return $stmts;
	}
	
	//returns an array of prior stock moves this item was in
	function getPriorStockMoves(){
		
		//returns array
	}





}//class StockItemModule




?>
