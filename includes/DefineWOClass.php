<?php

/**
 * Class to hold all the information for a purchase order and delivery
 */
class WorkOrder {

	var $OrderNumber;
	var $LocationCode;
	var $RequiredBy;
	var $StartDate;
	var $CostIssued;
	var $Closed;
	var $Items; //Array of WOItem objects
	var $NumberOfItems;
	var $Reference;
	var $Remark;

	function __construct() {
		$this->OrderNumber = 0;
		$this->LocationCode = $_SESSION['UserStockLocation'];
		$this->RequiredBy = Date($_SESSION['DefaultDateFormat']);
		$this->StartDate = Date($_SESSION['DefaultDateFormat']);
		$this->CostIssued = 0;
		$this->Closed = 0;
		$this->Items = array();
		$this->NumberOfItems = 0;
		$this->Reference = '';
		$this->Remark = '';
	}

	function AddItemToOrder($StockId, $Comments, $QuantityRequired, $QuantityReceived, $NextLotSerialNumber) {
		$this->Items[$this->NumberOfItems] = new WOItem($StockId, $Comments, $QuantityRequired, $QuantityReceived, $NextLotSerialNumber, $this->LocationCode, $this->NumberOfItems + 1);
		$this->NumberOfItems++;
	}

	function UpdateItem($StockId, $Comments, $QuantityRequired, $NextLotSerialNumber = '') {
		$this->Items[$this->ItemByStockID($StockId) ]->QuantityRequired = $QuantityRequired;
		$this->Items[$this->ItemByStockID($StockId) ]->Comments = $Comments;
		$this->Items[$this->ItemByStockID($StockId) ]->NextLotSerialNumbers = $NextLotSerialNumber;
		//		$this->Items[$this->ItemByStockID($StockId)]->RefreshRequirements($this->LocationCode);

	}

	function RemoveItemFromOrder($LineNumber) {

		$this->Items[$LineNumber]->QuantityRequired = $this->Items[$LineNumber]->QuantityReceived;
		$this->Items[$LineNumber]->RefreshRequirements($this->LocationCode);
		if ($this->OrderNumber != 0) {
			$SQL = "DELETE FROM worequirements WHERE wo='" . $this->OrderNumber . "' AND parentstockid='" . $this->Items[$LineNumber]->StockId . "'";
			$DeleteResult = DB_query($SQL);
			$SQL = "DELETE FROM woitems WHERE wo='" . $this->OrderNumber . "' AND stockid='" . $this->Items[$LineNumber]->StockId . "'";
			$DeleteResult = DB_query($SQL, __('Error deleting the item'));
		}
		unset($this->Items[$LineNumber]);
		$this->NumberOfItems--;
	}

	function ItemByStockID($StockId) {
		for ($i = 0;$i <= $this->NumberOfItems;$i++) {
			if (isset($this->Items[$i]) and $this->Items[$i]->StockId == $StockId) {
				return $i;
			}
		}
		return 0;
	}

	function Save() {

		if ($this->OrderNumber == 0) {
			$this->OrderNumber = GetNextTransNo(40);
			$SQL = "INSERT INTO workorders (wo,
											loccode,
											requiredby,
											startdate,
											costissued)
										VALUES (
											'" . $this->OrderNumber . "',
											'" . $this->LocationCode . "',
											'" . FormatDateForSQL($this->RequiredBy) . "',
											'" . FormatDateForSQL($this->StartDate) . "',
											'" . $this->CostIssued . "'
										)";
		} else {
			$SQL = "UPDATE workorders SET   loccode='" . $this->LocationCode . "',
											requiredby='" . FormatDateForSQL($this->RequiredBy) . "',
											startdate='" . FormatDateForSQL($this->StartDate) . "',
											costissued='" . $this->CostIssued . "'
										WHERE wo='" . $this->OrderNumber . "'";
		}
		$UpdateWOResult = DB_query($SQL);
		foreach ($this->Items as $i => $Item) {
			$Item->Save($this->OrderNumber);
		}
	}

	function Load($WONumber) {

		$SQL = "SELECT  loccode,
						requiredby,
						startdate,
						costissued,
						closed
					FROM workorders
					WHERE workorders.wo='" . $WONumber . "'";

		$WOResult = DB_query($SQL);
		if (DB_num_rows($WOResult) == 1) {

			$MyRow = DB_fetch_array($WOResult);
			$this->StartDate = ConvertSQLDate($MyRow['startdate']);
			$this->CostIssued = $MyRow['costissued'];
			$this->Closed = $MyRow['closed'];
			$this->RequiredBy = ConvertSQLDate($MyRow['requiredby']);
			$this->LocationCode = $MyRow['loccode'];
			$this->OrderNumber = $WONumber;

			$ErrMsg = __('Could not get the work order items');
			$WOItemsResult = DB_query("SELECT   stockid,
												qtyreqd,
												qtyrecd,
												stdcost,
												nextlotsnref,
												comments
											FROM woitems
											WHERE wo='" . $WONumber . "'", $ErrMsg);

			$NumberOfOutputs = DB_num_rows($WOItemsResult);
			$i = 0;
			while ($WOItem = DB_fetch_array($WOItemsResult)) {
				$this->Items[$i] = new WOItem($WOItem['stockid'], $WOItem['comments'], $WOItem['qtyreqd'], $WOItem['qtyrecd'], $WOItem['nextlotsnref'], $this->LocationCode, $i);
				++$i;
			}
			$this->NumberOfItems = $i;
		}
	}
}

