<?php

/* $Id: InventoryAtShop.php . Variation of Inventoryvaluation for inventory taking at shops */

include('includes/session.php');
include('includes/KLGeneralFunctions.php');

if (isset($_POST['PrintPDF'])){

	include('includes/PDFStarter.php');

	$pdf->addInfo('Title',_('KL Inventory At Shops Report'));
	$pdf->addInfo('Subject',_('KL Inventory At Shops Report'));
	$FontSize=9;
	$PageNumber=1;
	$line_height=12;

	$StartDate = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-RE_CHECK_PRICETAGS_CHANGED_DURING_LAST_X_DAYS));

	// SQL ORDER BY needs the 2 dates because in some cases 1 items has more than 1 row in klchenageprice
	// if there has been a typing error while entering the new price, then the system adds another row. is it a bug?
	// KL RICARD 31/03/2015
	$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.units,
				stockmaster.decimalplaces,
				locstock.quantity AS qtyonhand,
				(SELECT klchangeprice.newretailprice
					FROM klchangeprice
					WHERE klchangeprice.stockid = stockmaster.stockid
						AND klchangeprice.endprocessdate != '0000-00-00'
					ORDER BY klchangeprice.endprocessdate DESC,
						klchangeprice.startprocessdate DESC
					LIMIT 1) AS retailprice
			FROM stockmaster,
				stockcategory,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.categoryid=stockcategory.categoryid
			AND locstock.quantity!=0 ";
	if ($_POST['Category']!='All'){
		$SQL = $SQL . " AND stockmaster.categoryid = '" . $_POST['Category'] . "' ";
	}else{
		if ($_POST['DisplayingItems']=='No'){
			$SQL = $SQL . " AND stockmaster.categoryid != 'SHDISP' ";
		}
	}
	$SQL = $SQL . "	AND locstock.loccode = '" . $_POST['Location'] . "'
			ORDER BY stockmaster.stockid";
	
	$InventoryResult = DB_query($SQL,'','',false,true);

	if (DB_error_no() !=0) {
	  $Title = _('KL Inventory At Shops') . ' - ' . _('Problem Report');
	  include('includes/header.php');
	   prnMsg( _('The KL inventory at Shops could not be retrieved by the SQL because') . ' '  . DB_error_msg(),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   include('includes/footer.php');
	   exit;
	}
	if (DB_num_rows($InventoryResult)==0){
		$Title = _('Print KL Inventory At Shops Error');
		include('includes/header.php');
		prnMsg(_('There were no items to print out for the location specified'),'info');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit;
	}

	include ('includes/KLPDFInventoryAtShopPageHeader.inc');

	$Tot_Val=0;
	$Category = '';
	$CatTot_Qty=0;

	while ($InventoryValn = DB_fetch_array($InventoryResult)){

		$YPos -=(1*$line_height);

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,100,$FontSize,$InventoryValn['stockid']);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,200,$FontSize,$InventoryValn['description']);
		$DisplayQtyOnHand = locale_number_format($InventoryValn['qtyonhand'],$InventoryValn['decimalplaces']);

		$LeftOvers = $pdf->addTextWrap(300,$YPos,60,$FontSize,$DisplayQtyOnHand,'right');
		$LeftOvers = $pdf->addTextWrap(363,$YPos,15,$FontSize,$InventoryValn['units'],'left');

		$RetailPrice = locale_number_format_zero_blank($InventoryValn['retailprice'],0);
		$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$RetailPrice,'right');

		$pdf->line($Left_Margin, $YPos-$line_height+6,$Page_Width-$Right_Margin, $YPos-$line_height+6);
		$YPos -=(0.5*$line_height);

		$CatTot_Qty += $InventoryValn['qtyonhand'];

		if ($YPos < $Bottom_Margin + $line_height){
		   include('includes/KLPDFInventoryAtShopPageHeader.inc');
		}

	} /*end inventory valn while loop */

	$YPos -= (2*$line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200-$Left_Margin,$FontSize, _('Total in location: ')  , 'left');

	$DisplayCatTotQty = locale_number_format($CatTot_Qty,0);
	$LeftOvers = $pdf->addTextWrap(300,$YPos,60,$FontSize,$DisplayCatTotQty, 'right');
	$YPos -= ($line_height);
	$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);

	$YPos -= (2*$line_height);

	$pdf->OutputD($_SESSION['DatabaseName'] . '_Inventory_At_' . $_POST['Location'] . '_' . Date('Y-m-d') . '.pdf');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$Title=_('KL Inventory At Shops Reporting');
	include('includes/header.php');

	echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '
			</p>';

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
              <div>
            <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">';

		echo '<tr>
				<td>' . _('For Inventory in Location') . ':</td>
				<td><select name="Location">';

		$sql = "SELECT loccode,
						locationname
				FROM locations";

		$LocnResult=DB_query($sql);

		while ($myrow=DB_fetch_array($LocnResult)){
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
		echo '</select></td>
			</tr>';

		echo '<tr>
			<td>' . _('For Stock Categories') . ':</td>
			<td><select name="Category">';

		$sql = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription";
		$CatResult=DB_query($sql);

		echo '<option value="All">' . _('All Stock Categories') . '</option>';

		while ($myrow=DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		}
		echo '</select>
				</td>
			</tr>';
			
		echo '<tr>
				<td>' . _('Include Shop Displaying Category') . ':</td>
				<td><select name="DisplayingItems">
					<option selected="selected" value="No">' . _('No') . '</option>
					<option value="Yes">' . _('Yes') . '</option>
					</select></td>
			</tr>
			</table>
			<br />
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
			</div>';
        echo '</div>
              </form>';

include('includes/footer.php');

} /*end of else not PrintPDF */
?>