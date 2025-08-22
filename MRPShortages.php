<?php
// MRPShortages.php - Report of parts with demand greater than supply as determined by MRP
include('includes/session.php');

if (!DB_table_exists('mrprequirements')) {
	$Title = __('MRP error');
	include('includes/header.php');
	echo '<br />';
	prnMsg(__('The MRP calculation must be run before you can run this report') . '<br />' . __('To run the MRP calculation click') . ' ' . '<a href="' . $RootPath . '/MRP.php">' . __('here') . '</a>', 'error');
	include('includes/footer.php');
	exit();
}

if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	if ($_POST['ReportType'] == 'Shortage') {
		$pdf->addInfo('Title', __('MRP Shortages Report'));
		$pdf->addInfo('Subject', __('MRP Shortages'));
	} else {
		$pdf->addInfo('Title', __('MRP Excess Report'));
		$pdf->addInfo('Subject', __('MRP Excess'));
	}
	$FontSize = 9;
	$PageNumber = 1;
	$LineHeight = 12;

	// Create temporary tables for supply and demand, with one record per part with the
	// total for either supply or demand. Did this to simplify main sql where used
	// several subqueries.
	$SQL = "CREATE TEMPORARY TABLE demandtotal (
				part char(20),
				demand double,
				KEY `PART` (`part`)) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL, __('Create of demandtotal failed because'));

	$SQL = "INSERT INTO demandtotal
						(part,
						 demand)
			   SELECT part,
					  SUM(quantity) as demand
				FROM mrprequirements
				GROUP BY part";
	$Result = DB_query($SQL);

	$SQL = "CREATE TEMPORARY TABLE supplytotal (
				part char(20),
				supply double,
				KEY `PART` (`part`)) DEFAULT CHARSET=utf8";
	$Result = DB_query($SQL, __('Create of supplytotal failed because'));

	/* 21/03/2010: Ricard modification to allow items with total supply = 0 be included in the report */

	$SQL = "INSERT INTO supplytotal
						(part,
						 supply)
			SELECT stockid,
				  0
			FROM stockmaster";
	$Result = DB_query($SQL);

	$SQL = "UPDATE supplytotal
			SET supply = (SELECT SUM(mrpsupplies.supplyquantity)
							FROM mrpsupplies
							WHERE supplytotal.part = mrpsupplies.part
								AND mrpsupplies.supplyquantity > 0)";
	$Result = DB_query($SQL);

	$SQL = "UPDATE supplytotal SET supply = 0 WHERE supply IS NULL";
	$Result = DB_query($SQL);

	// Only include directdemand mrprequirements so don't have demand for top level parts and also
	// show demand for the lower level parts that the upper level part generates. See MRP.php for
	// more notes - Decided not to exclude derived demand so using $SQL, not $SQLexclude
	$SQLexclude = "SELECT stockmaster.stockid,
					   stockmaster.description,
					   stockmaster.mbflag,
					   stockmaster.actualcost,
					   stockmaster.decimalplaces,
					   (stockmaster.actualcost) as computedcost,
					   demandtotal.demand,
					   supplytotal.supply,
					   (demandtotal.demand - supplytotal.supply) *
					   (stockmaster.actualcost) as extcost
					FROM stockmaster
						LEFT JOIN demandtotal ON stockmaster.stockid = demandtotal.part
						LEFT JOIN supplytotal ON stockmaster.stockid = supplytotal.part
					  GROUP BY stockmaster.stockid,
							   stockmaster.description,
							   stockmaster.mbflag,
							   stockmaster.actualcost,
							   stockmaster.decimalplaces,
							   supplytotal.supply,
							   demandtotal.demand,
							   extcost
					  HAVING demand > supply
					  ORDER BY '" . $_POST['Sort'] . "'";

	if ($_POST['CategoryID'] == 'All') {
		$SQLCategory = ' ';
	} else {
		$SQLCategory = "WHERE stockmaster.categoryid = '" . $_POST['CategoryID'] . "'";
	}

	if ($_POST['ReportType'] == 'Shortage') {
		$SQLHaving = " HAVING demandtotal.demand > supplytotal.supply ";
	} else {
		$SQLHaving = " HAVING demandtotal.demand <= supplytotal.supply ";
	}

	$SQL = "SELECT stockmaster.stockid,
		stockmaster.description,
		stockmaster.mbflag,
		stockmaster.actualcost,
		stockmaster.decimalplaces,
		(stockmaster.actualcost) as computedcost,
		demandtotal.demand,
		supplytotal.supply,
	   (demandtotal.demand - supplytotal.supply) *
	   (stockmaster.actualcost) as extcost
		   FROM stockmaster
			 LEFT JOIN demandtotal ON stockmaster.stockid = demandtotal.part
			 LEFT JOIN supplytotal ON stockmaster.stockid = supplytotal.part
			 LEFT JOIN stockcategory ON stockmaster.categoryid = stockcategory.categoryid " . $SQLCategory . "WHERE stockcategory.stocktype<>'L'
			 GROUP BY stockmaster.stockid,
			   stockmaster.description,
			   stockmaster.mbflag,
			   stockmaster.actualcost,
			   stockmaster.decimalplaces,
			   stockmaster.actualcost,
			   supplytotal.supply,
			   demandtotal.demand " . $SQLHaving . " ORDER BY '" . $_POST['Sort'] . "'";

	$ErrMsg = __('The MRP shortages and excesses could not be retrieved');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result) == 0) {
		$Title = __('MRP Shortages and Excesses') . ' - ' . __('Problem Report');
		include('includes/header.php');
		prnMsg(__('No MRP shortages - Excess retrieved'), 'warn');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	PrintHeader($pdf, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin);

	$Total_Shortage = 0;
	$Partctr = 0;
	$Fill = false;
	$pdf->SetFillColor(224, 235, 255); // Defines color to make alternating lines highlighted
	while ($MyRow = DB_fetch_array($Result)) {

		if ($_POST['ReportType'] == 'Shortage') {
			$LineToPrint = ($MyRow['demand'] > $MyRow['supply']);
		} else {
			$LineToPrint = ($MyRow['demand'] <= $MyRow['supply']);
		}

		if ($LineToPrint) {
			$YPos-= $LineHeight;
			$FontSize = 8;

			// Use to alternate between lines with transparent and painted background
			if ($_POST['Fill'] == 'yes') {
				$Fill = !$Fill;
			}

			// Parameters for addTextWrap are defined in /includes/class.cpdf.php
			// 1) X position 2) Y position 3) Width
			// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
			// and False to set to transparent
			$Shortage = ($MyRow['demand'] - $MyRow['supply']) * -1;
			$Extcost = $Shortage * $MyRow['computedcost'];
			$pdf->addTextWrap($Left_Margin, $YPos, 90, $FontSize, $MyRow['stockid'], '', 0, $Fill);
			$pdf->addTextWrap(130, $YPos, 150, $FontSize, $MyRow['description'], '', 0, $Fill);
			$pdf->addTextWrap(280, $YPos, 25, $FontSize, $MyRow['mbflag'], 'right', 0, $Fill);
			$pdf->addTextWrap(305, $YPos, 55, $FontSize, locale_number_format($MyRow['computedcost'], 2), 'right', 0, $Fill);
			$pdf->addTextWrap(360, $YPos, 50, $FontSize, locale_number_format($MyRow['supply'], $MyRow['decimalplaces']), 'right', 0, $Fill);
			$pdf->addTextWrap(410, $YPos, 50, $FontSize, locale_number_format($MyRow['demand'], $MyRow['decimalplaces']), 'right', 0, $Fill);
			$pdf->addTextWrap(460, $YPos, 50, $FontSize, locale_number_format($Shortage, $MyRow['decimalplaces']), 'right', 0, $Fill);
			$pdf->addTextWrap(510, $YPos, 60, $FontSize, locale_number_format($MyRow['extcost'], 2), 'right', 0, $Fill);

			$Total_Shortage+= $MyRow['extcost'];
			$Partctr++;

			if ($YPos < $Bottom_Margin + $LineHeight) {
				PrintHeader($pdf, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin);
			}
		}

	} /*end while loop */

	$FontSize = 8;
	$YPos-= (2 * $LineHeight);

	if ($YPos < $Bottom_Margin + $LineHeight) {
		PrintHeader($pdf, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin);
	}
	/*Print out the grand totals */
	$pdf->addTextWrap($Left_Margin, $YPos, 120, $FontSize, __('Number of Parts: '), 'left');
	$pdf->addTextWrap(150, $YPos, 30, $FontSize, $Partctr, 'left');
	if ($_POST['ReportType'] == 'Shortage') {
		$pdf->addTextWrap(300, $YPos, 180, $FontSize, __('Total Extended Shortage:'), 'right');
	} else {
		$pdf->addTextWrap(300, $YPos, 180, $FontSize, __('Total Extended Excess:'), 'right');
	}
	$DisplayTotalVal = locale_number_format($Total_Shortage, 2);
	$pdf->addTextWrap(510, $YPos, 60, $FontSize, $DisplayTotalVal, 'right');

	if ($_POST['ReportType'] == 'Shortage') {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_MRPShortages_' . date('Y-m-d') . '.pdf');
	} else {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_MRPExcess_' . date('Y-m-d') . '.pdf');
	}
	$pdf->__destruct();
} else { /*The option to print PDF was not hit so display form */

	$Title = __('MRP Shortages - Excess Reporting');
	$ViewTopic = 'MRP';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Stock') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>';

	echo '<field>
			<label for="CategoryID">' . __('Inventory Category') . ':</label>
			<select name="CategoryID">';
	echo '<option selected="selected" value="All">' . __('All Stock Categories') . '</option>';
	$SQL = "SELECT categoryid,
			categorydescription
			FROM stockcategory";
	$Result = DB_query($SQL);
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
	} //end while loop
	echo '</select>
		</field>';

	echo '<field>
			<label for="Sort">' . __('Sort') . ':</label>
			<select name="Sort">
				<option selected="selected" value="extcost">' . __('Extended Shortage Dollars') . '</option>
				<option value="stockid">' . __('Part Number') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="ReportType">' . __('Shortage-Excess Option') . ':</label>
			<select name="ReportType">
				<option selected="selected" value="Shortage">' . __('Report MRP Shortages') . '</option>
				<option value="Excess">' . __('Report MRP Excesses') . '</option>
			</select>
		</field>';

	echo '<field>
			<label for="Fill">' . __('Print Option') . ':</label>
			<select name="Fill">
				<option selected="selected" value="yes">' . __('Print With Alternating Highlighted Lines') . '</option>
				<option value="no">' . __('Plain Print') . '</option>
			</select>
		</field>';
	echo '</fieldset>
		<div class="centre">
			<input type="submit" name="PrintPDF" value="' . __('Print PDF') . '" />
		</div>
		</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */

