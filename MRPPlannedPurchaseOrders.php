<?php

/* $Id: MRPPlannedPurchaseOrders.php 6310 2013-08-29 10:42:50Z daintree $*/
// MRPPlannedPurchaseOrders.php - Report of purchase parts that MRP has determined should have
// purchase orders created for them

include('includes/session.inc');

//Maybe not ANSI SQL??
$sql="SHOW TABLES WHERE Tables_in_" . $_SESSION['DatabaseName'] . "='mrprequirements'";

$result=DB_query($sql,$db);
if (DB_num_rows($result)==0) {
	$Title=_('MRP error');
	include('includes/header.inc');
	echo '<br />';
	prnMsg( _('The MRP calculation must be run before you can run this report') . '<br />' . 
			_('To run the MRP calculation click').' ' . '<a href='.$RootPath .'/MRP.php>' . _('here') . '</a>', 'error');
	include('includes/footer.inc');
	exit;
}
if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('MRP Planned Purchase Orders Report'));
	$pdf->addInfo('Subject',_('MRP Planned Purchase Orders'));
	$FontSize=9;
	$PageNumber=1;
	$line_height=12;

	$Xpos = $Left_Margin+1;
	$WhereDate = ' ';
	$ReportDate = ' ';
	if (Is_Date($_POST['cutoffdate'])) {
		$FormatDate = FormatDateForSQL($_POST['cutoffdate']);
		$WhereDate = " AND duedate <= '" . $FormatDate . "' ";
		$ReportDate = _(' Through  ') . Format_Date($_POST['cutoffdate']);
	}
	if ($_POST['Consolidation'] == 'None') {
		$sql = "SELECT mrpplannedorders.*,
					   stockmaster.stockid,
					   stockmaster.description,
					   stockmaster.mbflag,
					   stockmaster.decimalplaces,
					   stockmaster.actualcost,
					   (stockmaster.materialcost + stockmaster.labourcost +
						stockmaster.overheadcost ) as computedcost
				FROM mrpplannedorders, stockmaster
				WHERE mrpplannedorders.part = stockmaster.stockid " . $WhereDate . "
				 AND stockmaster.mbflag IN ('B','P')
				ORDER BY mrpplannedorders.part,mrpplannedorders.duedate";
	} elseif ($_POST['Consolidation'] == 'Weekly') {
		$sql = "SELECT mrpplannedorders.part,
					   SUM(mrpplannedorders.supplyquantity) as supplyquantity,
					   TRUNCATE(((TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
					   MIN(mrpplannedorders.duedate) as duedate,
					   MIN(mrpplannedorders.mrpdate) as mrpdate,
					   COUNT(*) AS consolidatedcount,
					   stockmaster.stockid,
					   stockmaster.description,
					   stockmaster.mbflag,
					   stockmaster.decimalplaces,
					   stockmaster.actualcost,
					   (stockmaster.materialcost + stockmaster.labourcost +
						stockmaster.overheadcost ) as computedcost
				FROM mrpplannedorders, stockmaster
				WHERE mrpplannedorders.part = stockmaster.stockid " . $WhereDate . "
				AND stockmaster.mbflag IN ('B','P')
				GROUP BY mrpplannedorders.part,
						 weekindex,
						 stockmaster.stockid,
						 stockmaster.description,
						 stockmaster.mbflag,
						 stockmaster.decimalplaces,
						 stockmaster.actualcost,
					   stockmaster.materialcost,
					   stockmaster.labourcost,
					   stockmaster.overheadcost,
					   computedcost
				ORDER BY mrpplannedorders.part,weekindex";
	} else {  // This else consolidates by month
		$sql = "SELECT mrpplannedorders.part,
					   SUM(mrpplannedorders.supplyquantity) as supplyquantity,
					   EXTRACT(YEAR_MONTH from duedate) AS yearmonth,
					   MIN(mrpplannedorders.duedate) as duedate,
					   MIN(mrpplannedorders.mrpdate) as mrpdate,
					   COUNT(*) AS consolidatedcount,
					   stockmaster.stockid,
					   stockmaster.description,
					   stockmaster.mbflag,
					   stockmaster.decimalplaces,
					   stockmaster.actualcost,
					   (stockmaster.materialcost + stockmaster.labourcost +
						stockmaster.overheadcost ) as computedcost
				FROM mrpplannedorders, stockmaster
				WHERE mrpplannedorders.part = stockmaster.stockid  " . $WhereDate . "
				AND stockmaster.mbflag IN ('B','P')
				GROUP BY mrpplannedorders.part,
						 yearmonth,
						 stockmaster.stockid,
						 stockmaster.description,
						 stockmaster.mbflag,
						 stockmaster.decimalplaces,
						 stockmaster.actualcost,
					   stockmaster.materialcost,
					   stockmaster.labourcost,
					   stockmaster.overheadcost,
					   computedcost
				ORDER BY mrpplannedorders.part,yearmonth ";
	}
	$result = DB_query($sql,$db,'','',false,true);

	if (DB_error_no($db) !=0) {
	  $Title = _('MRP Planned Purchase Orders') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	   prnMsg( _('The MRP planned purchase orders could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
		  echo '<br />' . $sql;
	   }
	   include('includes/footer.inc');
	   exit;
	}
	if (DB_num_rows($result)==0){ //then there is nothing to print
		$Title = _('Print MRP Planned Purchase Orders Error');
		include('includes/header.inc');
		prnMsg(_('There were no items with planned purchase orders'),'info');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	}

	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
				$Page_Width,$Right_Margin,$_POST['Consolidation'],$ReportDate);

	$Total_Shortage=0;
	$Partctr = 0;
	$fill = false;
	$pdf->SetFillColor(224,235,255);  // Defines color to make alternating lines highlighted
	$FontSize=8;
	$holdpart = ' ';
	$holddescription = ' ';
	$holdmbflag = ' ';
	$holdcost = ' ';
	$holddecimalplaces = 0;
	$totalpartqty = 0;
	$totalpartcost = 0;
	$Total_Extcost = 0;

	while ($myrow = DB_fetch_array($result,$db)){
			$YPos -=$line_height;

			// Use to alternate between lines with transparent and painted background
			if ($_POST['Fill'] == 'yes'){
				$fill=!$fill;
			}

			// Print information on part break
			if ($Partctr > 0 & $holdpart != $myrow['part']) {
				$pdf->addTextWrap(50,$YPos,130,$FontSize,$holddescription,'',0,$fill);
				$pdf->addTextWrap(180,$YPos,50,$FontSize,_('Unit Cost: '),'center',0,$fill);
				$pdf->addTextWrap(230,$YPos,40,$FontSize,locale_number_format($holdcost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);
				$pdf->addTextWrap(270,$YPos,50,$FontSize,locale_number_format($totalpartqty,	$holddecimalplaces),'right',0,$fill);
				$pdf->addTextWrap(320,$YPos,60,$FontSize,locale_number_format($totalpartcost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);
				$pdf->addTextWrap(380,$YPos,30,$FontSize,_('M/B: '),'right',0,$fill);
				$pdf->addTextWrap(410,$YPos,15,$FontSize,$holdmbflag,'right',0,$fill);
				// Get and print supplier info for part
				list($lastdate,$lastsupplier,$preferredsupplier) = GetPartInfo($db,$holdpart);
				$displaydate = $lastdate;
				if (!Is_Date($lastdate)) {
					$displaydate = ' ';
				}
				$YPos -= $line_height;
				$pdf->addTextWrap(50,$YPos,80,$FontSize,_('Last Purchase Date: '),'left',0,$fill);
				$pdf->addTextWrap(130,$YPos,60,$FontSize,$displaydate,'left',0,$fill);
				$pdf->addTextWrap(190,$YPos,60,$FontSize,_('Supplier: '),'left',0,$fill);
				$pdf->addTextWrap(250,$YPos,60,$FontSize,$lastsupplier,'left',0,$fill);
				$pdf->addTextWrap(310,$YPos,120,$FontSize,_('Preferred Supplier: '),'left',0,$fill);
				$pdf->addTextWrap(430,$YPos,60,$FontSize,$preferredsupplier,'left',0,$fill);
				$totalpartcost = 0;
				$totalpartqty = 0;
				$YPos -= (2*$line_height);
			}

			// Parameters for addTextWrap are defined in /includes/class.pdf.php
			// 1) X position 2) Y position 3) Width
			// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
			// and False to set to transparent
			$FormatedSupDueDate = ConvertSQLDate($myrow['duedate']);
			$FormatedSupMRPDate = ConvertSQLDate($myrow['mrpdate']);
			$extcost = $myrow['supplyquantity'] * $myrow['computedcost'];
			$pdf->addTextWrap($Left_Margin,$YPos,110,$FontSize,$myrow['part'],'',0,$fill);
			$pdf->addTextWrap(150,$YPos,50,$FontSize,$FormatedSupDueDate,'right',0,$fill);
			$pdf->addTextWrap(200,$YPos,60,$FontSize,$FormatedSupMRPDate,'right',0,$fill);
			$pdf->addTextWrap(260,$YPos,50,$FontSize,locale_number_format($myrow['supplyquantity'],$myrow['decimalplaces']),'right',0,$fill);
			$pdf->addTextWrap(310,$YPos,60,$FontSize,locale_number_format($extcost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);
			if ($_POST['Consolidation'] == 'None'){
				$pdf->addTextWrap(370,$YPos,80,$FontSize,$myrow['ordertype'],'right',0,$fill);
				$pdf->addTextWrap(450,$YPos,80,$FontSize,$myrow['orderno'],'right',0,$fill);
			} else {
				$pdf->addTextWrap(370,$YPos,100,$FontSize,$myrow['consolidatedcount'],'right',0,$fill);
			};
			$holddescription = $myrow['description'];
			$holdpart = $myrow['part'];
			$holdmbflag = $myrow['mbflag'];
			$holdcost = $myrow['computedcost'];
			$holddecimalplaces = $myrow['decimalplaces'];
			$totalpartcost += $extcost;
			$totalpartqty += $myrow['supplyquantity'];

			$Total_Extcost += $extcost;
			$Partctr++;

			if ($YPos < $Bottom_Margin + $line_height){
			   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
						   $Right_Margin,$_POST['Consolidation'],$ReportDate);
			}

	} /*end while loop */
	// Print summary information for last part
	$YPos -=$line_height;
	$pdf->addTextWrap(40,$YPos,130,$FontSize,$holddescription,'',0,$fill);
	$pdf->addTextWrap(170,$YPos,50,$FontSize,_('Unit Cost: '),'center',0,$fill);
	$pdf->addTextWrap(220,$YPos,40,$FontSize,locale_number_format($holdcost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);
	$pdf->addTextWrap(260,$YPos,50,$FontSize,locale_number_format($totalpartqty,$holddecimalplaces),'right',0,$fill);
	$pdf->addTextWrap(310,$YPos,60,$FontSize,locale_number_format($totalpartcost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);
	$pdf->addTextWrap(370,$YPos,30,$FontSize,_('M/B: '),'right',0,$fill);
	$pdf->addTextWrap(400,$YPos,15,$FontSize,$holdmbflag,'right',0,$fill);
	// Get and print supplier info for part
	list($lastdate,$lastsupplier,$preferredsupplier) = GetPartInfo($db,$holdpart);
	$displaydate = $lastdate;
	if (!Is_Date($lastdate)) {
		$displaydate = ' ';
	}
	$YPos -= $line_height;
	$pdf->addTextWrap(50,$YPos,80,$FontSize,_('Last Purchase Date: '),'left',0,$fill);
	$pdf->addTextWrap(130,$YPos,60,$FontSize,$displaydate,'left',0,$fill);
	$pdf->addTextWrap(190,$YPos,60,$FontSize,_('Supplier: '),'left',0,$fill);
	$pdf->addTextWrap(250,$YPos,60,$FontSize,$lastsupplier,'left',0,$fill);
	$pdf->addTextWrap(310,$YPos,120,$FontSize,_('Preferred Supplier: '),'left',0,$fill);
	$pdf->addTextWrap(430,$YPos,60,$FontSize,$preferredsupplier,'left',0,$fill);
	$FontSize =8;
	$YPos -= (2*$line_height);

	if ($YPos < $Bottom_Margin + $line_height){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
					   $Right_Margin,$_POST['Consolidation'],$ReportDate);
		  // include('includes/MRPPlannedPurchaseOrdersPageHeader.inc');
	}
/*Print out the grand totals */
	$pdf->addTextWrap($Left_Margin,$YPos,120,$FontSize,_('Number of Purchase Orders: '), 'left');
	$pdf->addTextWrap(150,$YPos,30,$FontSize,$Partctr, 'left');
	$pdf->addTextWrap(200,$YPos,100,$FontSize,_('Total Extended Cost:'), 'right');
	$DisplayTotalVal = locale_number_format($Total_Extcost,$_SESSION['CompanyRecord']['decimalplaces']);
	$pdf->addTextWrap(310,$YPos,60,$FontSize,$DisplayTotalVal, 'right');

	$pdf->OutputD($_SESSION['DatabaseName'] . '_MRP_Planned_Purchase_Orders_' . Date('Y-m-d') . '.pdf');
	$pdf->__destruct();

} else { /*The option to print PDF was not hit so display form */

	$Title=_('MRP Planned Purchase Orders Reporting');
	include('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
		_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>';
    echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<table class="selection">';
	echo '<tr>
                  <td>' . _('Consolidation') . ':</td>
                  <td><select required="required" name="Consolidation">
                      <option selected="selected" value="None">' . _('None') . '</option>
                      <option value="Weekly">' . _('Weekly') . '</option>
                      <option value="Monthly">' . _('Monthly') . '</option>
                  </select></td>
              </tr>
              <tr>
                  <td>' . _('Print Option') . ':</td>
                  <td><select name="Fill">
                      <option selected="selected" value="yes">' . _('Print With Alternating Highlighted Lines') . '</option>
                      <option value="no">' . _('Plain Print') . '</option>
                      </select></td>
              </tr>
              <tr>
                  <td>' . _('Cut Off Date') . ':</td>
                  <td><input type ="text" required="required" class="date" alt="'.$_SESSION['DefaultDateFormat'] .
			'" name="cutoffdate" size="10" value="'.date($_SESSION['DefaultDateFormat']).'" /></td>
             </tr>
             </table>
             <br />
             <div class="centre">
                  <input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
             </div>
          </div>
          </form>';

	include('includes/footer.inc');

} /*end of else not PrintPDF */

function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin,$consolidation,$ReportDate) {

	/*PDF page header for MRP Planned Work Orders report */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$line_height;

	$pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('MRP Planned Purchase Orders Report'));
	$pdf->addTextWrap(190,$YPos,100,$FontSize,$ReportDate);
	$pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,_('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -= $line_height;
	if ($consolidation == 'None') {
		$displayconsolidation = _('None');
	} elseif ($consolidation == 'Weekly') {
		$displayconsolidation = _('Weekly');
	} else {
		$displayconsolidation = _('Monthly');
	};
	$pdf->addTextWrap($Left_Margin,$YPos,65,$FontSize,_('Consolidation:'));
	$pdf->addTextWrap(110,$YPos,40,$FontSize,$displayconsolidation);

	$YPos -=(2*$line_height);

	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$pdf->addTextWrap($Xpos,$YPos,150,$FontSize,_('Part Number'), 'left');
	$pdf->addTextWrap(150,$YPos,50,$FontSize,_('Due Date'), 'right');
	$pdf->addTextWrap(200,$YPos,60,$FontSize,_('MRP Date'), 'right');
	$pdf->addTextWrap(260,$YPos,50,$FontSize,_('Quantity'), 'right');
	$pdf->addTextWrap(310,$YPos,60,$FontSize,_('Ext. Cost'), 'right');
	if ($consolidation == 'None') {
		$pdf->addTextWrap(370,$YPos,80,$FontSize,_('Source Type'), 'right');
		$pdf->addTextWrap(450,$YPos,80,$FontSize,_('Source Order'), 'right');
	} else {
		$pdf->addTextWrap(370,$YPos,100,$FontSize,_('Consolidation Count'), 'right');
	}

	$FontSize=8;
	$YPos =$YPos - (2*$line_height);
	$PageNumber++;
} // End of PrintHeader function

function GetPartInfo(&$db,$part) {
	// Get last purchase order date and supplier for part, and also preferred supplier
	// Printed when there is a part break
	$sql = "SELECT orddate as maxdate,
				   purchorders.orderno
			FROM purchorders INNER JOIN purchorderdetails
			ON purchorders.orderno = purchorderdetails.orderno
			WHERE purchorderdetails.itemcode = '" . $part ."'
			ORDER BY orddate DESC LIMIT 1";
	$result = DB_query($sql,$db);
	if (DB_num_rows($result)>0) {
		$myrow = DB_fetch_array($result,$db);
		$PartInfo[] = ConvertSQLDate($myrow['maxdate']);
		$OrderNo= $myrow['orderno'];
		$sql = "SELECT supplierno
				FROM purchorders
				WHERE purchorders.orderno = '" .$OrderNo. "'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_array($result,$db);
		$PartInfo[] = $myrow['supplierno'];
		$sql = "SELECT supplierno
				FROM purchdata
				WHERE stockid = '" . $part . "'
				AND preferred='1'";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_array($result,$db);
		$PartInfo[] = $myrow['supplierno'];
		return $PartInfo;
	} else {
		return array('','','');
	}

}

?>