<?php
/* $Revision: 1.2 $ */
// BOMIndented.php - Indented Bill of Materials
$PageSecurity = 2;
include('includes/session.inc');

If (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');

	$FontSize=9;
	$pdf->addinfo('Title',_('Indented BOM Listing'));
	$pdf->addinfo('Subject',_('Indentedd BOM Listing'));

	$PageNumber=1;
	$line_height=12;

	$sql = 'DROP TABLE IF EXISTS tempbom';
	$result = DB_query($sql,$db);
	$sql = 'DROP TABLE IF EXISTS passbom';
	$result = DB_query($sql,$db);
	$sql = 'DROP TABLE IF EXISTS passbom2';
	$result = DB_query($sql,$db);
	$sql = 'CREATE TEMPORARY TABLE passbom (
				part char(20),                                
				sortpart text)';
	$ErrMsg = _('The SQL to create passbom failed with the message');
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
	$sql = 'INSERT INTO passbom (part, sortpart)
			   SELECT bom.component AS part,
					  CONCAT(bom.parent,bom.component) AS sortpart
			  FROM bom 
			  WHERE bom.parent =' . "'" . $_POST['Part'] . "' 
			  AND bom.effectiveto >= NOW() AND bom.effectiveafter <= NOW()";
	$result = DB_query($sql,$db);

	$levelctr = 2; 
	// $levelctr is the level counter
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
					 CONCAT(bom.parent,bom.component) AS sortpart,
					 '$levelctr' as level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 bom.quantity
			  FROM bom 
			  WHERE bom.parent ='" . $_POST['Part'] . "' 
			  AND bom.effectiveto >= NOW() AND bom.effectiveafter <= NOW()";
	$result = DB_query($sql,$db);
	//echo "</br>sql is $sql</br>";
	// This while routine finds the other levels as long as $componentctr - the
	// component counter - finds there are more components that are used as
	// assemblies at lower levels

	$componentctr = 1;
	if ($_POST['Levels'] == 'All') {
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
						 bom.quantity
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
				sortpart text)';
			$result = DB_query($sql,$db);
			
			
			$sql = "INSERT INTO passbom (part, sortpart)
					   SELECT bom.component AS part,
							  CONCAT(passbom2.sortpart,bom.component) AS sortpart
					   FROM bom,passbom2 
					   WHERE bom.parent = passbom2.part
						AND bom.effectiveto >= NOW() AND bom.effectiveafter <= NOW()";
			$result = DB_query($sql,$db);
			
			
			$sql = 'SELECT COUNT(*) FROM bom,passbom WHERE bom.parent = passbom.part';
			$result = DB_query($sql,$db);
			
			$myrow = DB_fetch_row($result);
			$componentctr = $myrow[0];
				
		} // End of while $componentctr > 0
	} // End of if $_POST['Levels']

	if (DB_error_no($db) !=0) {
	  $title = _('Indented BOM Listing') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	   prnMsg( _('The Indented BOM Listing could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
	   echo "<br><a href='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
	   if ($debug==1){
	      echo "<br>$sql";
	   }
	   include('includes/footer.inc');
	   exit;
	}


    $sql = 'SELECT stockmaster.stockid,
                   stockmaster.description  
              FROM stockmaster 
              WHERE stockid = ' . "'" . $_POST['Part'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_array($result,$db);
	$assembly = $_POST['Part'];
	$assemblydesc = $myrow['description'];
	
	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$assemblydesc);

    $Tot_Val=0;
    $sql = 'SELECT tempbom.*,
                   stockmaster.description,
                   stockmaster.mbflag
              FROM tempbom,stockmaster 
              WHERE tempbom.component = stockmaster.stockid
              ORDER BY sortpart';
	$result = DB_query($sql,$db);
	
	// $fill is used to alternate between lines with transparent and painted background
	$fill = false;
    $pdf->SetFillColor(224,235,255);
    
	While ($myrow = DB_fetch_array($result,$db)){

		$YPos -=$line_height;
		$FontSize=8;
		
		$FormatedEffectiveAfter = ConvertSQLDate($myrow['effectiveafter']);
		$FormatedEffectiveTo = ConvertSQLDate($myrow['effectiveto']);
		

		if ($_POST['Fill'] == 'yes'){
		    $fill=!$fill;
		}
		
		// Parameters for addTextWrap are defined in /includes/class.pdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set to transparent
		$pdf->addTextWrap($Left_Margin+($myrow['level'] * 5),$YPos,90,$FontSize,$myrow['component'],'',0,$fill);
		$pdf->addTextWrap(160,$YPos,20,$FontSize,$myrow['mbflag'],'',0,$fill);
		$pdf->addTextWrap(180,$YPos,180,$FontSize,$myrow['description'],'',0,$fill);
		$pdf->addTextWrap(360,$YPos,30,$FontSize,$myrow['loccode'],'right',0,$fill);
		$pdf->addTextWrap(390,$YPos,25,$FontSize,$myrow['workcentreadded'],'right',0,$fill);
		$pdf->addTextWrap(415,$YPos,45,$FontSize,number_format($myrow['quantity'],2),'right',0,$fill);
		$pdf->addTextWrap(460,$YPos,55,$FontSize,$FormatedEffectiveAfter,'right',0,$fill);				
		$pdf->addTextWrap(515,$YPos,50,$FontSize,$FormatedEffectiveTo,'right',0,$fill);

		if ($YPos < $Bottom_Margin + $line_height){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$assemblydesc);
		}

	} /*end while loop */

	$FontSize =10;
	$YPos -= (2*$line_height);

	if ($YPos < $Bottom_Margin + $line_height){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$assemblydesc);
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

	$title=_('Indented BOM Listing');
	include('includes/header.inc');

	echo '</br></br><form action=' . $_SERVER['PHP_SELF'] . " method='post'><table>";
	echo '<tr><td>' . _('Part') . ":</td>";
	echo "<td><input type ='text' name='Part' size='20'>";
	
	echo '<tr><td>' . _('Levels') . ":</td><td><select name='Levels'>";
	echo "<option selected value='All'>" . _('All Levels');
	echo "<option value='One'>" . _('One Level');
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Print Option') . ":</td><td><select name='Fill'>";
	echo "<option selected value='yes'>" . _('Print With Alternating Highlighted Lines');
	echo "<option value='no'>" . _('Plain Print');
	echo '</select></td></tr>';
	echo "</table></br></br><div class='centre'><input type=submit name='PrintPDF' value='" . _('Print PDF') . "'></div>";

	include('includes/footer.inc');

} /*end of else not PrintPDF */


