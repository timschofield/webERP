<?php
/* $Revision: 1.4 $ */
$title = "Credit An Invoice ";

$PageSecurity =3;

/*Functions to get the GL codes to post the transaction to */
include("includes/GetSalesTransGLCodes.inc");
/*defines the structure of the data required to hold the transaction as a session variable */
include("includes/DefineCartClass.php");
include("includes/DefineSerialItems.php");
/* Session started in header.inc for password checking and authorisation level check */
include("includes/session.inc");
include("includes/header.inc");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");


if (!$_GET['InvoiceNumber'] && !$_SESSION['ProcessingCredit']) {
	/* This page can only be called with an invoice number for crediting*/
	die ("This page can only be opened if an invoice has been selected for crediting. Please select an invoice first - from the customer inquiry screen click the link to credit an invoice.");

} elseif ($_GET['InvoiceNumber']>0) {

	unset($_SESSION['CreditItems']->LineItems);
	unset($_SESSION['CreditItems']);

	Session_register("CreditItems");
	Session_register("ProcessingCredit");
	Session_register("TaxRate");
	Session_Register("TaxDescription");
	Session_Register("CurrencyRate");
	Session_Register("TaxGLCode");
	Session_Register("Old_FreightCost");

	$_SESSION['ExistingOrder']=0; /*required to avoid adding to database in error */
	$_SESSION['ProcessingCredit'] = $_GET['InvoiceNumber'];
	$_SESSION['CreditItems'] = new cart;

/*read in all the guff from the selected invoice into the Items cart	*/


	$InvoiceHeaderSQL = "SELECT DISTINCT
				DebtorTrans.ID AS TransID,
				DebtorTrans.DebtorNo,
				DebtorsMaster.Name,
				DebtorTrans.BranchCode,
				DebtorTrans.Reference,
				DebtorTrans.InvText,
				DebtorTrans.Order_,
				DebtorTrans.TranDate,
				DebtorTrans.Tpe,
				DebtorTrans.ShipVia,
				DebtorTrans.OvFreight,
				DebtorsMaster.CurrCode,
				CustBranch.DefaultLocation,
				TaxAuthorities.Description,
				Currencies.Rate AS Currency_Rate,
				TaxAuthorities.TaxGLCode,
				TaxAuthorities.TaxID,
				StockMoves.LocCode
			FROM DebtorTrans, DebtorsMaster, CustBranch, TaxAuthorities, Currencies, StockMoves, Locations
			WHERE DebtorTrans.DebtorNo = DebtorsMaster.DebtorNo
			AND DebtorTrans.BranchCode = CustBranch.BranchCode
			AND DebtorTrans.DebtorNo = CustBranch.DebtorNo
			AND CustBranch.TaxAuthority = TaxAuthorities.TaxID
			AND DebtorsMaster.CurrCode = Currencies.CurrAbrev
			AND DebtorTrans.TransNo = " . $_GET['InvoiceNumber'] . "
			AND DebtorTrans.Type=10
			AND StockMoves.Type=10
			AND StockMoves.TransNo=DebtorTrans.TransNo";

	$ErrMsg = "<BR>A credit cannot be produced for the selected invoice. The invoice details cannot be retrieved because:";
	$DbgMsg = "<BR>The SQL to retrieve the invoice details (and failed) was:";
	$GetInvHdrResult = DB_query($InvoiceHeaderSQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($GetInvHdrResult)==1) {

		$myrow = DB_fetch_array($GetInvHdrResult);

/*CustomerID variable registered by header.inc */
		$_SESSION['CustomerID'] = $myrow["DebtorNo"];
		$_SESSION['CreditItems']->TransID = $myrow["TransID"];
		$_SESSION['CreditItems']->Branch = $myrow["BranchCode"];
		$_SESSION['CreditItems']->CustomerName = $myrow["Name"];
		$_SESSION['CreditItems']->CustRef = $myrow["Reference"];
		$_SESSION['CreditItems']->Comments = $myrow["InvText"];
		$_SESSION['CreditItems']->DefaultSalesType =$myrow["Tpe"];
		$_SESSION['CreditItems']->DefaultCurrency = $myrow["CurrCode"];
		$_SESSION['CreditItems']->Location = $myrow["LocCode"];
		$_SESSION['Old_FreightCost'] = $myrow["OvFreight"];
		$_SESSION['TaxDescription'] = $myrow["Description"];
		$_SESSION['TaxGLCode'] = $myrow["TaxGLCode"];
		$_SESSION['TaxAuthority'] = $myrow['TaxID'];
		$_SESSION['CurrencyRate'] = $myrow["Currency_Rate"];
		$_SESSION['CreditItems']->OrderNo = $myrow["Order_"];
		$_SESSION['CreditItems']->ShipVia = $myrow["ShipVia"];

		DB_free_result($GetInvHdrResult);

		$DispTaxAuthResult = DB_query("SELECT TaxAuthority FROM Locations WHERE LocCode='" . $_SESSION['CreditItems']->Location . "'",$db);
		$myrow = DB_fetch_row($DispTaxAuthResult);

		$_SESSION['DispatchTaxAuthority'] = $myrow[0];
		$_SESSION['FreightTaxRate'] = GetTaxRate($_SESSION['TaxAuthority'], $_SESSION['DispatchTaxAuthority'],$DefaultTaxLevel,$db)*100;
/*now populate the line items array with the stock movement records for the invoice*/


		$LineItemsSQL = "SELECT StockMoves.StkMoveNo,
					StockMoves.StockID,
					StockMaster.Description,
					StockMaster.Volume,
					StockMaster.KGS,
					StockMaster.MBflag,
					StockMaster.Controlled,
					StockMaster.Serialised,
					StockMaster.DecimalPlaces,
					StockMaster.Units,
					StockMaster.DiscountCategory,
					(StockMoves.Price * " . $_SESSION['CurrencyRate'] . ") AS Price, -
					StockMoves.Qty AS Quantity,
					StockMoves.DiscountPercent,
					StockMoves.TranDate,
					StockMoves.TaxRate,
					StockMaster.Materialcost + StockMaster.Labourcost + StockMaster.OverheadCost AS StandardCost
				FROM StockMoves, StockMaster
				WHERE StockMoves.StockID = StockMaster.StockID
				AND StockMoves.TransNo =" . $_GET['InvoiceNumber'] . "
				AND StockMoves.Type=10
				AND StockMoves.Show_On_Inv_Crds=1";

		$ErrMsg = "<P>This invoice can not be credited using this program. A manual credit note will need to be prepared. The line items of the order cannot be retrieved because:";
		$Dbgmsg = "<P>The SQL used to get the transaction header was:";

		$LineItemsResult = DB_query($LineItemsSQL,$db,$ErrMsg, $DbgMsg);

		if (db_num_rows($LineItemsResult)>0) {

			while ($myrow=db_fetch_array($LineItemsResult)) {

				$_SESSION['CreditItems']->add_to_cart($myrow["StockID"],
								$myrow["Quantity"],
								$myrow["Description"],
								$myrow["Price"],
								$myrow["DiscountPercent"],
								$myrow["Units"],
								$myrow["Volume"],
								$myrow["KGS"],
								0,
								$myrow["MBflag"],
								$myrow['TranDate'],
								0,
								$myrow['DiscountCategory'],
								$myrow['Controlled'],
								$myrow['Serialised'],
								$myrow['DecimalPlaces']
								);

				$_SESSION['CreditItems']->LineItems[$myrow["StockID"]]->StandardCost= $myrow["StandardCost"];
				$_SESSION['CreditItems']->LineItems[$myrow['StockID']]->TaxRate = $myrow['TaxRate'];
				if ($myrow['Controlled']==1){/* Populate the SerialItems array too*/

					$SQL = "SELECT 	SerialNo,
							MoveQty
						FROM StockSerialMoves
						WHERE StockMoveNo=" . $myrow['StkMoveNo'] . "
						AND StockID = '" . $myrow['StockID'] . "'";

					$ErrMsg = "<P>This invoice can not be credited using this program. A manual credit note will need to be prepared. The line item " . $myrow['StockID'] . " is controlled, but the serial numbers/batch numbers could not be retrieved because:";
					$Dbgmsg = "<P>The SQL used to get the controlled item details was:";
					$SerialItemsResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

					while ($SerialItemsRow = DB_fetch_array($SerialItemsResult)){
						$_SESSION['CreditItems']->LineItems[$myrow['StockID']]->SerialItems[$SerialItemsRow['SerialNo']] = new SerialItem($SerialItemsRow['SerialNo'], $SerialItemsRow['MoveQty']);
					}
				} /* end if the item is a controlled item */
			} /* loop thro line items from stock movement records */

		} else { /* there are no stock movement records created for that invoice */

			echo "<CENTER><A HREF='$rootpath/index.php?" . SID . "'>Back to the menu</A></CENTER>";
			echo "<P>There are no line items that were retrieved for this invoice. The automatic credit program can not create a credit note from this invoice.";
			include("includes/footer.inc");
			exit;
		} //end of checks on returned data set
		DB_free_result($LineItemsResult);
	} else {
		echo "<P>This invoice can not be credited using the automatic facility. <BR>CRITICAL ERROR: Please report that a duplicate DebtorTrans header record was found for invoice " . $SESSION['ProcessingCredit'];
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

			$Quantity = $_POST['Quantity_' . $StockItem->StockID];
			$Price = $_POST['Price_' . $StockItem->StockID];
			$DiscountPercentage = $_POST['Discount_' . $StockItem->StockID];

			If ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
				echo "<BR>The item could not be updated because you are attempting to set the quantity credited to less than 0, or the price less than 0 or the discount more than 100% or less than 0%";
			} else {
				$_SESSION['CreditItems']->LineItems[$StockItem->StockID]->QtyDispatched=$Quantity;
				$_SESSION['CreditItems']->LineItems[$StockItem->StockID]->Price=$Price;
				$_SESSION['CreditItems']->LineItems[$StockItem->StockID]->DiscountPercent=($DiscountPercentage/100);
			}
		}
	}

}


/* Always display credit quantities
NB QtyDispatched in the LineItems array is used for the quantity to credit */

echo "<FONT COLOR=BLUE><B>Credit Invoice " .$_SESSION['ProcessingCredit'] . "</B></FONT>
	<CENTER><FONT SIZE=4><B><U>" . $_SESSION['CreditItems']->CustomerName . "</U></B></FONT>
	<FONT SIZE=3> - Credit Note amounts stated in " . $_SESSION['CreditItems']->DefaultCurrency . "</CENTER><BR>";

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";


echo "<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=0><TR>
<TD class='tableheader'>Item Code</TD>
<TD class='tableheader'>Item Description</TD>
<TD class='tableheader'>Invoiced</TD>
<TD class='tableheader'>Units</TD>
<TD class='tableheader'>Credit<BR>Quantity</TD>
<TD class='tableheader'>Price</TD>
<TD class='tableheader'>Discount</TD>
<TD class='tableheader'>Total<BR>Excl Tax</TD>
<TD class='tableheader'>Tax %</TD>
<TD class='tableheader'>Tax<BR>Amount</TD>
<TD class='tableheader'>Total<BR>Incl Tax</TD></TR>";

$_SESSION['CreditItems']->total = 0;
$_SESSION['CreditItems']->totalVolume = 0;
$_SESSION['CreditItems']->totalWeight = 0;

