<?php
/* $Revision: 1.27 $ */
$PageSecurity =3;


/*Functions to get the GL codes to post the transaction to */
include('includes/GetSalesTransGLCodes.inc');
/*defines the structure of the data required to hold the transaction as a session variable */
include('includes/DefineCartClass.php');
include('includes/DefineSerialItems.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Credit An Invoice');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (!$_GET['InvoiceNumber'] AND !$_SESSION['ProcessingCredit']) {
	/* This page can only be called with an invoice number for crediting*/
	prnMsg(_('This page can only be opened if an invoice has been selected for crediting') . '. ' . _('Please select an invoice first') . ' - ' . _('from the customer inquiry screen click the link to credit an invoice'),'info');
	include('includes/footer.inc');
	exit;

} elseif ($_GET['InvoiceNumber']>0) {

	unset($_SESSION['CreditItems']->LineItems);
	unset($_SESSION['CreditItems']);

	$_SESSION['ProcessingCredit'] = $_GET['InvoiceNumber'];
	$_SESSION['CreditItems'] = new cart;

/*read in all the guff from the selected invoice into the Items cart	*/


	$InvoiceHeaderSQL = "SELECT DISTINCT
				debtortrans.id as transid,
				debtortrans.debtorno,
				debtorsmaster.name,
				debtortrans.branchcode,
				debtortrans.reference,
				debtortrans.invtext,
				debtortrans.order_,
				debtortrans.trandate,
				debtortrans.tpe,
				debtortrans.shipvia,
				debtortrans.ovfreight,
				debtortrans.rate AS currency_rate,
				debtorsmaster.currcode,
				custbranch.defaultlocation,
				custbranch.taxgroupid,
				stockmoves.loccode,
				locations.taxprovinceid
			FROM debtortrans INNER JOIN debtorsmaster ON
				debtortrans.debtorno = debtorsmaster.debtorno
				INNER JOIN custbranch ON 
				debtortrans.branchcode = custbranch.branchcode
				AND debtortrans.debtorno = custbranch.debtorno
				INNER JOIN currencies ON
				debtorsmaster.currcode = currencies.currabrev
				INNER JOIN stockmoves ON
				stockmoves.transno=debtortrans.transno
				INNER JOIN locations ON
				stockmoves.loccode = locations.loccode
			WHERE debtortrans.transno = " . $_GET['InvoiceNumber'] . "
				AND debtortrans.type=10
				AND stockmoves.type=10";

	$ErrMsg = _('A credit cannot be produced for the selected invoice') . '. ' . _('The invoice details cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the invoice details was');
	$GetInvHdrResult = DB_query($InvoiceHeaderSQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($GetInvHdrResult)==1) {

		$myrow = DB_fetch_array($GetInvHdrResult);

/*CustomerID variable registered by header.inc */
		$_SESSION['CreditItems']->DebtorNo = $myrow['debtorno'];
		$_SESSION['CreditItems']->TransID = $myrow['transid'];
		$_SESSION['CreditItems']->Branch = $myrow['branchcode'];
		$_SESSION['CreditItems']->CustomerName = $myrow['name'];
		$_SESSION['CreditItems']->CustRef = $myrow['reference'];
		$_SESSION['CreditItems']->Comments = $myrow['invtext'];
		$_SESSION['CreditItems']->DefaultSalesType =$myrow['tpe'];
		$_SESSION['CreditItems']->DefaultCurrency = $myrow['currcode'];
		$_SESSION['CreditItems']->Location = $myrow['loccode'];
		$_SESSION['Old_FreightCost'] = $myrow['ovfreight'];
		$_SESSION['CurrencyRate'] = $myrow['currency_rate'];
		$_SESSION['CreditItems']->OrderNo = $myrow['order_'];
		$_SESSION['CreditItems']->ShipVia = $myrow['shipvia'];
		$_SESSION['CreditItems']->TaxGroup = $myrow['taxgroupid'];
		$_SESSION['CreditItems']->FreightCost = $myrow['ovfreight'];
		$_SESSION['CreditItems']->DispatchTaxProvince = $myrow['taxprovinceid'];
		$_SESSION['CreditItems']->GetFreightTaxes();
		
		DB_free_result($GetInvHdrResult);

/*now populate the line items array with the stock movement records for the invoice*/


		$LineItemsSQL = "SELECT stockmoves.stkmoveno,
					stockmoves.stockid,
					stockmaster.description,
					stockmaster.volume,
					stockmaster.kgs,
					stockmaster.mbflag,
					stockmaster.controlled,
					stockmaster.serialised,
					stockmaster.decimalplaces,
					stockmaster.taxcatid,
					stockmaster.units,
					stockmaster.discountcategory,
					(stockmoves.price * " . $_SESSION['CurrencyRate'] . ") AS price, -
					stockmoves.qty as quantity,
					stockmoves.discountpercent,
					stockmoves.trandate,
					stockmaster.materialcost 
						+ stockmaster.labourcost 
						+ stockmaster.overheadcost AS standardcost,
					stockmoves.narrative
				FROM stockmoves, stockmaster
				WHERE stockmoves.stockid = stockmaster.stockid
				AND stockmoves.transno =" . $_GET['InvoiceNumber'] . "
				AND stockmoves.type=10
				AND stockmoves.show_on_inv_crds=1";

		$ErrMsg = _('This invoice can not be credited using this program') . '. ' . _('A manual credit note will need to be prepared') . '. ' . _('The line items of the order cannot be retrieved because');
		$Dbgmsg = _('The SQL used to get the transaction header was');

		$LineItemsResult = DB_query($LineItemsSQL,$db,$ErrMsg, $DbgMsg);

		if (db_num_rows($LineItemsResult)>0) {

			while ($myrow=db_fetch_array($LineItemsResult)) {
				
				$LineNumber = $_SESSION['CreditItems']->LineCounter;
				
				$_SESSION['CreditItems']->add_to_cart($myrow['stockid'],
								$myrow['quantity'],
								$myrow['description'],
								$myrow['price'],
								$myrow['discountpercent'],
								$myrow['units'],
								$myrow['volume'],
								$myrow['kgs'],
								0,
								$myrow['mbflag'],
								$myrow['trandate'],
								0,
								$myrow['discountcategory'],
								$myrow['controlled'],
								$myrow['serialised'],
								$myrow['decimalplaces'],
								$myrow['narrative'],
								'No',
								-1,
								$myrow['taxcatid']
								);

				$_SESSION['CreditItems']->LineItems[$LineNumber]->StandardCost = $myrow['standardcost'];
				$_SESSION['CreditItems']->GetExistingTaxes($LineNumber, $myrow['stkmoveno']);
				
				if ($myrow['controlled']==1){/* Populate the SerialItems array too*/

					$SQL = "SELECT 	serialno,
							moveqty
						FROM stockserialmoves
						WHERE stockmoveno=" . $myrow['stkmoveno'] . "
						AND stockid = '" . $myrow['stockid'] . "'";

					$ErrMsg = _('This invoice can not be credited using this program') . '. ' . _('A manual credit note will need to be prepared') . '. ' . _('The line item') . ' ' . $myrow['stockid'] . ' ' . _('is controlled but the serial numbers or batch numbers could not be retrieved because');
					$DbgMsg = _('The SQL used to get the controlled item details was');
					$SerialItemsResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

					while ($SerialItemsRow = DB_fetch_array($SerialItemsResult)){
						$_SESSION['CreditItems']->LineItems[$LineNumber]->SerialItems[$SerialItemsRow['serialno']] = new SerialItem($SerialItemsRow['serialno'], -$SerialItemsRow['moveqty']);
						$_SESSION['CreditItems']->LineItems[$LineNumber]->QtyDispatched -= $SerialItemsRow['moveqty'];
					}
				} /* end if the item is a controlled item */
			} /* loop thro line items from stock movement records */

		} else { /* there are no stock movement records created for that invoice */

			echo "<CENTER><A HREF='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</A></CENTER>';
			prnMsg( _('There are no line items that were retrieved for this invoice') . '. ' . _('The automatic credit program can not create a credit note from this invoice'),'warn');
			include("includes/footer.inc");
			exit;
		} //end of checks on returned data set
		DB_free_result($LineItemsResult);
	} else {
		prnMsg( _('This invoice can not be credited using the automatic facility') . '<BR>' . _('CRITICAL ERROR') . ': ' . _('Please report that a duplicate DebtorTrans header record was found for invoice') . ' ' . $SESSION['ProcessingCredit'],'warn');
		include("includes/footer.inc");
		exit;
	} //valid invoice record returned from the entered invoice number

}