function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
                     $Page_Width,$Right_Margin,$assemblydesc) {


	$line_height=12;
	/*PDF page header for Indented BOM Listing report */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin-5;
	
	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	
	$YPos -=$line_height;
	
	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('Indented BOM Listing'));
	$pdf->addTextWrap($Page_Width-$Right_Margin-105,$YPos,160,$FontSize,_('Printed') . ': ' . 
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	
	$YPos -=(2*$line_height);
	
	/*set up the headings */
	$Xpos = $Left_Margin+1;
	
	$pdf->addTextWrap($Xpos,$YPos,90,$FontSize,_('Part Number'), 'left');
	$pdf->addTextWrap(160,$YPos,20,$FontSize,_('M/B'), 'left');
	$pdf->addTextWrap(180,$YPos,180,$FontSize,_('Description'), 'center');
	$pdf->addTextWrap(360,$YPos,30,$FontSize,_('Locn'), 'right');
	$pdf->addTextWrap(390,$YPos,25,$FontSize,_('WC'), 'right');
	$pdf->addTextWrap(415,$YPos,45,$FontSize,_('Quantity'), 'right');
	$pdf->addTextWrap(460,$YPos,55,$FontSize,_('From Date'), 'right');
	$pdf->addTextWrap(515,$YPos,50,$FontSize,_('To Date'), 'right');
	$YPos =$YPos - $line_height;
	
	$FontSize=8;
	$YPos =$YPos - (2*$line_height);
	
	$pdf->addTextWrap($Left_Margin+1,$YPos,40,$FontSize,_('Assembly:'),'',0);
	$pdf->addTextWrap(85,$YPos,100,$FontSize,strtoupper($_POST['Part']),'',0);
	$pdf->addTextWrap(185,$YPos,150,$FontSize,$assemblydesc,'',0);
	$YPos -=(2*$line_height);
	$Xpos = $Left_Margin+5;
	$pdf->addTextWrap($Xpos,$YPos,90,$FontSize,_(' 12345678901234567890'), 'left');
	$YPos -=$line_height;
				
	$PageNumber++;
	
} // End of PrintHeader function


?>