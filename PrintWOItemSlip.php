<?php
include('includes/session.php');

if (isset($_GET['WO'])) {
	$WO = filter_number_format($_GET['WO']);
} elseif (isset($_POST['WO'])) {
	$WO = filter_number_format($_POST['WO']);
} else {
	$WO = '';
}

if (isset($_GET['StockId'])) {
	$StockId = $_GET['StockId'];
} elseif (isset($_POST['StockId'])) {
	$StockId = $_POST['StockId'];
}

if (isset($_GET['Location'])) {
	$Location = $_GET['Location'];
} elseif (isset($_POST['Location'])) {
	$Location = $_POST['Location'];
}

if (isset($WO) and isset($StockId) and $WO != '') {

	$SQL = "SELECT woitems.qtyreqd,
					woitems.qtyrecd,
					stockmaster.description,
					stockmaster.decimalplaces,
					stockmaster.units
			FROM woitems, stockmaster
			WHERE stockmaster.stockid = woitems.stockid
				AND woitems.wo = '" . $WO . "'
				AND woitems.stockid = '" . $StockId . "' ";

	$ErrMsg = __('The SQL to find the details of the item to produce failed');
	$ResultItems = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($ResultItems) != 0) {
		include('includes/PDFStarter.php');

		$pdf->addInfo('Title', __('WO Production Slip'));
		$pdf->addInfo('Subject', __('WO Production Slip'));

		while ($MyItem = DB_fetch_array($ResultItems)) {
			// print the info of the parent product
			$FontSize = 10;
			$PageNumber = 1;
			$LineHeight = 12;
			$Xpos = $Left_Margin + 1;
			$Fill = false;

			$QtyPending = $MyItem['qtyreqd'] - $MyItem['qtyrecd'];

			PrintHeader($pdf, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $WO, $StockId, $MyItem['description'], $QtyPending, $MyItem['units'], $MyItem['decimalplaces'], $ReportDate);

			$PartCounter = 0;

			$SQLBOM = "SELECT bom.parent,
						bom.component,
						bom.quantity AS bomqty,
						stockmaster.decimalplaces,
						stockmaster.units,
						stockmaster.description,
						stockmaster.shrinkfactor,
						locstock.quantity AS qoh
					FROM bom, stockmaster, locstock
					WHERE bom.component = stockmaster.stockid
						AND bom.component = locstock.stockid
						AND locstock.loccode = '" . $Location . "'
						AND bom.parent = '" . $StockId . "'
                        AND bom.effectiveafter <= CURRENT_DATE
                        AND bom.effectiveto > CURRENT_DATE";

			$ErrMsg = __('The bill of material could not be retrieved because');
			$BOMResult = DB_query($SQLBOM, $ErrMsg);
			while ($MyComponent = DB_fetch_array($BOMResult)) {

				$ComponentNeeded = $MyComponent['bomqty'] * $QtyPending;
				$PrevisionShrinkage = $ComponentNeeded * ($MyComponent['shrinkfactor'] / 100);

				$Xpos = $Left_Margin + 1;

				$pdf->addTextWrap($Xpos, $YPos, 150, $FontSize, $MyComponent['component'], 'left');
				$pdf->addTextWrap(150, $YPos, 50, $FontSize, locale_number_format($MyComponent['bomqty'], 'Variable'), 'right');
				$pdf->addTextWrap(200, $YPos, 30, $FontSize, $MyComponent['units'], 'left');
				$pdf->addTextWrap(230, $YPos, 50, $FontSize, locale_number_format($ComponentNeeded, $MyComponent['decimalplaces']), 'right');
				$pdf->addTextWrap(280, $YPos, 30, $FontSize, $MyComponent['units'], 'left');
				$pdf->addTextWrap(310, $YPos, 50, $FontSize, locale_number_format($PrevisionShrinkage, $MyComponent['decimalplaces']), 'right');
				$pdf->addTextWrap(360, $YPos, 30, $FontSize, $MyComponent['units'], 'left');

				$YPos-= $LineHeight;

				if ($YPos < $Bottom_Margin + $LineHeight) {
					PrintHeader($pdf, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $WO, $Stockid, $MyItem['description'], $QtyPending, $MyItem['units'], $MyItem['decimalplaces'], $ReportDate);
				}
			}
		}

		// Production Notes
		$pdf->addTextWrap($Xpos, $YPos - 50, 200, $FontSize, __('Incidences / Production Notes') . ':', 'left');
		$YPos-= (8 * $LineHeight);

		PrintFooterSlip($pdf, __('Components Ready By'), __('Item Produced By'), __('Quality Control By'), $YPos, $FontSize, false);

		if ($YPos < $Bottom_Margin + $LineHeight) {
			PrintHeader($pdf, $YPos, $PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $WO, $Stockid, $MyItem['description'], $QtyPending, $MyItem['units'], $MyItem['decimalplaces'], $ReportDate);
		}

		$pdf->OutputD('WO-' . $WO . '-' . $StockId . '-' . Date('Y-m-d') . '.pdf');
		$pdf->__destruct();
	} else {
		$Title = __('WO Item production Slip');
		include('includes/header.php');
		prnMsg(__('There were no items with ready to produce'), 'info');
		prnMsg($SQL);
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();

	}
}

