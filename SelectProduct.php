<?php
/* $Revision: 1.24 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Search Inventory Items');

include('includes/header.inc');

include('includes/Wiki.php');

$msg='';

if (isset($_GET['StockID'])){  //The page is called with a StockID
	$_GET['StockID'] = trim(strtoupper($_GET['StockID']));
	$_POST['Select'] = trim(strtoupper($_GET['StockID']));
}

if (isset($_GET['NewSearch'])){
	unset($StockID);
	unset($_SESSION['SelectedStockItem']);
	unset($_POST['Select']);
}

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset']==0) {
		$_POST['PageOffset'] = 1;
	}
}

if( isset($_POST['StockCode']) ) {
    $_POST['StockCode'] = trim(strtoupper($_POST['StockCode']));
}

// Always show the search facilities

$SQL='SELECT categoryid,
		categorydescription
	FROM stockcategory
	ORDER BY categorydescription';

$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1)==0){
	echo '<P><FONT SIZE=4 COLOR=RED>' . _('Problem Report') . ':</FONT><BR>' . _('There are no stock categories currently defined please use the link below to set them up');
	echo '<BR><A HREF="' . $rootpath . '/StockCategories.php?' . SID .'">' . _('Define Stock Categories') . '</A>';
	exit;
}

?>
<CENTER>
<FORM ACTION="<?php echo $_SERVER['PHP_SELF'] . '?' . SID; ?>" METHOD=POST>
<B><?php echo $msg; ?></B>
<TABLE>
<TR>
<TD><?php echo _('In Stock Category'); ?>:
<SELECT NAME="StockCat">
<?php
	if (!isset($_POST['StockCat'])){
		$_POST['StockCat']="";
	}
	if ($_POST['StockCat']=="All"){
		echo '<OPTION SELECTED VALUE="All">' . _('All');
	} else {
		echo '<OPTION VALUE="All">' . _('All');
	}
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['categoryid']==$_POST['StockCat']){
			echo '<OPTION SELECTED VALUE="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
		} else {
			echo '<OPTION VALUE="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
		}
	}
?>

</SELECT>
<TD><?php echo _('Text in the'); ?> <B><?php echo _('description'); ?></B>:</TD>
<TD>
<?php
if (isset($_POST['Keywords'])) {
?>
<INPUT TYPE="Text" NAME="Keywords" value="<?php echo $_POST['Keywords']?>" SIZE=20 MAXLENGTH=25>
<?php
} else {
?>
<INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25>
<?php
}
?>
</TD>
</TR>
<TR><TD></TD>
<TD><FONT SIZE 3><B><?php echo _('OR'); ?> </B></FONT><?php echo _('Text in the'); ?> <B><?php echo _('Stock Code'); ?></B>:</TD>
<TD>
<?php
if (isset($_POST['StockCode'])) {
?>
<INPUT TYPE="Text" NAME="StockCode" value="<?php echo $_POST['StockCode']?>" SIZE=15 MAXLENGTH=18>
<?php
} else {
?>
<INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18>
<?php
}
?>
</TD>
</TR>
</TABLE>
<INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>"></CENTER>
<HR>


<?php

// end of showing search facilities

// query for list of record(s)

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){

	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])){
		// if Search then set to first page
    $_POST['PageOffset'] = 1;
	}

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

		if ($_POST['StockCat'] == 'All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.mbflag
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.description " . LIKE . " '$SearchString'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.mbflag
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND description " .  LIKE . " '$SearchString'
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		}
	} elseif (isset($_POST['StockCode'])){

		$_POST['StockCode'] = strtoupper($_POST['StockCode']);
		if ($_POST['StockCat'] == 'All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";

		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					sum(locstock.quantity) as qoh,
					stockmaster.units
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		}

	} elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['StockCat'] == 'All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		}
	}

	$ErrMsg = _('No stock items were returned by the SQL because');
	$Dbgmsg = _('The SQL that returned an error was');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($result)==0){
		prnMsg(_('No stock items were returned by this search please re-enter alternative criteria to try again'),'info');
	} elseif (DB_num_rows($result)==1){ /*autoselect it to avoid user hitting another keystroke */
		$myrow = DB_fetch_row($result);
		$_POST['Select'] = $myrow[0];
	}
	unset($_POST['Search']);
}

// end query for list of records

// display list if there is more than one record

if (isset($result) AND !isset($_POST['Select'])) {

	$ListCount = DB_num_rows($result);
	if ($ListCount > 0) {
	// If the user hit the search button and there is more than one item to show

		$ListPageMax=ceil($ListCount/$_SESSION['DisplayRecordsMax']);

		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}

		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}

		if ($_POST['PageOffset']>$ListPageMax){
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax >1) {
			echo "<P>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';

			echo '<SELECT NAME="PageOffset">';

			$ListPage=1;
			while($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<OPTION VALUE=' . $ListPage . ' SELECTED>' . $ListPage . '</OPTION>';
				} else {
					echo '<OPTION VALUE=' . $ListPage . '>' . $ListPage . '</OPTION>';
				}
				$ListPage++;
			}
			echo '</SELECT>
				<INPUT TYPE=SUBMIT NAME="Go" VALUE="' . _('Go') . '">
				<INPUT TYPE=SUBMIT NAME="Previous" VALUE="' . _('Previous') . '">
				<INPUT TYPE=SUBMIT NAME="Next" VALUE="' . _('Next') . '">';
			echo '<P>';
		}

		echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>';
		$tableheader = '<TR>
					<TD class="tableheader">' . _('Code') . '</TD>
					<TD class="tableheader">' . _('Description') . '</TD>
					<TD class="tableheader">' . _('Total Qty On Hand') . '</TD>
					<TD class="tableheader">' . _('Units') . '</TD>
				</TR>';
		echo $tableheader;

		$j = 1;

		$k = 0; //row counter to determine background colour

	$RowIndex = 0;

	if (DB_num_rows($result)<>0){
		DB_data_seek($result, ($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']);
	}

		while (($myrow=DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

			if ($k==1){
				echo '<tr bgcolor="#CCCCCC">';
				$k=0;
			} else {
				echo '<tr bgcolor="#EEEEEE">';
				$k++;
			}

			if ($myrow['mbflag']=='D') {
				$qoh = 'N/A';
			} else {
				$qoh = number_format($myrow["qoh"],1);
			}

			printf("<td><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</td>
				<td>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<td>%s</td>
				</tr>",
				$myrow['stockid'],
				$myrow['description'],
				$qoh,
				$myrow['units']);

			$j++;
			If ($j == 20 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])){
				$j=1;
				echo $tableheader;

			}
	$RowIndex = $RowIndex + 1;
	//end of page full new headings if
		}
	//end of while loop

		echo '</TABLE>';
		if ($ListPageMax >1) {
			echo "<P>&nbsp;&nbsp;" . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';

			echo '<SELECT NAME="PageOffset">';

			$ListPage=1;
			while($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<OPTION VALUE=' . $ListPage . ' SELECTED>' . $ListPage . '</OPTION>';
				} else {
					echo '<OPTION VALUE=' . $ListPage . '>' . $ListPage . '</OPTION>';
				}
				$ListPage++;
			}
			echo '</SELECT>
				<INPUT TYPE=SUBMIT NAME="Go" VALUE="' . _('Go') . '">
				<INPUT TYPE=SUBMIT NAME="Previous" VALUE="' . _('Previous') . '">
				<INPUT TYPE=SUBMIT NAME="Next" VALUE="' . _('Next') . '">';
			echo '<P>';
		}
	}
}
// end display list if there is more than one record

// displays item options if there is one and only one selected

