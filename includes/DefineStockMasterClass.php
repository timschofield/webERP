<?php

class StockMaster{


	Var $StockID, $CategoryID, $Description, $LongDescription, $Units;   	  
	Var $MBflag, $LastCurCostDate, $ActualCost, $LastCost, $Materialcost;
	Var $Labourcost, $Overheadcost, $lowestlevel;
	Var $EOQ, $Volume, $KGS, $BarCode, $DiscountCategory, $TaxLevel;
	Var $Discontinued, $Controlled, $Serialised, $StkModClass;
		 
	function StockMaster(){
	
	}			 
	

	function load($StkId){
		$sql = "SELECT * FROM StockMaster WHERE StockID=$StkId";
		$result = DB_query($sql,$db);
		if (DB_error_no($db) !=0) {
			echo "<BR>The stock details for " . $_POST['StockID'] . " could not be retrieved because - " . DB_error_msg($db);
			if ($debug==1){
			echo "<BR>The SQL used to retrieve the details of the item, but failed was:<BR>$sql";
			}
		} else {
			
			if (DB_num_rows($result)!=1) {
				echo "SHOULD NOT HAVE RETURNED MORE THAN 1 ITEM";
			}
			$myrow = DB_fetch_array($result);
			$this->StockID = $myrow["StockID"];
			$this->CategoryID = $myrow["CategoryID"];
			$this->Description  = $myrow["Description"];
			$this->LongDescription  = $myrow["LongDescription"];
			$this->Units = $myrow["Units"];
			$this->MBflag = $myrow["MBflag"];   	  
			$this->LastCurCostDate = $myrow["LastCurCostDate"];  
			$this->ActualCost = $myrow["ActualCost"];
			$this->LastCost = $myrow["LastCost"];
			$this->Materialcost = $myrow["Materialcost"];
			$this->Labourcost = $myrow["Labourcost"];
			$this->Overheadcost = $myrow["Overheadcost"];
			$this->lowestlevel = $myrow["lowestlevel"];  	  
			$this->Discontinued = $myrow["Discontinued"]; 
			$this->Controlled  = $myrow["Controlled"];
			$this->Serialised  = $myrow["Serialised"];
			$this->EOQ = $myrow["EOQ"];
			$this->Volume = $myrow["Volume"];
			$this->KGS  = $myrow["KGS"];
			$this->BarCode  = $myrow["BarCode"];
			$this->DiscountCategory  = $myrow["DiscountCategory"]; 
			$this->TaxLevel = $myrow["TaxLevel"];
			$this->StkModClass = $myrow["StkModClass"];
		}
	}//load
	




	function isDiscontinued(){
		/**MAYBE WRONG **/
		if ($this->Discontinued > 1) return true;
		else return false;
	}
	
	function isControllled(){
		if ($this->Controlled > 1) return true;
		else return false;
	}
	
	function isSerialised(){
		if ($this->Serialised > 1) return true;
		else return false;
	}







}//class StockMaster

?>