/*show the line items on the invoice with the quantity to credit and price being available for modification */

$k=0; //row colour counter

foreach ($_SESSION['CreditItems']->LineItems as $LnItm) {

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}

	$LineTotal =($LnItm->QtyDispatched * $LnItm->Price * (1 - $LnItm->DiscountPercent));

	$_SESSION['CreditItems']->total = $_SESSION['CreditItems']->total + $LineTotal;														$_SESSION['CreditItems']->totalVolume = $_SESSION['CreditItems']->totalVolume + $LnItm->QtyDispatched * $LnItm->Volume;
	$_SESSION['CreditItems']->totalWeight = $_SESSION['CreditItems']->totalWeight + $LnItm->QtyDispatched * $LnItm->Weight;

	echo "<TD>" . $LnItm->StockID . "</TD><TD>$LnItm->ItemDescription</TD><TD ALIGN=RIGHT>$LnItm->Quantity</TD><TD>$LnItm->Units</TD>";

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
	<TD><A HREF='". $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $LnItm->StockID . "'>Delete</A></TD></TR>";

	$TaxTotal += $LineTotal*$StockItem->TaxRate;
}

if (!isset($_POST['ChargeFreightCost'])){
	$_POST['ChargeFreightCost']=0;
}

if  (!isset($_POST['FreightTaxRate'])) {
        $_POST['FreightTaxRate']=$_SESSION['FreightTaxRate'];
} else {
	$_SESSION['FreightTaxRate']=$_POST['FreightTaxRate'];
}

echo "<TR>
	<TD COLSPAN=3 ALIGN=RIGHT>Freight Cost charged on invoice</TD>
	<TD ALIGN=RIGHT>" . $_SESSION['Old_FreightCost'] . "</TD>
	<TD></TD>
	<TD COLSPAN=2 ALIGN=RIGHT>Credit Freight Cost</TD>
	<TD><INPUT TYPE=TEXT SIZE=6 MAXLENGTH=6 NAME='ChargeFreightCost' VALUE=" . $_POST['ChargeFreightCost'] . "></TD>";


echo "<TD><INPUT TYPE=TEXT SIZE=2 MAXLENGTH=2 NAME='FreightTaxRate' VALUE=" . $_POST['FreightTaxRate'] . ">%</TD>
	<TD ALIGN=RIGHT>" . number_format($_POST['FreightTaxRate']*$_POST['ChargeFreightCost']/100,2) . "</TD>
	<TD ALIGN=RIGHT>" . number_format((100+$_POST['FreightTaxRate'])*$_POST['ChargeFreightCost']/100,2) . "</TD>
</TR>";

$DisplayTotal = number_format($_SESSION['CreditItems']->total + $_POST['ChargeFreightCost'],2);
$TaxTotal += $_POST['FreightTaxRate']*$_POST['ChargeFreightCost']/100;

echo "<TR>
	<TD COLSPAN=7 ALIGN=RIGHT>Credit Totals</TD>
	<TD ALIGN=RIGHT><HR><B>$DisplayTotal</B><HR></TD>
	<TD></TD>
	<TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal,2) . "<HR></TD>
	<TD ALIGN=RIGHT><HR><B>" . number_format($TaxTotal+($_SESSION['CreditItems']->total + $_POST['ChargeFreightCost']),2) . "</B><HR></TD>
</TR></TABLE>";

$DefaultDispatchDate = Date($DefaultDateFormat);


If (isset($_POST['Edit'])){

	echo "<input type='hidden' name='StockID' value='" . $_SESSION['CreditItems']->LineItems[$_POST['Edit']]->StockID ."'>";
	echo "<TABLE BORDER=1><TR><TD><B>Editing Item:</B></TD><TD>" . $_SESSION['CreditItems']->LineItems[$_POST['Edit']]->ItemDescription . "</TD></TR>";
	echo "<TR><TD>Quantity:</TD><TD><input type='Text' SIZE=7 MAXLENGTH=6 name='Qty' value=" . $_SESSION['CreditItems']->LineItems[$_POST['Edit']]->Quantity . "></TD></TR>";
	echo "<TR><TD>Discount %:</TD><TD><input type='Text' SIZE=5 MAXLENGTH=4 name='Disc' value=" . ($_SESSION['CreditItems']->LineItems[$_POST['Edit']]->DiscountPercent*100) . "></TD></TR>";
	echo "<TR><TD>Price:</TD><TD><input type='Text' SIZE=15 MAXLENGTH=14 name='Price' value=" . $_SESSION['CreditItems']->LineItems[$_POST['Edit']]->Price . "></TD></TR>";

	echo "</TABLE><CENTER><INPUT TYPE=SUBMIT NAME='UpdateItem' VALUE='Update'><INPUT TYPE=SUBMIT NAME='Delete' VALUE='Delete'></CENTER>";
}