function PrintHeader($pdf, &$YPos, &$PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin) {

	$LineHeight = 12;
	/*PDF page header for MRP Shortages report */
	if ($PageNumber > 1) {
		$pdf->newPage();
	}

	$FontSize = 9;
	$YPos = $Page_Height - $Top_Margin;

	$pdf->addTextWrap($Left_Margin, $YPos, 300, $FontSize, $_SESSION['CompanyRecord']['coyname']);

	$YPos-= $LineHeight;
	if ($_POST['ReportType'] == 'Shortage') {
		$pdf->addTextWrap($Left_Margin, $YPos, 300, $FontSize, __('MRP Shortages Report'));
	} else {
		$pdf->addTextWrap($Left_Margin, $YPos, 300, $FontSize, __('MRP Excess Report'));
	}

	$pdf->addTextWrap($Page_Width - $Right_Margin - 110, $YPos, 160, $FontSize, __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . __('Page') . ' ' . $PageNumber, 'left');

	$YPos-= (2 * $LineHeight);

	/*Draw a rectangle to put the headings in	 */

	//$pdf->line($Left_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos+$LineHeight);
	//$pdf->line($Left_Margin, $YPos+$LineHeight,$Left_Margin, $YPos- $LineHeight);
	//$pdf->line($Left_Margin, $YPos- $LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);
	//$pdf->line($Page_Width-$Right_Margin, $YPos+$LineHeight,$Page_Width-$Right_Margin, $YPos- $LineHeight);
	/*set up the headings */
	$Xpos = $Left_Margin + 1;

	$pdf->addTextWrap($Xpos, $YPos, 130, $FontSize, __('Part Number'), 'left');
	$pdf->addTextWrap(130, $YPos, 150, $FontSize, __('Description'), 'left');
	$pdf->addTextWrap(285, $YPos, 20, $FontSize, __('M/B'), 'right');
	$pdf->addTextWrap(305, $YPos, 55, $FontSize, __('Unit Cost'), 'right');
	$pdf->addTextWrap(360, $YPos, 50, $FontSize, __('Supply'), 'right');
	$pdf->addTextWrap(410, $YPos, 50, $FontSize, __('Demand'), 'right');
	if ($_POST['ReportType'] == 'Shortage') {
		$pdf->addTextWrap(460, $YPos, 50, $FontSize, __('Shortage'), 'right');
		$pdf->addTextWrap(510, $YPos, 60, $FontSize, __('Ext. Shortage'), 'right');
	} else {
		$pdf->addTextWrap(460, $YPos, 50, $FontSize, __('Excess'), 'right');
		$pdf->addTextWrap(510, $YPos, 60, $FontSize, __('Ext. Excess'), 'right');
	}
	$FontSize = 8;
	$YPos = $YPos - (2 * $LineHeight);
	$PageNumber++;
} // End of PrintHeader function
