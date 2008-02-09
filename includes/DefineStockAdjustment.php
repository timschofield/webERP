<?php
class StockAdjustment {

        var $StockID;
        Var $StockLocation;
        var $Controlled;
        var $Serialised;
        var $ItemDescription;
        Var $PartUnit;
        Var $StandardCost;
        Var $DecimalPlaces;
        Var $Quantity;
        var $SerialItems; /*array to hold controlled items*/

        //Constructor
        function StockAdjustment(){
        	$this->StockID = '';
        	$this->StockLocation = '';
        	$this->Controlled = '';
        	$this->Serialised = '';
        	$this->ItemDescription = '';
        	$this->PartUnit = '';
        	$this->StandardCost = 0;
        	$this->DecimalPlaces = 0;
            $this->SerialItems = array();
            $Quantity =0;
        }
}

