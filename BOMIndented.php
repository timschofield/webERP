<?php

/* $Id: BOMIndented.php 6338 2013-09-28 05:10:46Z daintree $*/

// BOMIndented.php - Indented Bill of Materials

include('includes/session.inc');

if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Indented BOM Listing'));
	$pdf->addInfo('Subject',_('Indented BOM Listing'));
    $FontSize=9;
	$PageNumber=1;
	$line_height=12;

	$sql = "DROP TABLE IF EXISTS tempbom";
	$result = DB_query($sql,$db);
	$sql = "DROP TABLE IF EXISTS passbom";
	$result = DB_query($sql,$db);
	$sql = "DROP TABLE IF EXISTS passbom2";
	$result = DB_query($sql,$db);
	$sql = "CREATE TEMPORARY TABLE passbom (
				part char(20),
				sortpart text) DEFAULT CHARSET=utf8";
	$ErrMsg = _('The SQL to create passbom failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);

	$sql = "CREATE TEMPORARY TABLE tempbom (
				parent char(20),
				component char(20),
				sortpart text,
				level int,
				workcentreadded char(5),
				loccode char(5),
				effectiveafter date,
				effectiveto date,
				quantity double) DEFAULT CHARSET=utf8";
	$result = DB_query($sql,$db,_('Create of tempbom failed because'));
	// First, find first level of components below requested assembly
	// Put those first level parts in passbom, use COMPONENT in passbom
	// to link to PARENT in bom to find next lower level and accumulate
	// those parts into tempbom

	// This finds the top level
	$sql = "INSERT INTO passbom (part, sortpart)
			   SELECT bom.component AS part,
					  CONCAT(bom.parent,bom.component) AS sortpart
			  FROM bom
			  WHERE bom.parent ='" . $_POST['Part'] . "'
			  AND bom.effectiveto >= '" . date('Y-m-d') . "'
			  AND bom.effectiveafter <= '" . date('Y-m-d') . "'";
	$result = DB_query($sql,$db);

	$LevelCounter = 2;
	// $LevelCounter is the level counter
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
					 " . $LevelCounter . " AS level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 bom.quantity
			  FROM bom
			  WHERE bom.parent ='" . $_POST['Part'] . "'
			  AND bom.effectiveto >= '" . date('Y-m-d') . "'
			  AND bom.effectiveafter <= '" . date('Y-m-d') . "'";
	$result = DB_query($sql,$db);
	//echo "<br />sql is $sql<br />";
	// This while routine finds the other levels as long as $ComponentCounter - the
	// component counter - finds there are more components that are used as
	// assemblies at lower levels

	$ComponentCounter = 1;
	if ($_POST['Levels'] == 'All') {
		while ($ComponentCounter > 0) {
			$LevelCounter++;
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
						 $LevelCounter as level,
						 bom.workcentreadded,
						 bom.loccode,
						 bom.effectiveafter,
						 bom.effectiveto,
						 bom.quantity
				 FROM bom, passbom
				 WHERE bom.parent = passbom.part
				  AND bom.effectiveto >= '" . date('Y-m-d') . "' AND bom.effectiveafter <= '" . date('Y-m-d') . "'";
			$result = DB_query($sql,$db);

			$sql = "DROP TABLE IF EXISTS passbom2";
			$result = DB_query($sql,$db);

			$sql = "ALTER TABLE passbom RENAME AS passbom2";
			$result = DB_query($sql,$db);

			$sql = "DROP TABLE IF EXISTS passbom";
			$result = DB_query($sql,$db);

			$sql = "CREATE TEMPORARY TABLE passbom (
								part char(20),
								sortpart text) DEFAULT CHARSET=utf8";
			$result = DB_query($sql,$db);


			$sql = "INSERT INTO passbom (part, sortpart)
					   SELECT bom.component AS part,
							  CONCAT(passbom2.sortpart,bom.component) AS sortpart
					   FROM bom,passbom2
					   WHERE bom.parent = passbom2.part
						AND bom.effectiveto >= '" . date('Y-m-d') . "'
						AND bom.effectiveafter <= '" . date('Y-m-d') . "'";
			$result = DB_query($sql,$db);


			$sql = "SELECT COUNT(*) FROM bom,passbom WHERE bom.parent = passbom.part";
			$result = DB_query($sql,$db);

			$myrow = DB_fetch_row($result);
			$ComponentCounter = $myrow[0];

		} // End of while $ComponentCounter > 0
	} // End of if $_POST['Levels']

	if (DB_error_no($db) !=0) {
	  $Title = _('Indented BOM Listing') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	   prnMsg( _('The Indented BOM Listing could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
	      echo '<br />' . $sql;
	   }
	   include('includes/footer.inc');
	   exit;
	}


    $sql = "SELECT stockmaster.stockid,
                   stockmaster.description
              FROM stockmaster
              WHERE stockid = " . "'" . $_POST['Part'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_array($result,$db);
	$assembly = $_POST['Part'];
	$assemblydesc = $myrow['description'];

	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$assemblydesc);

    $Tot_Val=0;
    $sql = "SELECT tempbom.*,
				stockmaster.description,
				stockmaster.mbflag
			FROM tempbom,stockmaster
			WHERE tempbom.component = stockmaster.stockid
			ORDER BY sortpart";
	$result = DB_query($sql,$db);

	// $fill is used to alternate between lines with transparent and painted background
	$fill = false;
	$pdf->SetFillColor(224,235,255);

	$ListCount = DB_num_rows($result);

	while ($myrow = DB_fetch_array($result,$db)){

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
		$pdf->addTextWrap(415,$YPos,45,$FontSize,locale_number_format($myrow['quantity'],'Variable'),'right',0,$fill);
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

    if ($ListCount == 0) {
			$Title = _('Print Indented BOM Listing Error');
			include('includes/header.inc');
			prnMsg(_('There were no items for the selected assembly'),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			include('includes/footer.inc');
			exit;
	} else {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_Bill_Of_Material_Indented_' . date('Y-m-d').'.pdf');
		$pdf->__destruct();
	}

} else { /*The option to print PDF was not hit so display form */

	$Title=_('Indented BOM Listing');
	include('includes/header.inc');
        echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
          <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="selection">';
	echo '<tr>
			<td>' . _('Part') . ':</td>
			<td><input type="text" name="Part" autofocus="autofocus" required="required" data-type="no-illegal-chars" title="' . _('Enter the item code of parent item to list the bill of material for') .  '" size="20" /></td>
		</tr>
		<tr>
			<td>' . _('Levels') . ':</td>
			<td><select name="Levels">
				<option selected="selected" value="All">' . _('All Levels') . '</option>
				<option value="One">' . _('One Level') . '</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>' . _('Print Option') . ':</td>
			<td><select name="Fill">
				<option selected="selected" value="yes">' . _('Print With Alternating Highlighted Lines') . '</option>
				<option value="no">' . _('Plain Print') . '</option>
				</select>
			</td>
		</tr>
		</table>
		<div class="centre">
            <br />
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>
        </div>
        </form>';

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
	$pdf->addTextWrap(85,$YPos,100,$FontSize,mb_strtoupper($_POST['Part']),'',0);
	$pdf->addTextWrap(185,$YPos,150,$FontSize,$assemblydesc,'',0);
	$YPos -=(2*$line_height);
	$Xpos = $Left_Margin+5;
	$pdf->addTextWrap($Xpos,$YPos,90,$FontSize,_(' 12345678901234567890'), 'left');
	$YPos -=$line_height;

	$PageNumber++;

} // End of PrintHeader function

?>
