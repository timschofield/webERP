<?php
// MRPPlannedWorkOrders.php - Report of manufactured parts that MRP has determined should have
// work orders created for them

include('includes/session.php');
if (isset($_POST['cutoffdate'])){$_POST['cutoffdate'] = ConvertSQLDate($_POST['cutoffdate']);};

if ( !DB_table_exists('mrprequirements') ) {
	$Title=_('MRP error');
	include('includes/header.php');
	echo '<br />';
	prnMsg( _('The MRP calculation must be run before you can run this report') . '<br />' .
			_('To run the MRP calculation click') . ' ' . '<a href="' . $RootPath . '/MRP.php">' . _('here') . '</a>', 'error');
	include('includes/footer.php');
	exit;
}

if ( isset($_POST['PrintPDF']) OR isset($_POST['Review']) ) {

	$WhereDate = ' ';
	$ReportDate = ' ';
	if (Is_Date($_POST['cutoffdate'])) {
		$FormatDate = FormatDateForSQL($_POST['cutoffdate']);
		$WhereDate = " AND duedate <= '" . $FormatDate . "' ";
		$ReportDate = ' ' . _('Through') . ' ' . $_POST['cutoffdate'];
	}

	if ($_POST['Consolidation'] == 'None') {
		$SQL = "SELECT mrpplannedorders.*,
					   stockmaster.stockid,
					   stockmaster.description,
					   stockmaster.mbflag,
					   stockmaster.decimalplaces,
						stockmaster.actualcost as computedcost
				FROM mrpplannedorders
				INNER JOIN stockmaster
					ON mrpplannedorders.part = stockmaster.stockid
				WHERE stockmaster.mbflag = 'M' " . $WhereDate . "
				ORDER BY mrpplannedorders.part,mrpplannedorders.duedate";
	} elseif ($_POST['Consolidation'] == 'Weekly') {
		$SQL = "SELECT mrpplannedorders.part,
					   SUM(mrpplannedorders.supplyquantity) as supplyquantity,
					   TRUNCATE(((TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
					   MIN(mrpplannedorders.duedate) as duedate,
					   MIN(mrpplannedorders.mrpdate) as mrpdate,
					   COUNT(*) AS consolidatedcount,
					   stockmaster.stockid,
					   stockmaster.description,
					   stockmaster.mbflag,
					   stockmaster.decimalplaces,
						stockmaster.actualcost as computedcost
				FROM mrpplannedorders
				INNER JOIN stockmaster
					ON mrpplannedorders.part = stockmaster.stockid
				WHERE stockmaster.mbflag = 'M' " . $WhereDate . "
				GROUP BY mrpplannedorders.part,
						 weekindex,
						 stockmaster.stockid,
						 stockmaster.description,
						 stockmaster.mbflag,
						 stockmaster.decimalplaces,
						 stockmaster.actualcost,
						 computedcost
				ORDER BY mrpplannedorders.part,weekindex";
	} else {  // This else consolidates by month
		$SQL = "SELECT mrpplannedorders.part,
					   SUM(mrpplannedorders.supplyquantity) as supplyquantity,
					   EXTRACT(YEAR_MONTH from duedate) AS yearmonth,
					   MIN(mrpplannedorders.duedate) as duedate,
					   MIN(mrpplannedorders.mrpdate) as mrpdate,
					   COUNT(*) AS consolidatedcount,
					   	stockmaster.stockid,
					   stockmaster.description,
					   stockmaster.mbflag,
					   stockmaster.decimalplaces,
						stockmaster.actualcost as computedcost
				FROM mrpplannedorders
				INNER JOIN stockmaster
					ON mrpplannedorders.part = stockmaster.stockid
				WHERE stockmaster.mbflag = 'M' " . $WhereDate . "
				GROUP BY mrpplannedorders.part,
						 yearmonth,
					   	 stockmaster.stockid,
						 stockmaster.description,
						 stockmaster.mbflag,
						 stockmaster.decimalplaces,
						 stockmaster.actualcost,
						 computedcost
				ORDER BY mrpplannedorders.part,yearmonth";
	}
	$Result = DB_query($SQL,'','',false,true);

	if (DB_error_no() !=0) {
	  $Title = _('MRP Planned Work Orders') . ' - ' . _('Problem Report');
	  include('includes/header.php');
	   prnMsg( _('The MRP planned work orders could not be retrieved by the SQL because') . ' '  . DB_error_msg(),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
		  echo '<br />' . $SQL;
	   }
	   include('includes/footer.php');
	   exit;
	}

	if (DB_num_rows($Result)==0){ //then there is nothing to print
		$Title = _('MRP Planned Work Orders');
		include('includes/header.php');
		prnMsg(_('There were no items with demand greater than supply'),'info');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit;
	}

	if (isset($_POST['PrintPDF'])) { // Print planned work orders

		include('includes/PDFStarter.php');

		$PDF->addInfo('Title',_('MRP Planned Work Orders Report'));
		$PDF->addInfo('Subject',_('MRP Planned Work Orders'));

		$FontSize=9;
		$PageNumber=1;
		$line_height=12;
		$Xpos = $Left_Margin+1;

		PrintHeader($PDF,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					$Page_Width,$Right_Margin,$_POST['Consolidation'],$ReportDate);

		$Partctr = 0;
		$fill = false;
		$PDF->SetFillColor(224,235,255);  // Defines color to make alternating lines highlighted
		$FontSize=8;
		$HoldPart = ' ';
		$HoldDescription = ' ';
		$HoldMBFlag = ' ';
		$HoldCost = ' ';
		$HoldDecimalPlaces = 0;
		$TotalPartQty = 0;
		$TotalPartCost = 0;
		$Total_ExtCost = 0;

		while ($MyRow = DB_fetch_array($Result)){
				$YPos -=$line_height;

				// Print information on part break
				if ($Partctr > 0 AND $HoldPart != $MyRow['part']) {
					$PDF->addTextWrap(50,$YPos,130,$FontSize,$HoldDescription,'',0,$fill);
					$PDF->addTextWrap(180,$YPos,50,$FontSize,_('Unit Cost: '),'center',0,$fill);
					$PDF->addTextWrap(220,$YPos,40,$FontSize,locale_number_format($HoldCost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);
					$PDF->addTextWrap(260,$YPos,50,$FontSize,locale_number_format($TotalPartQty, $HoldDecimalPlaces),'right',0,$fill);
					$PDF->addTextWrap(310,$YPos,60,$FontSize,locale_number_format($TotalPartCost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);
					$PDF->addTextWrap(370,$YPos,30,$FontSize,_('M/B: '),'right',0,$fill);
					$PDF->addTextWrap(400,$YPos,15,$FontSize,$HoldMBFlag,'right',0,$fill);
					$TotalPartCost = 0;
					$TotalPartQty = 0;
					$YPos -= (2*$line_height);

					// Use to alternate between lines with transparent and painted background
					if ($_POST['Fill'] == 'yes'){
						$fill=!$fill;
					}
				}

				// Parameters for addTextWrap are defined in /includes/class.pdf.php
				$FormatedSupDueDate = ConvertSQLDate($MyRow['duedate']);
				$FormatedSupMRPDate = ConvertSQLDate($MyRow['mrpdate']);
				$ExtCost = $MyRow['supplyquantity'] * $MyRow['computedcost'];
				$PDF->addTextWrap($Left_Margin,$YPos,110,$FontSize,$MyRow['part'],'',0,$fill);
				$PDF->addTextWrap(150,$YPos,50,$FontSize,$FormatedSupDueDate,'right',0,$fill);
				$PDF->addTextWrap(200,$YPos,60,$FontSize,$FormatedSupMRPDate,'right',0,$fill);
				$PDF->addTextWrap(260,$YPos,50,$FontSize,locale_number_format($MyRow['supplyquantity'], $MyRow['decimalplaces']),'right',0,$fill);
				$PDF->addTextWrap(310,$YPos,60,$FontSize,locale_number_format($ExtCost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);

				if ($_POST['Consolidation'] == 'None'){
					$PDF->addTextWrap(370,$YPos,80,$FontSize,$MyRow['ordertype'],'right',0,$fill);
					$PDF->addTextWrap(450,$YPos,80,$FontSize,$MyRow['orderno'],'right',0,$fill);
				} else {
					$PDF->addTextWrap(370,$YPos,100,$FontSize,$MyRow['consolidatedcount'],'right',0,$fill);
				}

				$HoldDescription = $MyRow['description'];
				$HoldPart = $MyRow['part'];
				$HoldMBFlag = $MyRow['mbflag'];
				$HoldCost = $MyRow['computedcost'];
				$HoldDecimalPlaces = $MyRow['decimalplaces'];
				$TotalPartCost += $ExtCost;
				$TotalPartQty += $MyRow['supplyquantity'];

				$Total_ExtCost += $ExtCost;
				$Partctr++;

				if ($YPos < $Bottom_Margin + $line_height){
				   PrintHeader($PDF,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
							   $Right_Margin,$_POST['Consolidation'],$ReportDate);
				}
		} /*end while loop */

		// Print summary information for last part
		$YPos -=$line_height;
		$PDF->addTextWrap(50,$YPos,130,$FontSize,$HoldDescription,'',0,$fill);
		$PDF->addTextWrap(180,$YPos,50,$FontSize,_('Unit Cost: '),'center',0,$fill);
		$PDF->addTextWrap(220,$YPos,40,$FontSize,locale_number_format($HoldCost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);
		$PDF->addTextWrap(260,$YPos,50,$FontSize,locale_number_format($TotalPartQty,$HoldDecimalPlaces),'right',0,$fill);
		$PDF->addTextWrap(310,$YPos,60,$FontSize,locale_number_format($TotalPartCost,$_SESSION['CompanyRecord']['decimalplaces']),'right',0,$fill);
		$PDF->addTextWrap(370,$YPos,30,$FontSize,_('M/B: '),'right',0,$fill);
		$PDF->addTextWrap(400,$YPos,15,$FontSize,$HoldMBFlag,'right',0,$fill);
		$FontSize =8;
		$YPos -= (2*$line_height);

		if ($YPos < $Bottom_Margin + $line_height){
			   PrintHeader($PDF,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
						   $Right_Margin,$_POST['Consolidation'],$ReportDate);
		}

		/*Print out the grand totals */
		$PDF->addTextWrap($Left_Margin,$YPos,120,$FontSize,_('Number of Work Orders: '), 'left');
		$PDF->addTextWrap(150,$YPos,30,$FontSize,$Partctr, 'left');
		$PDF->addTextWrap(200,$YPos,100,$FontSize,_('Total Extended Cost:'), 'right');
		$DisplayTotalVal = locale_number_format($Total_ExtCost,$_SESSION['CompanyRecord']['decimalplaces']);
		$PDF->addTextWrap(310,$YPos,60,$FontSize,$DisplayTotalVal, 'right');

		$PDF->OutputD($_SESSION['DatabaseName'] . '_MRP_Planned_Work_Orders_' . Date('Y-m-d') . '.pdf');
		$PDF->__destruct();

	} else { // Review planned work orders

		$Title = _('Review/Convert MRP Planned Work Orders');
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

		echo '<form action="MRPConvertWorkOrders.php" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">
			<tr>
				<th colspan="9">
					<h3>' . _('Consolidation') . ': ' . $_POST['Consolidation'] . '&nbsp;&nbsp;&nbsp;&nbsp;'
						  . _('Cutoff Date') . ': ' . $_POST['cutoffdate'] . '</h3>
				</th>
			</tr>
			<tr>
				<th></th>
				<th>' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('MRP Date') . '</th>
				<th>' . _('Due Date') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Unit Cost') . '</th>
				<th>' . _('Ext. Cost') . '</th>';
			if ($_POST['Consolidation'] != 'None') {
				echo '<th>' . _('Consolidations') . '</th>';
			}
		echo '</tr>';

		$TotalPartQty = 0;
		$TotalPartCost = 0;
		$Total_ExtCost = 0;
		$j=1; //row ID

		while ($MyRow = DB_fetch_array($Result)){

			echo '<tr class="striped_row">
				<td><a href="' . $RootPath . '/WorkOrderEntry.php?NewItem=' . urlencode($MyRow['part']) . '&amp;ReqQty=' . urlencode($MyRow['supplyquantity']) . '&amp;ReqDate=' . urlencode($MyRow['duedate']) . '&amp;StartDate=' . urlencode($MyRow['mrpdate']) . '">' . _('Convert') . '</a></td>
				<td>' . '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . urlencode($MyRow['part']) . '">' . $MyRow['part'] . '</a>' .  '<input type="hidden" name="' . $j . '_part" value="' . $MyRow['part']. '" /></td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . ConvertSQLDate($MyRow['mrpdate']) . '</td>
				<td>' . ConvertSQLDate($MyRow['duedate']) . '</td>
				<td class="number">' . locale_number_format($MyRow['supplyquantity'],$MyRow['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['computedcost'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($MyRow['supplyquantity'] * $MyRow['computedcost'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>';

			if ($_POST['Consolidation'] != 'None') {
				echo '<td class="number">' . $MyRow['consolidatedcount'] . '</td>';
			}
			echo '</tr>';

			$j++;
			$Total_ExtCost += ( $MyRow['supplyquantity'] * $MyRow['computedcost'] );

		} // end while loop

		// Print out the grand totals
		echo '<tr>
				<td colspan="3" class="number">' . _('Number of Work Orders') .': ' . ($j-1) . '</td>
				<td colspan="5" class="number">' . _('Total Extended Cost') . ': ' . locale_number_format($Total_ExtCost,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>
			</table>
			</form>';

		echo '<br /><a class="noPrint" href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', _('Select different criteria.'), '</a>';
		include('includes/footer.php');

	} // end Review planned work orders
} else { /*The option to print PDF was not hit so display form */

	$Title=_('MRP Planned Work Orders Reporting');
	$ViewTopic = 'MRP';
	$BookMark = '';
	include('includes/header.php');
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="Consolidation">' . _('Consolidation') . ':</label>
				<select required="required" name="Consolidation">
					<option selected="selected" value="None">' . _('None') . '</option>
					<option value="Weekly">' . _('Weekly') . '</option>
					<option value="Monthly">' . _('Monthly') . '</option>
				</select>
			 </field>
			 <field>
				<label for="Fill">' . _('Print Option') . ':</label>
				<select name="Fill">
					<option selected="selected" value="yes">' . _('Print With Alternating Highlighted Lines') . '</option>
					<option value="no">' . _('Plain Print') . '</option>
				</select>
			 </field>
			 <field>
				<label for="cutoffdate">' . _('Cut Off Date') . ':</label>
				<input required="required" type="date" name="cutoffdate" autofocus="autofocus" maxlength="10" size="11" value="' . date('Y-m-d') . '" />
			</field>
			 </fieldset>
			 <div class="centre">
				  <input type="submit" name="Review" value="' . _('Review') . '" /> <input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
			 </div>
		  </form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */

function PrintHeader(&$PDF,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin,$consolidation,$ReportDate) {

	/*PDF page header for MRP Planned Work Orders report */
	if ($PageNumber>1){
		$PDF->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;

	$PDF->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$line_height;

	$PDF->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('MRP Planned Work Orders Report'));
	$PDF->addTextWrap(190,$YPos,100,$FontSize,$ReportDate);
	$PDF->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,_('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -= $line_height;

	if ($consolidation == 'None') {
		$displayconsolidation = _('None');
	} elseif ($consolidation == 'Weekly') {
		$displayconsolidation = _('Weekly');
	} else {
		$displayconsolidation = _('Monthly');
	}

	$PDF->addTextWrap($Left_Margin,$YPos,65,$FontSize,_('Consolidation').':');
	$PDF->addTextWrap(110,$YPos,40,$FontSize,$displayconsolidation);

	$YPos -=(2*$line_height);

	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$PDF->addTextWrap($Xpos,$YPos,150,$FontSize,_('Part Number'), 'left');
	$PDF->addTextWrap(150,$YPos,50,$FontSize,_('Due Date'), 'right');
	$PDF->addTextWrap(200,$YPos,60,$FontSize,_('MRP Date'), 'right');
	$PDF->addTextWrap(260,$YPos,50,$FontSize,_('Quantity'), 'right');
	$PDF->addTextWrap(310,$YPos,60,$FontSize,_('Ext. Cost'), 'right');

	if ($consolidation == 'None') {
		$PDF->addTextWrap(370,$YPos,80,$FontSize,_('Source Type'), 'right');
		$PDF->addTextWrap(450,$YPos,80,$FontSize,_('Source Order'), 'right');
	} else {
		$PDF->addTextWrap(370,$YPos,100,$FontSize,_('Consolidation Count'), 'right');
	}

	$FontSize=8;
	$YPos =$YPos - (2*$line_height);
	$PageNumber++;
} // End of PrintHeader function
?>