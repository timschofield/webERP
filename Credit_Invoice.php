<?php
/* $Revision: 1.12 $ */
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


if (!$_GET['InvoiceNumber'] && !$_SESSION['ProcessingCredit']) {
	/* This page can only be called with an invoice number for crediting*/
	prnMsg(_('This page can only be opened if an invoice has been selected for crediting') . '. ' . _('Please select an invoice first') . ' - ' . _('from the customer inquiry screen click the link to credit an invoice'),'info');
	include('includes/footer.inc');
	exit;

} elseif ($_GET['InvoiceNumber']>0) {

	unset($_SESSION['CreditItems']->LineItems);
	unset($_SESSION['CreditItems']);

	Session_register('CreditItems');
	Session_register('ProcessingCredit');
	Session_register('TaxRate');
	Session_Register('TaxDescription');
	Session_Register('CurrencyRate');
	Session_Register('TaxGLCode');
	Session_Register('Old_FreightCost');

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
				debtorsmaster.currcode,
				custbranch.defaultlocation,
				taxauthorities.description,
				currencies.rate as currency_rate,
				taxauthorities.taxglcode,
				taxauthorities.taxid,
				stockmoves.loccode
			FROM debtortrans, 
				debtorsmaster, 
				custbranch, 
				taxauthorities, 
				currencies, 
				stockmoves, 
				locations
			WHERE debtortrans.debtorno = debtorsmaster.debtorno
			AND debtortrans.branchcode = custbranch.branchcode
			AND debtortrans.debtorno = custbranch.debtorno
			AND custbranch.taxauthority = taxauthorities.taxid
			AND debtorsmaster.currcode = currencies.currabrev
			AND debtortrans.transno = " . $_GET['InvoiceNumber'] . "
			AND debtortrans.type=10
			AND stockmoves.type=10
			AND stockmoves.transno=debtortrans.transno";

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
		$_SESSION['TaxDescription'] = $myrow['description'];
		$_SESSION['TaxGLCode'] = $myrow['taxglcode'];
		$_SESSION['TaxAuthority'] = $myrow['taxid'];
		$_SESSION['CurrencyRate'] = $myrow['currency_rate'];
		$_SESSION['CreditItems']->OrderNo = $myrow['order_'];
		$_SESSION['CreditItems']->ShipVia = $myrow['shipvia'];

		DB_free_result($GetInvHdrResult);

		$DispTaxAuthResult = DB_query("SELECT taxauthority FROM locations WHERE loccode='" . $_SESSION['CreditItems']->Location . "'",$db);
		$myrow = DB_fetch_row($DispTaxAuthResult);

		$_SESSION['DispatchTaxAuthority'] = $myrow[0];
		$_SESSION['FreightTaxRate'] = GetTaxRate($_SESSION['TaxAuthority'], $_SESSION['DispatchTaxAuthority'],$_SESSION['DefaultTaxLevel'],$db)*100;
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
					stockmaster.units,
					stockmaster.discountcategory,
					(stockmoves.price * " . $_SESSION['CurrencyRate'] . ") AS price, -
					stockmoves.qty as quantity,
					stockmoves.discountpercent,
					stockmoves.trandate,
					stockmoves.taxrate,
					stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost AS standardcost,
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
								$myrow['narrative']
								);

				$_SESSION['CreditItems']->LineItems[$myrow['stockid']]->StandardCost= $myrow['standardcost'];
				$_SESSION['CreditItems']->LineItems[$myrow['stockid']]->TaxRate = $myrow['taxrate'];
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
						$_SESSION['CreditItems']->LineItems[$myrow['stockid']]->SerialItems[$SerialItemsRow['serialno']] = new SerialItem($SerialItemsRow['serialno'], -$SerialItemsRow['moveqty']);
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
}


