<?php

require(__DIR__ . '/includes/session.php');

// Include DomPDF
require_once(__DIR__ . '/vendor/autoload.php'); // Make sure DomPDF is installed via composer

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');
use BarcodePack\code128;

if (isset($_POST['EffectiveDate'])) {
	$_POST['EffectiveDate'] = ConvertSQLDate($_POST['EffectiveDate']);
}

$PtsPerMM = 2.83464567; //pdf points per mm (72 dpi / 25.4 mm per inch)

if ((isset($_POST['ShowLabels']) or isset($_POST['SelectAll']))
	and isset($_POST['StockCategory'])
	and mb_strlen($_POST['StockCategory']) >= 1) {

	$Title = __('Print Labels');
	include('includes/header.php');

	$SQL = "SELECT prices.stockid,
					stockmaster.description,
					stockmaster.barcode,
					prices.price,
					currencies.decimalplaces
			FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN prices
				ON stockmaster.stockid=prices.stockid
			INNER JOIN currencies
				ON prices.currabrev=currencies.currabrev
			WHERE stockmaster.categoryid = '" . $_POST['StockCategory'] . "'
			AND prices.typeabbrev='" . $_POST['SalesType'] . "'
			AND prices.currabrev='" . $_POST['Currency'] . "'
			AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
			AND prices.enddate>'" . FormatDateForSQL($_POST['EffectiveDate']) . "'
			AND prices.debtorno=''
			ORDER BY prices.currabrev,
				stockmaster.categoryid,
				stockmaster.stockid,
				prices.startdate";

	$ErrMsg = __('The Price Labels could not be retrieved');
	$LabelsResult = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($LabelsResult) == 0) {
		prnMsg(__('There were no price labels to print out for the category specified'), 'warn');
		echo '<br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' .  __('Back') . '</a>';
		include('includes/footer.php');
		exit();
	}

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<th>' . __('Item Code') . '</th>
				<th>' . __('Item Description') . '</th>
				<th>' . __('Price') . '</th>
				<th>' . __('Print') . ' ?</th>
			</tr>
			<tr>
				<th colspan="4"><input type="submit" name="SelectAll" value="' . __('Select All Labels') . '" /><input type="checkbox" name="CheckAll" ';
	if (isset($_POST['CheckAll'])) {
		echo 'checked="checked" ';
	}
	echo 'onchange="ReloadForm(SelectAll)" /></td>
		</tr>';

	$i = 0;
	while ($LabelRow = DB_fetch_array($LabelsResult)) {
		echo '<tr>
				<td>' . $LabelRow['stockid'] . '</td>
				<td>' . $LabelRow['description'] . '</td>
				<td class="number">' . locale_number_format($LabelRow['price'], $LabelRow['decimalplaces']) . '</td>
				<td>';
		if (isset($_POST['SelectAll']) && isset($_POST['CheckAll'])) {
			echo '<input type="checkbox" checked="checked" name="PrintLabel' . $i . '" />';
		} else {
			echo '<input type="checkbox" name="PrintLabel' . $i . '" />';
		}
		echo '</td>
			</tr>';
		echo '<input type="hidden" name="StockID' . $i . '" value="' . $LabelRow['stockid'] . '" />
			<input type="hidden" name="Description' . $i . '" value="' . $LabelRow['description'] . '" />
			<input type="hidden" name="Barcode' . $i . '" value="' . $LabelRow['barcode'] . '" />
			<input type="hidden" name="Price' . $i . '" value="' . locale_number_format($LabelRow['price'], $LabelRow['decimalplaces']) . '" />';
		$i++;
	}
	$i--;
	echo '</table>
		<input type="hidden" name="NoOfLabels" value="' . $i . '" />
		<input type="hidden" name="LabelID" value="' . $_POST['LabelID'] . '" />
		<input type="hidden" name="StockCategory" value="' . $_POST['StockCategory'] . '" />
		<input type="hidden" name="SalesType" value="' . $_POST['SalesType'] . '" />
		<input type="hidden" name="Currency" value="' . $_POST['Currency'] . '" />
		<input type="hidden" name="EffectiveDate" value="' . FormatDateForSQL($_POST['EffectiveDate']) . '" />
		<input type="hidden" name="LabelsPerItem" value="' . $_POST['LabelsPerItem'] . '" />
		<div class="centre">
			<input type="submit" name="PrintLabels" value="' . __('Print Labels') . '" />
		</div>
		<div class="centre">
			<a href="' . $RootPath . '/Labels.php">' . __('Label Template Maintenance') . '</a>
		</div>
		</form>';
	include('includes/footer.php');
	exit();
}