if ($_POST['ProcessCredit']=="Process Credit"){

/* SQL to process the postings for sales credit notes... First Get the area where the credit note is to from the branches table */

	$SQL = "SELECT Area FROM CustBranch WHERE CustBranch.DebtorNo ='". $_SESSION['CustomerID'] . "' AND CustBranch.BranchCode = '" . $_SESSION['CreditItems']->Branch . "'";

	$Result = DB_query($SQL,$db);
	$myrow = DB_fetch_row($Result);
	$Area = $myrow[0];
	DB_free_result($Result);

/*Now Read in company record to get information on GL Links and debtors GL account*/

	$CompanyData = ReadInCompanyRecord($db);
	if ($CompanyData==0){
		/*The company data and preferences could not be retrieved for some reason */
		echo "<P>The company information and preferences could not be retrieved - see your system administrator";
		exit;
	}


/*Now Get the next credit note number - function in SQL_CommonFunctions*/
/*Start an SQL transaction */

	$SQL = "Begin";
	$Result = DB_query($SQL,$db);

	$CreditNo = GetNextTransNo(11, $db);
	$PeriodNo = GetPeriod($DefaultDispatchDate, $db);
	$DefaultDispatchDate= FormatDateForSQL($DefaultDispatchDate);


	/*Calculate the allocation and see if it is possible to allocate to the invoice being credited */

	$SQL = "SELECT (OvAmount+OvGST+OvFreight-OvDiscount-Alloc) AS BalToAllocate FROM DebtorTrans WHERE TransNo=" . $_SESSION['ProcessingCredit'] . " AND Type=10";
	$Result = DB_query($SQL,$db);
	$myrow = DB_fetch_row($Result);

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

		$SQL = "UPDATE DebtorTrans SET Alloc = Alloc + " . $Allocate_amount . ", Settled=" . $SettledInvoice . " WHERE TransNo = " . $_SESSION['ProcessingCredit'] . " AND Type=10";
		$Result = DB_query($SQL,$db);

		if (DB_error_no($db) !=0){
			echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The alteration to the invoice record to reflect the allocation of the credit note to the invoice could not be done because: -<BR>" . DB_error_msg($db);
			if ($debug==1){
				echo "<BR>The following SQL to update the invoice allocation was used:<BR>$SQL<BR>";
			}

			$SQL = "rollback";
			$Result = DB_query($SQL,$db);
			exit;
		}
	}

/*Now insert the Credit Note into the DebtorTrans table with the allocations as calculated above*/

	$SQL = "INSERT INTO DebtorTrans (TransNo, Type, DebtorNo, BranchCode, TranDate, Prd,
		Reference, Tpe, Order_, OvAmount, OvGST, OvFreight, Rate, InvText, Alloc, Settled)
		VALUES (". $CreditNo . ", 11, '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "',
		'" . $DefaultDispatchDate . "', " . $PeriodNo . ", 'Inv-" . $_SESSION['ProcessingCredit'] . "',
		'" . $_SESSION['CreditItems']->DefaultSalesType . "', " . $_SESSION['CreditItems']->OrderNo . ",
		" . -($_SESSION['CreditItems']->total) . ", " . -$TaxTotal . ", " . -$_POST['ChargeFreightCost'] . ",
		" . $_SESSION['CurrencyRate'] . ", '" . $_POST['CreditText'] . "', " . -$Allocate_amount . ", " . $Settled . ")";

	$Result = DB_query($SQL,$db);
	if (DB_error_no($db) !=0){
		echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The customer credit note transaction could not be added to the database because: -<BR>" . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The following SQL to insert the customer credit note was used:<BR>$SQL<BR>";
		}

		$SQL = "rollback";
		$Result = DB_query($SQL,$db);
		exit;
	}
	$CreditTransID = DB_Last_Insert_ID($db);

/*Now insert the allocation record if > 0 */
	if ($Allocate_amount!=0){
		$SQL = "INSERT INTO CustAllocns (Amt, TransID_AllocFrom, TransID_AllocTo, DateAlloc) VALUES (" . $Allocate_amount . ", " . $CreditTransID . ", " . $_SESSION['CreditItems']->TransID . ", '" . Date("Y-m-d") . "')";
		$Result = DB_query($SQL,$db);

		if (DB_error_no($db) !=0){
		    echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The allocation record for the credit note could not be added to the database because: -<BR>" . DB_error_msg($db);
		    if ($debug==1){
			 echo "<BR>The following SQL to insert the allocation record for the credit note was used:<BR>$SQL<BR>";
		    }

		    $SQL = "rollback";
		    $Result = DB_query($SQL,$db);
		    exit;
		 }
	}

