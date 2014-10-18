<?php

/* $Id: InventoryValuation.php 6536 2014-01-13 05:31:11Z daintree $ */

include('includes/session.inc');
if ((isset($_POST['PrintPDF']) OR isset($_POST['CSV']))
	AND isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1){

/*Now figure out the inventory data to report for the category range under review */
	if ($_POST['Location']=='All'){
		$SQL = "SELECT stockmaster.categoryid,
					stockcategory.categorydescription,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qtyonhand,
					stockmaster.units,
					stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost AS unitcost,
					SUM(locstock.quantity) *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS itemtotal
				FROM stockmaster,
					stockcategory,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.categoryid=stockcategory.categoryid
				GROUP BY stockmaster.categoryid,
					stockcategory.categorydescription,
					unitcost,
					stockmaster.units,
					stockmaster.decimalplaces,
					stockmaster.materialcost,
					stockmaster.labourcost,
					stockmaster.overheadcost,
					stockmaster.stockid,
					stockmaster.description
				HAVING SUM(locstock.quantity)!=0
				AND stockcategory.categorydescription >= '" . $_POST['FromCriteria'] . "'
				AND stockcategory.categorydescription <= '" . $_POST['ToCriteria'] . "'
				ORDER BY stockcategory.categorydescription,
					stockmaster.stockid";
	} else {
		$SQL = "SELECT stockmaster.categoryid,
					stockcategory.categorydescription,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.decimalplaces,
					locstock.quantity AS qtyonhand,
					stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost AS unitcost,
					locstock.quantity *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS itemtotal
				FROM stockmaster,
					stockcategory,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.categoryid=stockcategory.categoryid
				AND locstock.quantity!=0
				AND stockcategory.categorydescription >= '" . $_POST['FromCriteria'] . "'
				AND stockcategory.categorydescription <= '" . $_POST['ToCriteria'] . "'
				AND locstock.loccode = '" . $_POST['Location'] . "'
				ORDER BY stockcategory.categorydescription,
					stockmaster.stockid";
	}
	$InventoryResult = DB_query($SQL,$db,'','',false,true);

	if (DB_error_no($db) !=0) {
	  $Title = _('Inventory Valuation') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	   prnMsg( _('The inventory valuation could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
		  echo '<br />' . $SQL;
	   }
	   include('includes/footer.inc');
	   exit;
	}
}

if (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');

	$pdf->addInfo('Title',_('Inventory Valuation Report'));
	$pdf->addInfo('Subject',_('Inventory Valuation'));
	$FontSize=9;
	$PageNumber=1;
	$line_height=12;


	
	if (DB_num_rows($InventoryResult)==0){
		$Title = _('Print Inventory Valuation Error');
		include('includes/header.inc');
		prnMsg(_('There were no items with any value to print out for the location specified'),'info');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	}

	include ('includes/PDFInventoryValnPageHeader.inc');

	$Tot_Val=0;
	$Category = '';
	$CatTot_Val=0;
	$CatTot_Qty=0;

	while ($InventoryValn = DB_fetch_array($InventoryResult,$db)){

		if ($Category!=$InventoryValn['categoryid']){
			$FontSize=10;
			if ($Category!=''){ /*Then it's NOT the first time round */

				/* need to print the total of previous category */
				if ($_POST['DetailedReport']=='Yes'){
					$YPos -= (2*$line_height);
					if ($YPos < $Bottom_Margin + (3*$line_height)){
		 				  include('includes/PDFInventoryValnPageHeader.inc');
					}
					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Total for') . ' ' . $Category . ' - ' . $CategoryName);
				}

				$DisplayCatTotVal = locale_number_format($CatTot_Val,$_SESSION['CompanyRecord']['decimalplaces']);
				$DisplayCatTotQty = locale_number_format($CatTot_Qty,2);
				$LeftOvers = $pdf->addTextWrap(480,$YPos,80,$FontSize,$DisplayCatTotVal, 'right');
				$LeftOvers = $pdf->addTextWrap(360,$YPos,60,$FontSize,$DisplayCatTotQty, 'right');
				$YPos -=$line_height;

				If ($_POST['DetailedReport']=='Yes'){
				/*draw a line under the CATEGORY TOTAL*/
					$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
					$YPos -=(2*$line_height);
				}
				$CatTot_Val=0;
				$CatTot_Qty=0;
			}
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$InventoryValn['categoryid'] . ' - ' . $InventoryValn['categorydescription']);
			$Category = $InventoryValn['categoryid'];
			$CategoryName = $InventoryValn['categorydescription'];
		}

		if ($_POST['DetailedReport']=='Yes'){
			$YPos -=$line_height;
			$FontSize=8;

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,100,$FontSize,$InventoryValn['stockid']);
			$LeftOvers = $pdf->addTextWrap(170,$YPos,220,$FontSize,$InventoryValn['description']);
			$DisplayUnitCost = locale_number_format($InventoryValn['unitcost'],$_SESSION['CompanyRecord']['decimalplaces']);
			$DisplayQtyOnHand = locale_number_format($InventoryValn['qtyonhand'],$InventoryValn['decimalplaces']);
			$DisplayItemTotal = locale_number_format($InventoryValn['itemtotal'],$_SESSION['CompanyRecord']['decimalplaces']);

			$LeftOvers = $pdf->addTextWrap(360,$YPos,60,$FontSize,$DisplayQtyOnHand,'right');
			$LeftOvers = $pdf->addTextWrap(423,$YPos,15,$FontSize,$InventoryValn['units'],'left');
			$LeftOvers = $pdf->addTextWrap(438,$YPos,60,$FontSize,$DisplayUnitCost, 'right');

			$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayItemTotal, 'right');
		}
		$Tot_Val += $InventoryValn['itemtotal'];
		$CatTot_Val += $InventoryValn['itemtotal'];
		$CatTot_Qty += $InventoryValn['qtyonhand'];

		if ($YPos < $Bottom_Margin + $line_height){
		   include('includes/PDFInventoryValnPageHeader.inc');
		}

	} /*end inventory valn while loop */

	$FontSize =10;