class WOItem {

	var $StockId;
	var $Comments;
	var $Description;
	var $DecimalPlaces;
	var $QuantityRequired;
	var $QuantityReceived;
	var $Controlled;
	var $Serialised;
	var $StandardCost;
	var $NextLotSerialNumbers;
	var $LineNumber;
	var $Requirements; // Array of WORequirement objects
	var $NumberOfRequirements;
	var $NextLotSerialNumber;

	function __construct($StockId, $Comments, $QuantityRequired, $QuantityReceived, $NextLotSerialNumber, $LocationCode, $NumberOfItems) {

		$StockResult = DB_query("SELECT stockmaster.actualcost AS cost,
										stockmaster.description,
										stockmaster.decimalplaces,
										stockmaster.controlled,
										stockmaster.serialised
									FROM stockmaster
									INNER JOIN bom
										ON stockmaster.stockid=bom.parent
									WHERE bom.parent='" . $StockId . "'");
		$StockRow = DB_fetch_array($StockResult);
		$StandardCost = $StockRow['cost'] * $QuantityRequired;
		$Description = $StockRow['description'];
		$DecimalPlaces = $StockRow['decimalplaces'];
		$Controlled = $StockRow['controlled'];
		$Serialised = $StockRow['serialised'];

		$this->StockId = $StockId;
		$this->Comments = $Comments;
		$this->Description = $Description;
		$this->DecimalPlaces = $DecimalPlaces;
		$this->QuantityRequired = $QuantityRequired;
		$this->QuantityReceived = $QuantityReceived;
		$this->StandardCost = $StandardCost;
		$this->Controlled = $Controlled;
		$this->Serialised = $Serialised;
		$this->NextLotSerialNumber = $NextLotSerialNumber;
		$this->LineNumber = $NumberOfItems;
		$this->Requirements = array();
		$this->NumberOfRequirements = 0;

		$BOMResult = DB_query("SELECT   bom.component,
										bom.quantity,
										bom.autoissue,
										description,
										decimalplaces,
										stockmaster.actualcost
									FROM bom
									INNER JOIN stockmaster
										ON stockmaster.stockid=bom.component
									WHERE bom.parent='" . $StockId . "'
										AND bom.loccode='" . $LocationCode . "'");
		while ($BOMRow = DB_fetch_array($BOMResult)) {
			$this->AddRequirements($BOMRow['component'], $BOMRow['quantity'] * $QuantityRequired, $BOMRow['actualcost'], $BOMRow['autoissue'], $BOMRow['description'], $BOMRow['decimalplaces']);
		}
	}

	function Save($OrderNumber) {

		$CheckSQL = "SELECT wo,
							stockid
						FROM woitems
						WHERE wo='" . $OrderNumber . "'
							AND stockid='" . $this->StockId . "'";
		$CheckResult = DB_query($CheckSQL);

		if (DB_num_rows($CheckResult) == 0) {
			$SQL = "INSERT INTO woitems (wo,
										stockid,
										comments,
										qtyreqd,
										qtyrecd,
										stdcost,
										nextlotsnref
									) VALUES (
										'" . $OrderNumber . "',
										'" . $this->StockId . "',
										'" . $this->Comments . "',
										'" . $this->QuantityRequired . "',
										'" . $this->QuantityReceived . "',
										'" . $this->StandardCost . "',
										'" . $this->NextLotSerialNumbers . "'
									)";
		} else {
			$SQL = "UPDATE woitems SET  qtyreqd='" . $this->QuantityRequired . "',
										comments='" . $this->Comments . "',
										qtyrecd='" . $this->QuantityReceived . "',
										stdcost='" . $this->StandardCost . "',
										nextlotsnref='" . $this->NextLotSerialNumbers . "'
									WHERE wo='" . $OrderNumber . "'
										AND stockid='" . $this->StockId . "'";
		}
		$UpdateItems = DB_query($SQL);
		foreach ($this->Requirements as $i => $Requirement) {
			$Requirement->Save($OrderNumber);
		}
	}

	function AddRequirements($StockId, $Quantity, $StandardCost, $AutoIssue, $Description, $DecimalPlaces) {
		$this->Requirements[$this->NumberOfRequirements + 1] = new WORequirement($this->StockId, $StockId, $Quantity, $StandardCost, $AutoIssue, $Description, $DecimalPlaces);
		$this->NumberOfRequirements++;
	}

	function LoadRequirements() {
	}

	function RefreshRequirements($LocationCode) {

		$BOMResult = DB_query("SELECT   bom.component,
										bom.quantity,
										bom.autoissue,
										description,
										decimalplaces,
										stockcosts.materialcost,
										stockcosts.labourcost,
										stockcosts.overheadcost
									FROM bom
									INNER JOIN stockmaster
										ON stockmaster.stockid=bom.component
									LEFT JOIN stockcosts
										ON stockmaster.stockid=stockcosts.stockid
										AND stockcosts.succeeded=0
									WHERE bom.parent='" . $this->StockId . "'
										AND bom.loccode='" . $LocationCode . "'");
		while ($BOMRow = DB_fetch_array($BOMResult)) {
			$this->Requirements[$this->RequirementByStockID($this->StockId, $BOMRow['component']) ]->Quantity = $BOMRow['quantity'];
		}
	}

	function RequirementByStockID($Parent, $StockId) {
		for ($i = 1;$i <= $this->NumberOfRequirements;$i++) {
			if ($this->Requirements[$i]->StockId == $StockId and $this->Requirements[$i]->ParentStockId == $Parent) {
				return $i;
			}
		}
		return 0;
	}
}

class WORequirement {

	var $ParentStockId;
	var $StockId;
	var $Description;
	var $DecimalPlaces;
	var $Quantity;
	var $StandardCost;
	var $AutoIssue;

	function __construct($ParentStockId, $StockId, $Quantity, $StandardCost, $AutoIssue, $Description, $DecimalPlaces) {
		$this->ParentStockId = $ParentStockId;
		$this->StockId = $StockId;
		$this->Quantity = $Quantity;
		$this->StandardCost = $StandardCost;
		$this->AutoIssue = $AutoIssue;
		$this->Description = $Description;
		$this->DecimalPlaces = $DecimalPlaces;
	}

	function Save($OrderNumber) {

		$CheckSQL = "SELECT wo,
							parentstockid,
							stockid
						FROM worequirements
						WHERE wo='" . $OrderNumber . "'
							AND parentstockid='" . $this->ParentStockId . "'
							AND stockid='" . $this->StockId . "'";
		$CheckResult = DB_query($CheckSQL);

		if (DB_num_rows($CheckResult) == 0) {
			$SQL = "INSERT INTO worequirements (wo,
												parentstockid,
												stockid,
												qtypu,
												stdcost,
												autoissue
											) VALUES (
												'" . $OrderNumber . "',
												'" . $this->ParentStockId . "',
												'" . $this->StockId . "',
												'" . $this->Quantity . "',
												'" . $this->StandardCost . "',
												'" . $this->AutoIssue . "'
											)";
		} else {
			$SQL = "UPDATE worequirements SET   qtypu='" . $this->Quantity . "',
												stdcost='" . $this->StandardCost . "',
												autoissue='" . $this->AutoIssue . "'
											WHERE wo='" . $OrderNumber . "'
												AND parentstockid='" . $this->ParentStockId . "'
												AND stockid='" . $this->StockId . "'";
		}
		$UpdateRequirements = DB_query($SQL);
	}
}
