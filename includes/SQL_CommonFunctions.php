<?php

/* Common SQL Functions */

function GetNextTransNo($TransType) {

	/* SQL to get the next transaction number these are maintained in the table SysTypes - Transaction Types
	Also updates the transaction number

	10 sales invoice
	11 sales credit note
	12 sales receipt
	etc
	*
	*/
	DB_query("SELECT typeno FROM systypes WHERE typeid='" . $TransType . "' FOR UPDATE");
	$SQL = "UPDATE systypes SET typeno = typeno + 1 WHERE typeid = '" . $TransType . "'";
	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': '
		. __('The transaction number could not be incremented');
	DB_query($SQL, $ErrMsg);
	$SQL = "SELECT typeno FROM systypes WHERE typeid= '" . $TransType . "'";
	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>'
		. __('The next transaction number could not be retrieved from the database because');
	$GetTransNoResult = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_row($GetTransNoResult);
	return $MyRow[0];
}

function GetStockGLCode($StockID) {

	/*Gets the GL Codes relevant to the stock item account from the stock category record */
	$QuerySQL = "SELECT stockact,
						adjglact,
						issueglact,
						purchpricevaract,
						materialuseagevarac,
						wipact
				FROM stockmaster
				INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
				WHERE stockmaster.stockid = '" . $StockID . "'";

	$ErrMsg = __('The stock GL codes could not be retrieved because');
	$GetStkGLResult = DB_query($QuerySQL, $ErrMsg);

	$MyRow = DB_fetch_array($GetStkGLResult);
	return $MyRow;
}

function GetTaxRate($TaxAuthority, $DispatchTaxProvince, $TaxCategory) {

	/*Gets the Tax rate applicable to an item from the TaxAuthority of the branch and TaxLevel of the item */

	$QuerySQL = "SELECT taxrate
				FROM taxauthrates
				WHERE taxauthority='" . $TaxAuthority . "'
				AND dispatchtaxprovince='" . $DispatchTaxProvince . "'
				AND taxcatid = '" . $TaxCategory . "'";

	$ErrMsg = __('The tax rate for this item could not be retrieved because');
	$GetTaxRateResult = DB_query($QuerySQL, $ErrMsg);

	if (DB_num_rows($GetTaxRateResult) == 1) {
		$MyRow = DB_fetch_row($GetTaxRateResult);
		return $MyRow[0];
	} else {
		/*The tax rate is not defined for this Tax Authority and Dispatch Tax Authority */
		return 0;
	}
}

function GetTaxes($TaxGroup, $DispatchTaxProvince, $TaxCategory) {

	$SQL = "SELECT taxgrouptaxes.calculationorder,
					taxauthorities.description,
					taxgrouptaxes.taxauthid,
					taxauthorities.taxglcode,
					taxgrouptaxes.taxontax,
					taxauthrates.taxrate
			FROM taxauthrates
			INNER JOIN taxgrouptaxes
				ON taxauthrates.taxauthority=taxgrouptaxes.taxauthid
			INNER JOIN taxauthorities
				ON taxauthrates.taxauthority=taxauthorities.taxid
			WHERE taxgrouptaxes.taxgroupid='" . $TaxGroup . "'
				AND taxauthrates.dispatchtaxprovince='" . $DispatchTaxProvince . "'
				AND taxauthrates.taxcatid = '" . $TaxCategory . "'
			ORDER BY taxgrouptaxes.calculationorder";

	$ErrMsg = __('The taxes and rate for this tax group could not be retrieved because');
	$GetTaxesResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($GetTaxesResult) >= 1) {
		return $GetTaxesResult;
	} else {
		/*The tax group is not defined with rates */
		return 0;
	}
}

function GetSalesGLCode($salesarea, $partnumber) {
    $SQL="SELECT salesglcode FROM salesglpostings
			WHERE stkcat='any'";
    $Result = DB_query($SQL);
    $MyRow=DB_fetch_array($Result);
    return $MyRow[0];
}

