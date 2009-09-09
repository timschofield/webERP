<?php
/* $Revision: 1.1 $ */
// StockDispatch.php - Report of parts with quantity below reorder level
// Shows other locations with overstock for those parts that can be transferred
$PageSecurity = 2;
include('includes/session.inc');
If (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
    if (!is_numeric($_POST['Percent'])) {
        $_POST['Percent'] = 0;
    }
	$FontSize=9;
	$pdf->addinfo('Title',_('Stock Dispatch Report'));
	$pdf->addinfo('Subject',_('Parts to dispatch to other locations to cover reorder level'));

	$PageNumber=1;
	$line_height=12;
	$Xpos = $Left_Margin+1;
	$wherecategory = " ";
	$catdescription = " ";
	if ($_POST['StockCat'] != 'All') {
	    $wherecategory = " AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
		$sql= "SELECT categoryid, categorydescription FROM stockcategory WHERE categoryid='" . $_POST['StockCat'] . "' ";
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		$catdescription = $myrow[1];
	}
	$wherelocation = " ";
	if ($_POST['StockLocation'] != 'All') {
	    $wherelocation = " AND locstock.loccode='" . $_POST['StockLocation'] . "' ";
	}
	
	$sql = 'SELECT locstock.stockid,
				stockmaster.description,
				locstock.loccode,
				locations.locationname,
				locstock.quantity,
				locstock.reorderlevel,
				stockmaster.decimalplaces,
				stockmaster.serialised,
				stockmaster.controlled,
				ROUND((locstock.reorderlevel - locstock.quantity) *
            	   (1 + (' . $_POST['Percent'] . '/100)))
            	as neededqty
			FROM locstock, 
				stockmaster, 
				locations
			WHERE locstock.stockid=stockmaster.stockid ' .
			$wherelocation . 
			"AND locstock.loccode=locations.loccode
			AND locstock.reorderlevel > locstock.quantity
			AND (stockmaster.mbflag='B' OR stockmaster.mbflag='M') " .
			$wherecategory . ' ORDER BY locstock.loccode,locstock.stockid';
			
	$result = DB_query($sql,$db,'','',false,true);

	if (DB_error_no($db) !=0) {
	  $title = _('Stock Dispatch') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	   prnMsg( _('The Stock Dispatch report could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
	   echo "<br><a href='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
	   if ($debug==1){
	      echo "<br>$sql";
	   }
	   include('includes/footer.inc');
	   exit;
	}

	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
	            $Page_Width,$Right_Margin,$catdescription);

    $FontSize=8;

    $holdpart = "XXXXXXXX";
	While ($myrow = DB_fetch_array($result,$db)){
			$YPos -=(2 * $line_height);
			// Parameters for addTextWrap are defined in /includes/class.pdf.php
			// 1) X position 2) Y position 3) Width
			// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
			// and False to set to transparent

				$pdf->addTextWrap(50,$YPos,100,$FontSize,$myrow['stockid'],'',0,$fill);
				$pdf->addTextWrap(150,$YPos,150,$FontSize,$myrow['description'],'',0,$fill);
				$pdf->addTextWrap(310,$YPos,60,$FontSize,$myrow['loccode'],'left',0,$fill);
				$pdf->addTextWrap(350,$YPos,40,$FontSize,number_format($myrow['quantity'],
				                                    $myrow['decimalplaces']),'right',0,$fill);
				$pdf->addTextWrap(400,$YPos,40,$FontSize,number_format($myrow['reorderlevel'],
				                                    $myrow['decimalplaces']),'right',0,$fill);
				$shortage = $myrow['reorderlevel'] - $myrow['quantity'];                                    			                                    
                $pdf->addTextWrap(450,$YPos,50,$FontSize,number_format($myrow['neededqty']),
				                                    'right',0,$fill);		
			if ($YPos < $Bottom_Margin + $line_height){
			   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
			               $Right_Margin,$catdescription);
			}

            	// Print if stock for part in other locations
            	// The available qty is set up as quantity - reorderlevel; if reorderlevel means
            	// the quantity at which you have to reorder, that means you have to keep at least
            	// one on hand higher than what the reorderlevel is, so would define available like:
//             	    IF(locstock.reorderlevel > 0,
//             	                  (locstock.quantity - locstock.reorderlevel) - 1,
//             	                  (locstock.quantity - locstock.reorderlevel))
//             	                  as available
            	
            	$sql2 = "SELECT locstock.quantity,
            	                locstock.loccode,
            	                locstock.stockid,
            	                locstock.reorderlevel,
            	                (locstock.quantity - locstock.reorderlevel)
            	                  as available,
            	                stockmaster.decimalplaces
            	         FROM locstock, stockmaster
            	         WHERE locstock.quantity > 0
            	         AND locstock.quantity > reorderlevel
            	         AND locstock.stockid = stockmaster.stockid 
            	         AND locstock.stockid ='" . $myrow['stockid'] .
            	         "' AND locstock.loccode !='" . $myrow['loccode'] . "'
            	         ORDER BY available DESC";
            	$otherresult = DB_query($sql2,$db,'','',false,true);
            	While ($myrow2 = DB_fetch_array($otherresult,$db)){
            	    if ($holdpart != $myrow2['stockid']) {
            	        $holdneeded = $myrow['neededqty'];
            	        $holdpart = $myrow2['stockid'];
            	    }
            	    // If have displayed enought records to satisfy the needed quantity,
            	    // don't display any more by using "continue" to break out of the
            	    // while loop for $sql2
            	    if ($holdneeded < 1) {
            	        continue;
            	    }
					$YPos -=$line_height;
					$displayqty = $myrow2['available'];
					if ($myrow['neededqty'] < $myrow2['available']) {
					    $displayqty = $myrow['neededqty'];
					}
					$holdneeded -= $displayqty;
					// Parameters for addTextWrap are defined in /includes/class.pdf.php
					// 1) X position 2) Y position 3) Width
					// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
					// and False to set to transparent
		
						$pdf->addTextWrap(310,$YPos,60,$FontSize,$myrow2['loccode'],'left',0,$fill);
						$pdf->addTextWrap(350,$YPos,40,$FontSize,number_format($myrow2['quantity'],
															$myrow2['decimalplaces']),'right',0,$fill);
						$pdf->addTextWrap(400,$YPos,40,$FontSize,number_format($myrow2['reorderlevel'],
				                                    $myrow2['decimalplaces']),'right',0,$fill);		
                        $pdf->addTextWrap(500,$YPos,50,$FontSize,number_format($displayqty),
				                                    'right',0,$fill);
					if ($YPos < $Bottom_Margin + $line_height){
					   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
								   $Right_Margin,$catdescription);
					}

				} /*end while loop for reading of inventory in other location*/

	} /*end while loop  */

	if ($YPos < $Bottom_Margin + $line_height){
	       PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$catdescription);
	}
