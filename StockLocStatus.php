<?php

$PageSecurity = 2;

include('includes/session.inc');

$title = _('All Stock Status By Location/Category');

include('includes/header.inc');

if (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
}


echo '<HR><FORM ACTION="' . $_SERVER['PHP_SELF'] . '?'. SID . '" METHOD=POST>';

$sql = "SELECT loccode,
		locationname
	FROM locations";
$resultStkLocs = DB_query($sql,$db);

echo '<TABLE><TR><TD>';

echo '<TABLE><TR><TD>' . _('From Stock Location') . ':</TD><TD><SELECT name="StockLocation"> ';
while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
		     echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<OPTION SELECTED Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
}
echo '</SELECT></TD></TR>';

$SQL='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription';
$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1)==0){
	echo '</TABLE></TD></TR>
		</TABLE>
		<P>';
	prnMsg(_('There are no stock categories currently defined please use the link below to set them up'),'warn');
	echo '<BR><A HREF="' . $rootpath . '/StockCategories.php?' . SID .'">' . _('Define Stock Categories') . '</A>';
	include ('includes/footer.inc');
	exit;
}

echo '<TR><TD>' . _('In Stock Category') . ':</TD><TD><SELECT NAME="StockCat">';
if (!isset($_POST['StockCat'])){
	$_POST['StockCat']='All';
}
if ($_POST['StockCat']=='All'){
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

echo '</SELECT></TD></TR></TABLE>';



echo '</TD><TD VALIGN=CENTER><INPUT TYPE=SUBMIT NAME="ShowStatus" VALUE="' . _('Show Stock Status') . '">';

echo '</TD></TR></TABLE>';
echo '<HR>';


if (isset($_POST['ShowStatus'])){

	if ($_POST['StockCat']=='All') {
		$sql = "SELECT locstock.stockid,
				stockmaster.description,
				locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				stockmaster.decimalplaces,
				stockmaster.serialised,
				stockmaster.controlled
			FROM locstock, 
				stockmaster, 
				locations
			WHERE locstock.stockid=stockmaster.stockid
			AND locstock.loccode = '$_POST[StockLocation]'
			AND locstock.loccode=locations.loccode
			AND locstock.quantity > 0
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
			ORDER BY locstock.stockid";
	} else {
		$sql = "SELECT locstock.stockid,
				stockmaster.description,
				locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				stockmaster.decimalplaces,
				stockmaster.serialised,
				stockmaster.controlled
			FROM locstock, 
				stockmaster, 
				locations
			WHERE locstock.stockid=stockmaster.stockid
			AND locstock.loccode = '$_POST[StockLocation]'
			AND locstock.loccode=locations.loccode
			AND locstock.quantity > 0
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M')
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			ORDER BY locstock.stockid";
	}


	$ErrMsg =  _('The stock held at each location cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');
	$LocStockResult = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	echo '<TABLE CELLPADDING=5 CELLSPACING=4 BORDER=0>';

	$tableheader = '<TR>
			<TD class="tableheader">' . _('StockID') . '</TD>
			<TD class="tableheader">' . _('Description') . '</TD>
			<TD class="tableheader">' . _('Quantity On Hand') . '</TD>
			<TD class="tableheader">' . _('Re-Order Level') . '</FONT></TD>
			<TD class="tableheader">' . _('Demand') . '</TD>
			<TD class="tableheader">' . _('Available') . '</TD>
			<TD class="tableheader">' . _('On Order') . '</TD>
			</TR>';
	echo $tableheader;
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($LocStockResult)) {

		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}

		$StockID = $myrow['stockid'];

		$sql = "SELECT Sum(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
                   	FROM salesorderdetails,
                        	salesorders
                   	WHERE salesorders.orderno = salesorderdetails.orderno
			AND salesorders.fromstkloc='" . $myrow['loccode'] . "'
			AND salesorderdetails.completed=0
			AND salesorderdetails.stkcode='" . $StockID . "'";

		$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql,$db,$ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty =  $DemandRow[0];
		} else {
			$DemandQty =0;
		}

		//Also need to add in the demand as a component of an assembly items if this items has any assembly parents.
		$sql = "SELECT Sum((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
                   	FROM salesorderdetails,
                        	salesorders,
                        	bom,
                        	stockmaster
                   	WHERE salesorderdetails.stkcode=bom.parent
			AND salesorders.orderno = salesorderdetails.orderno
			AND salesorders.fromstkloc='" . $myrow['loccode'] . "'
			AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
			AND bom.component='" . $StockID . "'
			AND stockmaster.stockid=bom.parent
			AND stockmaster.mbflag='A'";

		$ErrMsg = _('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$DemandResult = DB_query($sql,$db, $ErrMsg);

		if (DB_num_rows($DemandResult)==1){
			$DemandRow = DB_fetch_row($DemandResult);
			$DemandQty += $DemandRow[0];
		}

		$sql = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) AS qoo
                   	FROM purchorderdetails
                   	INNER JOIN purchorders
                   		ON purchorderdetails.orderno=purchorders.orderno
                   	WHERE purchorders.intostocklocation='" . $myrow['loccode'] . "'
			AND purchorderdetails.itemcode='" . $StockID . "'";

		$ErrMsg = _('The quantity on order for this product to be received into') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because');
		$QOOResult = DB_query($sql,$db,$ErrMsg);

		if (DB_num_rows($QOOResult)==1){
			$QOORow = DB_fetch_row($QOOResult);
			$QOO =  $QOORow[0];
		} else {
			$QOOQty = 0;
		}


		printf("<td><a target='_blank' href='StockStatus.php?StockID=%s'>%s</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>",
			strtoupper($myrow['stockid']),
			strtoupper($myrow['stockid']),
			$myrow['description'],
			number_format($myrow['quantity'],0),
			number_format($myrow['reorderlevel'],0),
			number_format($DemandQty,0),
			number_format($myrow['quantity'] - $DemandQty,0),
			number_format($QOO,0));

		if ($myrow['serialised'] ==1){ /*The line is a serialised item*/

			echo '<TD><A target="_blank" HREF="' . $rootpath . '/StockSerialItems.php?' . SID . '&Serialised=Yes&Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Serial Numbers') . '</A></TD></TR>';
		} elseif ($myrow['controlled']==1){
			echo '<TD><A target="_blank" HREF="' . $rootpath . '/StockSerialItems.php?' . SID . '&Location=' . $myrow['loccode'] . '&StockID=' . $StockID . '">' . _('Batches') . '</A></TD></TR>';
		}

		$j++;
		If ($j == 12){
			$j=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

	echo '</TABLE><HR>';
	echo '</form>';
} /* Show status button hit */
include('includes/footer.inc');

?>