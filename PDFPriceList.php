<?php

//	Script to print a price list by inventory category.

/*
Output column sizes:
	* stockmaster.stockid, varchar(20), len = 20chr
	* stockmaster.description, varchar(50), len = 50chr
	* prices.startdate, date, len = 10chr
	* prices.enddate, date/'No End Date', len = 12chr
	* custbranch.brname, varchar(40), len = 40chr
	* Gross Profit, calculated, len = 8chr
	* prices.price, decimal(20,4), len = 20chr + 4spaces
Please note that addTextWrap() YPos is a font-size-height further down than addText() and other functions. Use addText() instead of addTextWrap() to print left aligned elements.
All coordinates are measured from the lower left corner of the sheet to the top left corner of the element.
*/
require(__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

if (isset($_POST['EffectiveDate'])){$_POST['EffectiveDate'] = ConvertSQLDate($_POST['EffectiveDate']);}

// Merges gets into posts:
if (isset($_GET['ShowObsolete'])) {// Option to show obsolete items.
	$_POST['ShowObsolete'] = $_GET['ShowObsolete'];
}
if (isset($_GET['ItemOrder'])) {// Option to select the order of the items in the report.
	$_POST['ItemOrder'] = $_GET['ItemOrder'];
}

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	$WhereCurrency = '';
	if ($_POST['Currency'] != "All") {
		$WhereCurrency = " AND prices.currabrev = '" . $_POST['Currency'] ."' ";// Query element to select a currency.
	}
	// Option to show obsolete items:
	$ShowObsolete = ' AND `stockmaster`.`discontinued` != 1 ';// Query element to exclude obsolete items.
	if (isset($_POST['ShowObsolete'])) {
		$ShowObsolete = '';// Cleans the query element to exclude obsolete items.
	}
	// Option to select the order of the items in the report:
	$ItemOrder = 'stockmaster.stockid';// Query element to sort by currency, item_stock_category, and item_code.
	if ($_POST['ItemOrder'] == 'Description') {
		$ItemOrder = 'stockmaster.description';// Query element to sort by currency, item_stock_category, and item_description.
	}

	$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'";
	$SalesTypeResult = DB_query($SQL);
	$SalesTypeRow = DB_fetch_row($SalesTypeResult);
	$SalesTypeName = $SalesTypeRow[0];

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {

		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}
	$HTML .= '<meta name="author" content="WebERP " . $Version">
				<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>
				<div class="centre" id="ReportHeader">
					' . $_SESSION['CompanyRecord']['coyname'] . '<br />
					' . __('Prices By Inventory Category') . '<br />
					' . __('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
					' . __('Price List') . ' - ' . $_POST['SalesType'] . ' - ' . $SalesTypeName . '<br />
					' . __('Effective as at') . ' - ' . $_POST['EffectiveDate'] . '<br />
				</div>
				<table>
					<thead>
						<tr>
							<th>' . __('Item Code') . '</th>
							<th>' . __('Item Description') . '</th>
							<th colspan="2">' . __('Effective Date Range') . '</th>';

	if ($_POST['CustomerSpecials']=='Customer Special Prices Only') {
		$HTML .= '<th>' .  __('Branch') . '</th>';
	}
	if ($_POST['ShowGPPercentages']=='Yes') {
		$HTML .= '<th>' . __('Gross Profit') . '</th>';
	}

	$HTML .= '<th>' . __('Price') . '</th>
			</tr>
		</thead>
	<tbody>';

	$HTML .= '<tr>
				<td colspan="4">*' . __('Prices excluding tax') . '</td>
			</tr>';

	/*Now figure out the inventory data to report for the category range under review */
	if ($_POST['CustomerSpecials']==__('Customer Special Prices Only')) {

		if ($_SESSION['CustomerID']=='') {
			$Title = __('Special price List - No Customer Selected');
			$ViewTopic = 'SalesTypes';// Filename in ManualContents.php's TOC.
			$BookMark = 'PDFPriceList';// Anchor's id in the manual's html document.
			include('includes/header.php');
			echo '<br />';
			prnMsg( __('The customer must first be selected from the select customer link') . '. ' . __('Re-run the price list once the customer has been selected') );
			echo '<br /><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Back') . '</a>';
			include('includes/footer.php');
			exit();
		}
		if (!Is_Date($_POST['EffectiveDate'])) {
			$Title = __('Special price List - No Customer Selected');
			$ViewTopic = 'SalesTypes';// Filename in ManualContents.php's TOC.
			$BookMark = 'PDFPriceList';// Anchor's id in the manual's html document.
			include('includes/header.php');
			prnMsg(__('The effective date must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
			echo '<br /><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Back') . '</a>';
			include('includes/footer.php');
			exit();
		}

		$SQL = "SELECT
					debtorsmaster.name,
					debtorsmaster.salestype
				FROM debtorsmaster
				WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";
		$CustNameResult = DB_query($SQL);
		$CustNameRow = DB_fetch_row($CustNameResult);
		$CustomerName = $CustNameRow[0];
		$SalesType = $CustNameRow[1];
		$SQL = "SELECT
					prices.typeabbrev,
					prices.stockid,
					stockmaster.description,
					stockmaster.longdescription,
					prices.currabrev,
					prices.startdate,
					prices.enddate,
					prices.price,
					stockmaster.actualcost AS standardcost,
					stockmaster.categoryid,
					stockcategory.categorydescription,
					prices.debtorno,
					prices.branchcode,
					custbranch.brname,
					currencies.decimalplaces
				FROM stockmaster
					INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN prices ON stockmaster.stockid=prices.stockid
					INNER JOIN currencies ON prices.currabrev=currencies.currabrev
					LEFT JOIN custbranch ON prices.debtorno=custbranch.debtorno AND prices.branchcode=custbranch.branchcode
				WHERE prices.typeabbrev = '" . $SalesType . "'
					AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					AND prices.debtorno='" . $_SESSION['CustomerID'] . "'
					AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
					AND prices.enddate >'" . FormatDateForSQL($_POST['EffectiveDate']) . "'" .
					$WhereCurrency .
					$ShowObsolete . "
				ORDER BY
					prices.currabrev,
					stockcategory.categorydescription," .
					$ItemOrder;

	} else { /* the sales type list only */

		$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'";
		$SalesTypeResult = DB_query($SQL);
		$SalesTypeRow = DB_fetch_row($SalesTypeResult);
		$SalesTypeName = $SalesTypeRow[0];

		$SQL = "SELECT
					prices.typeabbrev,
					prices.stockid,
					prices.startdate,
					prices.enddate,
					stockmaster.description,
					stockmaster.longdescription,
					prices.currabrev,
					prices.price,
					stockmaster.actualcost as standardcost,
					stockmaster.categoryid,
					stockcategory.categorydescription,
					currencies.decimalplaces
				FROM stockmaster
					INNER JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN prices ON stockmaster.stockid=prices.stockid
					INNER JOIN currencies ON prices.currabrev=currencies.currabrev
				WHERE stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					AND prices.typeabbrev='" . $_POST['SalesType'] . "'
					AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
					AND prices.enddate>'" . FormatDateForSQL($_POST['EffectiveDate']) . "'" .
					$WhereCurrency .
					$ShowObsolete . "
					AND prices.debtorno LIKE '%%'
				ORDER BY
					prices.currabrev,
					stockcategory.categorydescription," .
					$ItemOrder;
	}
	$ErrMsg = __('The Price List could not be retrieved');
	$PricesResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($PricesResult)==0) {
		$Title = __('Print Price List Error');
		include('includes/header.php');
		prnMsg(__('There were no price details to print out for the customer or category specified'),'warn');
		echo '<br /><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Back') . '</a>';
		include('includes/footer.php');
		exit();
	}

	$CurrCode ='';
	$Category = '';
	$CatTot_Val=0;

	require_once('includes/CurrenciesArray.php');// To get the currency name from the currency code.

	while ($PriceList = DB_fetch_array($PricesResult)) {

		if ($Category != $PriceList['categoryid']) {
			$HTML .= '<tr>
						<th colspan="6">' . $PriceList['categoryid'] . ' - ' . $PriceList['categorydescription'] . '</th>
					</tr>';
			$Category = $PriceList['categoryid'];
		}

		if ($CurrCode != $PriceList['currabrev']) {
			$HTML .= '<tr>
						<th colspan="6">' . $PriceList['currabrev'] . ' - ' . __($CurrencyName[$PriceList['currabrev']]) . '</th>
					</tr>';
			$CurrCode = $PriceList['currabrev'];
		}

		$FontSize = 8;

		if ($PriceList['enddate']!='9999-12-31') {
			$DisplayEndDate = ConvertSQLDate($PriceList['enddate']);
		} else {
			$DisplayEndDate = __('No End Date');
		}

		$HTML .= '<tr>
					<td>' . $PriceList['stockid'] . '</td>
					<td>' . $PriceList['description'] . '</td>
					<td>' . ConvertSQLDate($PriceList['startdate']) . '</td>
					<td>' . $DisplayEndDate . '</td>';

		if ($_POST['CustomerSpecials']=='Customer Special Prices Only') {
			/*Need to show to which branch the price relates */
			if ($PriceList['branchcode']!='') {
				$HTML .= '<td>' . $PriceList['brname'] . '</td>';
			} else {
				$HTML .= '<td>' . __('All') . '</td>';
			}

		} elseif ($_POST['CustomerSpecials']=='Full Description') {
			$YPos -= $FontSize;

			// Prints item image:
			$SupportedImgExt = array('png','jpg','jpeg');
            $Glob = (glob($_SESSION['part_pics_dir'] . '/' . $PriceList['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
			$ImageFile = reset($Glob);
			$YPosImage = $YPos;// Initializes the image bottom $YPos.
			if (file_exists($ImageFile) ) {
				if ($YPos-36 < $Bottom_Margin) {// If the image bottom reaches the bottom margin, do PageHeader().
					PageHeader();
				}
				$pdf->Image($ImageFile,$Left_Margin+3, $Page_Height-$YPos, 36, 36);
				$YPosImage = $YPos-36;// Stores the $YPos of the image bottom (see bottom).
			}
			// Prints stockmaster.longdescription:
			$XPos = $Left_Margin+80;// Takes out this calculation from the loop.
			$Width = $Page_Width-$Left_Margin-$Right_Margin-$XPos;// Takes out this calculation from the loop.
			$FontSize2 = $FontSize*0.80;// Font size and line height of Full Description section.
			PrintDetail($pdf,$PriceList['longdescription'],$Bottom_Margin,$XPos,$YPos,$Width,$FontSize2,'PageHeader',null, 'j', 0, $Fill);

			// Assigns to $YPos the lowest $YPos value between the image and the description:
			$YPos = min($YPosImage, $YPos);
			$YPos -= $FontSize;// Jumps additional line after the image and the description.
		}
		// Shows gross profit percentage:
		if ($_POST['ShowGPPercentages']=='Yes') {
			$DisplayGPPercent = '-';
			if ($PriceList['price']!=0) {
				$DisplayGPPercent = locale_number_format((($PriceList['price']-$PriceList['standardcost'])*100/$PriceList['price']), 2) . '%';
			}
			$HTML .= '<td class="number">' . $DisplayGPPercent . '</td>';
		}

		// Displays unit price:
		$HTML .= '<td class="number">' . locale_number_format($PriceList['price'],$PriceList['decimalplaces']) . '</td></tr>';

	} /*end inventory valn while loop */

	// Warns if obsolete items are included:
	if (isset($_POST['ShowObsolete'])) {
		$HTML .= '<tr>
					<td colspan="4">' . __('* Obsolete items included.') . '</td>
				</tr>';
	}

	$FontSize = 10;
	$FileName = $_SESSION['DatabaseName'] . '_' . __('Price_List') . '_' . date('Y-m-d') . '.pdf';

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
		$dompdf->stream($_SESSION['DatabaseName'] . '_ReOrderLevel_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = __('Prices By Inventory Category');
		include('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Prices By Inventory Category') . '" alt="" />' . ' ' . __('Prices By Inventory Category') . '</p>';
		echo $HTML;
		include('includes/footer.php');
	}


} else { /*The option to print PDF was not hit */
	$Title = __('Price Listing');
	$ViewTopic = 'SalesTypes';
	$BookMark = 'PDFPriceList';
	include('includes/header.php');

	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/customer.png" title="', // Icon image.
		__('Price List'), '" /> ', // Icon title.
		__('Print a price list by inventory category'), '</p>';// Page title.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>
		<field>
			<label for="Categories">', __('Select Inventory Categories'), ':</label>
			<select autofocus="autofocus" id="Categories" minlength="1" multiple="multiple" name="Categories[]" required="required">';
	$SQL = "SELECT categoryid, categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$CatResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($CatResult)) {
		echo '<option' ;
		if (isset($_POST['Categories']) AND in_array($MyRow['categoryid'], $_POST['Categories'])) {
			echo ' selected="selected"';
		}
		echo ' value="', $MyRow['categoryid'], '">', $MyRow['categorydescription'], '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="SalesType">', __('For Sales Type/Price List'), ':</label>
			<select name="SalesType">';
	$SQL = "SELECT sales_type, typeabbrev FROM salestypes";
	$SalesTypesResult = DB_query($SQL);

	while ($MyRow=DB_fetch_array($SalesTypesResult)) {
		echo '<option value="', $MyRow['typeabbrev'], '">', $MyRow['sales_type'], '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Currency">', __('For Currency'), ':</label>
			<select name="Currency">';
	$SQL = "SELECT currabrev, currency FROM currencies ORDER BY currency";
	$CurrencyResult = DB_query($SQL);
	echo '<option selected="selected" value="All">', __('All'), '</option>';
	while ($MyRow=DB_fetch_array($CurrencyResult)) {
		echo '<option value="', $MyRow['currabrev'], '">', $MyRow['currency'], '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowGPPercentages">', __('Show Gross Profit %'), ':</label>
			<select name="ShowGPPercentages">
				<option selected="selected" value="No">', __('Prices Only'), '</option>
				<option value="Yes">', __('Show GP % too'), '</option>
			</select>
		</field>';

	echo '<field>
			<label for="CustomerSpecials">', __('Price Listing Type'), ':</label>
			<select name="CustomerSpecials">
				<option selected="selected" value="Sales Type Prices">', __('Default Sales Type Prices'), '</option>
				<option value="Customer Special Prices Only">', __('Customer Special Prices Only'), '</option>
				<option value="Full Description">', __('Full Description'), '</option>
			</select>
		</field>';

	echo '<field>
			<label for="EffectiveDate">', __('Effective As At'), ':</label>
			<input required="required" maxlength="10" size="11" type="date" name="EffectiveDate" value="', Date('Y-m-d'), '" />
		</field>';

	// Option to show obsolete items:
	if (isset($_POST['ShowObsolete'])) {
		$Checked = ' checked="checked" ';
	} else {
		$Checked = ' ';
	}
	echo '<field>
			<label for="ShowObsolete">', __('Show obsolete items'), ':</label>
			<input',$Checked, ' id="ShowObsolete" name="ShowObsolete" type="checkbox" />
			<fieldhelp>', __('Check this box to show the obsolete items'), '</fieldhelp>
		</field>';

	// Option to select the order of the items in the report:
	echo '<fieldset>
			<legend>', __('Sort items by'), ':</legend>
		<field>
	 		<label>', __('Currency, category and code'), '</label>
	 		<input checked="checked" id="ItemOrder" name="ItemOrder" type="radio" value="Code" />
		</field>
		<field>
			<label>', __('Currency, category and description'), '</label>
			<input name="ItemOrder" type="radio" value="Description" />
		</field>
		</fieldset>',

		'</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . __('PDF Price List') . '" />
				<input type="submit" name="View" title="View" value="' . __('View Price List') . '" />
			</div>
	</form>';

	include('includes/footer.php');
} /*end of else not PrintPDF */
// END: Procedure division -----------------------------------------------------