If ($_SESSION['CreditItems']->ItemsOrdered > 0 OR isset($_POST['NewItem'])){

	If(isset($_GET['Delete'])){
		$_SESSION['CreditItems']->remove_from_cart($_GET['Delete']);
	}

	foreach ($_SESSION['CreditItems']->LineItems as $StockItem) {

		if (isset($_POST['Quantity_' . $StockItem->StockID])){

			$Narrative = $_POST['Narrative_' . $StockItem->StockID];
			$Quantity = $_POST['Quantity_' . $StockItem->StockID];
			$Price = $_POST['Price_' . $StockItem->StockID];
			$DiscountPercentage = $_POST['Discount_' . $StockItem->StockID];

			If ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
				prnMsg(_('The item could not be updated because you are attempting to set the quantity credited to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'),'error');
			} else {
				$_SESSION['CreditItems']->LineItems[$StockItem->StockID]->QtyDispatched=$Quantity;
				$_SESSION['CreditItems']->LineItems[$StockItem->StockID]->Price=$Price;
				$_SESSION['CreditItems']->LineItems[$StockItem->StockID]->DiscountPercent=($DiscountPercentage/100);
				$_SESSION['CreditItems']->LineItems[$StockItem->StockID]->Narrative=$Narrative;
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
<TD class='tableheader'>" . _('Tax') . ' %' . "</TD>
<TD class='tableheader'>" . _('Tax') . '<BR>' . _('Amount') . "</TD>
<TD class='tableheader'>" . _('Total') . '<BR>' . _('Incl Tax') . "</TD></TR>";

$_SESSION['CreditItems']->total = 0;
$_SESSION['CreditItems']->totalVolume = 0;
$_SESSION['CreditItems']->totalWeight = 0;

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

	$_SESSION['CreditItems']->total = $_SESSION['CreditItems']->total + $LineTotal;								$_SESSION['CreditItems']->totalVolume = $_SESSION['CreditItems']->totalVolume + $LnItm->QtyDispatched * $LnItm->Volume;
	$_SESSION['CreditItems']->totalWeight = $_SESSION['CreditItems']->totalWeight + $LnItm->QtyDispatched * $LnItm->Weight;

	echo $RowStarter .	"<TD>" . $LnItm->StockID . "</TD>
				<TD>$LnItm->ItemDescription</TD>
				<TD ALIGN=RIGHT>$LnItm->Quantity</TD>
				<TD>$LnItm->Units</TD>";

	if ($LnItm->Controlled==1){

		echo "<TD><input type=hidden name='Quantity_" . $LnItm->StockID ."'  value=" . $LnItm->QtyDispatched . "><A HREF='$rootpath/CreditItemsControlled.php?" . SID . "StockID=" . $LnItm->StockID . "&CreditInvoice=Yes'>" . $LnItm->QtyDispatched . "</A></TD>";

	} else {

		echo "<TD><input type=text name='Quantity_" . $LnItm->StockID ."' maxlength=6 SIZE=6 value=" . $LnItm->QtyDispatched . "></TD>";

	}

	$DisplayLineTotal = number_format($LineTotal,2);

	echo "<TD><INPUT TYPE=TEXT NAME='Price_" . $LnItm->StockID . "' MAXLENGTH=6 SIZE=6 VALUE=" . $LnItm->Price . "></TD>
	<TD><INPUT TYPE=TEXT NAME='Discount_" . $LnItm->StockID . "' MAXLENGTH=3 SIZE=3 VALUE=" . ($LnItm->DiscountPercent * 100) . "></TD>
	<TD ALIGN=RIGHT>$DisplayLineTotal</TD><TD ALIGN=RIGHT>" . round($StockItem->TaxRate * 100,2) . "%</TD>
	<TD ALIGN=RIGHT>" . number_format($LineTotal*$StockItem->TaxRate,2) . "</TD>
	<TD ALIGN=RIGHT>" . number_format($LineTotal*(1+$StockItem->TaxRate),2) . "</TD>
	<TD><A HREF='". $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $LnItm->StockID . "'>" . _('Delete') . '</A></TD></TR>';

	echo $RowStarter . "<TD COLSPAN=7><TEXTAREA  NAME='Narrative_" . $LnItm->StockID . "' cols=100% rows=1>" . $LnItm->Narrative . "</TEXTAREA><BR><HR></TD></TR>";

	$TaxTotal += $LineTotal*$StockItem->TaxRate;
} /*end foreach loop displaying the invoice lines to credit */

if (!isset($_POST['ChargeFreightCost'])){
	$_POST['ChargeFreightCost']=0;
}

if  (!isset($_POST['FreightTaxRate'])) {
        $_POST['FreightTaxRate']=$_SESSION['FreightTaxRate'];
} else {
	$_SESSION['FreightTaxRate']=$_POST['FreightTaxRate'];
}

echo '<TR>
	<TD COLSPAN=3 ALIGN=RIGHT>' . _('Freight cost charged on invoice') . '</TD>
	<TD ALIGN=RIGHT>' . $_SESSION['Old_FreightCost'] . '</TD>
	<TD></TD>
	<TD COLSPAN=2 ALIGN=RIGHT>' . _('Credit Freight Cost') . "</TD>
	<TD><INPUT TYPE=TEXT SIZE=6 MAXLENGTH=6 NAME='ChargeFreightCost' VALUE=" . $_POST['ChargeFreightCost'] . "></TD>";


echo "<TD><INPUT TYPE=TEXT SIZE=2 MAXLENGTH=2 NAME='FreightTaxRate' VALUE=" . $_POST['FreightTaxRate'] . ">%</TD>
	<TD ALIGN=RIGHT>" . number_format($_POST['FreightTaxRate']*$_POST['ChargeFreightCost']/100,2) . "</TD>
	<TD ALIGN=RIGHT>" . number_format((100+$_POST['FreightTaxRate'])*$_POST['ChargeFreightCost']/100,2) . "</TD>
</TR>";

$DisplayTotal = number_format($_SESSION['CreditItems']->total + $_POST['ChargeFreightCost'],2);
$TaxTotal += $_POST['FreightTaxRate']*$_POST['ChargeFreightCost']/100;

echo '<TR>
	<TD COLSPAN=7 ALIGN=RIGHT>' . _('Credit Totals') . "</TD>
	<TD ALIGN=RIGHT><HR><B>$DisplayTotal</B><HR></TD>
	<TD></TD>
	<TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal,2) . "<HR></TD>
	<TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal+($_SESSION['CreditItems']->total + $_POST['ChargeFreightCost']),2) . "</B><HR></TD>