if (isset($_POST['Location'])){
	$_SESSION['CreditItems']->Location = $_POST['Location'];
	
	$NewDispatchTaxProvResult = DB_query("SELECT taxprovinceid FROM locations WHERE loccode='" . $_POST['Location'] . "'",$db);
	$myrow = DB_fetch_array($NewDispatchTaxProvResult);
	
	$_SESSION['CreditItems']->DispatchTaxProvince = $myrow['taxprovinceid'];
	
	foreach ($_SESSION['CreditItems']->LineItems as $LineItem) {
		$_SESSION['CreditItems']->GetTaxes($LineItem->LineNumber);
	}
}
if (isset($_POST['ChargeFreightCost'])){
	$_SESSION['CreditItems']->FreightCost = $_POST['ChargeFreightCost'];
}

foreach ($_SESSION['CreditItems']->FreightTaxes as $FreightTaxLine) {
	if (isset($_POST['FreightTaxRate'  . $FreightTaxLine->TaxCalculationOrder])){
		$_SESSION['CreditItems']->FreightTaxes[$FreightTaxLine->TaxCalculationOrder]->TaxRate = $_POST['FreightTaxRate'  . $FreightTaxLine->TaxCalculationOrder]/100;
	}
}

If ($_SESSION['CreditItems']->ItemsOrdered > 0 OR isset($_POST['NewItem'])){

	If(isset($_GET['Delete'])){
		$_SESSION['CreditItems']->remove_from_cart($_GET['Delete']);
	}

	foreach ($_SESSION['CreditItems']->LineItems as $LineItem) {

		if (isset($_POST['Quantity_' . $LineItem->LineNumber])){

			$Narrative = $_POST['Narrative_' . $LineItem->LineNumber];
			$Quantity = $_POST['Quantity_' . $LineItem->LineNumber];
			$Price = $_POST['Price_' . $LineItem->LineNumber];
			$DiscountPercentage = $_POST['Discount_' . $LineItem->LineNumber];

			If ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
				prnMsg(_('The item could not be updated because you are attempting to set the quantity credited to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'),'error');
			} else {
				$_SESSION['CreditItems']->LineItems[$LineItem->LineNumber]->QtyDispatched=$Quantity;
				$_SESSION['CreditItems']->LineItems[$LineItem->LineNumber]->Price=$Price;
				$_SESSION['CreditItems']->LineItems[$LineItem->LineNumber]->DiscountPercent=($DiscountPercentage/100);
				$_SESSION['CreditItems']->LineItems[$LineItem->LineNumber]->Narrative=$Narrative;
			}
			foreach ($LineItem->Taxes as $TaxLine) {
				if (isset($_POST[$LineItem->LineNumber  . $TaxLine->TaxCalculationOrder . '_TaxRate'])){
					$_SESSION['CreditItems']->LineItems[$LineItem->LineNumber]->Taxes[$TaxLine->TaxCalculationOrder]->TaxRate = $_POST[$LineItem->LineNumber  . $TaxLine->TaxCalculationOrder . '_TaxRate']/100;
				}
			}
		}
	}
}


/* Always display credit quantities
NB QtyDispatched in the LineItems array is used for the quantity to credit */

echo '<FONT COLOR=BLUE><B>' . _('Credit Invoice') . ' ' . $_SESSION['ProcessingCredit'] . '</B></FONT>
	<CENTER><FONT SIZE=4><B><U>' . $_SESSION['CreditItems']->CustomerName . '</U></B></FONT>
	<FONT SIZE=3> - ' . _('Credit Note amounts stated in') . ' ' . $_SESSION['CreditItems']->DefaultCurrency . "</CENTER><BR>";

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


echo "<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=0><TR>
<TD class='tableheader'>" . _('Item Code') . "</TD>
<TD class='tableheader'>" . _('Item Description') . "</TD>
<TD class='tableheader'>" . _('Invoiced') . "</TD>
<TD class='tableheader'>" . _('Units') . "</TD>
<TD class='tableheader'>" . _('Credit') . '<BR>' . _('Quantity') . "</TD>
<TD class='tableheader'>" . _('Price') . "</TD>
<TD class='tableheader'>" . _('Discount') . "</TD>
<TD class='tableheader'>" . _('Total') . '<BR>' . _('Excl Tax') . "</TD>
<TD class='tableheader'>" . _('Tax Authority') . "</TD>
<TD class='tableheader'>" . _('Tax') . ' %' . "</TD>
<TD class='tableheader'>" . _('Tax') . '<BR>' . _('Amount') . "</TD>
<TD class='tableheader'>" . _('Total') . '<BR>' . _('Incl Tax') . "</TD></TR>";


$_SESSION['CreditItems']->total = 0;
$_SESSION['CreditItems']->totalVolume = 0;
$_SESSION['CreditItems']->totalWeight = 0;

$TaxTotals = array();
$TaxGLCodes = array();
$TaxTotal =0;

/*show the line items on the invoice with the quantity to credit and price being available for modification */

$k=0; //row colour counter

foreach ($_SESSION['CreditItems']->LineItems as $LnItm) {

	if ($k==1){
		$RowStarter = "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		$RowStarter = "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	$LineTotal =($LnItm->QtyDispatched * $LnItm->Price * (1 - $LnItm->DiscountPercent));

	$_SESSION['CreditItems']->total = $_SESSION['CreditItems']->total + $LineTotal;
	$_SESSION['CreditItems']->totalVolume = $_SESSION['CreditItems']->totalVolume + $LnItm->QtyDispatched * $LnItm->Volume;
	$_SESSION['CreditItems']->totalWeight = $_SESSION['CreditItems']->totalWeight + $LnItm->QtyDispatched * $LnItm->Weight;

	echo $RowStarter.'<TD>' . $LnItm->StockID . '</TD>
			  <TD>' . $LnItm->ItemDescription . '</TD>
			  <TD ALIGN=RIGHT>' . number_format($LnItm->Quantity,$LnItm->DecimalPlaces) . '</TD>
			  <TD>' . $LnItm->Units . '</TD>';

	if ($LnItm->Controlled==1){

		echo "<TD><input type=hidden name='Quantity_" . $LnItm->LineNumber ."'  value=" . $LnItm->QtyDispatched . "><A HREF='$rootpath/CreditItemsControlled.php?" . SID . "&LineNo=" . $LnItm->LineNumber . "&CreditInvoice=Yes'>" . $LnItm->QtyDispatched . "</A></TD>";

	} else {

		echo "<TD><input type=text name='Quantity_" . $LnItm->LineNumber ."' maxlength=6 SIZE=6 value=" . $LnItm->QtyDispatched . "></TD>";

	}

	$DisplayLineTotal = number_format($LineTotal,2);

	echo "<TD><INPUT TYPE=TEXT NAME='Price_" . $LnItm->LineNumber . "' MAXLENGTH=6 SIZE=6 VALUE=" . $LnItm->Price . "></TD>
	<TD><INPUT TYPE=TEXT NAME='Discount_" . $LnItm->LineNumber . "' MAXLENGTH=3 SIZE=3 VALUE=" . ($LnItm->DiscountPercent * 100) . "></TD>
	<TD ALIGN=RIGHT>$DisplayLineTotal</TD>";
	
		/*Need to list the taxes applicable to this line */
	echo '<TD>';
	$i=0;
	if (is_array($_SESSION['CreditItems']->LineItems[$LnItm->LineNumber]->Taxes) ){
		foreach ($_SESSION['CreditItems']->LineItems[$LnItm->LineNumber]->Taxes AS $Tax) {
			if ($i>0){
				echo '<BR>';
			}
			echo $Tax->TaxAuthDescription;
			$i++;
		}
	}
	echo '</TD>';
	echo '<TD ALIGN=RIGHT>';
	
	$i=0; // initialise the number of taxes iterated through
	$TaxLineTotal =0; //initialise tax total for the line
	
	if (is_array($LnItm->Taxes) ){
		foreach ($LnItm->Taxes AS $Tax) {
			if ($i>0){
				echo '<BR>';
			}
			echo '<input type=text name="' . $LnItm->LineNumber . $Tax->TaxCalculationOrder . '_TaxRate" maxlength=4 SIZE=4 value="' . $Tax->TaxRate*100 . '">';
			$i++;
			if ($Tax->TaxOnTax ==1){
				$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
				$TaxLineTotal += ($Tax->TaxRate * ($LineTotal + $TaxLineTotal));
			} else {
				$TaxTotals[$Tax->TaxAuthID] += ($Tax->TaxRate * $LineTotal);
				$TaxLineTotal += ($Tax->TaxRate * $LineTotal);
			}
			$TaxGLCodes[$Tax->TaxAuthID] = $Tax->TaxGLCode;
		}
	}
	echo '</TD>';		
	
	$TaxTotal += $TaxLineTotal;
	
	$DisplayTaxAmount = number_format($TaxLineTotal ,2);
	$DisplayGrossLineTotal = number_format($LineTotal+ $TaxLineTotal,2);

	echo '<TD ALIGN=RIGHT>' . $DisplayTaxAmount . '</TD>
	      <TD ALIGN=RIGHT>' . $DisplayGrossLineTotal . "</TD>
	<TD><A HREF='". $_SERVER['PHP_SELF'] . "?" . SID . "&Delete=" . $LnItm->LineNumber . "'>" . _('Delete') . '</A></TD></TR>';

	echo $RowStarter . "<TD COLSPAN=7><TEXTAREA  NAME='Narrative_" . $LnItm->LineNumber . "' cols=100% rows=1>" . $LnItm->Narrative . "</TEXTAREA><BR><HR></TD></TR>";

} /*end foreach loop displaying the invoice lines to credit */

if (!isset($_POST['ChargeFreightCost']) AND !isset($_SESSION['CreditItems']->FreightCost)){
	$_POST['ChargeFreightCost']=0;
}



echo '<TR>
	<TD COLSPAN=3 ALIGN=RIGHT>' . _('Freight cost charged on invoice') . '</TD>
	<TD ALIGN=RIGHT>' . number_format($_SESSION['Old_FreightCost'],2) . '</TD>
	<TD></TD>
	<TD COLSPAN=2 ALIGN=RIGHT>' . _('Credit Freight Cost') . "</TD>
	<TD><INPUT TYPE=TEXT SIZE=6 MAXLENGTH=6 NAME='ChargeFreightCost' VALUE=" . $_SESSION['CreditItems']->FreightCost . "></TD>";


$FreightTaxTotal =0; //initialise tax total

echo '<TD>';

$i=0; // initialise the number of taxes iterated through
foreach ($_SESSION['CreditItems']->FreightTaxes as $FreightTaxLine) {
	if ($i>0){
		echo '<BR>';
	}
	echo  $FreightTaxLine->TaxAuthDescription;
	$i++;
}

echo '</TD><TD>';

$i=0;
foreach ($_SESSION['CreditItems']->FreightTaxes as $FreightTaxLine) {
	if ($i>0){
		echo '<BR>';
	}
	
	echo  '<INPUT TYPE=TEXT NAME=FreightTaxRate' . $FreightTaxLine->TaxCalculationOrder . ' MAXLENGTH=4 SIZE=4 VALUE=' . $FreightTaxLine->TaxRate * 100 . '>';
	
	if ($FreightTaxLine->TaxOnTax ==1){
		$TaxTotals[$FreightTaxLine->TaxAuthID] += ($FreightTaxLine->TaxRate * ($_SESSION['CreditItems']->FreightCost + $FreightTaxTotal));
		$FreightTaxTotal += ($FreightTaxLine->TaxRate * ($_SESSION['CreditItems']->FreightCost + $FreightTaxTotal));
	} else {
		$TaxTotals[$FreightTaxLine->TaxAuthID] += ($FreightTaxLine->TaxRate * $_SESSION['CreditItems']->FreightCost);
		$FreightTaxTotal += ($FreightTaxLine->TaxRate * $_SESSION['CreditItems']->FreightCost);
	}
	$i++;
	$TaxGLCodes[$FreightTaxLine->TaxAuthID] = $FreightTaxLine->TaxGLCode;
}
echo '</TD>';

echo '<TD ALIGN=RIGHT>' . number_format($FreightTaxTotal,2) . '</TD>
	<TD ALIGN=RIGHT>' . number_format($FreightTaxTotal+ $_SESSION['CreditItems']->FreightCost,2) . '</TD>
	</TR>';

$TaxTotal += $FreightTaxTotal;
$DisplayTotal = number_format($_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost,2);


echo '<TR>
	<TD COLSPAN=7 ALIGN=RIGHT>' . _('Credit Totals') . "</TD>
	<TD ALIGN=RIGHT><HR><B>$DisplayTotal</B><HR></TD>
	<TD COLSPAN=2></TD>
	<TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal,2) . "<HR></TD>
	<TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal+($_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost),2) . "</B><HR></TD>
</TR></TABLE>";

$DefaultDispatchDate = Date($_SESSION['DefaultDateFormat']);

$OKToProcess = true;

if ($_POST['CreditType']=='WriteOff' AND !isset($_POST['WriteOffGLCode'])){

	prnMsg (_('The GL code to write off the credit value to must be specified. Please select the appropriate GL code for the selection box'),'info');

	$OKToProcess = false;

}

if (isset($_POST['ProcessCredit']) AND $OKToProcess == true) {

/* SQL to process the postings for sales credit notes... First Get the area where the credit note is to from the branches table */

	$SQL = "SELECT area 
		FROM custbranch 
		WHERE custbranch.debtorno ='". $_SESSION['CreditItems']->DebtorNo . "' 
		AND custbranch.branchcode = '" . $_SESSION['CreditItems']->Branch . "'";

	$Result = DB_query($SQL,$db);
	$myrow = DB_fetch_row($Result);
	$Area = $myrow[0];
	DB_free_result($Result);

/*company record is read in on login and has information on GL Links and debtors GL account*/

	if ($_SESSION['CompanyRecord']==0){
		/*The company data and preferences could not be retrieved for some reason */
		prnMsg(_('The company information and preferences could not be retrieved') . ' - ' . _('see your system administrator'),'error');
		include('includes/footer.inc');
		exit;
	}


/*Now Get the next credit note number - function in SQL_CommonFunctions*/
	
	$CreditNo = GetNextTransNo(11, $db);
	$PeriodNo = GetPeriod($DefaultDispatchDate, $db);
	
/*Start an SQL transaction */

	$SQL = "BEGIN";
	$Result = DB_query($SQL,$db);

	$DefaultDispatchDate= FormatDateForSQL($DefaultDispatchDate);


	/*Calculate the allocation and see if it is possible to allocate to the invoice being credited */

	$SQL = "SELECT (ovamount+ovgst+ovfreight-ovdiscount-alloc) as baltoallocate
		FROM debtortrans
		WHERE transno=" . $_SESSION['ProcessingCredit'] . " AND type=10";
	$Result = DB_query($SQL,$db);
	$myrow = DB_fetch_row($Result);


	/*Do some rounding */

	$_SESSION['CreditItems']->total = round($_SESSION['CreditItems']->total,2);
	$TaxTotal = round($TaxTotal,2);

	$Allocate_amount=0;
	$Settled =0;
	$SettledInvoice=0;
	if ($myrow[0]>0){ /*the invoice is not already fully allocated */

		if ($myrow[0] > ($_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost + $TaxTotal)){

			$Allocate_amount = $_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost + $TaxTotal;
			$Settled = 1;
		} else { /*the balance left to allocate is less than the credit note value */
			$Allocate_amount = $myrow[0];
			$SettledInvoice = 1;
			$Settled =0;
		}

/*Now need to update the invoice DebtorTrans record for the amount to be allocated and if the invoice is now settled*/

		$SQL = "UPDATE debtortrans
			SET alloc = alloc + " . $Allocate_amount . ",
			settled=" . $SettledInvoice . "
			WHERE transno = " . $_SESSION['ProcessingCredit'] . "
			AND type=10";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The alteration to the invoice record to reflect the allocation of the credit note to the invoice could not be done because');
		$DbgMsg = _('The following SQL to update the invoice allocation was used');
		$Result = DB_query($SQL,$db,$ErrMsg,DbgMsg,true);
	}

/*Now insert the Credit Note into the DebtorTrans table with the allocations as calculated above*/

	$SQL = "INSERT INTO debtortrans (transno,
					type,
					debtorno,
					branchcode,
					trandate,
					prd,
					reference,
					tpe,
					order_,
					ovamount,
					ovgst,
					ovfreight,
					rate,
					invtext,
					alloc,
					settled)
		VALUES (". $CreditNo . ",
			11,
			'" . $_SESSION['CreditItems']->DebtorNo . "',
			'" . $_SESSION['CreditItems']->Branch . "',
			'" . $DefaultDispatchDate . "',
			" . $PeriodNo . ", 'Inv-" . $_SESSION['ProcessingCredit'] . "',
			'" . $_SESSION['CreditItems']->DefaultSalesType . "',
			" . $_SESSION['CreditItems']->OrderNo . ",
			" . -($_SESSION['CreditItems']->total) . ",
			" . -$TaxTotal . ", " . -$_SESSION['CreditItems']->FreightCost . ",
			" . $_SESSION['CurrencyRate'] . ",
			'" . $_POST['CreditText'] . "',
			" . -$Allocate_amount . ",
			" . $Settled . ")";

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The customer credit note transaction could not be added to the database because');
	$DbgMsg = _('The following SQL to insert the customer credit note was used');
	$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

	$CreditTransID = DB_Last_Insert_ID($db,'debtortrans','id');
	
	/* Insert the tax totals for each tax authority where tax was charged on the invoice */
	foreach ($TaxTotals AS $TaxAuthID => $TaxAmount) {
	
		$SQL = 'INSERT INTO debtortranstaxes (debtortransid,
							taxauthid,
							taxamount)
				VALUES (' . $CreditTransID . ',
					' . $TaxAuthID . ',
					' . -($TaxAmount/$_SESSION['CurrencyRate']) . ')';
		
		$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction taxes records could not be inserted because');
		$DbgMsg = _('The following SQL to insert the debtor transaction taxes record was used');
 		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
	}

/*Now insert the allocation record if > 0 */
	if ($Allocate_amount!=0){
		$SQL = "INSERT INTO custallocns (amt,
						transid_allocfrom,
						transid_allocto,
						datealloc)
			VALUES (" . $Allocate_amount . ",
				" . $CreditTransID . ",
				" . $_SESSION['CreditItems']->TransID . ",
				'" . Date('Y-m-d') . "')";

		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The allocation record for the credit note could not be added to the database because');
		$DbgMsg = _('The following SQL to insert the allocation record for the credit note was used');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

	}