/* Update sales order details quantity invoiced less this credit quantity. */

	foreach ($_SESSION['CreditItems']->LineItems as $OrderLine) {

		if ($OrderLine->QtyDispatched >0){
			$LocalCurrencyPrice= ($OrderLine->Price / $_SESSION['CurrencyRate']);

			/*Determine the type of stock item being credited */
			$SQL = "SELECT MBflag FROM StockMaster WHERE StockID = '" . $OrderLine->StockID . "'";
			$Result = DB_query($SQL,$db);
			$MBFlagRow = DB_fetch_row($Result);
			$MBFlag = $MBFlagRow[0];
			if ($MBFlag=="M" oR $MBFlag=="B"){
				/*Need to get the current location quantity will need it later for the stock movements */
		 		$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $OrderLine->StockID . "' AND LocCode= '" . $_SESSION['CreditItems']->Location . "'";
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

				$SQL = "UPDATE SalesOrderDetails SET QtyInvoiced = QtyInvoiced - " . $OrderLine->QtyDispatched . ", Completed=0 WHERE OrderNo = " . $_SESSION['ProcessingCredit'] . " AND StkCode = '" . $OrderLine->StockID . "'";
				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The sales order detail record could not be updated for the reduced quantity invoiced because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to update the sales order detail record was used:<BR>$SQL<BR>";
					}
					$SQL = "Rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}

				/* Update location stock records if not a dummy stock item */

				if ($MBFlag=="B" || $MBFlag=="M") {

					$SQL = "UPDATE LocStock SET LocStock.Quantity = LocStock.Quantity + " . $OrderLine->QtyDispatched . " WHERE LocStock.StockID = '" . $OrderLine->StockID . "' AND LocCode = '" . $_SESSION['CreditItems']->Location . "'";

					$Result = DB_query($SQL, $db);
					if (DB_error_no($db) !=0) {
						echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Location stock record could not be updated because: -<BR>" . DB_error_msg($db);
						$SQL = "Rollback";
						$Result = DB_query($SQL,$db);

						if ($debug==1){
							echo "<BR>The following SQL to update the location stock record was used:<BR>$SQL<BR>";
						}
						exit;
					}
				} else if ($MBFlag=='A'){ /* its an assembly */
					/*Need to get the BOM for this part and make stock moves for the components
					and of course update the Location stock balances */

				    $StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
				    $sql = "SELECT BOM.Component, BOM.Quantity, StockMaster.Materialcost+StockMaster.Labourcost+StockMaster.Overheadcost AS Standard FROM BOM, StockMaster WHERE BOM.Component=StockMaster.StockID AND BOM.Parent='" . $OrderLine->StockID . "' AND BOM.EffectiveTo > '" . Date("Y-m-d") . "' AND BOM.EffectiveAfter < '" . Date("Y-m-d") . "'";
				    $AssResult = DB_query($sql,$db);
				    if (DB_error_no($db)!=0){
					echo "<BR>Could not retrieve assembly components from the database for " . $OrderLine->StockID . " because - " . DB_error_msg($db);
					if ($debug==1){
					    echo "<BR> The SQL that failed was:<BR>$sql";
					}
					exit;
				    }
				    while ($AssParts = DB_fetch_array($AssResult,$db)){
					   $StandardCost += $AssParts["Standard"];
					   /*Determine the type of stock item being credited */
					   $SQL = "SELECT MBflag FROM StockMaster WHERE StockID = '" . $AssParts['Component'] . "'";
					   $Result = DB_query($SQL,$db);
					   $MBFlagRow = DB_fetch_row($Result);
				 	   $Component_MBFlag = $MBFlagRow[0];

					   /* Insert stock movements for the stock coming back in - with unit cost */
					   if ($Component_MBFlag=="M" oR $Component_MBFlag=="B"){
			   			/*Need to get the current location quantity will need it later for the stock movement */
		 	    			$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $AssParts['Component'] . "' AND LocCode= '" . $_SESSION['CreditItems']->Location . "'";
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

			    		   if ($Component_MBFlag=="M" OR $Component_MBFlag=="B"){

					   	$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Prd, Reference, Qty, StandardCost, Show_On_Inv_Crds, NewQOH) VALUES ('" . $AssParts["Component"] . "', 11, " . $CreditNo . ", '" . $_SESSION['CreditItems']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "', " . $PeriodNo . ", 'Ex Inv:  " . $_SESSION['ProcessingCredit'] . " Assemly: " . $OrderLine->StockID . "', " . $AssParts["Quantity"] * $OrderLine->QtyDispatched . ", " . $AssParts["Standard"] . ", 0, " . ($QtyOnHandPrior + ($AssParts["Quantity"] * $OrderLine->QtyDispatched)) . ")";
					    } else {

					    	$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Prd, Reference, Qty, StandardCost, Show_On_Inv_Crds) VALUES ('" . $AssParts["Component"] . "', 11, " . $CreditNo . ", '" . $_SESSION['CreditItems']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "', " . $PeriodNo . ", 'Ex Inv:  " . $_SESSION['ProcessingCredit'] . " Assemly: " . $OrderLine->StockID . "', " . $AssParts["Quantity"] * $OrderLine->QtyDispatched . ", " . $AssParts["Standard"] . ", 0)";
					    }

					   $Result = DB_query($SQL, $db);

					   if (DB_error_no($db) !=0){
					      echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records for the assembly components of $OrderLine->StockID could not be inserted because: -<BR>" . DB_error_msg($db);
					      if ($debug==1){
						   echo "<BR>The following SQL to insert the assembly components stock movement records was used:<BR>$SQL<BR>";
					      }
					      $SQL = "Rollback";
					      $Result = DB_query($SQL,$db);
					      exit;
					   }
   					   if ($Component_MBFlag=="M" OR $Component_MBFlag=="B"){
					   	$SQL = "UPDATE LocStock SET LocStock.Quantity = LocStock.Quantity + " . $AssParts["Quantity"] * $OrderLine->QtyDispatched . " WHERE LocStock.StockID = '" . $AssParts["Component"] . "' AND LocCode = '" . $_SESSION['CreditItems']->Location . "'";
					   	$Result = DB_query($SQL, $db);
					   	if (DB_error_no($db) !=0) {
							echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Location stock record could not be updated for an assembly component because: -<BR>" . DB_error_msg($db);
							if ($debug==1){
								echo "<BR>The following SQL to update the component's location stock record was used:<BR>$SQL<BR>";
							}
							$SQL = "Rollback";
							$Result = DB_query($SQL,$db);


							exit;
					    	}
					    }
				    } /* end of assembly explosion and updates */
				    /*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
				    $_SESSION['CreditItems']->LineItems[$OrderLine->StockID]->StandardCost = $StandardCost;
				    $OrderLine->StandardCost = $StandardCost;
				}

/* Insert stock movements for the stock coming back in - with unit cost */

			    	if ($MBFlag=="M" OR $MBFlag=="B"){
					$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Price, Prd, Reference, Qty, DiscountPercent, StandardCost, NewQOH) VALUES ('" . $OrderLine->StockID . "', 11, " . $CreditNo . ", '" . $_SESSION['CreditItems']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "', " . $LocalCurrencyPrice . ", " . $PeriodNo . ", 'Ex Inv - " . $_SESSION['ProcessingCredit'] . "', " . $OrderLine->QtyDispatched . ", " . $OrderLine->DiscountPercent . ", " . $OrderLine->StandardCost . "," .  ($QtyOnHandPrior + $OrderLine->QtyDispatched) . ")";

				} else {

					$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Price, Prd, Reference, Qty, DiscountPercent, StandardCost) VALUES ('" . $OrderLine->StockID . "', 11, " . $CreditNo . ", '" . $_SESSION['CreditItems']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "', " . $LocalCurrencyPrice . ", " . $PeriodNo . ", 'Ex Inv - " . $_SESSION['ProcessingCredit'] . "', " . $OrderLine->QtyDispatched . ", " . $OrderLine->DiscountPercent . ", " . $OrderLine->StandardCost . ")";

				}

				$Result = DB_query($SQL, $db);

				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the stock movement records was used:<BR>$SQL<BR>";
					}
					$SQL = "Rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}

			}  elseif ($_POST['CreditType']=="WriteOff") {
			   /*Insert a stock movement coming back in to show the credit note and
			   a reversing stock movement to show the write off
			   no mods to location stock records*/

				$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Price, Prd, Reference, Qty, DiscountPercent, StandardCost, NewQOH) VALUES ('" . $OrderLine->StockID . "', 11, " . $CreditNo . ", '" . $_SESSION['CreditItems']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "', " . $LocalCurrencyPrice . ", " . $PeriodNo . ", 'Ex Inv - " . $_SESSION['ProcessingCredit'] . "', " . $OrderLine->QtyDispatched . ", " . $OrderLine->DiscountPercent . ", " . $OrderLine->StandardCost . ", " . ($QtyOnHandPrior +$OrderLine->QtyDispatched)  . ")";
				$Result = DB_query($SQL, $db);

				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the stock movement records was used:<BR>$SQL<BR>";
					}
					$SQL = "Rollback";
					$Result = DB_query($SQL,$db);


					exit;
				}

				$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Price, Prd, Reference, Qty, DiscountPercent, StandardCost, Show_On_Inv_Crds, NewQOH) VALUES ('" . $OrderLine->StockID . "', 11, " . $CreditNo . ", '" . $_SESSION['CreditItems']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "', " . $LocalCurrencyPrice . ", " . $PeriodNo . ", 'Written off ex Inv - " . $_SESSION['ProcessingCredit'] . "', " . -$OrderLine->QtyDispatched . ", " . $OrderLine->DiscountPercent . ", " . $OrderLine->StandardCost . ",0," . $QtyOnHandPrior . ")";
				$Result = DB_query($SQL, $db);

				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the stock movement records was used:<BR>$SQL<BR>";
					}
					$SQL = "Rollback";
					$Result = DB_query($SQL,$db);


					exit;
				}
			} elseif ($_POST['CreditType']=="ReverseOverCharge") {
			   /*Insert a stock movement coming back in to show the credit note  - flag the stockmovement not to show on stock movement enquiries - its is not a real stock movement only for invoice line - also no mods to location stock records*/
				$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, Bundle, TranDate, DebtorNo, BranchCode, Price, Prd, Reference, Qty, DiscountPercent, StandardCost, NewQOH, HideMovt) VALUES ('" . $OrderLine->StockID . "', 11, " . $CreditNo . ", '" . $_SESSION['CreditItems']->Location . "', 1, '" . $DefaultDispatchDate . "', '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "', " . $LocalCurrencyPrice . ", " . $PeriodNo . ", 'Ex Inv - " . $_SESSION['ProcessingCredit'] . "', " . $OrderLine->QtyDispatched . ", " . $OrderLine->DiscountPercent . ", " . $OrderLine->StandardCost . ", " . $QtyOnHandPrior  . ",1)";

				$Result = DB_query($SQL, $db);

				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: Stock movement records could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the stock movement records for the purpose of display on the credit note was used:<BR>$SQL<BR>";
					}
					$SQL = "Rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}
			}

