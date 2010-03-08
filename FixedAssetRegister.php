<?php
$PageSecurity = 11;
include ('includes/session.inc');
$title = _('Fixed Asset Register');
$TotalCost = 0;
$Totaldepn = 0;
$TotaldepnInt = 0;
$Totaldisp = 0;
$TotaldepInt = 0;
$TotalNBV = 0;
$TotalNBVInt = 0;
$csv_output = '';
// Reports being generated in HTML, PDF and CSV/EXCEL format
if (isset($_POST['submit']) or isset($_POST['pdf']) or isset($_POST['csv'])) {
	if (isset($_POST['pdf'])) {
		$PaperSize = 'A4_Landscape';
		include ('includes/PDFStarter.php');
	} else if (empty($_POST['csv'])) {
		include ('includes/header.inc');
		echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title;
	}
	$dateFrom = FormatDateForSQL($_POST['fromDate']);
	$dateTo = FormatDateForSQL($_POST['toDate']);
	$sql = 'SELECT assetmanager.id,
			assetmanager.stockid,
			stockmaster.longdescription,
			stockmaster.categoryid,
			assetmanager.serialno,
			fixedassetlocations.locationdescription,
			assetmanager.cost,
			assetmanager.datepurchased,
			assetmanager.depn,
			assetmanager.disposalvalue,
			fixedassetlocations.parentlocationid,
			assetmanager.location
			FROM assetmanager
		LEFT JOIN stockmaster ON assetmanager.stockid=stockmaster.stockid
		LEFT JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid
		LEFT JOIN fixedassetlocations ON assetmanager.location=fixedassetlocations.locationid
		WHERE stockmaster.categoryid like "' . $_POST['assetcategory'] . '"
		AND stockmaster.stockid like "' . $_POST['assettype'] . '"
		AND assetmanager.datepurchased BETWEEN "' . $dateFrom . '" AND "' . $dateTo . '"';
	$result = DB_query($sql, $db);
	if (isset($_POST['pdf'])) {
		$FontSize = 10;
		$pdf->addinfo('Title', _('Fixed Asset Register'));
		$PageNumber = 1;
		$line_height = 12;
		include ('includes/PDFAssetRegisterHeader.inc');
	} elseif (isset($_POST['csv'])) {
		$csv_output = "Asset ID,Stock ID"; // 'Description','Serial Number','Location','Date Acquired','Cost','Depreciation','NBV','Cost','Depreciation','NBV','Disposal Value'";
		$csv_output.= "\n";
	} else {
		echo '<form name="RegisterForm" method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '"><table>';
		echo '<br><table width=80% cellspacing="9"><tr>';
		echo '<th colspan=6></th>';
		echo '<th colspan=3>' . _('External Depreciation') . '</th>';
		echo '<th colspan=3>' . _('Internal Depreciation') . '</th><th></th></tr><tr>';
		echo '<th>' . _('Asset ID') . '</th>';
		echo '<th>' . _('Stock ID') . '</th>';
		echo '<th>' . _('Description') . '</th>';
		echo '<th>' . _('Serial Number') . '</th>';
		echo '<th>' . _('Location') . '</th>';
		echo '<th>' . _('Date Acquired') . '</th>';
		echo '<th>' . _('Cost') . '</th>';
		echo '<th>' . _('Depreciation') . '</th>';
		echo '<th>' . _('NBV') . '</th>';
		echo '<th>' . _('Cost') . '</th>';
		echo '<th>' . _('Depreciation') . '</th>';
		echo '<th>' . _('NBV') . '</th>';
		echo '<th>' . _('Disposal Value') . '</th></tr>';
	}
	while ($myrow = DB_fetch_array($result)) {
		$ancestors = array();
		$ancestors[0] = $myrow['locationdescription'];
		$i = 0;
		while ($ancestors[$i] != '') {
			$locationsql = 'SELECT parentlocationid from fixedassetlocations where locationdescription="' . $ancestors[$i] . '"';
			$locationresult = DB_query($locationsql, $db);
			$locationrow = DB_fetch_array($locationresult);
			$parentsql = 'SELECT locationdescription from fixedassetlocations where locationid="' . $locationrow['parentlocationid'] . '"';
			$parentresult = DB_query($parentsql, $db);
			$parentrow = DB_fetch_array($parentresult);
			$i++;
			$ancestors[$i] = $parentrow['locationdescription'];
		}
		$catidsql = 'SELECT stkcatpropid FROM stockcatproperties WHERE categoryid="' . $myrow['categoryid'] . '" AND label="' . _('Annual Internal Depreciation Percentage') . '"';
		$catidresult = DB_query($catidsql, $db);
		$catidrow = DB_fetch_array($catidresult);
		$catvaluesql = 'SELECT value FROM stockitemproperties WHERE stockid="' . $myrow['stockid'] . '" AND stkcatpropid=' . $catidrow['stkcatpropid'];
		$catvalueresult = DB_query($catvaluesql, $db);
		$catvaluerow = DB_fetch_array($catvalueresult);
		$MonthsOld = DateDiff(date('d/m/Y'), ConvertSQLDate($myrow['datepurchased']), 'm');
		$InternalDepreciation = $myrow['cost'] * $catvaluerow['value'] / 100 * $MonthsOld / 12;
		if (($InternalDepreciation + $myrow['disposalvalue']) > $myrow['cost']) {
			$InternalDepreciation = $myrow['cost'] - $myrow['disposalvalue'];
		}
		if (in_array($_POST['assetlocation'], $ancestors) or $_POST['assetlocation'] == 'All') {
			if (isset($_POST['pdf'])) {
				$LeftOvers = $pdf->addTextWrap($Xpos, $YPos, 300 - $Left_Margin, $FontSize, $myrow['id']);
				$LeftOvers = $pdf->addTextWrap($Xpos + 40, $YPos, 300 - $Left_Margin, $FontSize, $myrow['stockid']);
				$LeftOvers = $pdf->addTextWrap($Xpos + 80, $YPos, 300 - $Left_Margin, $FontSize, $myrow['longdescription']);
				$LeftOvers = $pdf->addTextWrap($Xpos + 250, $YPos, 300 - $Left_Margin, $FontSize, $myrow['serialno']);
				$LeftOvers = $pdf->addTextWrap($Xpos + 300, $YPos, 300 - $Left_Margin, $FontSize, $myrow['locationdescription']);
				$TempYPos = $YPos;
				for ($i = 1;$i < sizeOf($ancestors) - 1;$i++) {
					for ($j = 0;$j < $i;$j++) {
						$TempYPos-= (0.8 * $line_height);
						$LeftOvers = $pdf->addTextWrap($Xpos + 300, $TempYPos, 300 - $Left_Margin, $FontSize, '	');
					}
					$LeftOvers = $pdf->addTextWrap($Xpos + 300, $TempYPos, 300 - $Left_Margin, $FontSize, '|_' . $ancestors[$i]);
				}
				$LeftOvers = $pdf->addTextWrap($Xpos + 380, $YPos, 300 - $Left_Margin, $FontSize, ConvertSQLDate($myrow['datepurchased']));
				$LeftOvers = $pdf->addTextWrap($Xpos + 440, $YPos, 55, $FontSize, number_format($myrow['cost'], 0), 'right');
				$LeftOvers = $pdf->addTextWrap($Xpos + 495, $YPos, 55, $FontSize, number_format($myrow['depn'], 0), 'right');
				$LeftOvers = $pdf->addTextWrap($Xpos + 550, $YPos, 50, $FontSize, number_format($myrow['cost'] - $myrow['depn'], 0), 'right');
				$LeftOvers = $pdf->addTextWrap($Xpos + 600, $YPos, 55, $FontSize, number_format($myrow['cost'], 0), 'right');
				$LeftOvers = $pdf->addTextWrap($Xpos + 655, $YPos, 55, $FontSize, number_format($InternalDepreciation, 0), 'right');
				$LeftOvers = $pdf->addTextWrap($Xpos + 710, $YPos, 50, $FontSize, number_format($myrow['cost'] - $InternalDepreciation, 0), 'right');
				$YPos = $TempYPos - (0.8 * $line_height);
				if ($YPos < $Bottom_Margin + $line_height) {
					include ('includes/PDFAssetRegisterHeader.inc');
				}
			} elseif (isset($_POST['csv'])) {
				$csv_output.= $myrow['id'] . "," . $myrow['stockid'] . "\n"; //;.",".$myrow['longdescription'].",".$myrow['serialno'].",".$myrow['locationdescription'].",".$myrow['datepurchased'].",".$myrow['cost'].",".$myrow['depn'].",".($myrow['cost']-$myrow['depn']).",".$myrow['cost'].",".$InternalDepreciation.",".($myrow['cost']-$InternalDepreciation).",".$myrow['disposalvalue']."\n";

			} else {
				echo '<tr><td style="vertical-align:top">' . $myrow['id'] . '</td>';
				echo '<td style="vertical-align:top">' . $myrow['stockid'] . '</td>';
				echo '<td style="vertical-align:top">' . $myrow['longdescription'] . '</td>';
				echo '<td style="vertical-align:top">' . $myrow['serialno'] . '</td>';
				echo '<td>' . $myrow['locationdescription'] . '<br>';
				for ($i = 1;$i < sizeOf($ancestors) - 1;$i++) {
					for ($j = 0;$j < $i;$j++) {
						echo '&nbsp;&nbsp;&nbsp;&nbsp;';
					}
					echo '|_' . $ancestors[$i] . '<br>';
				}
				echo '</td><td style="vertical-align:top">' . ConvertSQLDate($myrow['datepurchased']) . '</td>';
				echo '<td style="vertical-align:top" class=number>' . number_format($myrow['cost'], 2) . '</td>';
				echo '<td style="vertical-align:top" class=number>' . number_format($myrow['depn'], 2) . '</td>';
				echo '<td style="vertical-align:top" class=number>' . number_format($myrow['cost'] - $myrow['depn'], 2) . '</td>';
				echo '<td style="vertical-align:top" class=number>' . number_format($myrow['cost'], 2) . '</td>';
				echo '<td style="vertical-align:top" class=number>' . number_format($InternalDepreciation, 2) . '</td>';
				echo '<td style="vertical-align:top" class=number>' . number_format($myrow['cost'] - $InternalDepreciation, 2) . '</td>';
				echo '<td style="vertical-align:top" class=number>' . number_format($myrow['disposalvalue'], 2) . '</td></tr>';
			}
			$TotalCost = $TotalCost + $myrow['cost'];
			$Totaldepn = $Totaldepn_pdf + $myrow['depn'];
			$TotalNBV = $TotalCost - $Totaldepn;
			$TotaldepnInt = $TotaldepnInt + $InternalDepreciation;
			$TotalNBVInt = $TotalCost - $TotaldepInt;
			$Totaldisp = $Totaldisp + $myrow['disposalvalue'];
		}
	}
	if (isset($_POST['pdf'])) {
		$LeftOvers = $pdf->addTextWrap($Xpos, $YPos, 300 - $Left_Margin, $FontSize, "TOTAL");
		$LeftOvers = $pdf->addTextWrap($Xpos + 40, $YPos, 300 - $Left_Margin, $FontSize, '  ');
		$LeftOvers = $pdf->addTextWrap($Xpos + 80, $YPos, 300 - $Left_Margin, $FontSize, '  ');
		$LeftOvers = $pdf->addTextWrap($Xpos + 250, $YPos, 300 - $Left_Margin, $FontSize, '  ');
		$LeftOvers = $pdf->addTextWrap($Xpos + 300, $YPos, 300 - $Left_Margin, $FontSize, '  ');
		$LeftOvers = $pdf->addTextWrap($Xpos + 300, $YPos, 300 - $Left_Margin, $FontSize, '  ');
		$LeftOvers = $pdf->addTextWrap($Xpos + 380, $YPos, 300 - $Left_Margin, $FontSize, ' '); // number_format($Totaldepn_pdf,2),'right');
		$LeftOvers = $pdf->addTextWrap($Xpos + 440, $YPos, 55, $FontSize, number_format($TotalCost, 2), 'right');
		$LeftOvers = $pdf->addTextWrap($Xpos + 495, $YPos, 55, $FontSize, number_format($Totaldepn, 2), 'right');
		$LeftOvers = $pdf->addTextWrap($Xpos + 550, $YPos, 50, $FontSize, number_format($TotalNBV, 2), 'right');
		//$LeftOvers = $pdf->addTextWrap($Xpos+600,$YPos,50,$FontSize, number_format($TotalNBV_pdf,2),'right');
		$LeftOvers = $pdf->addTextWrap($Xpos + 600, $YPos, 55, $FontSize, number_format($TotalCost, 2), 'right');
		$LeftOvers = $pdf->addTextWrap($Xpos + 655, $YPos, 50, $FontSize, number_format($TotaldepnInt, 2), 'right');
		$LeftOvers = $pdf->addTextWrap($Xpos + 705, $YPos, 55, $FontSize, number_format($TotalNBVInt, 2), 'right');
		$pdf->Output($_SESSION['DatabaseName'] . '_Asset Register_' . date('Y-m-d') . '.pdf', 'I');
		exit;
	} elseif (isset($_POST['csv'])) {
		// download now. WFT am I waiting for??? Don't use headers, kinda messy
		// $filename="/tmp/".date("Y-m-d").".csv";
		$filename = "/home/tim/workbench/webERP/trunk/companies/weberpdemo/reportwriter/test.csv";
		$csvfile = fopen($filename, 'w');
		$i = fwrite($csvfile, $csv_output);
		header("Location: companies/weberpdemo/reportwriter/test.csv");
		// echo "Testing successfully done";
		// header("Content-Type: text/csv");
		// header("Content-disposition: attachment; filename= $cvsfile");

	} else {
		echo '<input type=hidden name=fromDate value="' . $_POST['fromDate'] . '">';
		echo '<input type=hidden name=toDate value=' . $_POST['toDate'] . '>';
		echo '<input type=hidden name=assetcategory value=' . $_POST['assetcategory'] . '>';
		echo '<input type=hidden name=assettype value=' . $_POST['assettype'] . '>';
		echo '<input type=hidden name=assetlocation value=' . $_POST['assetlocation'] . '>';
		//Total Values
		echo '<tr></tr>';
		echo '<tr><th style="vertical-align:top">TOTAL</th>';
		echo '<th style="vertical-align:top"></th>';
		echo '<th style="vertical-align:top"></th>';
		echo '<th style="vertical-align:top"></th>';
		echo '<th style="vertical-align:top"></th>';
		echo '<th style="vertical-align:top"></th>';
		echo '<th style="vertical-align:top" class=number>' . number_format($TotalCost, 2) . '</th>';
		echo '<th style="vertical-align:top" class=number>' . number_format($Totaldepn, 2) . '</th>';
		echo '<th style="vertical-align:top" class=number>' . number_format($TotalNBV, 2) . '</th>';
		echo '<th style="vertical-align:top" class=number>' . number_format($TotalCost, 2) . '</th>';
		echo '<th style="vertical-align:top" class=number>' . number_format($TotaldepnInt, 2) . '</th>';
		echo '<th style="vertical-align:top" class=number>' . number_format($TotalNBVInt, 2) . '</th>';
		echo '<th style="vertical-align:top" class=number>' . number_format($Totaldisp, 2) . '</th></tr>';
		echo '</table>';
		echo '<div class="centre"><input type="Submit" name="pdf" value="' . _('Print as a pdf') . '"></div></form>';
		echo '</p>';
		echo '<div class="centre"><input type="Submit" name="csv" value="' . _('Print as CSV') . '"></div></form>';
	}
} else {
	include ('includes/header.inc');
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title;
	$sql = "SELECT * FROM stockcategory WHERE stocktype='" . 'A' . "'";
	$result = DB_query($sql, $db);
	echo '<form name="RegisterForm" method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '"><table>';
	echo '<tr><th>' . _('Asset Category') . '</th>';
	echo '<td><select name=assetcategory>';
	echo '<option value="%">' . _('ALL') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['assetcategory']) and $myrow['categoryid'] == $_POST['assetcategory']) {
			echo '<option selected value=' . $myrow['categoryid'] . '>' . $myrow['categorydescription'] . '</option>';
		} else {
			echo '<option value=' . $myrow['categoryid'] . '>' . $myrow['categorydescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	$sql = "SELECT  locationid, locationdescription FROM fixedassetlocations";
	$result = DB_query($sql, $db);
	echo '<tr><th>' . _('Asset Location') . '</th>';
	echo '<td><select name=assetlocation>';
	echo '<option value="All">' . _('ALL') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['assetlocation']) and $myrow['locationdescription'] == $_POST['assetlocation']) {
			echo '<option selected value="' . $myrow['locationdescription'] . '">' . $myrow['locationdescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['locationdescription'] . '">' . $myrow['locationdescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	$sql = "SELECT stockid, description FROM stockmaster LEFT JOIN stockcategory ON stockmaster.categoryid=stockcategory.categoryid WHERE stocktype='A'";
	$result = DB_query($sql, $db);
	echo '<tr><th>' . _('Asset Type') . '</th>';
	echo '<td><select name=assettype>';
	echo '<option value="%">' . _('ALL') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['assettype']) and $myrow['stockid'] == $_POST['assettype']) {
			echo '<option selected value=' . $myrow['stockid'] . '>' . $myrow['description'] . '</option>';
		} else {
			echo '<option value=' . $myrow['stockid'] . '>' . $myrow['description'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	if (empty($_POST['fromDate'])) {
		$_POST['fromDate'] = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1));
	}
	if (empty($_POST['toDate'])) {
		$_POST['toDate'] = date($_SESSION['DefaultDateFormat']);
	}
	//FULUSI CHANGE BELOW TO ADD TIME
	echo '<tr><th>' . _(' From Date') . "</th><td><input type='text' class='date' alt='" . $_SESSION['DefaultDateFormat'] . "' name='fromDate' maxlength=10 size=11 value='" . $_POST['fromDate'] . "'></td>";
	echo '</tr>';
	echo '<tr><th>' . _('To Date ') . "</th><td><input type='text' class='date' alt='" . $_SESSION['DefaultDateFormat'] . "' name='toDate' maxlength=10 size=11 value='" . $_POST['toDate'] . "'></td>";
	echo '</tr>';
	//end of FULUSI STUFF
	echo '</table><br>';
	echo '<div class="centre"><input type="Submit" name="submit" value="' . _('Show Assets') . '"></div><br />';
	echo '<div class="centre"><input type="Submit" name="pdf" value="' . _('Print as a pdf') . '"></div><br/>';
	echo '<div class="centre"><input type="Submit" name = "csv" value= "' . _('Print as CSV') . '"></div>';
	echo '</form>';
}
include ('includes/footer.inc');
?>