function PrintHeader($pdf, &$YPos, &$PageNumber, $Page_Height, $Top_Margin, $Left_Margin, $Page_Width, $Right_Margin, $WO, $StockId, $Description, $Qty, $UOM, $DecimalPlaces, $ReportDate) {

	if ($PageNumber > 1) {
		$pdf->newPage();
	}
	$LineHeight = 12;
	$FontSize = 10;
	$YPos = $Page_Height - $Top_Margin;

	$pdf->addTextWrap($Left_Margin, $YPos, 300, $FontSize, $_SESSION['CompanyRecord']['coyname']);
	$pdf->addTextWrap(190, $YPos, 100, $FontSize, $ReportDate);
	$pdf->addTextWrap($Page_Width - $Right_Margin - 150, $YPos, 160, $FontSize, __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . __('Page') . ' ' . $PageNumber, 'left');
	$YPos-= $LineHeight;

	$pdf->addTextWrap($Left_Margin, $YPos, 150, $FontSize, __('Work Order Item Production Slip'));
	$YPos-= (2 * $LineHeight);

	$pdf->addTextWrap($Left_Margin, $YPos, 150, $FontSize, __('WO') . ': ' . $WO);
	$YPos-= $LineHeight;

	$pdf->addTextWrap($Left_Margin, $YPos, 500, $FontSize, __('Item Code') . ': ' . $StockId . ' --> ' . $Description);
	$YPos-= $LineHeight;

	$pdf->addTextWrap($Left_Margin, $YPos, 150, $FontSize, __('Quantity') . ': ' . locale_number_format($Qty, $DecimalPlaces) . ' ' . $UOM);
	$YPos-= (2 * $LineHeight);

	if (file_exists($_SESSION['part_pics_dir'] . '/' . $StockId . '.jpg')) {
		$pdf->Image($_SESSION['part_pics_dir'] . '/' . $StockId . '.jpg', 135, $Page_Height - $Top_Margin - $YPos + 10, 200, 200);
		$YPos-= (16 * $LineHeight);
	} /*end checked file exist*/

	/*set up the headings */
	$Xpos = $Left_Margin + 1;

	$pdf->addTextWrap($Xpos, $YPos, 150, $FontSize, __('Component Code'), 'left');
	$pdf->addTextWrap(150, $YPos, 50, $FontSize, __('Qty BOM'), 'right');
	$pdf->addTextWrap(200, $YPos, 30, $FontSize, '', 'left');
	$pdf->addTextWrap(230, $YPos, 50, $FontSize, __('Qty Needed'), 'right');
	$pdf->addTextWrap(280, $YPos, 30, $FontSize, '', 'left');
	$pdf->addTextWrap(310, $YPos, 50, $FontSize, __('Shrinkage'), 'right');
	$pdf->addTextWrap(360, $YPos, 30, $FontSize, '', 'left');

	$FontSize = 10;
	$YPos-= $LineHeight;

	$PageNumber++;
}

function PrintFooterSlip($pdf, $Column1, $Column2, $Column3, $YPos, $FontSize, $Fill) {
	//add column 1
	$pdf->addTextWrap(40, $YPos - 50, 100, $FontSize, $Column1 . ':', 'left');
	$pdf->addTextWrap(40, $YPos - 70, 100, $FontSize, __('Name'), 'left');
	$pdf->addTextWrap(80, $YPos - 70, 200, $FontSize, ':__________________', 'left', 0, $Fill);
	$pdf->addTextWrap(40, $YPos - 90, 100, $FontSize, __('Date'), 'left');
	$pdf->addTextWrap(80, $YPos - 90, 200, $FontSize, ':__________________', 'left', 0, $Fill);
	$pdf->addTextWrap(40, $YPos - 110, 100, $FontSize, __('Hour'), 'left');
	$pdf->addTextWrap(80, $YPos - 110, 200, $FontSize, ':__________________', 'left', 0, $Fill);
	$pdf->addTextWrap(40, $YPos - 150, 100, $FontSize, __('Signature'), 'left');
	$pdf->addTextWrap(80, $YPos - 150, 200, $FontSize, ':__________________', 'left', 0, $Fill);

	//add column 2
	$pdf->addTextWrap(220, $YPos - 50, 100, $FontSize, $Column2 . ':', 'left');
	$pdf->addTextWrap(220, $YPos - 70, 100, $FontSize, __('Name'), 'left');
	$pdf->addTextWrap(260, $YPos - 70, 200, $FontSize, ':__________________', 'left', 0, $Fill);
	$pdf->addTextWrap(220, $YPos - 90, 100, $FontSize, __('Date'), 'left');
	$pdf->addTextWrap(260, $YPos - 90, 200, $FontSize, ':__________________', 'left', 0, $Fill);
	$pdf->addTextWrap(220, $YPos - 110, 100, $FontSize, __('Hour'), 'left');
	$pdf->addTextWrap(260, $YPos - 110, 200, $FontSize, ':__________________', 'left', 0, $Fill);
	$pdf->addTextWrap(220, $YPos - 150, 100, $FontSize, __('Signature'), 'left');
	$pdf->addTextWrap(260, $YPos - 150, 200, $FontSize, ':__________________', 'left', 0, $Fill);

	//add column 3
	$pdf->addTextWrap(400, $YPos - 50, 100, $FontSize, $Column3 . ':', 'left');
	$pdf->addTextWrap(400, $YPos - 70, 100, $FontSize, __('Name'), 'left');
	$pdf->addTextWrap(440, $YPos - 70, 200, $FontSize, ':__________________', 'left', 0, $Fill);
	$pdf->addTextWrap(400, $YPos - 90, 100, $FontSize, __('Date'), 'left');
	$pdf->addTextWrap(440, $YPos - 90, 200, $FontSize, ':__________________', 'left', 0, $Fill);
	$pdf->addTextWrap(400, $YPos - 110, 100, $FontSize, __('Hour'), 'left');
	$pdf->addTextWrap(440, $YPos - 110, 200, $FontSize, ':__________________', 'left', 0, $Fill);
	$pdf->addTextWrap(400, $YPos - 150, 100, $FontSize, __('Signature'), 'left');
	$pdf->addTextWrap(440, $YPos - 150, 200, $FontSize, ':__________________', 'left', 0, $Fill);
}