/*Insert Sales Analysis records */

			$SQL="SELECT Count(*), StkCategory, SalesAnalysis.Area, Salesperson FROM SalesAnalysis, CustBranch, StockMaster WHERE SalesAnalysis.StkCategory=StockMaster.CategoryID AND SalesAnalysis.StockID=StockMaster.StockID AND SalesAnalysis.Cust=CustBranch.DebtorNo AND SalesAnalysis.CustBranch=CustBranch.BranchCode AND SalesAnalysis.Area=CustBranch.Area AND SalesAnalysis.Salesperson=CustBranch.Salesman AND TypeAbbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "' AND PeriodNo=" . $PeriodNo . " AND Cust LIKE '" . $_SESSION['CustomerID'] . "' AND CustBranch LIKE '" . $_SESSION['CreditItems']->Branch . "' AND SalesAnalysis.StockID LIKE '" . $OrderLine->StockID . "' AND BudgetOrActual=1 GROUP BY StkCategory, SalesAnalysis.Area, Salesperson";

			if (DB_error_no($db) !=0){
				echo "<BR>The count to check for existing Sales analysis records could not run because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<P>SQL to count the no of sales analysis records:<BR>$SQL";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				exit;
			}

			$Result = DB_query($SQL,$db);
			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				if ($_POST['CreditType']=="ReverseOverCharge"){

					$SQL = "UPDATE SalesAnalysis SET Amt=Amt-" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", Disc=Disc-" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . " WHERE SalesAnalysis.Area='" . $myrow[2] . "' AND SalesAnalysis.Salesperson='" . $myrow[3] . "' AND TypeAbbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "' AND PeriodNo = " . $PeriodNo . " AND Cust LIKE '" . $_SESSION['CustomerID'] . "' AND CustBranch LIKE '" . $_SESSION['CreditItems']->Branch . "' AND StockID LIKE '" . $OrderLine->StockID . "' AND SalesAnalysis.StkCategory ='" . $myrow[1] . "' AND BudgetOrActual=1";

				} else {

					$SQL = "UPDATE SalesAnalysis SET Amt=Amt-" . ($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", Cost=Cost-" . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ", Qty=Qty-" . $OrderLine->QtyDispatched . ", Disc=Disc-" . ($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . " WHERE SalesAnalysis.Area='" . $myrow[2] . "' AND SalesAnalysis.Salesperson='" . $myrow[3] . "' AND TypeAbbrev ='" . $_SESSION['CreditItems']->DefaultSalesType . "' AND PeriodNo = " . $PeriodNo . " AND Cust LIKE '" . $_SESSION['CustomerID'] . "' AND CustBranch LIKE '" . $_SESSION['CreditItems']->Branch . "' AND StockID LIKE '" . $OrderLine->StockID . "' AND SalesAnalysis.StkCategory ='" . $myrow[1] . "' AND BudgetOrActual=1";
				}

			} else { /* insert a new sales analysis record */

				if ($_POST['CreditType']=="ReverseOverCharge"){

					$SQL = "INSERT SalesAnalysis (TypeAbbrev, PeriodNo, Amt, Cust, CustBranch, Qty, Disc, StockID, Area, BudgetOrActual, Salesperson, StkCategory) SELECT '" . $_SESSION['CreditItems']->DefaultSalesType . "', " . $PeriodNo . ", " . -($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "', 0, " . -($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", '" . $OrderLine->StockID . "', CustBranch.Area, 1, CustBranch.Salesman, StockMaster.CategoryID FROM StockMaster, CustBranch WHERE StockMaster.StockID = '" . $OrderLine->StockID . "' AND CustBranch.DebtorNo = '" . $_SESSION['CustomerID'] . "' AND CustBranch.BranchCode='" . $_SESSION['CreditItems']->Branch . "'";

				} else {

					$SQL = "INSERT SalesAnalysis (TypeAbbrev, PeriodNo, Amt, Cost, Cust, CustBranch, Qty, Disc, StockID, Area, BudgetOrActual, Salesperson, StkCategory) SELECT '" . $_SESSION['CreditItems']->DefaultSalesType . "', " . $PeriodNo . ", " . -($OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", " . -($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ", '" . $_SESSION['CustomerID'] . "', '" . $_SESSION['CreditItems']->Branch . "', " . -$OrderLine->QtyDispatched . ", " . -($OrderLine->DiscountPercent * $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate']) . ", '" . $OrderLine->StockID . "', CustBranch.Area, 1, CustBranch.Salesman, StockMaster.CategoryID FROM StockMaster, CustBranch WHERE StockMaster.StockID = '" . $OrderLine->StockID . "' AND CustBranch.DebtorNo = '" . $_SESSION['CustomerID'] . "' AND CustBranch.BranchCode='" . $_SESSION['CreditItems']->Branch . "'";

				}
			}

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
					echo "<BR>The sales analysis record for this credit note could not be added because: -<BR>" . DB_error_msg($db);
				if ($debug==1){
					echo "<BR>The following SQL to insert the sales analysis record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				exit;
			}


/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/

			if ($CompanyData["GLLink_Stock"]==1 AND $OrderLine->StandardCost !=0 AND $_POST['CreditType']!="ReverseOverCharge"){

/*first the cost of sales entry*/

				$COGSAccount = GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['CreditItems']->DefaultSalesType, $db);

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (11, " . $CreditNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $COGSAccount . ", '" . $_SESSION['CustomerID'] . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "', " . -($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ")";
				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The cost of sales GL posting could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}

/*now the stock entry*/


				if ($_POST['CreditType']=="WriteOff"){
					$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (11, " . $CreditNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $_POST['WriteOffGLCode'] . ", '" . $_SESSION['CustomerID'] . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "', " . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ")";
				} else {
					$StockGLCode = GetStockGLCode($OrderLine->StockID, $db);
					$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (11, " . $CreditNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $StockGLCode["StockAct"] . ", '" . $_SESSION['CustomerID'] . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "', " . ($OrderLine->StandardCost * $OrderLine->QtyDispatched) . ")";
				}
				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock side (or write off) of the cost of sales GL posting could not be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}


			} /* end of if GL and stock integrated and standard cost !=0 */

			if ($CompanyData["GLLink_Debtors"]==1 && $OrderLine->Price !=0){

//Post sales transaction to GL credit sales
				$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['CreditItems']->DefaultSalesType, $db);

				$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (11, " . $CreditNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $SalesGLAccounts["SalesGLCode"] . ", '" . $_SESSION['CustomerID'] . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . "', " . ($OrderLine->Price * $OrderLine->QtyDispatched) . ")";

				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The credit note GL posting could not be inserted because: -<BR>" . DB_error_msg($db);

					if ($debug==1){
						echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
					}
					$SQL = "rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}

				if ($OrderLine->DiscountPercent !=0){

					$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (11, " . $CreditNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $SalesGLAccounts["DiscountGLCode"] . ", '" . $_SESSION['CustomerID'] . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%', " . -($OrderLine->Price * $OrderLine->QtyDispatched * $OrderLine->DiscountPercent) . ")";
					$Result = DB_query($SQL,$db);
					if (DB_error_no($db) !=0){
						echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The credit note discount GL posting could not be inserted because: -<BR>" . DB_error_msg($db);

						if ($debug==1){
							echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
						}
						$SQL = "Rollback";
						$Result = DB_query($SQL,$db);
						exit;
					}
				} /*end of if discount !=0 */
			} /*end of if sales integrated with debtors */
		} /*Quantity dispatched is more than 0 */
	} /*end of OrderLine loop */


	if ($CompanyData["GLLink_Debtors"]==1){

/*Post credit note transaction to GL credit debtors, debit freight re-charged and debit sales */
		if (($_SESSION['CreditItems']->total + $_POST['ChargeFreightCost'] + $TaxTotal) !=0) {
			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (11, " . $CreditNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $CompanyData["DebtorsAct"] . ", '" . $_SESSION['CustomerID'] . "', " . -($_SESSION['CreditItems']->total + $_POST['ChargeFreightCost'] + $TaxTotal) . ")";

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The total debtor GL posting for the credit note could not be inserted because: -<BR>" . DB_error_msg($db);

				if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				exit;
			}
		}
		if ($_POST['ChargeFreightCost'] !=0) {
			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (11, " . $CreditNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $CompanyData["FreightAct"] . ", '" . $_SESSION['CustomerID'] . "', " . $_POST['ChargeFreightCost'] . ")";

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The freight GL posting for this credit note could not be inserted because: -<BR>" . DB_error_msg($db);

				if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL<BR>";
				}
				$SQL = "Rollback";
				$Result = DB_query($SQL,$db);
				exit;
			}
		}
		if ($TaxTotal !=0){
			$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount) VALUES (11, " . $CreditNo . ", '" . $DefaultDispatchDate . "', " . $PeriodNo . ", " . $_SESSION['TaxGLCode'] . ", '" . $_SESSION['CustomerID'] . "', " . $TaxTotal . ")";

			$Result = DB_query($SQL,$db);
			if (DB_error_no($db) !=0){
				echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The tax GL posting for this credit note could not be inserted because: -<BR>" . DB_error_msg($db);

				if ($debug==1){
					echo "<BR>The following SQL to insert the GLTrans record was used:<BR>$SQL";
				}
				$SQL = "rollback";
				$Result = DB_query($SQL,$db);
				exit;
			}
		}
	} /*end of if Sales and GL integrated */

	$SQL="Commit";
	$Result = DB_query($SQL,$db);

	unset($_SESSION['CreditItems']->LineItems);
	unset($_SESSION['CreditItems']);
	unset($_SESSION['ProcessingCredit']);

	echo "Credit Note number $CreditNo processed<BR>";
	echo "<A HREF='". $rootpath . "/PrintCustTrans.php?" . SID . "FromTransNo=" . $CreditNo . "&InvOrCredit=Credit&PrintPDF=True'>Print this Credit Note</A>";

