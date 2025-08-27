<?php

require(__DIR__ . '/includes/session.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}

/* Check that the config variable is set for
 * picking notes and get out if not.
 */
if ($_SESSION['RequirePickingNote']==0) {
	$Title = __('Picking Lists Not Enabled');
	include('includes/header.php');
	echo '<br />';
	prnMsg( __('The system is not configured for picking lists. A configuration parameter is required where picking slips are required. Please consult your system administrator.'), 'info');
	include('includes/footer.php');
	exit();
}

/* Show selection screen if we have no orders to work with */
if ((!isset($_GET['TransNo']) or $_GET['TransNo']=='') and !isset($_POST['TransDate'])){
	$Title = __('Select Picking Lists');
	$ViewTopic = 'Sales';
	$BookMark = '';
	include('includes/header.php');
	$SQL="SELECT locations.loccode,
				locationname
			FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$Result = DB_query($SQL);
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/sales.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" name="form">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
			<legend>', __('Selection Criteria'), '</legend>
		<field>
			<label for="TransDate">' . __('Create picking lists for all deliveries to be made on').' : ' . '</label>
			<input required="required" autofocus="autofocus" type="date" name="TransDate" maxlength="10" size="11" value="' . date('Y-m-d', mktime(date('m'),date('Y'),date('d')+1)) . '" />
		</field>
		<field>
			<label for="loccode">' . __('From Warehouse').' : ' . '</label>
			<select required="required" name="loccode">';
	while ($MyRow=DB_fetch_array($Result)) {
		echo '<option value="'.$MyRow['loccode'].'">' . $MyRow['locationname'] . '</option>';
	}
	echo '</select>
		</field>
		</fieldset>';
	echo '<div class="centre">
			<input type="submit" name="Process" value="' . __('Print Picking Lists') . '" />
		</div>
		</form>';
	include('includes/footer.php');
	exit();
}

/*retrieve the order details from the database to print */
$ErrMsg = __('There was a problem retrieving the order header details from the database');

if (!isset($_POST['TransDate']) AND $_GET['TransNo'] != 'Preview') {
/* If there is no transaction date set, then it must be for a single order */
	$SQL = "SELECT salesorders.debtorno,
        		salesorders.orderno,
        		salesorders.customerref,
        		salesorders.comments,
        		salesorders.orddate,
        		salesorders.deliverto,
        		salesorders.deladd1,
        		salesorders.deladd2,
        		salesorders.deladd3,
        		salesorders.deladd4,
        		salesorders.deladd5,
        		salesorders.deladd6,
        		salesorders.deliverblind,
        		salesorders.deliverydate,
        		debtorsmaster.name,
        		debtorsmaster.address1,
        		debtorsmaster.address2,
        		debtorsmaster.address3,
        		debtorsmaster.address4,
        		debtorsmaster.address5,
        		debtorsmaster.address6,
        		shippers.shippername,
        		salesorders.printedpackingslip,
        		salesorders.datepackingslipprinted,
        		locations.locationname
        	FROM salesorders,
        		debtorsmaster,
        		shippers,
        		locations
        	WHERE salesorders.debtorno=debtorsmaster.debtorno
        	AND salesorders.shipvia=shippers.shipper_id
        	AND salesorders.fromstkloc=locations.loccode
        	AND salesorders.orderno='" . $_GET['TransNo']."'";
} else if (isset($_POST['TransDate'])
		OR (isset($_GET['TransNo']) AND $_GET['TransNo'] != 'Preview')) {
/* We are printing picking lists for all orders on a day */
	$SQL = "SELECT salesorders.debtorno,
            		salesorders.orderno,
            		salesorders.customerref,
            		salesorders.comments,
            		salesorders.orddate,
            		salesorders.deliverto,
            		salesorders.deladd1,
            		salesorders.deladd2,
            		salesorders.deladd3,
            		salesorders.deladd4,
            		salesorders.deladd5,
            		salesorders.deladd6,
            		salesorders.deliverblind,
            		salesorders.deliverydate,
            		debtorsmaster.name,
            		debtorsmaster.address1,
            		debtorsmaster.address2,
            		debtorsmaster.address3,
            		debtorsmaster.address4,
            		debtorsmaster.address5,
            		debtorsmaster.address6,
            		shippers.shippername,
            		salesorders.printedpackingslip,
            		salesorders.datepackingslipprinted,
            		locations.locationname
            	FROM salesorders,
            		debtorsmaster,
            		shippers,
            		locations
            	WHERE salesorders.debtorno=debtorsmaster.debtorno
            	AND salesorders.shipvia=shippers.shipper_id
            	AND salesorders.fromstkloc=locations.loccode
            	AND salesorders.fromstkloc='".$_POST['loccode']."'
            	AND salesorders.deliverydate<='" . FormatDateForSQL($_POST['TransDate'])."'";
}

if ($_SESSION['SalesmanLogin'] != '') {
	$SQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
}

if (isset($_POST['TransDate'])
	OR (isset($_GET['TransNo']) AND $_GET['TransNo'] != 'Preview')) {
	$Result = DB_query($SQL, $ErrMsg);

	/*if there are no rows, there's a problem. */
	if (DB_num_rows($Result)==0){
		$Title = __('Print Picking List Error');
		include('includes/header.php');
		echo '<br />';
		prnMsg( __('Unable to Locate any orders for this criteria '), 'info');
		echo '<br />
				<table class="selection">
				<tr>
					<td><a href="'. $RootPath . '/PDFPickingList.php">' . __('Enter Another Date') . '</a></td>
				</tr>
				</table>
				<br />';
		include('includes/footer.php');
		exit();
	}

	/*retrieve the order details from the database and place them in an array */
	$i=0;
	while ($MyRow=DB_fetch_array($Result)) {
		$OrdersToPick[$i]=$MyRow;
		$i++;
	}
} else {
	$OrdersToPick[0]['debtorno']=str_pad('',10,'x');
	$OrdersToPick[0]['orderno']='Preview';
	$OrdersToPick[0]['customerref']=str_pad('',20,'x');
	$OrdersToPick[0]['comments']=str_pad('',100,'x');
	$OrdersToPick[0]['orddate']='1000-01-01';
	$OrdersToPick[0]['deliverto']=str_pad('',20,'x');
	$OrdersToPick[0]['deladd1']=str_pad('',20,'x');
	$OrdersToPick[0]['deladd2']=str_pad('',20,'x');
	$OrdersToPick[0]['deladd3']=str_pad('',20,'x');
	$OrdersToPick[0]['deladd4']=str_pad('',20,'x');
	$OrdersToPick[0]['deladd5']=str_pad('',20,'x');
	$OrdersToPick[0]['deladd6']=str_pad('',20,'x');
	$OrdersToPick[0]['deliverblind']=str_pad('',20,'x');
	$OrdersToPick[0]['deliverydate']='1000-01-01';
	$OrdersToPick[0]['name']=str_pad('',20,'x');
	$OrdersToPick[0]['address1']=str_pad('',20,'x');
	$OrdersToPick[0]['address2']=str_pad('',20,'x');
	$OrdersToPick[0]['address3']=str_pad('',20,'x');
	$OrdersToPick[0]['address4']=str_pad('',20,'x');
	$OrdersToPick[0]['address5']=str_pad('',20,'x');
	$OrdersToPick[0]['address6']=str_pad('',20,'x');
	$OrdersToPick[0]['shippername']=str_pad('',20,'x');
	$OrdersToPick[0]['printedpackingslip']=str_pad('',20,'x');
	$OrdersToPick[0]['datepackingslipprinted']='1000-01-01';
	$OrdersToPick[0]['locationname']=str_pad('',15,'x');
}
/* Then there's an order to print and its not been printed already (or its been flagged for reprinting/ge_Width=807;
)
LETS GO */

if ($OrdersToPick[0]['orderno']=='Preview') {
	$FormDesign = simplexml_load_file(sys_get_temp_dir().'/PickingList.xml');
} else {
	$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/PickingList.xml');
}

$PaperSize = $FormDesign->PaperSize;
include('includes/PDFStarter.php');
$pdf->addInfo('Title', __('Picking List') );
$pdf->addInfo('Subject', __('Laser Picking List') );
$FontSize=12;
$ListCount = 0;
$Copy='';

$LineHeight=$FormDesign->LineHeight;

for ($i=0;$i<sizeof($OrdersToPick);$i++){
/*Cycle through each of the orders to pick */
	if ($i>0) {
		$pdf->newPage();
	}

	/* Now ... Has the order got any line items still outstanding to be picked */

	$PageNumber = 1;

	if (isset($_POST['TransDate']) or (isset($_GET['TransNo']) and $_GET['TransNo'] != 'Preview')) {
		$ErrMsg = __('There was a problem retrieving the order line details for Order Number') . ' ' .
			$OrdersToPick[$i]['orderno'] . ' ' . __('from the database');

		/* Are there any picking lists for this order already */
		$SQL="SELECT COUNT(orderno)
				FROM pickinglists
				WHERE orderno='" . $OrdersToPick[$i]['orderno'] . "'";
		$CountResult = DB_query($SQL);
		$Count=DB_fetch_row($CountResult);
		if ($Count[0]==0) {
		/* There are no previous picking lists for this order */
			$SQL = "SELECT salesorderdetails.stkcode,
            				stockmaster.description,
            				salesorderdetails.orderlineno,
            				salesorderdetails.quantity,
            				salesorderdetails.qtyinvoiced,
            				salesorderdetails.unitprice,
            				salesorderdetails.narrative,
            				stockmaster.decimalplaces
            			FROM salesorderdetails
            			INNER JOIN stockmaster
            				ON salesorderdetails.stkcode=stockmaster.stockid
            			WHERE salesorderdetails.orderno='" . $OrdersToPick[$i]['orderno'] ."'";
		} else {
		/* There are previous picking lists for this order so
		 * need to take those quantities into account
		 */
			$SQL = "SELECT salesorderdetails.stkcode,
            				stockmaster.description,
            				salesorderdetails.orderlineno,
            				salesorderdetails.quantity,
            				salesorderdetails.qtyinvoiced,
            				SUM(pickinglistdetails.qtyexpected) as qtyexpected,
            				SUM(pickinglistdetails.qtypicked) as qtypicked,
            				salesorderdetails.unitprice,
            				salesorderdetails.narrative,
            				stockmaster.decimalplaces
            			FROM salesorderdetails
            			INNER JOIN stockmaster
            				ON salesorderdetails.stkcode=stockmaster.stockid
            			LEFT JOIN pickinglists
            				ON salesorderdetails.orderno=pickinglists.orderno
            			LEFT JOIN pickinglistdetails
            				ON pickinglists.pickinglistno=pickinglistdetails.pickinglistno
            			WHERE salesorderdetails.orderno='" . $OrdersToPick[$i]['orderno'] ."'
            			AND salesorderdetails.orderlineno=pickinglistdetails.orderlineno";
		}
		$LineResult = DB_query($SQL, $ErrMsg);
	}
	if ((isset($_GET['TransNo'])
		AND $_GET['TransNo'] == 'Preview')
		OR (isset($LineResult)
		AND DB_num_rows($LineResult)>0)){
		/*Yes there are line items to start the ball rolling with a page header */
		include('includes/PDFPickingListHeader.php');
		if (isset($_POST['TransDate']) or (isset($_GET['TransNo']) and $_GET['TransNo'] != 'Preview')) {
			$LinesToShow=DB_num_rows($LineResult);
			$PickingListNo = GetNextTransNo(19);
			$SQL="INSERT INTO pickinglists
				VALUES (
				'" . $PickingListNo ."',
				'" . $OrdersToPick[$i]['orderno']."',
				'" . FormatDateForSQL($_POST['TransDate'])."',
				CURRENT_DATE,
				'1000-01-01')";
			$HeaderResult = DB_query($SQL);
		} else {
			$LinesToShow=1;
		}
		$YPos=$FormDesign->Data->y;
		$Lines=0;

		while ($Lines<$LinesToShow){
			if (isset($_GET['TransNo']) and $_GET['TransNo'] == 'Preview') {
				$MyRow2['stkcode']=str_pad('',10,'x');
				$MyRow2['decimalplaces']=2;
				$DisplayQty='XXXX.XX';
				$DisplayPrevDel='XXXX.XX';
				$DisplayQtySupplied='XXXX.XX';
				$MyRow2['description']=str_pad('',18,'x');
				$MyRow2['narrative']=str_pad('',18,'x');
				$ItemDesc = $MyRow2['description'] . ' - ' . $MyRow2['narrative'];
			} else {
				$MyRow2=DB_fetch_array($LineResult);
				if ($Count[0]==0) {
					$MyRow2['qtyexpected']=0;
					$MyRow2['qtypicked']=0;
				}
				$DisplayQty = locale_number_format($MyRow2['quantity'],$MyRow2['decimalplaces']);
				$DisplayPrevDel = locale_number_format($MyRow2['qtyinvoiced'],$MyRow2['decimalplaces']);
				$DisplayQtySupplied = locale_number_format($MyRow2['quantity'] - $MyRow2['qtyinvoiced']-$MyRow2['qtyexpected']-$MyRow2['qtypicked'],$MyRow2['decimalplaces']);
				$ItemDesc = $MyRow2['description'] . ' - ' . $MyRow2['narrative'];
				$SQL="INSERT INTO pickinglistdetails
					VALUES(
					'" . $PickingListNo ."',
					'" . $Lines."',
					'" . $MyRow2['orderlineno']."',
					'" . $DisplayQtySupplied ."',
					0)";
					$LineResult = DB_query($SQL);
			}
			$ListCount ++;

			$pdf->addTextWrap($FormDesign->Headings->Column1->x,$Page_Height - $YPos,$FormDesign->Headings->Column1->Length,$FormDesign->Headings->Column1->FontSize,$MyRow2['stkcode'],'left');
			$pdf->addTextWrap($FormDesign->Headings->Column2->x,$Page_Height - $YPos,$FormDesign->Headings->Column2->Length,$FormDesign->Headings->Column2->FontSize,$ItemDesc);
			$pdf->addTextWrap($FormDesign->Headings->Column3->x,$Page_Height - $YPos,$FormDesign->Headings->Column3->Length,$FormDesign->Headings->Column3->FontSize,$DisplayQty,'right');
			$pdf->addTextWrap($FormDesign->Headings->Column4->x,$Page_Height - $YPos,$FormDesign->Headings->Column4->Length,$FormDesign->Headings->Column4->FontSize,$DisplayQtySupplied,'right');
			$pdf->addTextWrap($FormDesign->Headings->Column5->x,$Page_Height - $YPos,$FormDesign->Headings->Column5->Length,$FormDesign->Headings->Column5->FontSize,$DisplayPrevDel,'right');

			if ($Page_Height-$YPos-$LineHeight <= 50){
			/* We reached the end of the page so finish off the page and start a new */
				$PageNumber++;
				include('includes/PDFPickingListHeader.php');
			} //end if need a new page headed up
			else{
				/*increment a line down for the next line item */
				$YPos += ($LineHeight);
			}
			$Lines++;
		} //end while there are line items to print out

	} /*end if there are order details to show on the order*/
} /*end for loop to print the whole lot twice */

if ($ListCount == 0){
	$Title = __('Print Picking List Error');
	include('includes/header.php');
	include('includes/footer.php');
	exit();
} else {
	$pdf->OutputD($_SESSION['DatabaseName'] . '_PickingLists_' . date('Y-m-d') . '.pdf');
	$pdf->__destruct();
}
