<?php

/* $Id: BOMExtendedQty.php 6310 2013-08-29 10:42:50Z daintree $*/

// BOMExtendedQty.php - Quantity Extended Bill of Materials

include('includes/session.inc');

if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Quantity Extended BOM Listing'));
	$pdf->addInfo('Subject',_('Quantity Extended BOM Listing'));
	$FontSize=9;
	$PageNumber=1;
	$line_height=12;
    PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,$Right_Margin);

	if (!$_POST['Quantity'] or !is_numeric(filter_number_format($_POST['Quantity']))) {
		$_POST['Quantity'] = 1;
	}

	$result = DB_query("DROP TABLE IF EXISTS tempbom",$db);
	$result = DB_query("DROP TABLE IF EXISTS passbom",$db);
	$result = DB_query("DROP TABLE IF EXISTS passbom2",$db);
	$sql = "CREATE TEMPORARY TABLE passbom (
				part char(20),
				extendedqpa double,
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
	$sql = "INSERT INTO passbom (part, extendedqpa, sortpart)
			   SELECT bom.component AS part,
					  (" . filter_number_format($_POST['Quantity']) . " * bom.quantity) as extendedqpa,
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
					 CONCAT(bom.parent,bom.component) AS sortpart,"
					 . $LevelCounter . " as level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 (" . filter_number_format($_POST['Quantity']) . " * bom.quantity) as extendedqpa
			FROM bom
			WHERE bom.parent ='" . $_POST['Part'] . "'
			AND bom.effectiveto >= '" . date('Y-m-d') . "'
			AND bom.effectiveafter <= '" . date('Y-m-d') . "'";
	$result = DB_query($sql,$db);
	//echo "<br />sql is $sql<br />";
	// This while routine finds the other levels as long as $ComponentCounter - the
	// component counter finds there are more components that are used as
	// assemblies at lower levels

	$ComponentCounter = 1;
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
					 " . $LevelCounter . " as level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 (bom.quantity * passbom.extendedqpa)
			 FROM bom,passbom
			 WHERE bom.parent = passbom.part
			  AND bom.effectiveto >= '" . date('Y-m-d') . "'
			  AND bom.effectiveafter <= '" . date('Y-m-d') . "'";
		$result = DB_query($sql,$db);

		$result = DB_query("DROP TABLE IF EXISTS passbom2",$db);
		$result = DB_query("ALTER TABLE passbom RENAME AS passbom2",$db);
		$result = DB_query("DROP TABLE IF EXISTS passbom",$db);

		$sql = "CREATE TEMPORARY TABLE passbom (part char(20),
												extendedqpa decimal(10,3),
												sortpart text) DEFAULT CHARSET=utf8";
		$result = DB_query($sql,$db);

		$sql = "INSERT INTO passbom (part,
									extendedqpa,
									sortpart)
									SELECT bom.component AS part,
											(bom.quantity * passbom2.extendedqpa),
											CONCAT(passbom2.sortpart,bom.component) AS sortpart
									FROM bom
									INNER JOIN passbom2
									ON bom.parent = passbom2.part
									WHERE bom.effectiveto >= '" . date('Y-m-d') . "'
									AND bom.effectiveafter <= '" . date('Y-m-d') . "'";
		$result = DB_query($sql,$db);

		$sql = "SELECT COUNT(bom.parent) AS components
					FROM bom
					INNER JOIN passbom
					ON bom.parent = passbom.part
					GROUP BY passbom.part";
		$result = DB_query($sql,$db);

		$myrow = DB_fetch_array($result);
		$ComponentCounter = $myrow['components'];

	} // End of while $ComponentCounter > 0

	if (DB_error_no($db) !=0) {
		$Title = _('Quantity Extended BOM Listing') . ' - ' . _('Problem Report');
		include('includes/header.inc');
		prnMsg( _('The Quantiy Extended BOM Listing could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo '<br />' . $sql;
		}
		include('includes/footer.inc');
		exit;
	}

	$Tot_Val=0;
	$fill = false;
	$pdf->SetFillColor(224,235,255);
	$sql = "SELECT tempbom.component,
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
					  FROM purchorderdetails INNER JOIN purchorders
					  ON purchorderdetails.orderno=purchorders.orderno
					  WHERE purchorderdetails.itemcode = tempbom.component
					  AND purchorderdetails.completed = 0
					  AND (purchorders.status = 'Authorised' OR purchorders.status='Printed')
					  GROUP BY purchorderdetails.itemcode) AS poqty,
				   (SELECT
					  SUM(woitems.qtyreqd - woitems.qtyrecd) as netwoqty
					  FROM woitems INNER JOIN workorders
					  ON woitems.wo = workorders.wo
					  WHERE woitems.stockid = tempbom.component
					  AND workorders.closed=0
					  GROUP BY woitems.stockid) AS woqty
			  FROM tempbom INNER JOIN stockmaster
			  ON tempbom.component = stockmaster.stockid
			  GROUP BY tempbom.component,
					   stockmaster.description,
					   stockmaster.decimalplaces,
					   stockmaster.mbflag";
	$result = DB_query($sql,$db);
	$ListCount = DB_num_rows($result);
	while ($myrow = DB_fetch_array($result,$db)){

		// Parameters for addTextWrap are defined in /includes/class.pdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set to transparent
		$Difference = $myrow['quantity'] - ($myrow['qoh'] + $myrow['poqty'] + $myrow['woqty']);
		if (($_POST['Select'] == 'All') or ($Difference > 0)) {
			$YPos -=$line_height;
			$FontSize=8;
			// Use to alternate between lines with transparent and painted background
			if ($_POST['Fill'] == 'yes'){
				$fill=!$fill;
			}
			$pdf->addTextWrap($Left_Margin+1,$YPos,90,$FontSize,$myrow['component'],'',0,$fill);
			$pdf->addTextWrap(140,$YPos,30,$FontSize,$myrow['mbflag'],'',0,$fill);
			$pdf->addTextWrap(170,$YPos,140,$FontSize,$myrow['description'],'',0,$fill);
			$pdf->addTextWrap(310,$YPos,50,$FontSize,locale_number_format($myrow['quantity'],$myrow['decimalplaces']),'right',0,$fill);
			$pdf->addTextWrap(360,$YPos,40,$FontSize,locale_number_format($myrow['qoh'],$myrow['decimalplaces']),'right',0,$fill);
			$pdf->addTextWrap(400,$YPos,40,$FontSize,locale_number_format($myrow['poqty'],$myrow['decimalplaces']),'right',0,$fill);
			$pdf->addTextWrap(440,$YPos,40,$FontSize,locale_number_format($myrow['woqty'],$myrow['decimalplaces']),'right',0,$fill);
			$pdf->addTextWrap(480,$YPos,50,$FontSize,locale_number_format($Difference,$myrow['decimalplaces']),'right',0,$fill);
		}
		if ($YPos < $Bottom_Margin + $line_height){
			PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,$Right_Margin);
		}

	} /*end while loop */

	$FontSize =10;
	$YPos -= (2*$line_height);

	if ($YPos < $Bottom_Margin + $line_height){
		PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,$Right_Margin);
	}
	if ($ListCount == 0) {
		$Title = _('Print Indented BOM Listing Error');
		include('includes/header.inc');
		prnMsg(_('There were no items for the selected assembly'),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_BOM_Extended_Qty_' . date('Y-m-d').'.pdf');
		$pdf->__destruct();
	}

} else { /*The option to print PDF was not hit so display form */

	$Title=_('Quantity Extended BOM Listing');
	include('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
        <div>
        <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<table class="selection">
		<tr>
			<td>' . _('Part') . ':</td>
			<td><input type="text" autofocus="autofocus" required="required" name="Part" size="20" title="' . _('Enter the item code that you wish to display the extended bill of material for') . '" /></td>
		</tr>
		<tr>
			<td>' . _('Quantity') . ':</td>
			<td><input type="text" class="number" required="required" name="Quantity" size="4" /></td>
		</tr>
		<tr>
			<td>' . _('Selection Option') . ':</td>
			<td><select name="Select">
				<option selected="selected" value="All">' . _('Show All Parts') . '</option>
				<option value="Shortages">' . _('Only Show Shortages') . '</option>
			</select></td>
		</tr>
		<tr>
			<td>' . _('Print Option') . ':</td>
			<td><select name="Fill">
				<option selected="selected" value="yes">' . _('Print With Alternating Highlighted Lines') . '</option>
				<option value="no">' . _('Plain Print') . '</option>
			</select></td>
		</tr>
		</table>
		<br />
		<br />
		<div class="centre">
			<br />
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>
        </div>
        </form>';

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

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('Extended Quantity BOM Listing For	   ')
		. mb_strtoupper($_POST['Part']));
	$pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos,160,$FontSize,_('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -=$line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('Build Quantity:  ') . locale_number_format($_POST['Quantity'],'Variable'),'left');

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