function GetCreditAvailable($DebtorNo) {

	$SQL = "SELECT debtorsmaster.debtorno,
				debtorsmaster.creditlimit,
				SUM(debtortrans.balance) as balance
			FROM debtorsmaster
			INNER JOIN debtortrans
				ON debtorsmaster.debtorno=debtortrans.debtorno
			WHERE debtorsmaster.debtorno='" . $DebtorNo . "'
			GROUP BY debtorsmaster.debtorno,
				debtorsmaster.creditlimit";

	$ErrMsg = __('The current account balance of the customer could not be retrieved because');
	$GetAccountBalanceResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($GetAccountBalanceResult) == 1) {

		$MyRow = DB_fetch_array($GetAccountBalanceResult);
		$CreditAvailable = $MyRow['creditlimit'] - $MyRow['balance'];
	} else {
		$SQL = "SELECT creditlimit
				FROM debtorsmaster
				WHERE debtorno='" . $DebtorNo . "'";
		$GetAccountBalanceResult = DB_query($SQL, $ErrMsg);
		$MyRow = DB_fetch_array($GetAccountBalanceResult);
		$CreditAvailable = $MyRow['creditlimit'];
	}
	/*Take into account the value of outstanding sales orders too */
	$SQL = "SELECT SUM(salesorderdetails.unitprice *
				(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) *
				(1 - salesorderdetails.discountpercent)) AS ordervalue
			FROM salesorders
			INNER JOIN salesorderdetails
				ON salesorders.orderno = salesorderdetails.orderno
			WHERE salesorders.debtorno = '" . $DebtorNo . "'
				AND salesorderdetails.completed = 0
				AND salesorders.quotation = 0";

	$ErrMsg = __('The value of outstanding orders for the customer could not be retrieved because');
	$GetOSOrdersResult = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_array($GetOSOrdersResult);
	$CreditAvailable -= $MyRow['ordervalue'];

	return $CreditAvailable;
}

function ItemCostUpdateGL($StockID, $NewCost, $OldCost, $QOH) {

	if ($_SESSION['CompanyRecord']['gllink_stock'] == 1
		AND $QOH != 0
		AND (abs($NewCost - $OldCost) > pow(10, -($_SESSION['StandardCostDecimalPlaces'] + 1)))) {

		$CostUpdateNo = GetNextTransNo(35);
		$PeriodNo = GetPeriod(date($_SESSION['DefaultDateFormat']));
		$StockGLCode = GetStockGLCode($StockID);

		$ValueOfChange = $QOH * ($NewCost - $OldCost);

		$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
						VALUES ('35',
						'" . $CostUpdateNo . "',
						CURRENT_DATE,
						'" . $PeriodNo . "',
						'" . $StockGLCode['adjglact'] . "',
						'" . mb_substr($StockID . ' ' . __('cost was') . ' ' . $OldCost . ' ' . __('changed to') . ' '
							. $NewCost . ' x ' . __('Quantity on hand of') . ' ' . $QOH, 0, 200) . "',
						'" . -$ValueOfChange . "')";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': '
			. __('The GL credit for the stock cost adjustment posting could not be inserted because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		$SQL = "INSERT INTO gltrans (type,
						typeno,
						trandate,
						periodno,
						account,
						narrative,
						amount)
					VALUES ('35',
						'" . $CostUpdateNo . "',
						CURRENT_DATE,
						'" . $PeriodNo . "',
						'" . $StockGLCode['stockact'] . "',
						'" . mb_substr($StockID . ' ' . __('cost was') . ' ' . $OldCost . ' ' . __('changed to') . ' '
							. $NewCost . ' x ' . __('Quantity on hand of') . ' ' . $QOH, 0, 200) . "',
						'" . $ValueOfChange . "')";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': '
			. __('The GL debit for stock cost adjustment posting could not be inserted because');
		$Result = DB_query($SQL, $ErrMsg, '', true);
	}
}