/* Update sales order details quantity invoiced less this credit quantity. */

	foreach ($_SESSION['CreditItems']->LineItems as $OrderLine) {

		if ($OrderLine->QtyDispatched >0){
			$LocalCurrencyPrice= round(($OrderLine->Price / $_SESSION['CurrencyRate']),2);

			/*Determine the type of stock item being credited */
			$SQL = "SELECT mbflag FROM stockmaster WHERE stockid = '" . $OrderLine->StockID . "'";
			$Result = DB_query($SQL,
					$db,
					_('Could not determine if the item') . ' ' . $OrderLine->StockID . ' ' . _('is purchased or manufactured'),
					_('The SQL used that failed was'),true);
			$MBFlagRow = DB_fetch_row($Result);
			$MBFlag = $MBFlagRow[0];
			if ($MBFlag=='M' OR $MBFlag=='B'){
				/*Need to get the current location quantity will need it later for the stock movements */
		 		$SQL="SELECT locstock.quantity
					FROM locstock
					WHERE locstock.stockid='" . $OrderLine->StockID . "'
					AND loccode= '" . $_SESSION['CreditItems']->Location . "'";
				$Result = DB_query($SQL, $db);
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
			    	} else {
					/*There must actually be some error this should never happen */
					$QtyOnHandPrior = 0;
			    	}
			} else {
				$QtyOnHandPrior =0; //because its a dummy/assembly/kitset part
			}

			if ($_POST['CreditType']=='Return'){

				$SQL = "UPDATE salesorderdetails
							SET qtyinvoiced = qtyinvoiced - " . $OrderLine->QtyDispatched . ",
								completed=0
						WHERE orderno = " . $_SESSION['CreditItems']->OrderNo . "
						AND stkcode = '" . $OrderLine->StockID . "'
						AND orderlineno=" . $OrderLine->LineNumber;

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order detail record could not be updated for the reduced quantity invoiced because');
				$DbgMsg = _('The following SQL to update the sales order detail record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				/* Update location stock records if not a dummy stock item */

				if ($MBFlag=="B" OR $MBFlag=="M") {

					$SQL = "UPDATE locstock
						SET locstock.quantity = locstock.quantity + " . $OrderLine->QtyDispatched . "
						WHERE locstock.stockid = '" . $OrderLine->StockID . "'
						AND loccode = '" . $_SESSION['CreditItems']->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated because');
					$DbgMsg = _('The following SQL to update the location stock record was used');
					$Result = DB_query($SQL, $db, $ErrMsg,$DbgMsg,true);

				} else if ($MBFlag=='A'){ /* its an assembly */
					/*Need to get the BOM for this part and make stock moves for the components
					and of course update the Location stock balances */

				    $StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
				    $sql = "SELECT
				    	bom.component,
				    	bom.quantity,
					stockmaster.materialcost 
						+ stockmaster.labourcost 
						+ stockmaster.overheadcost AS standard
					FROM bom, 
						stockmaster
					WHERE bom.component=stockmaster.stockid
					AND bom.parent='" . $OrderLine->StockID . "'
					AND bom.effectiveto > '" . Date('Y-m-d') . "'
					AND bom.effectiveafter < '" . Date('Y-m-d') . "'";

				    $ErrMsg = _('Could not retrieve assembly components from the database for') . ' ' . $OrderLine->StockID . ' ' . _('because');
				    $DbgMsg = _('The SQL that failed was');
				    $AssResult = DB_query($sql,$db, $ErrMsg, $DbgMsg, true);

				    while ($AssParts = DB_fetch_array($AssResult,$db)){
				
					   $StandardCost += $AssParts['standard'];
					   /*Determine the type of stock item being credited */
					   $SQL = "SELECT mbflag
							FROM
							stockmaster
							WHERE stockid = '" . $AssParts['component'] . "'";
					   $Result = DB_query($SQL,$db);
					   $MBFlagRow = DB_fetch_row($Result);
				 	   $Component_MBFlag = $MBFlagRow[0];

					   /* Insert stock movements for the stock coming back in - with unit cost */
					   if ($Component_MBFlag=='M' OR $Component_MBFlag=='B'){
			   			/*Need to get the current location quantity will need it later for the stock movement */
		 	    			$SQL="SELECT locstock.quantity
							FROM locstock
							WHERE locstock.stockid='" . $AssParts['component'] . "'
							AND loccode= '" . $_SESSION['CreditItems']->Location . "'";
			    			$Result = DB_query($SQL, $db, _('Couldnt get the current location stock of the assembly component') . ' ' . $AssParts['component'], _('The SQL that failed was'), true);
			    			if (DB_num_rows($Result)==1){
							$LocQtyRow = DB_fetch_row($Result);
							$QtyOnHandPrior = $LocQtyRow[0];
			    			} else {
							/*There must actually be some error this should never happen */
							$QtyOnHandPrior = 0;
			    			}
			    		   } else {
			    			$QtyOnHandPrior =0; //because its a dummy/assembly/kitset part
			    		   }

			    		   if ($Component_MBFlag=="M" OR $Component_MBFlag=="B"){

					   	$SQL = "INSERT INTO stockmoves (
									stockid,
									type,
									transno,
									loccode,
									trandate,
									debtorno,
									branchcode,
									prd,
									reference,
									qty,
									standardcost,
									show_on_inv_crds,
									newqoh )
							VALUES ('" . $AssParts['component'] . "',
								11,
								" . $CreditNo . ",
								'" . $_SESSION['CreditItems']->Location . "',
								'" . $DefaultDispatchDate . "',
								'" . $_SESSION['CreditItems']->DebtorNo . "',
								'" . $_SESSION['CreditItems']->Branch . "',
								" . $PeriodNo . ",
								'" . _('Ex Inv') . ': ' .  $_SESSION['ProcessingCredit'] . ' ' . _('Assembly') . ': ' . $OrderLine->StockID . "',
								" . $AssParts['quantity'] * $OrderLine->QtyDispatched . ",
								" . $AssParts['standard'] . ",
								0,
								" . ($QtyOnHandPrior + ($AssParts['quantity'] * $OrderLine->QtyDispatched)) . "
								)";
					    } else {

						$SQL = "INSERT INTO stockmoves (
								stockid,
								type,
								transno,
								loccode,
								trandate,
								debtorno,
								branchcode,
								prd,
								reference,
								qty,
								standardcost,
								show_on_inv_crds)
							VALUES ('" . $AssParts['component'] . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $DefaultDispatchDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $PeriodNo . ",
							'" . _('Ex Inv') . ': ' . $_SESSION['ProcessingCredit'] . ' ' . _('Assembly') . ': ' . $OrderLine->StockID . "',
							" . $AssParts['quantity'] * $OrderLine->QtyDispatched . ",
							" . $AssParts['standard'] . ",
							0)";
					    }

					   $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for the assembly components of') .' ' . $OrderLine->StockID . ' ' . _('could not be inserted because');
					   $DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
					   $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

   					   if ($Component_MBFlag=="M" OR $Component_MBFlag=="B"){
					   	$SQL = "UPDATE locstock
							SET locstock.quantity = locstock.quantity + " . $AssParts['quantity'] * $OrderLine->QtyDispatched . "
							WHERE locstock.stockid = '" . $AssParts['component'] . "'
							AND loccode = '" . $_SESSION['CreditItems']->Location . "'";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated for an assembly component because');
						$DbgMsg = _('The following SQL to update the components location stock record was used');
						$Result = DB_query($SQL,
								$db,
								$ErrMsg,
								$DbgMsg,
								true);
					    }
				    } /* end of assembly explosion and updates */
				    /*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
				    $_SESSION['CreditItems']->LineItems[$OrderLine->LineNumber]->StandardCost = $StandardCost;
				    $OrderLine->StandardCost = $StandardCost;
				}

/* Insert stock movements for the stock coming back in - with unit cost */

			    	if ($MBFlag=="M" OR $MBFlag=="B"){
					$SQL = "INSERT INTO stockmoves (
								stockid,
								type,
								transno,
								loccode,
								trandate,
								debtorno,
								branchcode,
								price,
								prd,
								reference,
								qty,
								discountpercent,
								standardcost,
								newqoh,
								narrative)
						VALUES ('" . $OrderLine->StockID . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $DefaultDispatchDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							'" . _('Ex Inv') .' - ' . $_SESSION['ProcessingCredit'] . "',
							" . $OrderLine->QtyDispatched . ",
							" . $OrderLine->DiscountPercent . ",
							" . $OrderLine->StandardCost . ",
							" .  ($QtyOnHandPrior + $OrderLine->QtyDispatched) . ",
							'" . DB_escape_string($OrderLine->Narrative) . "')";
				} else {

					$SQL = "INSERT INTO stockmoves (
							stockid,
							type,
							transno,
							loccode,
							trandate,
							debtorno,
							branchcode,
							price,
							prd,
							reference,
							qty,
							discountpercent,
							standardcost,
							narrative)
						VALUES ('" . $OrderLine->StockID . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $DefaultDispatchDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							'" . _('Ex Inv') . " - " . $_SESSION['ProcessingCredit'] . "',
							" . $OrderLine->QtyDispatched . ",
							" . $OrderLine->DiscountPercent . ",
							" . $OrderLine->StandardCost . ",
							'" . DB_escape_string($OrderLine->Narrative) . "'
						)";
				}

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);
				
				$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
				
				/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/
				//echo "<div align=left><pre>"; var_dump($OrderLine); echo "</pre> </div>";
				if ($OrderLine->Controlled ==1){
					foreach($OrderLine->SerialItems as $Item){
					/*We need to add the StockSerialItem record and The StockSerialMoves as well */
						$SQL = "SELECT quantity from stockserialitems
								WHERE stockid='" . $OrderLine->StockID . "'
								AND loccode='" . $_SESSION['CreditItems']->Location . "'
								AND serialno='" . $Item->BundleRef . "'";
						
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be selected because');
						$DbgMsg = _('The following SQL to select the serial stock item record was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						
						if (DB_num_rows($Result)==0){
							$SQL = "INSERT INTO stockserialitems (stockid, loccode, serialno, quantity)
								VALUES
								('" . $OrderLine->StockID . "', '" . $_SESSION['CreditItems']->Location . "', '" . $Item->BundleRef . "',  ". $Item->BundleQty .")";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
							$DbgMsg = _('The following SQL to update the serial stock item record was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						} else {
						
							$SQL = "UPDATE stockserialitems
									SET quantity= quantity + " . $Item->BundleQty . "
									WHERE stockid='" . $OrderLine->StockID . "'
									AND loccode='" . $_SESSION['CreditItems']->Location . "'
									AND serialno='" . $Item->BundleRef . "'";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
							$DbgMsg = _('The following SQL to update the serial stock item record was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						}

						/* now insert the serial stock movement */

						$SQL = "INSERT INTO stockserialmoves (stockmoveno, stockid, serialno, moveqty) VALUES (" . $StkMoveNo . ", '" . $OrderLine->StockID . "', '" . $Item->BundleRef . "', " . $Item->BundleQty . ")";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

					}/* foreach controlled item in the serialitems array */
				} /*end if the orderline is a controlled item */

			} elseif ($_POST['CreditType']=='WriteOff') {
			   /*Insert a stock movement coming back in to show the credit note and
			   a reversing stock movement to show the write off
			   no mods to location stock records*/

				$SQL = "INSERT INTO stockmoves (
							stockid,
							type,
							transno,
							loccode,
							trandate,
							debtorno,
							branchcode,
							price,
							prd,
							reference,
							qty,
							discountpercent,
							standardcost,
							newqoh,
							narrative )
					VALUES ('" . $OrderLine->StockID . "',
						11,
						" . $CreditNo . ",
						'" . $_SESSION['CreditItems']->Location . "',
						'" . $DefaultDispatchDate . "',
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						'" . $_SESSION['CreditItems']->Branch . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ", 
						'" . _('Ex Inv') . ' - ' . $_SESSION['ProcessingCredit'] . "',
						" . $OrderLine->QtyDispatched . ",
						" . $OrderLine->DiscountPercent . ",
						" . $OrderLine->StandardCost . ",
						" . ($QtyOnHandPrior +$OrderLine->QtyDispatched)  . ",
						'" . DB_escape_string($OrderLine->Narrative) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL, $db,$ErrMsg, $DbgMsg, true);

				$SQL = "INSERT INTO stockmoves (
							stockid,
							type,
							transno,
							loccode,
							trandate,
							debtorno,
							branchcode,
							price,
							prd,
							reference,
							qty,
							discountpercent,
							standardcost,
							show_on_inv_crds,
							newqoh,
							narrative
							)
					VALUES ('" . $OrderLine->StockID . "',
						11,
						" . $CreditNo . ",
						'" . $_SESSION['CreditItems']->Location . "',
						'" . $DefaultDispatchDate . "',
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						'" . $_SESSION['CreditItems']->Branch . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ",
						'" . _('Written off ex Inv') . ' - ' . $_SESSION['ProcessingCredit'] . "',
						" . -$OrderLine->QtyDispatched . ",
						" . $OrderLine->DiscountPercent . ",
						" . $OrderLine->StandardCost . ",
						0,
						" . $QtyOnHandPrior . ",
						'" . DB_escape_string($OrderLine->Narrative) . "')";


				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

			} elseif ($_POST['CreditType']=='ReverseOverCharge') {
			   /*Insert a stock movement coming back in to show the credit note  - flag the stockmovement not to show on stock movement enquiries - its is not a real stock movement only for invoice line - also no mods to location stock records*/
				$SQL = "INSERT INTO stockmoves (
							stockid,
							type,
							transno,
							loccode,
							trandate,
							debtorno,
							branchcode,
							price,
							prd,
							reference,
							qty,
							discountpercent,
							standardcost,
							newqoh,
							hidemovt,
							narrative)
					VALUES ('" . $OrderLine->StockID . "',
						11,
						" . $CreditNo . ",
						'" . $_SESSION['CreditItems']->Location . "',
						'" . $DefaultDispatchDate . "',
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						'" . $_SESSION['CreditItems']->Branch . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ",
						'" . _('Ex Inv') .' - ' . $_SESSION['ProcessingCredit'] . "',
						" . $OrderLine->QtyDispatched . ",
						" . $OrderLine->DiscountPercent . ",
						" . $OrderLine->StandardCost . ",
						" . $QtyOnHandPrior  . ",
						1,
						'" . DB_escape_string($OrderLine->Narrative) . "')";


				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records for the purpose of display on the credit note was used');

				$Result = DB_query($SQL, $db,$ErrMsg, $DbgMsg, true);
			}
			
			/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

			/*Insert the taxes that applied to this line */
			foreach ($OrderLine->Taxes as $Tax) {
			
				$SQL = 'INSERT INTO stockmovestaxes (stkmoveno,
									taxauthid,
									taxrate,
									taxcalculationorder,
									taxontax)
						VALUES (' . $StkMoveNo . ',
							' . $Tax->TaxAuthID . ',
							' . $Tax->TaxRate . ',
							' . $Tax->TaxCalculationOrder . ',
							' . $Tax->TaxOnTax . ')';
							
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Taxes and rates applicable to this credit note line item could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement tax detail records was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}

