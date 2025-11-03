<?php
require (__DIR__ . '/includes/session.php');

use Dompdf\Dompdf;

include('includes/SetDomPDFOptions.php');

include (__DIR__ . '/includes/SQL_CommonFunctions.php');

if (isset($_GET['KeyValue'])) {
	$SelectedProdSpec = $_GET['KeyValue'];
} elseif (isset($_POST['KeyValue'])) {
	$SelectedProdSpec = $_POST['KeyValue'];
} else {
	$SelectedProdSpec = '';
}

//Get Out if we have no product specification
if (isset($SelectedProdSpec) and $SelectedProdSpec != '') {

	/*retrieve the order details from the database to print */
	$ErrMsg = __('There was a problem retrieving the Product Specification') . ' ' . $SelectedProdSpec . ' ' . __('from the database');

	$SQL = "SELECT keyval,
				description,
				longdescription,
				prodspecs.testid,
				name,
				method,
				qatests.units,
				type,
				numericvalue,
				prodspecs.targetvalue,
				prodspecs.rangemin,
				prodspecs.rangemax,
				groupby
			FROM prodspecs INNER JOIN qatests
			ON qatests.testid=prodspecs.testid
			LEFT OUTER JOIN stockmaster on stockmaster.stockid=prodspecs.keyval
			LEFT OUTER JOIN prodspecgroups on prodspecgroups.groupname=qatests.groupby
			WHERE prodspecs.keyval='" . $SelectedProdSpec . "'
			AND prodspecs.showonspec='1'
			ORDER by groupbyNo, prodspecs.testid";

	$Result = DB_query($SQL, $ErrMsg);

	//If there are no rows, there's a problem.
	if (DB_num_rows($Result) == 0) {
		$Title = __('Print Product Specification Error');
		include ('includes/header.php');
		echo '<div class="centre">';
		prnMsg(__('Unable to Locate Specification') . ' : ' . $_SelectedProdSpec . ' ', 'error');
		echo '<table class="table_index">
			<tr>
				<td class="menu_group_item">
					<ul><li><a href="' . $RootPath . '/PDFProdSpec.php">' . __('Product Specifications') . '</a></li></ul>
				</td>
			</tr>
			</table>
			</div>';
		include ('includes/footer.php');
		exit();
	}

	// Prepare product spec data
	$SectionsArray = [];
	$Result2 = DB_query("SELECT groupname, headertitle, trailertext, labels, numcols FROM prodspecgroups", $db);
	while ($MyGroupRow = DB_fetch_array($Result2)) {
		if ($MyGroupRow['numcols'] == 2) {
			$Align = array('left', 'center');
			$Cols = array(240, 265);
		}
		else {
			$Align = array('left', 'center', 'center');
			$Cols = array(260, 110, 135);
		}
		$SectionsArray[] = array($MyGroupRow['groupname'], $MyGroupRow['numcols'], $MyGroupRow['headertitle'], $MyGroupRow['trailertext'], $Cols, explode(",", $MyGroupRow['labels']), $Align);
	}
	DB_data_seek($Result2, 0);

	// Build HTML for DomPDF
	$HTML = '';
	$HTML .= '<html>';
	$HTML .= '<head>';
	$HTML .= '<style>
		body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
		.title { font-size: 22px; font-weight: bold; text-align: center; margin-bottom: 10px; }
		.subtitle { font-size: 15px; font-weight: bold; text-align: center; margin-bottom: 10px; }
		.sectiontitle { background: #ccc; font-weight: bold; text-align: center; }
		.table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
		.table th, .table td { border: 1px solid #999; padding: 4px; }
		.trailer { font-size: 10px; margin-top: 4px; margin-bottom: 10px; color: #333; }
		.disclaimer, .footertext { font-size: 9px; color: #333; margin-top: 12px; }
	</style>';
	$HTML .= '</head>';
	$HTML .= '<body>';

	$Spec = '';
	$SpecDesc = '';
	$CurrentSection = '';
	$SectionTrailer = '';
	$SectionTitle = '';
	$SectionColLabs = [];
	$SectionColSizes = [];
	$SectionAlign = [];
	$First = true;
	$RowsBySection = [];

	// Organize rows by section/group
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['description'] == '') {
			$MyRow['description'] = $MyRow['keyval'];
		}
		$Spec = $MyRow['description'];
		$SpecDesc = $MyRow['longdescription'];
		$Group = $MyRow['groupby'];
		if (!isset($RowsBySection[$Group])) {
			$RowsBySection[$Group] = [];
		}
		$RowsBySection[$Group][] = $MyRow;
	}

	$HTML .= '<div class="title">' . __('Product Specification') . '</div>';
	$HTML .= '<div class="subtitle">' . htmlspecialchars($Spec, ENT_QUOTES, 'UTF-8') . '</div>';
	if ($SpecDesc) {
		$HTML .= '<div style="text-align:center; margin-bottom: 14px;">' . htmlspecialchars($SpecDesc, ENT_QUOTES, 'UTF-8') . '</div>';
	}

	// Loop through sections/groups
	foreach ($SectionsArray as $Section) {
		list($Groupname, $numcols, $headertitle, $trailertext, $Cols, $labels, $Align) = $Section;
		if (empty($RowsBySection[$Groupname])) continue;

		$HTML .= '<table class="table">';
		$HTML .= '<tr><td colspan="' . count($labels) . '" class="sectiontitle">' . htmlspecialchars($headertitle, ENT_QUOTES, 'UTF-8') . '</td></tr>';
		$HTML .= '<tr>';
		foreach ($labels as $colLabel) {
			$HTML .= '<th>' . htmlspecialchars($colLabel, ENT_QUOTES, 'UTF-8') . '</th>';
		}
		$HTML .= '</tr>';
		foreach ($RowsBySection[$Groupname] as $MyRow) {
			// Calculate Value
			$Value = '';
			if ($MyRow['targetvalue'] > '') {
				$Value = $MyRow['targetvalue'];
			}
			elseif ($MyRow['rangemin'] > '' or $MyRow['rangemax'] > '') {
				if ($MyRow['rangemin'] > '' and $MyRow['rangemax'] == '') {
					$Value = '> ' . $MyRow['rangemin'];
				}
				elseif ($MyRow['rangemin'] == '' and $MyRow['rangemax'] > '') {
					$Value = '< ' . $MyRow['rangemax'];
				}
				else {
					$Value = $MyRow['rangemin'] . ' - ' . $MyRow['rangemax'];
				}
			}
			if (strtoupper($Value) != 'NB' && strtoupper($Value) != 'NO BREAK') {
				$Value .= ' ' . $MyRow['units'];
			}
			$HTML .= '<tr>';
			for ($x = 0;$x < count($labels);$x++) {
				switch ($x) {
					case 0:
						$DispValue = $MyRow['name'];
					break;
					case 1:
						$DispValue = $Value;
					break;
					case 2:
						$DispValue = $MyRow['method'];
					break;
					default:
						$DispValue = '';
				}
				$HTML .= '<td style="text-align: ' . $Align[$x] . ';">' . htmlspecialchars($DispValue, ENT_QUOTES, 'UTF-8') . '</td>';
			}
			$HTML .= '</tr>';
		}
		if ($trailertext) {
			$HTML .= '<tr><td colspan="' . count($labels) . '" class="trailer">' . htmlspecialchars($trailertext, ENT_QUOTES, 'UTF-8') . '</td></tr>';
		}
		$HTML .= '</table>';
	}

	// Disclaimer from config
	$Disclaimer = __('The information provided on this datasheet should only be used as a guideline. Actual lot to lot values will vary.');
	$SQL = "SELECT confvalue FROM config WHERE confname='QualityProdSpecText'";
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow = DB_fetch_array($Result);
	if ($MyRow && isset($MyRow[0]) && $MyRow[0]) {
		$Disclaimer = $MyRow[0];
	}
	$HTML .= '<div class="disclaimer">' . htmlspecialchars($Disclaimer, ENT_QUOTES, 'UTF-8') . '</div>';

	$HTML .= '</body>';
	$HTML .= '</html>';

	// Output PDF using DomPDF
	$DomPDF = new Dompdf($DomPDFOptions); // Pass the options object defined in SetDomPDFOptions.php containing common options

	$DomPDF->loadHtml($HTML, 'UTF-8');
	$DomPDF->setPaper('letter');
	$DomPDF->render();

	$FileName = $_SESSION['DatabaseName'] . '_ProductSpecification_' . date('Y-m-d') . '.pdf';

	$DomPDF->stream($FileName, array("Attachment" => false));
} else {

	$Title = __('Select Product Specification To Print');
	$ViewTopic = 'QualityAssurance';
	$BookMark = '';
	include ('includes/header.php');
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/printer.png" title="' . __('Print') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<legend>', __('Select Product Specification'), '</legend>
			<field>
				<label for="KeyValue">' . __('Enter Specification Name') . ':</label>
				<input type="text" name="KeyValue" size="25" maxlength="25" />
			</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PickSpec" value="' . __('Submit') . '" />
		</div>
	</form>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<fieldset>
			<field>
			<label for="KeyValue">' . __('Or Select Existing Specification') . ':</label>';
	$SQLSpecSelect = "SELECT DISTINCT(keyval),
							description
						FROM prodspecs LEFT OUTER JOIN stockmaster
						ON stockmaster.stockid=prodspecs.keyval";

	$ResultSelection = DB_query($SQLSpecSelect);
	echo '<select name="KeyValue">';

	while ($MyRowSelection = DB_fetch_array($ResultSelection)) {
		echo '<option value="' . $MyRowSelection['keyval'] . '">' . $MyRowSelection['keyval'] . ' - ' . htmlspecialchars($MyRowSelection['description'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
	echo '</select>';
	echo '</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PickSpec" value="' . __('Submit') . '" />
		</div>
		</form>';
	include ('includes/footer.php');
}