/* Calculates the material cost of a bill of materials, given parent code */
function BomMaterialCost($Parent) {
	$SQL = "SELECT materialcost FROM stockmaster WHERE stockid='" . $Parent . "'";
	$Result1 = DB_query($SQL);
	$MyRow1 = DB_fetch_row($Result1);
	$OldCost = $MyRow1[0];
	$SQL = "SELECT sum(quantity) as qoh from locstock where stockid='" . $Parent . "'";
	$Result1 = DB_query($SQL);
	$MyRow1 = DB_fetch_row($Result1);
	$QOH = $MyRow1[0];
	$SQL = "SELECT SUM(stockmaster.actualcost * bom.quantity) AS SumOfmaterialcost
			FROM bom
			LEFT JOIN stockmaster
				ON bom.component = stockmaster.stockid
			WHERE bom.parent='". $Parent . "'
				AND bom.effectiveafter <= CURRENT_DATE
				AND bom.effectiveto > CURRENT_DATE";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	$MaterialCost = $MyRow[0];
	if (abs($QOH*($MaterialCost-$OldCost))>0) {
		ItemCostUpdateGL($Parent, $MaterialCost, $OldCost, $QOH);
	}
	return $MaterialCost;
}

/* Iterates through the levels of the bom, recalculating each bom it meets */
function UpdateCost($Item) {
	$SQL = "SELECT parent FROM bom where component = '" . $Item . "'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		$NewParent = $MyRow['parent'];
		$MaterialCost = BomMaterialCost($NewParent);
		$SQL = "UPDATE stockmaster SET materialcost=" . $MaterialCost . " WHERE stockid='" . $NewParent . "'";
		$Result1 = DB_query($SQL);
		if (DB_error_no() != 0) {
			return 1;
		}
		UpdateCost($NewParent);
	}
	return 0;
}

/* Accepts work order information and iterates through the bom, inserting real components (dissolving phantom assemblies) */
function WoRealRequirements($WO, $LocCode, $StockID, $Qty = 1, $ParentID = '') {

	// remember, 'G' is for ghost (phantom part type)

	// all components should be referenced to the initial parent
	if ($ParentID == '') {
		$ParentID = $StockID;
	}

	// insert new real immediate components of this item
	$SQL = "INSERT INTO worequirements (wo,
				parentstockid,
				stockid,
				qtypu,
				stdcost,
				autoissue)
			SELECT '" . $WO . "',
				'" . $ParentID . "',
				bom.component,
				bom.quantity * " . $Qty . ",
				actualcost,
				bom.autoissue
			FROM bom
			INNER JOIN stockmaster
				ON bom.component=stockmaster.stockid
			WHERE bom.parent='" . $StockID . "'
				AND bom.loccode ='" . $LocCode . "'
				AND bom.effectiveafter <= CURRENT_DATE
				AND bom.effectiveto > CURRENT_DATE
				AND stockmaster.mbflag <> 'G'
				AND bom.component NOT IN (
					SELECT stockid
					FROM worequirements
					WHERE wo = '" . $WO . "'
					AND parentstockid = '" . $ParentID . "'
				)";
	$Result = DB_query($SQL);

	// combine real immediate components of this item with other occurrences in this work order
	// otherwise, we could encounter a uniqueness violation:
	//     - the same component could occur in multiple dissolved phantom assemblies
	//     - need to sum quantities of multiple component occurrences
	if ($ParentID != $StockID) {
		$SQL = "UPDATE worequirements
					INNER JOIN (
						SELECT CAST('" . $WO . "' AS SIGNED) as wo,
							CAST('NODE-1' AS CHAR) as parentstockid,
							bom.component AS stockid,
							bom.quantity*1 AS qtypu,
							actualcost AS stdcost,
							bom.autoissue
						FROM bom
						INNER JOIN stockmaster
							ON bom.component=stockmaster.stockid
						WHERE bom.parent='" . $StockID . "'
							AND bom.loccode ='" . $LocCode . "'
							AND bom.effectiveafter <= CURRENT_DATE
							AND bom.effectiveto > CURRENT_DATE
							AND stockmaster.mbflag <> 'G'
							AND bom.component IN (
								SELECT stockid
								FROM worequirements
								WHERE wo = '" . $WO . "'
								AND parentstockid = '". $ParentID . "'
							)
					) AS g ON g.wo=worequirements.wo
						AND g.parentstockid=worequirements.parentstockid
						AND g.stockid=worequirements.stockid
					SET worequirements.qtypu = worequirements.qtypu + g.qtypu";
		$Result = DB_query($SQL);
	}

	// dissolve phantom assemblies
	$SQL = "SELECT
				bom.component,
				bom.quantity
			FROM bom
			INNER JOIN stockmaster
				ON bom.component=stockmaster.stockid
			WHERE parent='" . $StockID . "'
				AND loccode ='" . $LocCode . "'
				AND bom.effectiveafter <= CURRENT_DATE
				AND bom.effectiveto > CURRENT_DATE
				AND stockmaster.mbflag='G'";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		WoRealRequirements($WO, $LocCode, $MyRow['component'], $MyRow['quantity'], $ParentID);
	}
}