/*Insert Sales Analysis records */

			$SQL="SELECT COUNT(*),
				stkcategory,
				salesanalysis.area,
				salesperson
			FROM salesanalysis,
				custbranch,
				stockmaster
			WHERE salesanalysis.stkcategory=stockmaster.categoryid
			AND salesanalysis.stockid=stockmaster.stockid
			AND salesanalysis.cust=custbranch.debtorno
			AND salesanalysis.custbranch=custbranch.branchcode
			AND salesanalysis.area=custbranch.area
			AND salesanalysis.salesperson=custbranch.salesman
			AND typeabbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
			AND periodno=" . $PeriodNo . "
			AND cust = '" . $_SESSION['CreditItems']->DebtorNo . "'
			AND custbranch = '" . $_SESSION['CreditItems']->Branch . "'
			AND salesanalysis.stockid = '" . $OrderLine->StockID . "'
			AND budgetoractual=1
			GROUP BY stkcategory, salesanalysis.area, salesperson";

			$ErrMsg = _('The count to check for existing Sales analysis records could not run because');
			$DbgMsg = _('SQL to count the no of sales analysis records');

			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				if ($_POST['CreditType']=='ReverseOverCharge'){

					$SQL = "UPDATE salesanalysis
						SET amt=amt-" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
							disc=disc-" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . "
						WHERE salesanalysis.area='" . $myrow[2] . "'
						AND salesanalysis.salesperson='" . $myrow[3] . "'
						AND typeabbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
						AND periodno = " . $PeriodNo . "
						AND cust = '" . $_SESSION['CreditItems']->DebtorNo . "'
						AND custbranch = '" . $_SESSION['CreditItems']->Branch . "'
						AND stockid = '" . $OrderLine->StockID . "'
						AND salesanalysis.stkcategory ='" . $myrow[1] . "'
						AND budgetoractual=1";

				} else {

					$SQL = "UPDATE salesanalysis
							SET amt=amt-" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
							cost=cost-" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
							qty=qty-" . $OrderLine->QtyDispatched . ",
							disc=disc-" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . "
						WHERE salesanalysis.area='" . $myrow[2] . "'
						AND salesanalysis.salesperson='" . $myrow[3] . "'
						AND typeabbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "'
						AND periodno = " . $PeriodNo . "
						AND cust = '" . $_SESSION['CreditItems']->DebtorNo . "'
						AND custbranch = '" . $_SESSION['CreditItems']->Branch . "'
						AND stockid = '" . $OrderLine->StockID . "'
						AND salesanalysis.stkcategory ='" . $myrow[1] . "'
						AND budgetoractual=1";
				}

			} else { /* insert a new sales analysis record */

				if ($_POST['CreditType']=='ReverseOverCharge'){

					$SQL = "INSERT INTO salesanalysis (typeabbrev,
									periodno,
									amt,
									cust,
									custbranch,
									qty,
									disc,
									stockid,
									area,
									budgetoractual,
									salesperson,
									stkcategory)
						SELECT '" . $_SESSION['CreditItems']->DefaultSalesType . "',
							" . $PeriodNo . ",
							" . -($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							0,
							" . -($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
							'" . $OrderLine->StockID . "',
							custbranch.area,
							1,
							custbranch.salesman,
							stockmaster.categoryid
						FROM stockmaster,
							custbranch
						WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
						AND custbranch.debtorno = '" . $_SESSION['CreditItems']->DebtorNo . "'
						AND custbranch.branchcode='" . $_SESSION['CreditItems']->Branch . "'";

				} else {

					$SQL = "INSERT INTO salesanalysis (typeabbrev,
									periodno,
									amt,
									cost,
									cust,
									custbranch,
									qty,
									disc,
									stockid,
									area,
									budgetoractual,
									salesperson,
									stkcategory)
						SELECT '" . $_SESSION['CreditItems']->DefaultSalesType . "',
							" . $PeriodNo . ", " . -($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
							" . -($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ",
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . -$OrderLine->QtyDispatched . ",
							" . -($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ",
							'" . $OrderLine->StockID . "',
							custbranch.area,
							1,
							custbranch.salesman,
							stockmaster.categoryid
						FROM stockmaster,
							custbranch
						WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
						AND custbranch.debtorno = '" . $_SESSION['CreditItems']->DebtorNo . "'
						AND custbranch.branchcode='" . $_SESSION['CreditItems']->Branch . "'";

				}
			}

			$ErrMsg = _('The sales analysis record for this credit note could not be added because');
			$DbgMsg = _('The following SQL to insert the sales analysis record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);


/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

			if ($_SESSION['CompanyRecord']['gllink_stock']==1 
				AND ($OrderLine->StandardCost !=0  OR $StandardCost !=0)
				AND $_POST['CreditType']!='ReverseOverCharge'){

/*first the cost of sales entry*/

				$COGSAccount = GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['CreditItems']->DefaultSalesType, $db);

				$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
						VALUES (11,
							" . $CreditNo . ",
							'" . $DefaultDispatchDate . "',
							" . $PeriodNo . ",
							" . $COGSAccount . ",
							'" . $_SESSION['CreditItems']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "',
							" . -round($OrderLine->StandardCost * $OrderLine->QtyDispatched,2) . "
							)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

/*now the stock entry*/


				if ($_POST['CreditType']=='WriteOff'){
					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES (11,
								" . $CreditNo . ",
								'" . $DefaultDispatchDate . "',
								" . $PeriodNo . ",
								" . $_POST['WriteOffGLCode'] . ",
								'" . $_SESSION['CreditItems']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "',
								" . round($OrderLine->StandardCost * $OrderLine->QtyDispatched,2) . ")";
				} else {
					$StockGLCode = GetStockGLCode($OrderLine->StockID, $db);
					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES (11,
								" . $CreditNo . ",
								'" . $DefaultDispatchDate . "',
								" . $PeriodNo . ",
								" . $StockGLCode['stockact'] . ",
								'" . $_SESSION['CreditItems']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "',
								" . round($OrderLine->StandardCost * $OrderLine->QtyDispatched,2) . ")";
				}

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side or write off of the cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

			} /* end of if GL and stock integrated and standard cost !=0 */

			if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $OrderLine->Price !=0){

//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['CreditItems']->DefaultSalesType, $db);

				$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
						VALUES (11,
							" . $CreditNo . ",
							'" . $DefaultDispatchDate . "',
							" . $PeriodNo . ",
							" . $SalesGLAccounts['salesglcode'] . ",
							'" . $_SESSION['CreditItems']->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . "',
							" . round(($OrderLine->Price * $OrderLine->QtyDispatched)/$_SESSION['CurrencyRate'],2) . "
							)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The credit note GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

				if ($OrderLine->DiscountPercent !=0){

					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES (11,
								" . $CreditNo . ",
								'" . $DefaultDispatchDate . "',
								" . $PeriodNo . ",
								" . $SalesGLAccounts['discountglcode'] . ",
								'" . $_SESSION['CreditItems']->DebtorNo . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%',
								" . -round(($OrderLine->Price * $OrderLine->QtyDispatched * $OrderLine->DiscountPercent)/$_SESSION['CurrencyRate'],2) . "
								)";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The credit note discount GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */
		} /*Quantity dispatched is more than 0 */
	} /*end of OrderLine loop */


	if ($_SESSION['CompanyRecord']['gllink_debtors']==1){

/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
		if (($_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost + $TaxTotal) !=0) {
			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					VALUES (11,
						" . $CreditNo . ",
						'" . $DefaultDispatchDate . "',
						" . $PeriodNo . ",
						" . $_SESSION['CompanyRecord']['debtorsact'] . ",
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						" . -round(($_SESSION['CreditItems']->total + $_SESSION['CreditItems']->FreightCost + $TaxTotal)/$_SESSION['CurrencyRate'],2) . "
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting for the credit note could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
		}


/*Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery */


		if (round($_SESSION['CreditItems']->FreightCost,2) !=0 ) {
			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
				VALUES (11,
					" . $CreditNo . ",
					'" . $DefaultDispatchDate . "',
					" . $PeriodNo . ",
					" . $_SESSION['CompanyRecord']['freightact'] . ",
					'" . $_SESSION['CreditItems']->DebtorNo . "',
					" . round($_SESSION['CreditItems']->FreightCost/$_SESSION['CurrencyRate'],2) . "
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting for this credit note could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
		}

		foreach ( $TaxTotals as $TaxAuthID => $TaxAmount){	
			if ($TaxAmount !=0 ){
				$SQL = "INSERT INTO gltrans (
						type, 
						typeno, 
						trandate, 
						periodno, 
						account, 
						narrative, 
						amount
						) 
					VALUES (
						11, 
						" . $CreditNo . ", 
						'" . $DefaultDispatchDate . "', 
						" . $PeriodNo . ", 
						" . $TaxGLCodes[$TaxAuthID] . ", 
						'" . $_SESSION['CreditItems']->DebtorNo . "', 
						" . ($TaxAmount/$_SESSION['CurrencyRate']) . "
					)";
	
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
			}
		}
	} /*end of if Sales and GL integrated */

	$SQL='COMMIT';
	$Result = DB_query($SQL,$db);
	
	unset($_SESSION['CreditItems']->LineItems);
	unset($_SESSION['CreditItems']);
	unset($_SESSION['ProcessingCredit']);

	echo _('Credit Note number') . ' ' . $CreditNo . ' ' . _('has been processed');
	if ($_SESSION['InvoicePortraitFormat']==0){
		echo "<BR><A HREF='". $rootpath . "/PrintCustTrans.php?" . SID . "&FromTransNo=" . $CreditNo . "&InvOrCredit=Credit&PrintPDF=True'>" . _('Print this credit note') . '</A>';
	} else {
		echo "<BR><A HREF='". $rootpath . "/PrintCustTransPortrait.php?" . SID . "&FromTransNo=" . $CreditNo . "&InvOrCredit=Credit&PrintPDF=True'>" . _('Print this credit note') . '</A>';
	}

/*end of process credit note */


} else { /*Process Credit NOT set so allow inputs to set up the credit note */

	echo "<TABLE>";

	echo '<TR><TD>' . _('Credit Note Type') . "</TD><TD><SELECT NAME=CreditType>";

	if (!isset($_POST['CreditType']) OR $_POST['CreditType']=="Return"){
		echo "<OPTION SELECTED VALUE='Return'>" . _('Goods returned to store');
		echo "<OPTION VALUE='WriteOff'>" . _('Goods written off');
		echo "<OPTION VALUE='ReverseOverCharge'>" . _('Reverse overcharge');
	} elseif($_POST['CreditType']=='WriteOff') {
		echo "<OPTION SELECTED VALUE='WriteOff'>" . _('Goods written off');
		echo "<OPTION VALUE='Return'>" . _('Goods returned to store');
		echo "<OPTION VALUE='ReverseOverCharge'>" . _('Reverse overcharge');
	} else {
		echo "<OPTION VALUE='WriteOff'>" . _('Goods written off');
		echo "<OPTION VALUE='Return'>" . _('Goods returned to store');
		echo "<OPTION SELECTED VALUE='ReverseOverCharge'>" . _('Reverse overcharge');
	}
	echo "</SELECT></TD></TR>";


	if (!isset($_POST['CreditType']) OR $_POST['CreditType']=='Return'){

/*if the credit note is a return of goods then need to know which location to receive them into */

		echo '<TR><TD>' . _('Goods returned to location') . '</TD><TD><SELECT NAME=Location>';

		$SQL="SELECT loccode, locationname FROM locations";
		$Result = DB_query($SQL,$db);

		if (!isset($_POST['Location'])){
			$_POST['Location'] = $_SESSION['CreditItems']->Location;
		}
		while ($myrow = DB_fetch_array($Result)) {

			if ($_POST['Location']==$myrow['loccode']){
				echo "<OPTION SELECTED VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			} else {
				echo "<OPTION VALUE='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			}
		}
		echo "</SELECT></TD></TR>";

	} elseif($_POST['CreditType']=='WriteOff') { /* the goods are to be written off to somewhere */

		echo '<TR><TD>' . _('Write off the cost of the goods to') . '</TD><TD><SELECT NAME="WriteOffGLCode">';

		$SQL='SELECT accountcode, 
				accountname 
			FROM chartmaster, accountgroups 
			WHERE chartmaster.group_=accountgroups.groupname 
			AND accountgroups.pandl=1 ORDER BY chartmaster.accountcode';

		$Result = DB_query($SQL,$db);

		while ($myrow = DB_fetch_array($Result)) {

			if ($_POST['WriteOffGLCode']==$myrow["accountcode"]){
				echo "<OPTION SELECTED VALUE=" . $myrow["accountcode"] . ">" . $myrow["accountname"];
			} else {
				echo "<OPTION VALUE=" . $myrow["accountcode"] . ">" . $myrow["accountname"];
			}
		}
		echo "</SELECT></TD></TR>";
	}
	echo '<TR><TD>' . _('Credit note text') . '</TD><TD><TEXTAREA NAME=CreditText COLS=31 ROWS=5>' . $_POST['CreditText'] . '</TEXTAREA></TD></TR>';
	echo '</TABLE><CENTER><INPUT TYPE=SUBMIT NAME=Update Value=' . _('Update') . '><P>';
	echo "<INPUT TYPE=SUBMIT NAME='ProcessCredit' Value='" . _('Process Credit') ."'></CENTER>";
}

echo "</FORM>";
include("includes/footer.inc");
?>