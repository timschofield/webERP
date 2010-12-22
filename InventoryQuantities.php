<?php

/* $Revision: 1.4 $ */

/* $Id$ */

// InventoryQuantities.php - Report of parts with quantity. Sorts by part and shows
// all locations where there are quantities of the part
//$PageSecurity = 2;
include('includes/session.inc');
If (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Inventory Quantities Report'));
	$pdf->addInfo('Subject',_('Parts With Quantities'));
	$FontSize=9;
	$PageNumber=1;
	$line_height=12;

	$Xpos = $Left_Margin+1;
	$wherecategory = " ";
	$catdescription = " ";
	if ($_POST['StockCat'] != 'All') {
	    $wherecategory = " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' AND stockcategory.stocktype<>'A'";
		$sql= "SELECT categoryid, categorydescription FROM stockcategory WHERE categoryid='" . $_POST['StockCat'] . "' ";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		$catdescription = $myrow[1];
	}

    if ($_POST['Selection'] == 'All') {
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
					stockmaster
			LEFT JOIN stockcategory
			ON stockmaster.categoryid=stockcategory.categoryid,
					locations
				WHERE locstock.stockid=stockmaster.stockid
				AND locstock.loccode=locations.loccode
				AND locstock.quantity <> 0
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " .
				$wherecategory . " ORDER BY locstock.stockid,locstock.loccode";
	} else {
		// sql to only select parts in more than one location
		// The SELECT statement at the beginning of the WHERE clause limits the selection to
		// parts with quantity in more than one location
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
				WHERE (SELECT count(*)
					  FROM locstock
					  WHERE stockmaster.stockid = locstock.stockid
					  AND locstock.quantity <> 0
					  GROUP BY locstock.stockid) > 1
				AND locstock.stockid=stockmaster.stockid
				AND locstock.loccode=locations.loccode
				AND locstock.quantity <> 0
				AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " .
				$wherecategory . " ORDER BY locstock.stockid,locstock.loccode";
	}


	$result = DB_query($sql,$db,'','',false,true);

	if (DB_error_no($db) !=0) {
	  $title = _('Inventory Quantities') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	   prnMsg( _('The Inventory Quantity report could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
	   echo "<br><a href='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
	   if ($debug==1){
	      echo "<br>$sql";
	   }
	   include('includes/footer.inc');
	   exit;
	}
	if (DB_num_rows($result)==0){
			$title = _('Print Inventory Quantities Report');
			include('includes/header.inc');
			prnMsg(_('There were no items with inventory quantities'),'error');
			echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
			include('includes/footer.inc');
			exit;
	}

	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
	            $Page_Width,$Right_Margin,$catdescription);

    $FontSize=8;

    $holdpart = " ";
	While ($myrow = DB_fetch_array($result,$db)){
	      if ($myrow['stockid'] != $holdpart) {
			  $YPos -=(2 * $line_height);
			  $holdpart = $myrow['stockid'];
		  } else {
	          $YPos -=($line_height);
		  }

			// Parameters for addTextWrap are defined in /includes/class.pdf.php
			// 1) X position 2) Y position 3) Width
			// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
			// and False to set to transparent

				$pdf->addTextWrap(50,$YPos,100,$FontSize,$myrow['stockid'],'',0);
				$pdf->addTextWrap(150,$YPos,150,$FontSize,$myrow['description'],'',0);
				$pdf->addTextWrap(310,$YPos,60,$FontSize,$myrow['loccode'],'left',0);
				$pdf->addTextWrap(370,$YPos,50,$FontSize,number_format($myrow['quantity'],
				                                    $myrow['decimalplaces']),'right',0);
				$pdf->addTextWrap(420,$YPos,50,$FontSize,number_format($myrow['reorderlevel'],
				                                    $myrow['decimalplaces']),'right',0);

			if ($YPos < $Bottom_Margin + $line_height){
			   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
			               $Right_Margin,$catdescription);
			}
	} /*end while loop */

	if ($YPos < $Bottom_Margin + $line_height){
	       PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$catdescription);
	}
/*Print out the grand totals */

	$pdf->OutputD($_SESSION['DatabaseName'] . '_Inventory_Quantities_' . Date('Y-m-d') . '.pdf');
	$pdf->__destruct();
} else { /*The option to print PDF was not hit so display form */

	$title=_('Inventory Quantities Reporting');
	include('includes/header.inc');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="">' . ' ' . _('Inventory Quantities Report') . '';
echo '<div class="page_help_text">' . _('Use this report to display the quantity of Inventory items in different categories.') . '</div><br>';


	echo '</br></br><form action=' . $_SERVER['PHP_SELF'] . " method='post'><table>";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class=selection><tr><td>';
	echo '<tr><td>' . _('Selection') . ":</td><td><select name='Selection'>";
	echo "<option selected value='All'>" . _('All');
	echo "<option value='Multiple'>" . _('Only Parts With Multiple Locations');
	echo '</select></td></tr>';

	$SQL="SELECT categoryid, categorydescription FROM stockcategory where stocktype<>'A' ORDER BY categorydescription";
	$result1 = DB_query($SQL,$db);
	if (DB_num_rows($result1)==0){
		echo '</table></td></tr>
			</table>
			<p>';
		prnMsg(_('There are no stock categories currently defined please use the link below to set them up'),'warn');
		echo '<br><a href="' . $rootpath . '/StockCategories.php?' . SID .'">' . _('Define Stock Categories') . '</a>';
		include ('includes/footer.inc');
		exit;
	}

	echo '<tr><td>' . _('In Stock Category') . ':</td><td><select name="StockCat">';
	if (!isset($_POST['StockCat'])){
		$_POST['StockCat']='All';
	}
	if ($_POST['StockCat']=='All'){
		echo '<option selected value="All">' . _('All');
	} else {
		echo '<option value="All">' . _('All');
	}
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['categoryid']==$_POST['StockCat']){
			echo '<option selected value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
		} else {
			echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
		}
	}
	echo '</select></td></tr>';
	echo "</table><p><div class='centre'><input type=submit name='PrintPDF' value='" . _('Print PDF') . "'></div>";

	include('includes/footer.inc');

} /*end of else not PrintPDF */

function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
                     $Page_Width,$Right_Margin,$catdescription) {

	/*PDF page header for Reorder Level report */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$line_height;

	$pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('Inventory Quantities Report'));
	$pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,_('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Category'));
	$pdf->addTextWrap(95,$YPos,50,$FontSize,$_POST['StockCat']);
	$pdf->addTextWrap(160,$YPos,150,$FontSize,$catdescription,'left');
	$YPos -=(2*$line_height);

	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$pdf->addTextWrap(50,$YPos,100,$FontSize,_('Part Number'), 'left');
	$pdf->addTextWrap(150,$YPos,150,$FontSize,_('Description'), 'left');
	$pdf->addTextWrap(310,$YPos,60,$FontSize,_('Location'), 'left');
	$pdf->addTextWrap(370,$YPos,50,$FontSize,_('Quantity'), 'right');
	$pdf->addTextWrap(420,$YPos,50,$FontSize,_('Reorder'), 'right');
	$YPos -=$line_height;
	$pdf->addTextWrap(415,$YPos,50,$FontSize,_('Level'), 'right');


	$FontSize=8;
//	$YPos =$YPos - (2*$line_height);
	$PageNumber++;
} // End of PrintHeader() function
?>