$NoOfLabels = 0;
if (isset($_POST['PrintLabels']) && isset($_POST['NoOfLabels']) && $_POST['NoOfLabels'] > 0) {
	for ($i = 0; $i < $_POST['NoOfLabels']; $i++) {
		if (isset($_POST['PrintLabel' . $i])) {
			$NoOfLabels++;
		}
	}
	if ($NoOfLabels == 0) {
		prnMsg(__('There are no labels selected to print'), 'info');
	}
}

if (isset($_POST['PrintLabels']) && $NoOfLabels > 0) {

	$Result = DB_query("SELECT description,
								pagewidth*" . $PtsPerMM . " as page_width,
								pageheight*" . $PtsPerMM . " as page_height,
								width*" . $PtsPerMM . " as label_width,
								height*" . $PtsPerMM . " as label_height,
								rowheight*" . $PtsPerMM . " as label_rowheight,
								columnwidth*" . $PtsPerMM . " as label_columnwidth,
								topmargin*" . $PtsPerMM . " as label_topmargin,
								leftmargin*" . $PtsPerMM . " as label_leftmargin
						FROM labels
						WHERE labelid='" . $_POST['LabelID'] . "'");
	$LabelDimensions = DB_fetch_array($Result);

	$Result = DB_query("SELECT fieldvalue,
								vpos,
								hpos,
								fontsize,
								barcode
						FROM labelfields
						WHERE labelid = '" . $_POST['LabelID'] . "'");
	$LabelFields = array();
	$i = 0;
	while ($LabelFieldRow = DB_fetch_array($Result)) {
		if ($LabelFieldRow['fieldvalue'] == 'itemcode') {
			$LabelFields[$i]['FieldValue'] = 'stockid';
		} elseif ($LabelFieldRow['fieldvalue'] == 'itemdescription') {
			$LabelFields[$i]['FieldValue'] = 'description';
		} else {
			$LabelFields[$i]['FieldValue'] = $LabelFieldRow['fieldvalue'];
		}
		$LabelFields[$i]['VPos'] = $LabelFieldRow['vpos'] * $PtsPerMM;
		$LabelFields[$i]['HPos'] = $LabelFieldRow['hpos'] * $PtsPerMM;
		$LabelFields[$i]['FontSize'] = $LabelFieldRow['fontsize'];
		$LabelFields[$i]['Barcode'] = $LabelFieldRow['barcode'];
		$i++;
	}

	// Prepare HTML output for DomPDF
	$HTML = '<html>
	<head>
		<style>
			.label-table { border-collapse: separate; }
			.label-cell {
				border: 1px solid #000;
				width: ' . $LabelDimensions['label_width'] . 'pt;
				height: ' . $LabelDimensions['label_height'] . 'pt;
				padding: 0;
				vertical-align: top;
				text-align: left;
				overflow: hidden;
				position: relative;
			}
			.label-content {
				position: absolute;
				width: 100%;
				height: 100%;
				left: 0;
				top: 0;
			}
		</style>
	</head>
	<body>
		<table class="label-table">';
	$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';

	$TotalLabels = $NoOfLabels * $_POST['LabelsPerItem'];
	$LabelsPrinted = 0;
	$rowCount = 0;
	$colCount = 0;

	for ($i = 0; $i < $_POST['NoOfLabels']; $i++) {
		if (isset($_POST['PrintLabel' . $i])) {
			for ($LabelNumber = 0; $LabelNumber < $_POST['LabelsPerItem']; $LabelNumber++) {

				if ($colCount == 0) {
					$HTML .= '<tr>';
				}
				$HTML .= '<td class="label-cell"><div class="label-content" style="font-size:' . $LabelFields[0]['FontSize'] . 'pt;">';

				foreach ($LabelFields as $Field) {
					if ($Field['FieldValue'] == 'price') {
						$Value = $_POST['Price' . $i] . ' ' . $_POST['Currency'];
						$HTML .= '<div style="position:absolute;top:' . $Field['VPos'] . 'pt;left:' . $Field['HPos'] . 'pt;">' . htmlspecialchars($Value) . '</div>';
					} elseif ($Field['FieldValue'] == 'stockid') {
						$Value = $_POST['StockID' . $i];
						$HTML .= '<div style="position:absolute;top:' . $Field['VPos'] . 'pt;left:' . $Field['HPos'] . 'pt;">' . htmlspecialchars($Value) . '</div>';
					} elseif ($Field['FieldValue'] == 'description') {
						$Value = $_POST['Description' . $i];
						$HTML .= '<div style="position:absolute;top:' . $Field['VPos'] . 'pt;left:' . $Field['HPos'] . 'pt;">' . htmlspecialchars($Value) . '</div>';
					} elseif ($Field['FieldValue'] == 'barcode') {
						$Value = $_POST['Barcode' . $i];
						if ($Field['Barcode'] == 1 && !empty($Value)) {
							// Generate barcode using an external library and embed as an image
							// For demonstration, just output barcode value as text
							$HTML .= '<div style="position:absolute;top:' . $Field['VPos'] . 'pt;left:' . $Field['HPos'] . 'pt;"><span style="font-family:monospace;">' . htmlspecialchars($Value) . '</span></div>';
						}
					} elseif ($Field['FieldValue'] == 'logo') {
						if (!empty($_SESSION['LogoFile'])) {
							$LogoPath = $_SESSION['LogoFile'];
							$HTML .= '<img src="' . $LogoPath . '" style="position:absolute;top:' . $Field['VPos'] . 'pt;left:' . $Field['HPos'] . 'pt;max-height:' . $Field['FontSize'] . 'pt;" />';
						}
					}
				}

				$HTML .= '</div></td>';
				$colCount++;
				$LabelsPrinted++;

				if ($colCount >= floor($LabelDimensions['page_width'] / $LabelDimensions['label_columnwidth'])) {
					$HTML .= '</tr>';
					$rowCount++;
					$colCount = 0;
				}
			}
		}
	}

	// Close last row if needed
	if ($colCount > 0) {
		$HTML .= '</tr>';
	}
	$HTML .= '</table>
	</body>
	</html>';

	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options
	$DomPDF->loadHtml($HTML);
	$DomPDF->setPaper($_SESSION['PageSize'], 'landscape');
	$DomPDF->render();

	$FileName = $_SESSION['DatabaseName'] . '_' . __('Price_Labels') . '_' . date('Y-m-d') . '.pdf';
	// Output the PDF inline to the browser
	header('Content-Type: application/pdf');
	header('Content-Disposition: inline; filename="' . $FileName . '"');
	echo $DomPDF->output();
	exit();

} else { /*The option to print PDF was not hit */

	$Title = __('Price Labels');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/customer.png" title="' . __('Price Labels') . '" alt="" />
         ' . ' ' . __('Print Price Labels') . '</p>';

	if (!function_exists('gd_info')) {
		prnMsg(__('The GD module for PHP is required to print barcode labels. Your PHP installation is not capable currently. You will most likely experience problems with this script until the GD module is installed.'), 'warn');
	}

	if (!isset($_POST['StockCategory'])) {

		/*if $StockCategory is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">
				<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<fieldset>
				<legend>' . __('Label Criteria') . '</legend>
				<field>
					<label for="LabelID">' . __('Label to print') . ':</label>
					<select required="required" autofocus="autofocus" name="LabelID">';

		$LabelResult = DB_query("SELECT labelid, description FROM labels");
		while ($LabelRow = DB_fetch_array($LabelResult)) {
			echo '<option value="' . $LabelRow['labelid'] . '">' . $LabelRow['description'] . '</option>';
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="StockCategory">' .  __('For Stock Category') . ':</label>
				<select name="StockCategory">';

		$CatResult = DB_query("SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription");
		while ($MyRow = DB_fetch_array($CatResult)) {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="SalesType">' . __('For Sales Type/Price List') . ':</label>
				<select name="SalesType">';
		$SQL = "SELECT sales_type, typeabbrev FROM salestypes";
		$SalesTypesResult = DB_query($SQL);

		while ($MyRow = DB_fetch_array($SalesTypesResult)) {
			if ($_SESSION['DefaultPriceList'] == $MyRow['typeabbrev']) {
				echo '<option selected="selected" value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['typeabbrev'] . '">' . $MyRow['sales_type'] . '</option>';
			}
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="Currency">' . __('For Currency') . ':</label>
				<select name="Currency">';
		$SQL = "SELECT currabrev, country, currency FROM currencies";
		$CurrenciesResult = DB_query($SQL);

		while ($MyRow = DB_fetch_array($CurrenciesResult)) {
			if ($_SESSION['CompanyRecord']['currencydefault'] == $MyRow['currabrev']) {
				echo '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['country'] . ' - ' . $MyRow['currency'] . '</option>';
			} else {
				echo '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['country'] . ' - ' . $MyRow['currency'] . '</option>';
			}
		}
		echo '</select>
			</field>';

		echo '<field>
				<label for="EffectiveDate">' . __('Effective As At') . ':</label>
				<input maxlength="10" size="11" type="date" name="EffectiveDate" value="' . date('Y-m-d') . '" />
			</field>';

		echo '<field>
				<label for="LabelsPerItem">' . __('Number of labels per item') . ':</label>
				<input type="text" class="number" name="LabelsPerItem" size="3" value="1" /></field>';

		echo '</fieldset>
				<div class="centre">
					<input type="submit" name="ShowLabels" value="' . __('Show Labels') . '" />
				</div>
				<div class="centre">
					<a href="' . $RootPath . '/Labels.php">' . __('Label Template Maintenance') . '</a>
				</div>
				</form>';

	}
	include('includes/footer.php');

} /*end of else not PrintPDF */