If (!isset($_POST['Search']) AND (isset($_POST['Select']) OR isset($_SESSION['SelectedStockItem']))) {

	if (isset($_POST['Select'])){
		$_SESSION['SelectedStockItem']= $_POST['Select'];
		$StockID = $_POST['Select'];
		unset($_POST['Select']);
	} else {
		$StockID = $_SESSION['SelectedStockItem'];
	}

	$result = DB_query("SELECT stockmaster.description,
							stockmaster.mbflag,
							stockmaster.units,
							stockmaster.decimalplaces,
							stockmaster.controlled,
							stockmaster.serialised,
							stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS cost,
							stockmaster.discontinued,
							stockmaster.eoq,
							stockmaster.volume,
							stockmaster.kgs
							FROM stockmaster WHERE stockid='" . $StockID . "'",$db);
	$myrow = DB_fetch_array($result);

	$Its_A_Kitset_Assembly_Or_Dummy=False;
	$Its_A_Dummy=False;
	$Its_A_Kitset=False;

	echo '<TABLE BORDER=1><TR><TD colspan=3 class="tableheader"><font size=4>' . $StockID . ' - ' . $myrow['description'] . ' </font></TD></TR>';

	echo '<TR><TD WIDTH="40%">
			<TABLE>'; //nested table

	echo '<TR><TD align=right class="tableheader">' . _('Item type:') . '</TD><TD>';

	switch ($myrow['mbflag']) {
		case 'A':
			echo _('Assembly Item');
			$Its_A_Kitset_Assembly_Or_Dummy=True;
			break;
		case 'K':
			echo _('Kitset Item');
			$Its_A_Kitset_Assembly_Or_Dummy=True;
			$Its_A_Kitset=True;
			break;
		case 'D':
			echo _('Service Item');
			$Its_A_Kitset_Assembly_Or_Dummy=True;
			$Its_A_Dummy=True;
			break;
		case 'B':
			echo _('Purchased Item');
			break;
		default:
			echo _('Manufactured Item');
			break;
	}
	echo '</TD><TD align=right class="tableheader">' . _('Control Level:') .'</TD><TD>';
	if ($myrow['serialised']==1){
		echo _('serialised');
	} elseif ($myrow['controlled']==1){
		echo _('Batchs/Lots');
	} else {
		echo _('N/A');
	}
	echo '</TD><TD align=right class="tableheader">' . _('Units') . ':</TD><TD>' . $myrow['units'] . '</TD></TR>';
	echo '<TR><TD align=right class="tableheader">' . _('Volume') . ':</TD><TD align=right>' . number_format($myrow['volume'],3) . '</TD>
			<TD align=right class="tableheader">' . _('Weight') . ':</TD><TD align=right>' . number_format($myrow['kgs'],3) . '</TD>
			<TD align=right class="tableheader">' . _('EOQ') . ':</TD><TD align=right>' . number_format($myrow['eoq'],$myrow['decimalplaces']) . '</TD></TR>';

	echo '<TR><TD class="tableheader">' . _('Sell Price') . ':</TD><TD align=right>';

	$PriceResult = DB_query("SELECT price FROM prices
								WHERE currabrev ='" . $_SESSION['CompanyRecord']['currencydefault'] . "'
								AND typeabbrev = '" . $_SESSION['DefaultPriceList'] . "'
								AND debtorno=''
								AND branchcode=''
								AND stockid='".$StockID."'",
								$db);
	if (DB_num_rows($PriceResult)==0){
			echo _('No Price Set');
			$Price =0;
	} else {
			$PriceRow = DB_fetch_row($PriceResult);
			$Price = $PriceRow[0];
			echo number_format($Price,2);
	}
	if ($myrow['mbflag']=='K' OR $myrow['mbflag']=='A'){
		$CostResult = DB_query("SELECT SUM(bom.quantity*
						(stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost)) AS cost
					FROM bom INNER JOIN
						stockmaster
					ON bom.component=stockmaster.stockid
					WHERE bom.parent='" . $StockID . "'
					AND bom.effectiveto > '" . Date("Y-m-d") . "'
					AND bom.effectiveafter < '" . Date("Y-m-d") . "'",
					$db);
		$CostRow = DB_fetch_row($CostResult);
		$Cost = $CostRow[0];
	} else {
		$Cost = $myrow['cost'];
	}
	echo '<TD align=right class="tableheader">' . _('Cost') . '</TD><TD align=right>' . number_format($Cost,3) . '</TD>
			<TD align=right class="tableheader">' . _('Gross Profit') . '</TD><TD align=right>';

	if ($Price >0) {
		$GP = number_format(($Price - $Cost)*100/$Price,2);
	} else {
		$GP=_('N/A');
	}
	echo $GP.'%'. '</TD></TR>';

	echo '</TD></TR>
		</TABLE>'; //end of first nested table

	echo '<TD WIDTH="15%">
			<TABLE>'; //nested table to show QOH/orders


	$QOH=0;
	switch ($myrow['mbflag']) {
		case 'A':
 		case 'D':
		case 'K':
			$QOH=_('N/A');
			$QOO =_('N/A');
			break;
		case 'M':
		case 'B':
			$QOHResult = DB_query("SELECT sum(quantity)
                            			FROM locstock
                        				WHERE stockid = '" . $StockID . "'",
                        				$db);
            $QOHRow = DB_fetch_row($QOHResult);
            $QOH = number_format($QOHRow[0],$myrow['decimalplaces']);

            $QOOResult = DB_query("SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd)
                   					FROM purchorderdetails
                   					WHERE purchorderdetails.itemcode='" . $StockID . "'",
                   					$db);
			if (DB_num_rows($QOOResult)==0){
				$QOO=0;
			} else {
				$QOORow = DB_fetch_row($QOOResult);
				$QOO = $QOORow[0];
			}
			//Also the on work order quantities
			$sql = "SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
				FROM woitems INNER JOIN workorders
				ON woitems.wo=workorders.wo
				WHERE workorders.closed=0
				AND woitems.stockid='" . $StockID . "'";
			$ErrMsg = _('The quantity on work orders for this product cannot be retrieved because');
			$QOOResult = DB_query($sql,$db,$ErrMsg);

			if (DB_num_rows($QOOResult)==1){
				$QOORow = DB_fetch_row($QOOResult);
				$QOO +=  $QOORow[0];
			}
			$QOO = number_format($QOO,$myrow['decimalplaces']);
			break;
	}
	$Demand =0;
	$DemResult = DB_query("SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
                 					FROM salesorderdetails INNER JOIN salesorders
                 					ON salesorders.orderno = salesorderdetails.orderno
                 					WHERE salesorderdetails.completed=0
		 							AND salesorders.quotation=0
                 					AND salesorderdetails.stkcode='" . $StockID . "'",
                 			$db);

    $DemRow = DB_fetch_row($DemResult);
    $Demand = $DemRow[0];
	$DemAsComponentResult =	DB_query("SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
                 						FROM salesorderdetails,
	                  						salesorders,
    						                bom,
                      						stockmaster
                 						WHERE salesorderdetails.stkcode=bom.parent AND
                       						salesorders.orderno = salesorderdetails.orderno AND
                                            salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0 AND
                       						bom.component='" . $StockID . "' AND stockmaster.stockid=bom.parent AND
                       						stockmaster.mbflag='A'
		       								AND salesorders.quotation=0",
		       							$db);
	$DemAsComponentRow = DB_fetch_row($DemAsComponentResult);
	$Demand += $DemAsComponentRow[0];
	//Also the demand for the item as a component of works orders

	$sql = "SELECT SUM(qtypu*(woitems.qtyreqd - woitems.qtyrecd)) AS woqtydemo
				FROM woitems INNER JOIN worequirements
				ON woitems.stockid=worequirements.parentstockid
				INNER JOIN workorders
				ON woitems.wo=workorders.wo
				AND woitems.wo=worequirements.wo
				WHERE  worequirements.stockid='" . $StockID . "'
				AND workorders.closed=0";

	$ErrMsg = _('The workorder component demand for this product cannot be retrieved because');
	$DemandResult = DB_query($sql,$db,$ErrMsg);

	if (DB_num_rows($DemandResult)==1){
		$DemandRow = DB_fetch_row($DemandResult);
		$Demand += $DemandRow[0];
	}

	echo '<TR><TD align=right width="15%" class="tableheader">' . _('Quantity On Hand') . ':</TD><TD width="17%" align=right>' . $QOH . '</TD></TR>';
	echo '<TR><TD align=right width="15%" class="tableheader">' . _('Quantity Demand') . ':</TD><TD width="17%" align=right>' . number_format($Demand,$myrow['decimalplaces']) . '</TD></TR>';
	echo '<TR><TD align=right width="15%" class="tableheader">' . _('Quantity On Order') . ':</TD><TD width="17%" align=right>' . $QOO . '</TD></TR>
				</TABLE>';//end of nested table

    echo '</TD>'; //end cell of master table
    if ($myrow['mbflag']=='B'){
		echo '<TD WIDTH="40%" VALIGN="TOP"><TABLE>
			<TR><TD width="50%" class="tableheader">' . _('Supplier') . '</TD>
				<TD width="20%" class="tableheader">' . _('Cost') . '</TD>
				<TD width="10%" class="tableheader">' . _('Currency') . '</TD>
				<TD width="20%" class="tableheader">' . _('Lead Time') . '</TD></TR>';

		$SuppResult = DB_query("SELECT  suppliers.suppname,
								purchdata.price,
								suppliers.currcode,
								purchdata.leadtime,
								purchdata.conversionfactor,
								purchdata.preferred
						FROM purchdata INNER JOIN suppliers
						ON purchdata.supplierno=suppliers.supplierid
						WHERE purchdata.stockid = '" . $StockID . "'",
						$db);
		while ($SuppRow = DB_fetch_array($SuppResult)){
			echo '<TR><TD>' . $SuppRow['suppname'] . '</TD>
						<TD align=right>' . number_format($SuppRow['price']/$SuppRow['conversionfactor'],2) . '</TD>
						<TD>' . $SuppRow['currcode'] . '</TD>
						<TD>' . $SuppRow['leadtime'] . '</TD></TR>';

		}
		echo '</TR></TABLE></TD>';
	}

	echo '</TR></TABLE><HR>'; // end first item details table

	echo '<TABLE WIDTH="100%" BORDER=1><TR>
		<TD WIDTH=33% class="tableheader">' . _('Item Inquiries') . '</TD>
		<TD WIDTH=33% class="tableheader">' . _('Item Transactions') . '</TD>
		<TD WIDTH=33% class="tableheader">' . _('Item Maintenance') . '</TD>
	</TR>';
	echo '<TR><TD valign="top">';

	/*Stock Inquiry Options */

        echo '<A HREF="' . $rootpath . '/StockMovements.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Movements') . '</A><BR>';

	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
        echo '<A HREF="' . $rootpath . '/StockStatus.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Status') . '</A><BR>';
        echo '<A HREF="' . $rootpath . '/StockUsage.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Usage') . '</A><BR>';
	}
        echo '<A HREF="' . $rootpath . '/SelectSalesOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</A><BR>';
        echo '<A HREF="' . $rootpath . '/SelectCompletedOrder.php?' .SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</A><BR>';
	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
		echo '<A HREF="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Purchase Orders') . '</A><BR>';
		echo '<A HREF="' . $rootpath . '/PO_SelectPurchOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search All Purchase Orders') . '</A><BR>';
		echo '<A HREF="' . $rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg?' . SID . '">' . _('Show Part Picture (if available)') . '</A><BR>';
	}

	if ($Its_A_Dummy==False){
		echo '<A HREF="' . $rootpath . '/BOMInquiry.php?' . SID . '&StockID=' . $StockID . '">' . _('View Costed Bill Of Material') . '</A><BR>';
		echo '<A HREF="' . $rootpath . '/WhereUsedInquiry.php?' . SID . '&StockID=' . $StockID . '">' . _('Where This Item Is Used') . '</A><BR>';
	}

	wikiLink('Product', $StockID);

	echo '</TD><TD valign="top">';

	/* Stock Transactions */
	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
		echo '<A HREF="' . $rootpath . '/StockAdjustments.php?' . SID . '&StockID=' . $StockID . '">' . _('Quantity Adjustments') . '</A><BR>';
        echo '<A HREF="' . $rootpath . '/StockTransfers.php?' . SID . '&StockID=' . $StockID . '">' . _('Location Transfers') . '</A><BR>';
	}

	echo '</TD><TD valign="top">';

	/*Stock Maintenance Options */

  echo '<A HREF="' . $rootpath . '/Stocks.php?">' . _('Add Inventory Items') . '</A><BR>';
  echo '<A HREF="' . $rootpath . '/Stocks.php?' . SID . '&StockID=' . $StockID . '">' . _('Modify Item Details') . '</A><BR>';
	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
		echo '<A HREF="' . $rootpath . '/StockReorderLevel.php?' . SID . '&StockID=' . $StockID . '">' . _('Maintain Reorder Levels') . '</A><BR>';
        	echo '<A HREF="' . $rootpath . '/StockCostUpdate.php?' . SID . '&StockID=' . $StockID . '">' . _('Maintain Standard Cost') . '</A><BR>';
        	echo '<A HREF="' . $rootpath . '/PurchData.php?' . SID . '&StockID=' . $StockID . '">' . _('Maintain Purchasing Data') . '</A><BR>';
	}
	if (! $Its_A_Kitset){
		echo '<A HREF="' . $rootpath . '/Prices.php?' . SID . '&Item=' . $StockID . '">' . _('Maintain Pricing') . '</A><BR>';
        	if (isset($_SESSION['CustomerID']) AND $_SESSION['CustomerID']!="" AND Strlen($_SESSION['CustomerID'])>0){
			echo '<A HREF="' . $rootpath . '/Prices_Customer.php?' . SID . '&Item=' . $StockID . '">' . _('Special Prices for customer') . ' - ' . $_SESSION['CustomerID'] . '</A><BR>';
        	}
	}

	echo '</TD></TR></TABLE>';

} else {
  // options (links) to pages. This requires stock id also to be passed.
	echo '<CENTER><TABLE WIDTH=90% COLSPAN=2 BORDER=2 CELLPADDING=4>';
	echo '<TR>
		<TD WIDTH=33% class="tableheader">' . _('Item Inquiries') . '</TD>
		<TD WIDTH=33% class="tableheader">' . _('Item Transactions') . '</TD>
		<TD WIDTH=33% class="tableheader">' . _('Item Maintenance') . '</TD>
	</TR>';
	echo '<TR><TD>';

	/*Stock Inquiry Options */

	echo '</TD><TD>';

	/* Stock Transactions */

	echo '</TD><TD>';

	/*Stock Maintenance Options */

  echo '<A HREF="' . $rootpath . '/Stocks.php?">' . _('Add Inventory Items') . '</A><BR>';

echo '</TD></TR></TABLE>';

}// end displaying item options if there is one and only one record

?>
</CENTER>
</FORM>
<script language="JavaScript" type="text/javascript">
    //<![CDATA[
            <!--
            document.forms[0].StockCode.select();
            document.forms[0].StockCode.focus();
            //-->
    //]]>
</script>

<?php
include('includes/footer.inc');
?>