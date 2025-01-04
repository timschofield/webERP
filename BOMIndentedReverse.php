<?php


// BOMIndented.php - Reverse Indented Bill of Materials - From lowest level component to top level
// assembly

include('includes/session.php');

if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Indented BOM Listing'));
	$pdf->addInfo('Subject',_('Indented BOM Listing'));
    	$FontSize=9;
	$PageNumber=1;
	$LineHeight=12;

	$Result = DB_query("DROP TABLE IF EXISTS tempbom");
	$Result = DB_query("DROP TABLE IF EXISTS passbom");
	$Result = DB_query("DROP TABLE IF EXISTS passbom2");
	$SQL = "CREATE TEMPORARY TABLE passbom (
				part char(20),
				sortpart text) DEFAULT CHARSET=utf8";

	$ErrMsg = _('The SQL to create passbom failed with the message');
	$Result = DB_query($SQL,$ErrMsg);

	$SQL = "CREATE TEMPORARY TABLE tempbom (
				parent char(20),
				component char(20),
				sortpart text,
				level int,
				workcentreadded char(5),
				loccode char(5),
				effectiveafter date,
				effectiveto date,
				quantity double) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL,_('Create of tempbom failed because'));
	// First, find first level of components below requested assembly
	// Put those first level parts in passbom, use COMPONENT in passbom
	// to link to PARENT in bom to find next lower level and accumulate
	// those parts into tempbom

	// This finds the top level
	$SQL = "INSERT INTO passbom (part, sortpart)
			   SELECT bom.parent AS part,
					  CONCAT(bom.component,bom.parent) AS sortpart
					  FROM bom
			  WHERE bom.component ='" . $_POST['Part'] . "'
              AND bom.effectiveafter <= '" . date('Y-m-d') . "'
              AND bom.effectiveto > '" . date('Y-m-d') . "'";
	$Result = DB_query($SQL);

	$LevelCounter = 2;
	// $LevelCounter is the level counter
	$SQL = "INSERT INTO tempbom (
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
					 CONCAT(bom.component,bom.parent) AS sortpart,
					 " . $LevelCounter . " AS level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 bom.quantity
					 FROM bom
			  WHERE bom.component ='" . $_POST['Part'] . "'
              AND bom.effectiveafter <= '" . date('Y-m-d') . "'
              AND bom.effectiveto > '" . date('Y-m-d') . "'";
	$Result = DB_query($SQL);

	// This while routine finds the other levels as long as $ComponentCounter - the
	// component counter finds there are more components that are used as
	// assemblies at lower levels

	$ComponentCounter = 1;
	while ($ComponentCounter > 0) {
		$LevelCounter++;
		$SQL = "INSERT INTO tempbom (parent,
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
						 CONCAT(passbom.sortpart,bom.parent) AS sortpart,
						 " . $LevelCounter . " AS level,
						 bom.workcentreadded,
						 bom.loccode,
						 bom.effectiveafter,
						 bom.effectiveto,
						 bom.quantity
				FROM bom,passbom
				WHERE bom.component = passbom.part
                AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                AND bom.effectiveto > '" . date('Y-m-d') . "'";
		$Result = DB_query($SQL);

		$Result = DB_query("DROP TABLE IF EXISTS passbom2");

		$Result = DB_query("ALTER TABLE passbom RENAME AS passbom2");
		$Result = DB_query("DROP TABLE IF EXISTS passbom");

		$SQL = "CREATE TEMPORARY TABLE passbom (
						part char(20),
						sortpart text) DEFAULT CHARSET=utf8";
		$Result = DB_query($SQL);


		$SQL = "INSERT INTO passbom (part, sortpart)
				   SELECT bom.parent AS part,
						  CONCAT(passbom2.sortpart,bom.parent) AS sortpart
				   FROM bom,passbom2
				   WHERE bom.component = passbom2.part
                   AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                   AND bom.effectiveto > '" . date('Y-m-d') . "'";
		$Result = DB_query($SQL);
		$Result = DB_query("SELECT COUNT(*) FROM bom,passbom WHERE bom.component = passbom.part");

		$MyRow = DB_fetch_row($Result);
		$ComponentCounter = $MyRow[0];

	} // End of while $ComponentCounter > 0

	if (DB_error_no() !=0) {
	  $Title = _('Indented BOM Listing') . ' - ' . _('Problem Report');
	  include('includes/header.php');
	   prnMsg( _('The Indented BOM Listing could not be retrieved by the SQL because') . ' '  . DB_error_msg(),'error');
	   echo '<br />
			<a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($Debug==1){
	      echo '<br />' . $SQL;
	   }
	   include('includes/footer.php');
	   exit;
	}


    $SQL = "SELECT stockmaster.stockid,
                   stockmaster.description
              FROM stockmaster
              WHERE stockid = '" . $_POST['Part'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$Assembly = $_POST['Part'];
	$AssemblyDesc = $MyRow['description'];

	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,$Right_Margin,$AssemblyDesc);

    $Tot_Val=0;
    $Fill = false;
    $pdf->SetFillColor(224,235,255);
    $SQL = "SELECT tempbom.*,
                   stockmaster.description,
                   stockmaster.mbflag,
                   stockmaster.units
              FROM tempbom INNER JOIN stockmaster
              ON tempbom.parent = stockmaster.stockid
			  INNER JOIN locationusers ON locationusers.loccode=tempbom.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
              ORDER BY sortpart";
	$Result = DB_query($SQL);

    $ListCount = DB_num_rows($Result);

	While ($MyRow = DB_fetch_array($Result)){

		$YPos -=$LineHeight;
		$FontSize=8;

		$FormatedEffectiveAfter = ConvertSQLDate($MyRow['effectiveafter']);
		$FormatedEffectiveTo = ConvertSQLDate($MyRow['effectiveto']);

		// Use to alternate between lines with transparent and painted background
		if ($_POST['Fill'] == 'yes'){
		    $Fill=!$Fill;
		}

		// Parameters for addTextWrap are defined in /includes/class.pdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set to transparent
		$pdf->addTextWrap($Left_Margin+($MyRow['level'] * 5),$YPos,90,$FontSize,$MyRow['component'],'left',0,$Fill);
		$pdf->addTextWrap(160,$YPos,20,$FontSize,$MyRow['mbflag'],'left',0,$Fill);
		$pdf->addTextWrap(180,$YPos,165,$FontSize,$MyRow['description'],'left',0,$Fill);
		$pdf->addTextWrap(345,$YPos,30,$FontSize,$MyRow['loccode'],'left',0,$Fill);
		$pdf->addTextWrap(375,$YPos,25,$FontSize,$MyRow['workcentreadded'],'left',0,$Fill);
		$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($MyRow['quantity'],'Variable'),'right',0,$Fill);
		$pdf->addTextWrap(445,$YPos,20,$FontSize,$MyRow['units'],'left',0,$Fill);
		$pdf->addTextWrap(465,$YPos,50,$FontSize,$FormatedEffectiveAfter,'left',0,$Fill);
		$pdf->addTextWrap(515,$YPos,50,$FontSize,$FormatedEffectiveTo,'left',0,$Fill);

		if ($YPos < $Bottom_Margin + $LineHeight){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$AssemblyDesc);
		}

	} /*end while loop */

	$FontSize =10;
	$YPos -= (2*$LineHeight);

	if ($YPos < $Bottom_Margin + $LineHeight){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$AssemblyDesc);
	}

	if ($ListCount == 0) {
			$Title = _('Print Reverse Indented BOM Listing Error');
			include('includes/header.php');
			prnMsg(_('There were no items for the selected component'),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			include('includes/footer.php');
			exit;
	} else {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_Customer_trans_' . date('Y-m-d').'.pdf');
		$pdf->__destruct();
	}

} else { /*The option to print PDF was not hit so display form */

	$ViewTopic = 'Manufacturing';
	$BookMark = '';

	$Title=_('Reverse Indented BOM Listing');
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' .
		_('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
        <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
			<legend>', _('Report Criteria'), '</legend>
		<field>
			<label for="Part">' . _('Part') . ':</label>
			<input type="text" autofocus="autofocus" required="required" title="" name="Part" size="20" />
			<fieldhelp>' ._('Enter the item code required to list the bill of material for') . '</fieldhelp>
		</field>
		<field>
			<label for="Fill">' . _('Print Option') . ':</label>
			<select name="Fill">
				<option selected="selected" value="yes">' . _('Print With Alternating Highlighted Lines') . '</option>
				<option value="no">' . _('Plain Print') . '</option>
			</select>
		</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>
        </form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */


function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
                     $Page_Width,$Right_Margin,$AssemblyDesc) {

	$LineHeight=12;
	/*PDF page header for Reverse Indented BOM Listing report */
	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin-5;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$LineHeight;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('Reverse Indented BOM Listing'));
	$pdf->addTextWrap($Page_Width-$Right_Margin-115,$YPos,160,$FontSize,_('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');

	$YPos -=(2*$LineHeight);

	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$pdf->addTextWrap($Xpos,$YPos,90,$FontSize,_('Part Number'), 'left');
	$pdf->addTextWrap(160,$YPos,20,$FontSize,_('M/B'), 'left');
	$pdf->addTextWrap(180,$YPos,165,$FontSize,_('Description'), 'center');
	$pdf->addTextWrap(345,$YPos,30,$FontSize,_('Locn'), 'left');
	$pdf->addTextWrap(375,$YPos,25,$FontSize,_('WC'), 'left');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,_('Quantity'), 'right');
	$pdf->addTextWrap(445,$YPos,20,$FontSize,_('UOM'), 'left');
	$pdf->addTextWrap(465,$YPos,50,$FontSize,_('From Date'), 'left');
	$pdf->addTextWrap(515,$YPos,50,$FontSize,_('To Date'), 'left');
	$YPos =$YPos - $LineHeight;

	$FontSize=8;
	$YPos =$YPos - (2*$LineHeight);

	$pdf->addTextWrap($Left_Margin+1,$YPos,60,$FontSize,_('Component:'),'',0);
	$pdf->addTextWrap(100,$YPos,100,$FontSize,mb_strtoupper($_POST['Part']),'',0);
	$pdf->addTextWrap(200,$YPos,150,$FontSize,$AssemblyDesc,'',0);
	$YPos -=(2*$LineHeight);
	$Xpos = $Left_Margin+5;

	$PageNumber++;

} // End of PrintHeader function

?>