/*Print out the category totals */
	if ($_POST['DetailedReport']=='Yes'){
		$YPos -= (2*$line_height);
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200-$Left_Margin,$FontSize, _('Total for') . ' ' . $Category . ' - ' . $CategoryName, 'left');
	}
	$DisplayCatTotVal = locale_number_format($CatTot_Val,$_SESSION['CompanyRecord']['decimalplaces']);

	$LeftOvers = $pdf->addTextWrap(480,$YPos,80,$FontSize,$DisplayCatTotVal, 'right');
	$DisplayCatTotQty = locale_number_format($CatTot_Qty,2);
	$LeftOvers = $pdf->addTextWrap(360,$YPos,60,$FontSize,$DisplayCatTotQty, 'right');

	if ($_POST['DetailedReport']=='Yes'){
		/*draw a line under the CATEGORY TOTAL*/
		$YPos -= ($line_height);
		$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
	}

	$YPos -= (2*$line_height);

	if ($YPos < $Bottom_Margin + $line_height){
		   include('includes/PDFInventoryValnPageHeader.inc');
	}
/*Print out the grand totals */
	$LeftOvers = $pdf->addTextWrap(80,$YPos,260-$Left_Margin,$FontSize,_('Grand Total Value'), 'right');
	$DisplayTotalVal = locale_number_format($Tot_Val,$_SESSION['CompanyRecord']['decimalplaces']);
	$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayTotalVal, 'right');

	$pdf->OutputD($_SESSION['DatabaseName'] . '_Inventory_Valuation_' . Date('Y-m-d') . '.pdf');
	$pdf->__destruct();
	
} elseif (isset($_POST['CSV'])) {

	$CSVListing = _('Category ID') .','. _('Category Description') .','. _('Stock ID') .','. _('Description') .','. _('Decimal Places') .','. _('Qty On Hand') .','. _('Units') .','. _('Unit Cost') .','. _('Total') . "\n";
	while ($InventoryValn = DB_fetch_row($InventoryResult, $db)) {
		$CSVListing .= implode(',', $InventoryValn) . "\n";
	}
	header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    header("Content-disposition: attachment; filename=InventoryValuation_Categories_" .  $_POST['FromCriteria']  . '-' .  $_POST['ToCriteria']  .'.csv');
    header("Pragma: public");
    header("Expires: 0");
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $CSVListing;
	exit;

} else { /*The option to print PDF nor to create the CSV was not hit */

	$Title=_('Inventory Valuation Reporting');
	include('includes/header.inc');


	if (empty($_POST['FromCriteria']) OR empty($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/
		echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '
			</p>';

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
              <div>
            <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">
			<tr>
				<td>' . _('From Inventory Category Code') . ':</td>
				<td><select name="FromCriteria">';

		$sql="SELECT categoryid,
					categorydescription
				FROM stockcategory
				ORDER BY categorydescription";

		$CatResult= DB_query($sql,$db);
		While ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categorydescription'] . '">' . $myrow['categorydescription'] . ' - ' . $myrow['categoryid'] . '</option>';
		}
		echo '</select></td>
			</tr>';

		echo '<tr>
				<td>' . _('To Inventory Category Code') . ':</td>
				<td><select name="ToCriteria">';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		While ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categorydescription'] . '">' . $myrow['categorydescription'] . ' - ' . $myrow['categoryid'] . '</option>';
		}
		echo '</select></td>
			</tr>';

		echo '<tr>
				<td>' . _('For Inventory in Location') . ':</td>
				<td><select name="Location">';

		$sql = "SELECT loccode,
						locationname
				FROM locations";

		$LocnResult=DB_query($sql,$db);

		echo '<option value="All">' . _('All Locations') . '</option>';

		while ($myrow=DB_fetch_array($LocnResult)){
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
		echo '</select></td>
			</tr>';

		echo '<tr>
				<td>' . _('Summary or Detailed Report') . ':</td>
				<td><select name="DetailedReport">
					<option selected="selected" value="No">' . _('Summary Report') . '</option>
					<option value="Yes">' . _('Detailed Report') . '</option>
					</select></td>
			</tr>
			</table>
			<br />
			<div class="centre">
				<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
				<input type="submit" name="CSV" value="' . _('Output to CSV') . '" />
			</div>';
        echo '</div>
              </form>';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */
?>