/*Ensures general ledger entries balance for a given transaction */
function EnsureGLEntriesBalance($TransType, $TransTypeNo) {

	$Result = DB_query("SELECT COALESCE(SUM(amount), 0)
						FROM gltrans
						WHERE type = '" . $TransType . "'
							AND typeno = '" . $TransTypeNo . "'");
	$MyRow = DB_fetch_row($Result);
	$Difference = $MyRow[0];
	if (abs($Difference) != 0) {
		if (abs($Difference) > 0.1) {
			prnMsg(__('The general ledger entries created do not balance. See your system administrator'), 'error');
			DB_Txn_Rollback();
		} else {
			$Result = DB_query("SELECT counterindex,
										MAX(amount)
								FROM gltrans
								WHERE type = '" . $TransType . "'
									AND typeno = '" . $TransTypeNo . "'
								GROUP BY counterindex");
			$MyRow = DB_fetch_array($Result);
			$TransToAmend = $MyRow['counterindex'];
			$Result = DB_query("UPDATE gltrans
								SET amount = amount - " . $Difference . "
								WHERE counterindex = '" . $TransToAmend . "'");

		}
	}
}

/*Creates sample and testresults */
function CreateQASample($ProdSpecKey, $LotKey, $Identifier, $Comments, $Cert, $DuplicateOK) {
	$Result = DB_query("SELECT COUNT(testid)
						FROM prodspecs
						WHERE keyval='" . $ProdSpecKey . "'
							AND active='1'");
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		if ($DuplicateOK == 0) {
			$Result = DB_query("SELECT COUNT(sampleid)
								FROM qasamples
								WHERE prodspeckey='" . $ProdSpecKey . "'
									AND lotkey='" . $LotKey . "'");
			$MyRow2 = DB_fetch_row($Result);
		} else {
			$MyRow2[0] = 0;
		}
		if ($MyRow2[0] == 0 OR $DuplicateOK == 1) {
			$SQL = "INSERT INTO qasamples (prodspeckey,
											lotkey,
											identifier,
											comments,
											cert,
											createdby,
											sampledate)
								VALUES('" . $ProdSpecKey . "',
										'" . $LotKey . "',
										'" . $Identifier . "',
										'" . $Comments . "',
										'" . $Cert . "',
										'" . $_SESSION['UserID'] . "',
										CURRENT_DATE)";
			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': '
				. __('The create of the qasamples record failed');
			$Result = DB_query($SQL, $ErrMsg, '', true);
			$SampleID = DB_Last_Insert_ID('qasamples', 'sampleid');
			$SQL = "INSERT INTO sampleresults (sampleid,
											testid,
											defaultvalue,
											targetvalue,
											rangemin,
											rangemax,
											showoncert,
											showontestplan)
								SELECT '" . $SampleID . "',
											testid,
											defaultvalue,
											targetvalue,
											rangemin,
											rangemax,
											showoncert,
											showontestplan
											FROM prodspecs WHERE keyval='" . $ProdSpecKey . "'
											AND prodspecs.active='1'";
			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': '
				. __('The create of the sampleresults record failed');
			$Result = DB_query($SQL, $ErrMsg, '', true);

		} //$MyRow2[0]==0
	} //$MyRow[0]>0
}

function PettyCashTabCurrentBalance($Tab) {
	$SQL = "SELECT SUM(amount) AS balance
			FROM pcashdetails
			WHERE tabcode='" . $Tab . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	if (!isset($MyRow['balance'])) {
		$Balance = 0;
	} else {
		$Balance = $MyRow['balance'];
	}
	return $Balance;
}
