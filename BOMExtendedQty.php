<?php
/* $Revision: 1.4 $ */
// BOMExtendedQty.php - Quantiy Extended Bill of Materials
$PageSecurity = 2;
include('includes/session.inc');

If (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');

	$FontSize=9;
	$pdf->addinfo('Title',_('Quantiy Extended BOM Listing'));
	$pdf->addinfo('Subject',_('Quantiy Extended BOM Listing'));

	$PageNumber=1;
	$line_height=12;
	if (!$_POST['Quantity'] || !is_numeric($_POST['Quantity'])) {
	    $_POST['Quantity'] = 1;
	}

	$sql = 'DROP TABLE IF EXISTS tempbom';
	$result = DB_query($sql,$db);
	$sql = 'DROP TABLE IF EXISTS passbom';
	$result = DB_query($sql,$db);
	$sql = 'DROP TABLE IF EXISTS passbom2';
	$result = DB_query($sql,$db);
	$sql = 'CREATE TEMPORARY TABLE passbom (
				part char(20),
				extendedqpa double,
				sortpart text)';
	$ErrMsg = _('The SQL to to create passbom failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);

	$sql = 'CREATE TEMPORARY TABLE tempbom (
				parent char(20),                                
				component char(20),
				sortpart text,
				level int,
				workcentreadded char(5),
				loccode char(5),
				effectiveafter date,
				effectiveto date,
				quantity double)';
	$result = DB_query($sql,$db,_('Create of tempbom failed because'));
	// First, find first level of components below requested assembly
	// Put those first level parts in passbom, use COMPONENT in passbom
	// to link to PARENT in bom to find next lower level and accumulate
	// those parts into tempbom

	// This finds the top level
	$sql = 'INSERT INTO passbom (part, extendedqpa, sortpart)
			   SELECT bom.component AS part,
					  (' . $_POST['Quantity'] . ' * bom.quantity) as extendedqpa, 
					   CONCAT(bom.parent,bom.component) AS sortpart
					  FROM bom 
			  WHERE bom.parent =' . "'" . $_POST['Part'] . "' 
			  AND bom.effectiveto >= NOW() AND bom.effectiveafter <= NOW()";
	$result = DB_query($sql,$db);

	$levelctr = 2; 
	// $levelctr is the level counter
	$sql = 'INSERT INTO tempbom (
				parent, 
				component, 
				sortpart, 
				level,
				workcentreadded,
				loccode,
				effectiveafter,
				effectiveto,
				quantity)
			  SELECT bom.parent,
					 bom.component,
					 CONCAT(bom.parent,bom.component) AS sortpart,'
					 . $levelctr . ' as level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 (' . $_POST['Quantity'] . ' * bom.quantity) as extendedqpa
					 FROM bom 
			  WHERE bom.parent =' . "'" . $_POST['Part'] . "' 
			  AND bom.effectiveto >= NOW() AND bom.effectiveafter <= NOW()";
	$result = DB_query($sql,$db);
	//echo "</br>sql is $sql</br>";
	// This while routine finds the other levels as long as $componentctr - the
	// component counter finds there are more components that are used as
	// assemblies at lower levels

	$componentctr = 1;
	while ($componentctr > 0) {
		$levelctr++;
		$sql = "INSERT INTO tempbom (
				parent, 
				component, 
				sortpart, 
				level,
				workcentreadded,
				loccode,
				effectiveafter,
				effectiveto,
				quantity)
			  SELECT bom.parent,
					 bom.component,
					 CONCAT(passbom.sortpart,bom.component) AS sortpart,
					 $levelctr as level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 (bom.quantity * passbom.extendedqpa)
			 FROM bom,passbom 
			 WHERE bom.parent = passbom.part
			  AND bom.effectiveto >= NOW() AND bom.effectiveafter <= NOW()";
		$result = DB_query($sql,$db);
		
		$sql = 'DROP TABLE IF EXISTS passbom2';
		$result = DB_query($sql,$db);
		
		$sql = 'ALTER TABLE passbom RENAME AS passbom2';
		$result = DB_query($sql,$db);
		
		$sql = 'DROP TABLE IF EXISTS passbom';
		$result = DB_query($sql,$db);
		
		$sql = 'CREATE TEMPORARY TABLE passbom (
			part char(20),
			extendedqpa decimal(10,3),
			sortpart text)';
		$result = DB_query($sql,$db);
		
		
		$sql = "INSERT INTO passbom (part, extendedqpa, sortpart)
				   SELECT bom.component AS part,
				   (bom.quantity * passbom2.extendedqpa),
						  CONCAT(passbom2.sortpart,bom.component) AS sortpart
						  FROM bom,passbom2 
				   WHERE bom.parent = passbom2.part
					AND bom.effectiveto >= NOW() AND bom.effectiveafter <= NOW()";
		$result = DB_query($sql,$db);
		
		
		$sql = 'SELECT COUNT(*) FROM bom,passbom WHERE bom.parent = passbom.part
		          GROUP BY passbom.part';
		$result = DB_query($sql,$db);
		
		$myrow = DB_fetch_row($result);
		$componentctr = $myrow[0];
			
	} // End of while $componentctr > 0

	if (DB_error_no($db) !=0) {
	  $title = _('Quantiy Extended BOM Listing') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	   prnMsg( _('The Quantiy Extended BOM Listing could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
	   echo "<br><a href='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
	   if ($debug==1){
	      echo "<br>$sql";
	   }
	   include('includes/footer.inc');
	   exit;
	}

	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin);
    $sql = 'SELECT stockmaster.stockid,stockmaster.description  
              FROM stockmaster 
              WHERE stockid = ' . "'" . $_POST['Part'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_array($result,$db);
	$assembly = $_POST['Part'];
	$assemblydesc = $myrow['description'];
    $FontSize=8;

    $Tot_Val=0;
    $fill = false;
    $pdf->SetFillColor(224,235,255);
    $sql = 'SELECT tempbom.component,
                   SUM(tempbom.quantity) as quantity,
                   stockmaster.description,
                   stockmaster.decimalplaces,
                   stockmaster.mbflag,
                   (SELECT 
                      SUM(locstock.quantity) as invqty
                      FROM locstock
                      WHERE locstock.stockid = tempbom.component
                      GROUP BY locstock.stockid) AS qoh,                   
                   (SELECT 
                      SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as netqty
                      FROM purchorderdetails
                      WHERE purchorderdetails.itemcode = tempbom.component
                      AND completed = 0
                      GROUP BY purchorderdetails.itemcode) AS poqty,
                   (SELECT 
                      SUM(woitems.qtyreqd - woitems.qtyrecd) as netwoqty
                      FROM woitems
                      WHERE woitems.stockid = tempbom.component
                      GROUP BY woitems.stockid) AS woqty                      
              FROM tempbom,stockmaster 
              WHERE tempbom.component = stockmaster.stockid
              GROUP BY tempbom.component,
                       stockmaster.description,
                       stockmaster.decimalplaces,
                       stockmaster.mbflag';
	$result = DB_query($sql,$db);
	While ($myrow = DB_fetch_array($result,$db)){


		
		// Parameters for addTextWrap are defined in /includes/class.pdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set to transparent
		$difference = $myrow['quantity'] - ($myrow['qoh'] + $myrow['poqty'] + $myrow['woqty']);
		if (($_POST['Select'] == 'All') or ($difference > 0)) {
			$YPos -=$line_height;
			$FontSize=8;
			// Use to alternate between lines with transparent and painted background
			if ($_POST['Fill'] == 'yes'){
				$fill=!$fill;
			}
			$pdf->addTextWrap($Left_Margin+1,$YPos,90,$FontSize,$myrow['component'],'',0,$fill);
			$pdf->addTextWrap(140,$YPos,30,$FontSize,$myrow['mbflag'],'',0,$fill);
			$pdf->addTextWrap(170,$YPos,140,$FontSize,$myrow['description'],'',0,$fill);
			$pdf->addTextWrap(310,$YPos,50,$FontSize,number_format($myrow['quantity'],
											  $myrow['decimalplaces']),'right',0,$fill);
			$pdf->addTextWrap(360,$YPos,40,$FontSize,number_format($myrow['qoh'],
											  $myrow['decimalplaces']),'right',0,$fill);
			$pdf->addTextWrap(400,$YPos,40,$FontSize,number_format($myrow['poqty'],
											  $myrow['decimalplaces']),'right',0,$fill);		                                  
			$pdf->addTextWrap(440,$YPos,40,$FontSize,number_format($myrow['woqty'],
											  $myrow['decimalplaces']),'right',0,$fill);		                                  
			$pdf->addTextWrap(480,$YPos,50,$FontSize,number_format($difference,
											  $myrow['decimalplaces']),'right',0,$fill);		                                  
        }
		if ($YPos < $Bottom_Margin + $line_height){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin);
		}

	} /*end while loop */

	$FontSize =10;
	$YPos -= (2*$line_height);

	if ($YPos < $Bottom_Margin + $line_height){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin);
	}

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

	if ($len<=20){
			$title = _('Print Indented BOM Listing Error');
			include('includes/header.inc');
			prnMsg(_('There were no items for the selected assembly'),'error');
			echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
			include('includes/footer.inc');
			exit;
	} else {
			header('Content-type: application/pdf');
			header("Content-Length: " . $len);
			header('Content-Disposition: inline; filename=Customer_trans.pdf');
			header('Expires: 0');
			header('Cache-Control: private, post-check=0, pre-check=0');
			header('Pragma: public');
	
			$pdf->Stream();
	}
	
} else { /*The option to print PDF was not hit so display form */

	$title=_('Extended Quantity BOM Listing');
	include('includes/header.inc');

	echo '</br></br><form action=' . $_SERVER['PHP_SELF'] . " method='post'><table>";
	echo '<tr><td>' . _('Part') . ":</td><td><input type ='text' name='Part' size='20'>";
	echo '<tr><td>' . _('Quantity') . ":</td><td><input type='text' class=number name='Quantity' size='4'>";
	echo '<tr><td>' . _('Selection Option') . ":</td><td><select name='Select'>";
	echo "<option selected value='All'>" . _('Show All Parts');
	echo "<option value='Shortages'>" . _('Only Show Shortages');
	echo '</select></td></tr>';
	echo '<tr><td>' . _('Print Option') . ":</td><td><select name='Fill'>";
	echo "<option selected value='yes'>" . _('Print With Alternating Highlighted Lines');
	echo "<option value='no'>" . _('Plain Print');
	echo '</select></td></tr>';
	echo "</table></br></br><div class='centre'><input type=submit name='PrintPDF' value='" . _('Print PDF') . "'></div>";

	include('includes/footer.inc');

} /*end of else not PrintPDF */