/*end of process credit note */


} else { /*Process Credit NOT set so allow inputs to set up the credit note */

	echo "<TABLE>";

	echo "<TR><TD>Credit Note Type</TD><TD><SELECT NAME=CreditType>";
	if (!isset($_POST['CreditType']) OR $_POST['CreditType']=="Return"){
		echo "<OPTION SELECTED VALUE='Return'>Goods returned to store";
		echo "<OPTION VALUE='WriteOff'>Goods written off";
		echo "<OPTION VALUE='ReverseOverCharge'>Reverse Over-Charge";
	} elseif($_POST['CreditType']=="WriteOff") {
		echo "<OPTION SELECTED VALUE='WriteOff'>Goods written off";
		echo "<OPTION VALUE='Return'>Goods returned to store";
		echo "<OPTION VALUE='ReverseOverCharge'>Reverse Over-Charge";
	} else {
		echo "<OPTION VALUE='WriteOff'>Goods written off";
		echo "<OPTION VALUE='Return'>Goods returned to store";
		echo "<OPTION SELECTED VALUE='ReverseOverCharge'>Reverse Over-Charge";
	}
	echo "</SELECT></TD></TR>";


	if (!isset($_POST['CreditType']) OR $_POST['CreditType']=="Return"){

/*if the credit note is a return of goods then need to know which location to receive them into */

		echo "<TR><TD>Goods Returned to Location</TD><TD><SELECT NAME=Location>";

		$SQL="SELECT LocCode, LocationName FROM Locations";
		$Result = DB_query($SQL,$db);

		if (!isset($_POST['Location'])){
			$_POST['Location'] = $_SESSION['CreditItems']->Location;
		}
		while ($myrow = DB_fetch_array($Result)) {

			if ($_POST['Location']==$myrow["LocCode"]){
				echo "<OPTION SELECTED VALUE='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
			} else {
				echo "<OPTION VALUE='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
			}
		}
		echo "</SELECT></TD></TR>";

	} elseif($_POST['CreditType']=='WriteOff') { /* the goods are to be written off to somewhere */

		echo "<TR><TD>Write off the cost of the goods to</TD><TD><SELECT NAME=WriteOffGLCode>";

		$SQL="SELECT AccountCode, AccountName FROM ChartMaster, AccountGroups WHERE ChartMaster.Group_=AccountGroups.GroupName AND AccountGroups.PandL=1 ORDER BY AccountCode";
		$Result = DB_query($SQL,$db);

		while ($myrow = DB_fetch_array($Result)) {

			if ($_POST['WriteOffGLCode']==$myrow["AccountCode"]){
				echo "<OPTION SELECTED VALUE=" . $myrow["AccountCode"] . ">" . $myrow["AccountName"];
			} else {
				echo "<OPTION VALUE=" . $myrow["AccountCode"] . ">" . $myrow["AccountName"];
			}
		}
		echo "</SELECT></TD></TR>";
	}
	echo "<TR><TD>Credit Note Text</TD><TD><TEXTAREA NAME=CreditText COLS=31 ROWS=5>" . $_POST['CreditText'] . "</TEXTAREA></TD></TR>";
	echo "</TABLE><CENTER><INPUT TYPE=SUBMIT NAME=Update Value=Update><P>";
	echo "<INPUT TYPE=SUBMIT NAME='ProcessCredit' Value='Process Credit'></CENTER>";

}

echo "</FORM>";
include("includes/footer.inc");
?>

