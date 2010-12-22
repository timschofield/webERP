<?php
/* $Revision: 1.6 $ */
/* $Id$*/
// MRPPlannedWorkOrders.php - Report of manufactured parts that MRP has determined should have
// work orders created for them
//$PageSecurity = 2;
include('includes/session.inc');
$sql='show tables where Tables_in_'.$_SESSION['DatabaseName'].'="mrprequirements"';
$result=DB_query($sql,$db);
if (DB_num_rows($result)==0) {
	$title='MRP error';
	include('includes/header.inc');
	echo '<br>';
	prnMsg( _('The MRP calculation must be run before you can run this report').'<br>'.
			_('To run the MRP calculation click').' '.'<a href='.$rootpath .'/MRP.php?' . SID .'>'._('here').'</a>', 'error');
	include('includes/footer.inc');
	exit;
}
if ( isset($_POST['PrintPDF']) OR isset($_POST['Review']) ) {

	$wheredate = " ";
	$reportdate = " ";
	if (is_Date($_POST['cutoffdate'])) {
		$formatdate = FormatDateForSQL($_POST['cutoffdate']);
		$wheredate = ' AND duedate <= "' . $formatdate . '" ';
		$reportdate = _(' Through  ') . $_POST['cutoffdate'];
	}

	if ($_POST['Consolidation'] == 'None') {
		$sql = 'SELECT mrpplannedorders.*,
					   stockmaster.stockid,
					   stockmaster.description,
					   stockmaster.mbflag,
					   stockmaster.decimalplaces,
					   stockmaster.actualcost,
					  (stockmaster.materialcost + stockmaster.labourcost +
					   stockmaster.overheadcost ) as computedcost
				FROM mrpplannedorders, stockmaster
				WHERE mrpplannedorders.part = stockmaster.stockid '  . "$wheredate" .
				  ' AND stockmaster.mbflag = "M"
				ORDER BY mrpplannedorders.part,mrpplannedorders.duedate';
	} elseif ($_POST['Consolidation'] == 'Weekly') {
		$sql = 'SELECT mrpplannedorders.part,
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
				WHERE mrpplannedorders.part = stockmaster.stockid '  . "$wheredate" .
				  ' AND stockmaster.mbflag = "M"
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
				ORDER BY mrpplannedorders.part,weekindex
		';
	} else {
		$sql = 'SELECT mrpplannedorders.part,
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
					   stockmaster.overheadcost ) as computedcost,
				FROM mrpplannedorders, stockmaster
				WHERE mrpplannedorders.part = stockmaster.stockid '  . "$wheredate" .
				  ' AND stockmaster.mbflag = "M"
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
				ORDER BY mrpplannedorders.part,yearmonth ';
	};
	$result = DB_query($sql,$db,'','',false,true);

	if (DB_error_no($db) !=0) {
	  $title = _('MRP Planned Work Orders') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	   prnMsg( _('The MRP planned work orders could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
	   echo "<br><a href='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
	   if ($debug==1){
		  echo "<br>$sql";
	   }
	   include('includes/footer.inc');
	   exit;
	}
	if (DB_num_rows($result)==0){ //then there's nothing to print
		$title = _('MRP Planned Work Orders');
		include('includes/header.inc');
		prnMsg(_('There were no items with demand greater than supply'),'info');
		echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	}



	if (isset($_POST['PrintPDF'])) { // Print planned work orders

		include('includes/PDFStarter.php');

		$pdf->addInfo('Title',_('MRP Planned Work Orders Report'));
		$pdf->addInfo('Subject',_('MRP Planned Work Orders'));

		$FontSize=9;
		$PageNumber=1;
		$line_height=12;
		$Xpos = $Left_Margin+1;

		PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					$Page_Width,$Right_Margin,$_POST['Consolidation'],$reportdate);

		$Total_EXTcost=0;
		$Partctr = 0;
		$fill = false;
		$pdf->SetFillColor(224,235,255);  // Defines color to make alternating lines highlighted
		$FontSize=8;
		$holdpart = " ";
		$holddescription = " ";
		$holdmbflag = " ";
		$holdcost = " ";
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
					$pdf->addTextWrap(180,$YPos,40,$FontSize,_('Unit Cost: '),'center',0,$fill);
					$pdf->addTextWrap(220,$YPos,40,$FontSize,number_format($holdcost,2),'right',0,$fill);
					$pdf->addTextWrap(260,$YPos,50,$FontSize,number_format($totalpartqty,
														$holddecimalplaces),'right',0,$fill);
					$pdf->addTextWrap(310,$YPos,60,$FontSize,number_format($totalpartcost,2),'right',0,$fill);
					$pdf->addTextWrap(370,$YPos,30,$FontSize,_('M/B: '),'right',0,$fill);
					$pdf->addTextWrap(400,$YPos,15,$FontSize,$holdmbflag,'right',0,$fill);
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
				$pdf->addTextWrap(260,$YPos,50,$FontSize,number_format($myrow['supplyquantity'],
														  $myrow['decimalplaces']),'right',0,$fill);
				$pdf->addTextWrap(310,$YPos,60,$FontSize,number_format($extcost,2),'right',0,$fill);
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
							   $Right_Margin,$_POST['Consolidation'],$reportdate);
				  // include('includes/MRPPlannedWorkOrdersPageHeader.inc');
				}

		} /*end while loop */
		// Print summary information for last part
		$YPos -=$line_height;
		$pdf->addTextWrap(40,$YPos,130,$FontSize,$holddescription,'',0,$fill);
		$pdf->addTextWrap(170,$YPos,50,$FontSize,_('Unit Cost: '),'center',0,$fill);
		$pdf->addTextWrap(220,$YPos,40,$FontSize,number_format($holdcost,2),'right',0,$fill);
		$pdf->addTextWrap(260,$YPos,50,$FontSize,number_format($totalpartqty,$holddecimalplaces),'right',0,$fill);
		$pdf->addTextWrap(310,$YPos,60,$FontSize,number_format($totalpartcost,2),'right',0,$fill);
		$pdf->addTextWrap(370,$YPos,30,$FontSize,_('M/B: '),'right',0,$fill);
		$pdf->addTextWrap(400,$YPos,15,$FontSize,$holdmbflag,'right',0,$fill);
		$FontSize =8;
		$YPos -= (2*$line_height);

		if ($YPos < $Bottom_Margin + $line_height){
			   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
						   $Right_Margin,$_POST['Consolidation'],$reportdate);
			  // include('includes/MRPPlannedWorkOrdersPageHeader.inc');
		}
		/*Print out the grand totals */
		$pdf->addTextWrap($Left_Margin,$YPos,120,$FontSize,_('Number of Work Orders: '), 'left');
		$pdf->addTextWrap(150,$YPos,30,$FontSize,$Partctr, 'left');
		$pdf->addTextWrap(200,$YPos,100,$FontSize,_('Total Extended Cost:'), 'right');
		$DisplayTotalVal = number_format($Total_Extcost,2);
		$pdf->addTextWrap(310,$YPos,60,$FontSize,$DisplayTotalVal, 'right');

		$pdf->OutputD($_SESSION['DatabaseName'] . '_MRP_Planned_Work_Orders_' . Date('Y-m-d') . '.pdf');
		$pdf->__destruct();



	} else { // Review planned work orders

		$title = _('Review/Convert MRP Planned Work Orders');
		include('includes/header.inc');
		echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' .
			_('Inventory') . '" alt="">' . ' ' . $title . '</p>';

		echo "<form action='MRPConvertWorkOrders.php' method='post'>";
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo "<table class=selection>";
		echo "<tr><th colspan=9><font size=3 color=blue>Consolidation: " . $_POST['Consolidation'] .
			"&nbsp;&nbsp;&nbsp;&nbsp;Cutoff Date: " . $_POST['cutoffdate'] . "</font></th></tr>";
		echo "<tr><th></th>
				<th>" . _('Code') . "</th>
				<th>" . _('Description') . "</th>
				<th>" . _('MRP Date') . "</th>
				<th>" . _('Due Date') . "</th>
				<th>" . _('Quantity') . "</th>
				<th>" . _('Unit Cost') . "</th>
				<th>" . _('Ext. Cost') . "</th>
				<th>" . _('Consolidations') . "</th>
			</tr>";

		$totalpartqty = 0;
		$totalpartcost = 0;
		$Total_Extcost = 0;
		$j=1; //row ID
		$k=0; //row colour counter
		While ($myrow = DB_fetch_array($result,$db)){

			// Alternate row color
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			printf("\n <td><a href='%s/WorkOrderEntry.php?NewItem=%s&ReqQty=%s&ReqDate=%s'>%s</a></td>",
				$rootpath,
				$myrow['part'],
				$myrow['supplyquantity'],
				ConvertSQLDate($myrow['duedate']),
				_('Convert')
			);
			printf("\n <td>%s <input type='hidden' name='%s_part' value='%s' /></td>", $myrow['part'], $j, $myrow['part']);
			printf("\n <td>%s</td>", $myrow['description']);
			printf("\n <td>%s</td>", ConvertSQLDate($myrow['mrpdate']));
			printf("\n <td>%s</td>", ConvertSQLDate($myrow['duedate']));
			printf("\n <td class=number>%s</td>", number_format($myrow['supplyquantity'],$myrow['decimalplaces']));
			printf("\n <td class=number>%.2f</td>", number_format($myrow['computedcost'],2));
			printf("\n <td class=number>%.2f</td>", number_format($myrow['supplyquantity'] * $myrow['computedcost'],2));
			if ($_POST['Consolidation']!='None') {
				printf("\n <td class=number>%s</td>", $myrow['consolidatedcount']);
			}
			echo "</tr>";

			$j++;
			$Total_Extcost += ( $myrow['supplyquantity'] * $myrow['computedcost'] );

		} // end while loop

		// Print out the grand totals
		printf("
			<tr><td colspan='4' class='number'>%s %s</td>
			<td colspan='4' class='number'>%s %s</td></tr></table>
			",
			_('Number of Work Orders: '),
			$j-1,
			_('Total Extended Cost: '),
			number_format($Total_Extcost,2)
		);

		echo "<br /><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
		include('includes/footer.inc');

	}



} else { /*The option to print PDF was not hit so display form */

	$title=_('MRP Planned Work Orders Reporting');
	include('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' .
		_('Inventory') . '" alt="">' . ' ' . $title . '</p>';

	echo '</br></br><form action=' . $_SERVER['PHP_SELF'] . " method='post'><table class=selection>";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<tr><td>' . _('Consolidation') . ":</td><td><select name='Consolidation'>";
	echo "<option selected value='None'>" . _('None');
	echo "<option value='Weekly'>" . _('Weekly');
	echo "<option value='Monthly'>" . _('Monthly');
	echo '</select></td></tr>';
	echo '<tr><td>' . _('Print Option') . ":</td><td><select name='Fill'>";
	echo "<option selected value='yes'>" . _('Print With Alternating Highlighted Lines');
	echo "<option value='no'>" . _('Plain Print');
	echo '</select></td></tr>';
	echo '<tr><td>' . _('Cut Off Date') . ":</td><td><input type ='text' class=date alt='".$_SESSION['DefaultDateFormat'] ."' name='cutoffdate' size='10' value='".date($_SESSION['DefaultDateFormat'])."'></tr>";
	echo "</table><p><div class='centre'><input type=submit name='Review' value='" . _('Review') . "'> <input type=submit name='PrintPDF' value='" . _('Print PDF') . "'></div>";

	include('includes/footer.inc');

} /*end of else not PrintPDF */

function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin,$consolidation,$reportdate) {

	/*PDF page header for MRP Planned Work Orders report */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$line_height;

	$pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('MRP Planned Work Orders Report'));
	$pdf->addTextWrap(190,$YPos,100,$FontSize,$reportdate);
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
} // End of PrintHeader() function
?>