function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
                     $Page_Width,$Right_Margin) {

	/*PDF page header for BOMExtendedQTY report */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin-5;
	
	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	
	$YPos -=$line_height;
	
	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('Extended Quantity BOM Listing For       ') 
		. strtoupper($_POST['Part']));
	$pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos,160,$FontSize,_('Printed') . ': ' . 
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -=$line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('Build Quantity:  ') . $_POST['Quantity']);
	
	$YPos -=(2*$line_height);
	
	/*set up the headings */
	$Xpos = $Left_Margin+1;
	
	$pdf->addTextWrap(310,$YPos,50,$FontSize,_('Build'), 'center');
	$pdf->addTextWrap(360,$YPos,40,$FontSize,_('On Hand'), 'right');
	$pdf->addTextWrap(400,$YPos,40,$FontSize,_('P.O.'), 'right');
	$pdf->addTextWrap(440,$YPos,40,$FontSize,_('W.O.'), 'right');
	$YPos -=$line_height;
	$pdf->addTextWrap($Xpos,$YPos,90,$FontSize,_('Part Number'), 'left');
	$pdf->addTextWrap(140,$YPos,30,$FontSize,_('M/B'), 'left');
	$pdf->addTextWrap(170,$YPos,140,$FontSize,_('Part Description'), 'left');
	$pdf->addTextWrap(310,$YPos,50,$FontSize,_('Quantity'), 'right');
	$pdf->addTextWrap(360,$YPos,40,$FontSize,_('Quantity'), 'right');
	$pdf->addTextWrap(400,$YPos,40,$FontSize,_('Quantity'), 'right');
	$pdf->addTextWrap(440,$YPos,40,$FontSize,_('Quantity'), 'right');
	$pdf->addTextWrap(480,$YPos,50,$FontSize,_('Shortage'), 'right');
	
	$YPos =$YPos - (2*$line_height);
	$PageNumber++;
} // End of PrintHeader function


?>