</TR></TABLE>";

$DefaultDispatchDate = Date($_SESSION['DefaultDateFormat']);


if (isset($_POST['ProcessCredit'])){

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
/*Start an SQL transaction */

	$SQL = "BEGIN";
	$Result = DB_query($SQL,$db);

	$CreditNo = GetNextTransNo(11, $db);
	$PeriodNo = GetPeriod($DefaultDispatchDate, $db);
	$DefaultDispatchDate= FormatDateForSQL($DefaultDispatchDate);


	/*Calculate the allocation and see if it is possible to allocate to the invoice being credited */

	$SQL = "SELECT (ovamount+ovgst+ovfreight-ovdiscount-alloc) as baltoallocate
		FROM debtortrans
		WHERE transno=" . $_SESSION['ProcessingCredit'] . " AND type=10";
	$Result = DB_query($SQL,$db);
	$myrow = DB_fetch_row($Result);


	/*Do some rounding */

	$_SESSION['CreditItems']->total = round($_SESSION['CreditItems']->total,2);
	$_POST['ChargeFreightCost'] = round($_POST['ChargeFreightCost'],2);
	$TaxTotal = round($TaxTotal,2);

	$Allocate_amount=0;
	$Settled =0;
	$SettledInvoice=0;
	if ($myrow[0]>0){ /*the invoice is not already fully allocated */

		if ($myrow[0] > ($_SESSION['CreditItems']->total + $_POST['ChargeFreightCost'] + $TaxTotal)){

			$Allocate_amount = $_SESSION['CreditItems']->total + $_POST['ChargeFreightCost'] + $TaxTotal;
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
			" . -$TaxTotal . ", " . -$_POST['ChargeFreightCost'] . ",
			" . $_SESSION['CurrencyRate'] . ",
			'" . $_POST['CreditText'] . "',
			" . -$Allocate_amount . ",
			" . $Settled . ")";

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The customer credit note transaction could not be added to the database because');
	$DbgMsg = _('The following SQL to insert the customer credit note was used');
	$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

	$CreditTransID = DB_Last_Insert_ID($db,'debtortrans','id');

/*Now insert the allocation record if > 0 */
	if ($Allocate_amount!=0){
		$SQL = "INSERT INTO custallocns (amt,
						transid_allocfrom,
						transid_allocto,
						datealloc)
			VALUES (" . $Allocate_amount . ",
				" . $CreditTransID . ",
				" . $_SESSION['CreditItems']->TransID . ",
				'" . Date("Y-m-d") . "')";

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
			if ($MBFlag=="M" oR $MBFlag=="B"){
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

			if ($_POST['CreditType']=="Return"){

				$SQL = "UPDATE salesorderdetails
					SET qtyinvoiced = qtyinvoiced - " . $OrderLine->QtyDispatched . ",
					completed=0
					WHERE orderno = " . $_SESSION['ProcessingCredit'] . "
					AND stkcode = '" . $OrderLine->StockID . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order detail record could not be updated for the reduced quantity invoiced because');
				$DbgMsg = _('The following SQL to update the sales order detail record was used');
				$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
				/* Update location stock records if not a dummy stock item */

				if ($MBFlag=="B" OR $MBFlag=="M") {

					$SQL = "UPDATE
						locstock
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
				    	bom.quantity, 			stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost as standard
					FROM bom, 
						stockmaster
					WHERE bom.component=stockmaster.stockid
					AND bom.parent='" . $OrderLine->StockID . "'
					AND bom.effectiveto > '" . Date("Y-m-d") . "'
					AND bom.effectiveafter < '" . Date("Y-m-d") . "'";

				    $ErrMsg = _('Could not retrieve assembly components from the database for') . ' ' . $OrderLine->StockID . ' ' . _('because');
				    $DbgMsg = _('The SQL that failed was');
				    $AssResult = DB_query($sql,$db, $ErrMsg, $DbgMsg, true);

				    while ($AssParts = DB_fetch_array($AssResult,$db)){
					   $StandardCost += $AssParts['standard'];
					   /*Determine the type of stock item being credited */
					   $SQL = "SELECT
					   	mbflag
						FROM
						stockmaster
						WHERE stockid = '" . $AssParts['component'] . "'";
					   $Result = DB_query($SQL,$db);
					   $MBFlagRow = DB_fetch_row($Result);
				 	   $Component_MBFlag = $MBFlagRow[0];

					   /* Insert stock movements for the stock coming back in - with unit cost */
					   if ($Component_MBFlag=="M" oR $Component_MBFlag=="B"){
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

					   	$SQL = "INSERT INTO
							stockmoves (
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
								newqoh
								)
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
				    $_SESSION['CreditItems']->LineItems[$OrderLine->StockID]->StandardCost = $StandardCost;
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
								narrative,
								taxrate)
						VALUES ('" . $OrderLine->StockID . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $DefaultDispatchDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							'Ex Inv - " . $_SESSION['ProcessingCredit'] . "',
							" . $OrderLine->QtyDispatched . ",
							" . $OrderLine->DiscountPercent . ",
							" . $OrderLine->StandardCost . ",
							" .  ($QtyOnHandPrior + $OrderLine->QtyDispatched) . ",
							'" . $OrderLine->Narrative . "',
							" . $OrderLine->TaxRate . "
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
							price,
							prd,
							reference,
							qty,
							discountpercent,
							standardcost,
							narrative,
							taxrate)
						VALUES ('" . $OrderLine->StockID . "',
							11,
							" . $CreditNo . ",
							'" . $_SESSION['CreditItems']->Location . "',
							'" . $DefaultDispatchDate . "',
							'" . $_SESSION['CreditItems']->DebtorNo . "',
							'" . $_SESSION['CreditItems']->Branch . "',
							" . $LocalCurrencyPrice . ",
							" . $PeriodNo . ",
							'Ex Inv - " . $_SESSION['ProcessingCredit'] . "',
							" . $OrderLine->QtyDispatched . ",
							" . $OrderLine->DiscountPercent . ",
							" . $OrderLine->StandardCost . ",
							'" . $OrderLine->Narrative . "',
							" . $OrderLine->TaxRate . "
						)";
				}

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records was used');
				$Result = DB_query($SQL, $db,$ErrMsg,$DbgMsg,true);


			}  elseif ($_POST['CreditType']=="WriteOff") {
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
							narrative,
							taxrate)
					VALUES ('" . $OrderLine->StockID . "',
						11,
						" . $CreditNo . ",
						'" . $_SESSION['CreditItems']->Location . "',
						'" . $DefaultDispatchDate . "',
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						'" . $_SESSION['CreditItems']->Branch . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ",
						'Ex Inv - " . $_SESSION['ProcessingCredit'] . "',
						" . $OrderLine->QtyDispatched . ",
						" . $OrderLine->DiscountPercent . ",
						" . $OrderLine->StandardCost . ",
						" . ($QtyOnHandPrior +$OrderLine->QtyDispatched)  . ",
						'" . $OrderLine->Narrative . "',
						" . $OrderLine->TaxRate . "
						)";

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
						'" . $OrderLine->Narrative . "'
						)";


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
							narrative,
							taxrate)
					VALUES ('" . $OrderLine->StockID . "',
						11,
						" . $CreditNo . ",
						'" . $_SESSION['CreditItems']->Location . "',
						'" . $DefaultDispatchDate . "',
						'" . $_SESSION['CreditItems']->DebtorNo . "',
						'" . $_SESSION['CreditItems']->Branch . "',
						" . $LocalCurrencyPrice . ",
						" . $PeriodNo . ",
						" . _('Ex Inv') .' - ' . $_SESSION['ProcessingCredit'] . "',
						" . $OrderLine->QtyDispatched . ",
						" . $OrderLine->DiscountPercent . ",
						" . $OrderLine->StandardCost . ",
						" . $QtyOnHandPrior  . ",
						1,
						'" . $OrderLine->Narrative . "',
						" . $OrderLine->TaxRate . "
					)";


				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement records for the purpose of display on the credit note was used');

				$Result = DB_query($SQL, $db,$ErrMsg, $DbgMsg, true);
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

				if ($_POST['CreditType']=="ReverseOverCharge"){

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

				if ($_POST['CreditType']=="ReverseOverCharge"){

					$SQL = "INSERT salesanalysis (typeabbrev,
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

					$SQL = "INSERT salesanalysis (typeabbrev,
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

			if ($_SESSION['CompanyRecord']["GLLink_Stock"]==1 AND $OrderLine->StandardCost !=0 AND $_POST['CreditType']!='ReverseOverCharge'){

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


				if ($_POST['CreditType']=="WriteOff"){
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

			if ($_SESSION['CompanyRecord']['GLLink_Debtors']==1 && $OrderLine->Price !=0){

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
							" . round($OrderLine->Price * $OrderLine->QtyDispatched,2) . "
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
								" . -round($OrderLine->Price * $OrderLine->QtyDispatched * $OrderLine->DiscountPercent,2) . "
								)";
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The credit note discount GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */
		} /*Quantity dispatched is more than 0 */
	} /*end of OrderLine loop */


	if ($_SESSION['CompanyRecord']["GLLink_Debtors"]==1){

/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
		if (($_SESSION['CreditItems']->total + $_POST['ChargeFreightCost'] + $TaxTotal) !=0) {
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
						" . -round($_SESSION['CreditItems']->total + $_POST['ChargeFreightCost'] + $TaxTotal,2) . "
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting for the credit note could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
		}
		if ($_POST['ChargeFreightCost'] !=0) {
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
					" . $_POST['ChargeFreightCost'] . "
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting for this credit note could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
		}
		if ($TaxTotal !=0){
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
					" . $_SESSION['TaxGLCode'] . ",
					'" . $_SESSION['CreditItems']->DebtorNo . "',
					" . $TaxTotal . "
					)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting for this credit note could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
		}
	} /*end of if Sales and GL integrated */

	$SQL="Commit";
	$Result = DB_query($SQL,$db);

	unset($_SESSION['CreditItems']->LineItems);
	unset($_SESSION['CreditItems']);
	unset($_SESSION['ProcessingCredit']);

	echo _('Credit Note number') . ' ' . $CreditNo . ' ' . _('has been processed');
	echo "<BR><A HREF='". $rootpath . "/PrintCustTrans.php?" . SID . "FromTransNo=" . $CreditNo . "&InvOrCredit=Credit&PrintPDF=True'>" . _('Print this credit note') . '</A>';

/*end of process credit note */


} else { /*Process Credit NOT set so allow inputs to set up the credit note */

	echo "<TABLE>";

	echo '<TR><TD>' . _('Credit Note Type') . "</TD><TD><SELECT NAME=CreditType>";

	if (!isset($_POST['CreditType']) OR $_POST['CreditType']=="Return"){
		echo "<OPTION SELECTED VALUE='Return'>" . _('Goods returned to store');
		echo "<OPTION VALUE='WriteOff'>" . _('Goods written off');
		echo "<OPTION VALUE='ReverseOverCharge'>" . _('Reverse overcharge');
	} elseif($_POST['CreditType']=="WriteOff") {
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

		echo '<TR><TD>' . _('Write off the cost of the goods to') . '</TD><TD><SELECT NAME=WriteOffGLCode>';

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