/*Print out the grand totals */

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

	if ($len<=20){
			$title = _('Print Stock Dispatch Report');
			include('includes/header.inc');
			prnMsg(_('There were no items for the stock dispatch report'),'error');
			echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
			include('includes/footer.inc');
			exit;
	} else {
			header('Content-type: application/pdf');
			header("Content-Length: " . $len);
			header('Content-Disposition: inline; filename=StockDispatch.pdf');
			header('Expires: 0');
			header('Cache-Control: private, post-check=0, pre-check=0');
			header('Pragma: public');
	
			$pdf->Stream();
	}
	
} else { /*The option to print PDF was not hit so display form */

	$title=_('Stock Dispatch Report');
	include('includes/header.inc');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="">' . ' ' . _('Inventory Stock Dispatch Report') . '';
echo '<div class="page_help_text">' . _('Report of locations with overstock that can transfer to other locations below reorder level.') . '</div><br>';

	echo '<br/><br/><form action=' . $_SERVER['PHP_SELF'] . " method='post'><table>";
	$sql = "SELECT loccode,
			locationname
		FROM locations";
	$resultStkLocs = DB_query($sql,$db);
	echo '<table><tr><td>' . _('Dispatch Percent') . ":</td><td><input type ='text' name='Percent' size='8'>";
	echo '<tr><td>' . _('From Stock Location') . ':</td><td><select name="StockLocation"> ';
	if (!isset($_POST['StockLocation'])){
		$_POST['StockLocation']='All';
	}
	if ($_POST['StockLocation']=='All'){
		echo '<option selected value="All">' . _('All');
	} else {
		echo '<option value="All">' . _('All');
	}
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if ($myrow['loccode'] == $_POST['StockLocation']){
			 echo '<option selected Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		} else {
			 echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
		}
	} 
	echo '</select></td></tr>';
	
	$SQL='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription';
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
	echo "</table><br/><br/><br/><div class='centre'><input type=submit name='PrintPDF' value='" . _('Print PDF') . "'></div>";

	include('includes/footer.inc');

} /*end of else not PrintPDF */

function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
                     $Page_Width,$Right_Margin,$catdescription) {

	/*PDF page header for Stock Dispatch report */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin-5;
	
	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	
	$YPos -=$line_height;
	
	$pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('Stock Dispatch Report'));	
	$pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,_('Printed') . ': ' . 
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Category'));
	$pdf->addTextWrap(95,$YPos,50,$FontSize,$_POST['StockCat']);
	$pdf->addTextWrap(160,$YPos,150,$FontSize,$catdescription,'left');
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Location'));
	$pdf->addTextWrap(95,$YPos,50,$FontSize,$_POST['StockLocation']);
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Percent'));
	$pdf->addTextWrap(95,$YPos,50,$FontSize,$_POST['Percent']);
	$YPos -=(2*$line_height);	
	/*set up the headings */
	$Xpos = $Left_Margin+1;
				
	$pdf->addTextWrap(50,$YPos,100,$FontSize,_('Part Number'), 'left');
	$pdf->addTextWrap(150,$YPos,150,$FontSize,_('Description'), 'left');
	$pdf->addTextWrap(310,$YPos,40,$FontSize,_('Location'), 'left');
	$pdf->addTextWrap(350,$YPos,40,$FontSize,_('On Hand'), 'right');
	$pdf->addTextWrap(400,$YPos,40,$FontSize,_('Reorder'), 'right');
	$pdf->addTextWrap(450,$YPos,50,$FontSize,_('Needed'), 'right');
	$pdf->addTextWrap(500,$YPos,50,$FontSize,_('Dispatch'), 'right');
	$YPos -= $line_height;
	$pdf->addTextWrap(390,$YPos,40,$FontSize,_('Level'), 'right');
    $pdf->addTextWrap(500,$YPos,50,$FontSize,_('Quantity'), 'right');

	$FontSize=8;
//	$YPos =$YPos - (2*$line_height);
	$PageNumber++;
} // End of PrintHeader() function
?>
