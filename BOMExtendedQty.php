<?php

// Quantity Extended Bill of Materials

require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	if (!$_POST['Quantity'] or !is_numeric(filter_number_format($_POST['Quantity']))) {
		$_POST['Quantity'] = 1;
	}

	$Result = DB_query("DROP TABLE IF EXISTS tempbom");
	$Result = DB_query("DROP TABLE IF EXISTS passbom");
	$Result = DB_query("DROP TABLE IF EXISTS passbom2");
	$SQL = "CREATE TEMPORARY TABLE passbom (
				part char(20),
				extendedqpa double,
				sortpart text) DEFAULT CHARSET=utf8";
	$ErrMsg = __('The SQL to create passbom failed with the message');
	$Result = DB_query($SQL, $ErrMsg);

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
	$Result = DB_query($SQL,__('Create of tempbom failed because'));
	// First, find first level of components below requested assembly
	// Put those first level parts in passbom, use COMPONENT in passbom
	// to link to PARENT in bom to find next lower level and accumulate
	// those parts into tempbom

	// This finds the top level
	$SQL = "INSERT INTO passbom (part, extendedqpa, sortpart)
			   SELECT bom.component AS part,
					  (" . filter_number_format($_POST['Quantity']) . " * bom.quantity) as extendedqpa,
					   CONCAT(bom.parent,bom.component) AS sortpart
					  FROM bom
			  WHERE bom.parent ='" . $_POST['Part'] . "'
              AND bom.effectiveafter <= CURRENT_DATE
              AND bom.effectiveto > CURRENT_DATE";
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
					 CONCAT(bom.parent,bom.component) AS sortpart,"
					 . $LevelCounter . " as level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 (" . filter_number_format($_POST['Quantity']) . " * bom.quantity) as extendedqpa
			FROM bom
			WHERE bom.parent ='" . $_POST['Part'] . "'
            AND bom.effectiveafter <= CURRENT_DATE
            AND bom.effectiveto > CURRENT_DATE";
	$Result = DB_query($SQL);
	//echo "<br />sql is $SQL<br />";
	// This while routine finds the other levels as long as $ComponentCounter - the
	// component counter finds there are more components that are used as
	// assemblies at lower levels

	$ComponentCounter = 1;
	while ($ComponentCounter > 0) {
		$LevelCounter++;
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
					 CONCAT(passbom.sortpart,bom.component) AS sortpart,
					 " . $LevelCounter . " as level,
					 bom.workcentreadded,
					 bom.loccode,
					 bom.effectiveafter,
					 bom.effectiveto,
					 (bom.quantity * passbom.extendedqpa)
			 FROM bom,passbom
			 WHERE bom.parent = passbom.part
             AND bom.effectiveafter <= CURRENT_DATE
             AND bom.effectiveto > CURRENT_DATE";
		$Result = DB_query($SQL);

		$Result = DB_query("DROP TABLE IF EXISTS passbom2");
		$Result = DB_query("ALTER TABLE passbom RENAME AS passbom2");
		$Result = DB_query("DROP TABLE IF EXISTS passbom");

		$SQL = "CREATE TEMPORARY TABLE passbom (part char(20),
												extendedqpa decimal(10,3),
												sortpart text) DEFAULT CHARSET=utf8";
		$Result = DB_query($SQL);

		$SQL = "INSERT INTO passbom (part,
									extendedqpa,
									sortpart)
									SELECT bom.component AS part,
											(bom.quantity * passbom2.extendedqpa),
											CONCAT(passbom2.sortpart,bom.component) AS sortpart
									FROM bom
									INNER JOIN passbom2
									ON bom.parent = passbom2.part
									WHERE bom.effectiveafter <= CURRENT_DATE
                                    AND bom.effectiveto > CURRENT_DATE";
		$Result = DB_query($SQL);

		$SQL = "SELECT COUNT(bom.parent) AS components
					FROM bom
					INNER JOIN passbom
					ON bom.parent = passbom.part
					GROUP BY passbom.part";
		$Result = DB_query($SQL);

		$MyRow = DB_fetch_array($Result);
		$ComponentCounter = $MyRow['components'];

	} // End of while $ComponentCounter > 0

	$Tot_Val=0;
	$SQL = "SELECT tempbom.component,
				   SUM(tempbom.quantity) as quantity,
				   stockmaster.description,
				   stockmaster.decimalplaces,
				   stockmaster.mbflag,
				   (SELECT
					  SUM(locstock.quantity) as invqty
					  FROM locstock
					  INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
					  WHERE locstock.stockid = tempbom.component
					  GROUP BY locstock.stockid) AS qoh,
				   (SELECT
					  SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as netqty
					  FROM purchorderdetails INNER JOIN purchorders
					  ON purchorderdetails.orderno=purchorders.orderno
					  INNER JOIN locationusers ON locationusers.loccode=purchorders.intostocklocation AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
					  WHERE purchorderdetails.itemcode = tempbom.component
					  AND purchorderdetails.completed = 0
					  AND (purchorders.status = 'Authorised' OR purchorders.status='Printed')
					  GROUP BY purchorderdetails.itemcode) AS poqty,
				   (SELECT
					  SUM(woitems.qtyreqd - woitems.qtyrecd) as netwoqty
					  FROM woitems INNER JOIN workorders
					  ON woitems.wo = workorders.wo
					  INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
					  WHERE woitems.stockid = tempbom.component
					  AND workorders.closed=0
					  GROUP BY woitems.stockid) AS woqty
			  FROM tempbom INNER JOIN stockmaster
			  ON tempbom.component = stockmaster.stockid
			  INNER JOIN locationusers ON locationusers.loccode=tempbom.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			  GROUP BY tempbom.component,
					   stockmaster.description,
					   stockmaster.decimalplaces,
					   stockmaster.mbflag";
	$Result = DB_query($SQL);
	$ListCount = DB_num_rows($Result);


	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Extended Quantity BOM Listing For') . ' ' . mb_strtoupper($_POST['Part']) . '<br />
					' . __('Build Quantity:') . ' ' . locale_number_format($_POST['Quantity'],'Variable') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Part Number') . '</th>
							<th>' . __('M/B') . '</th>
							<th>' . __('Part Description') . '</th>
							<th>' . __('Build') . '<br />' . __('Quantity') . '</th>
							<th>' . __('On Hand') . '<br />' . __('Quantity') . '</th>
							<th>' . __('P.O.') . '<br />' . __('Quantity') . '</th>
							<th>' . __('W.O.') . '<br />' . __('Quantity') . '</th>
							<th>' . __('Shortage') . '</th>
						</tr>
					</thead>
					<tbody>';

	while ($MyRow = DB_fetch_array($Result)){

		// Parameters for addTextWrap are defined in /includes/class.cpdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set to transparent
		$Difference = $MyRow['quantity'] - ($MyRow['qoh'] + $MyRow['poqty'] + $MyRow['woqty']);
		if (($_POST['Select'] == 'All') or ($Difference > 0)) {
			$HTML .= '<tr class="striped_row">
						<td>' . $MyRow['component'] . '</td>
						<td>' . $MyRow['mbflag'] . '</td>
						<td>' . $MyRow['description'] . '</td>
						<td class="number">' . locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['qoh'],$MyRow['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['poqty'],$MyRow['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($MyRow['woqty'],$MyRow['decimalplaces']) . '</td>
						<td class="number">' . locale_number_format($Difference,$MyRow['decimalplaces']) . '</td>
					</tr>';
		}

	} /*end while loop */


	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . __('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'landscape');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_BOMExtendedQty_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('BOM Extended Quantity');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}

} else { /*The option to print PDF was not hit so display form */

	$ViewTopic = 'Manufacturing';
	$BookMark = '';

	$Title=__('Quantity Extended BOM Listing');
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">
        <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
			<legend>', __('Report Criteria'), '</legend>
		<field>
			<label for="Part">' . __('Part') . ':</label>
			<input type="text" autofocus="autofocus" required="required" name="Part" size="20" title="" />
			<fieldhelp>' . __('Enter the item code that you wish to display the extended bill of material for') . '</fieldhelp
		</field>
		<field>
			<label for="Quantity">' . __('Quantity') . ':</label>
			<input type="text" class="number" required="required" name="Quantity" size="4" />
		</field>
		<field>
			<label for="Select">' . __('Selection Option') . ':</label>
			<select name="Select">
				<option selected="selected" value="All">' . __('Show All Parts') . '</option>
				<option value="Shortages">' . __('Only Show Shortages') . '</option>
			</select>
		</field>
		</fieldset>
		<div class="centre">
				<input type="submit" name="PrintPDF" title="Produce PDF Report" value="' . __('Print PDF') . '" />
				<input type="submit" name="View" title="View Report" value="' . __('View') . '" />
		</div>
	</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */
