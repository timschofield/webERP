<?php
$PageSecurity = 11;
include ('includes/session.inc');
$title = _('Fixed Asset Register');
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
	$DateFrom = FormatDateForSQL($_POST['FromDate']);
	$DateTo = FormatDateForSQL($_POST['ToDate']);
	$sql = 'SELECT fixedassets.assetid,
								fixedassets.description,
								fixedassets.longdescription,
								fixedassets.assetcategoryid,
								fixedassets.serialno,
								fixedassetlocations.locationdescription,
								fixedassets.datepurchased,
								fixedassetlocations.parentlocationid,
								fixedassets.assetlocation,
								SUM(CASE WHEN (fixedassettrans.transdate <"' . $DateFrom . '" AND fixedassettrans.fixedassettranstype="cost") THEN fixedassettrans.amount ELSE 0 END) AS bfwdcost,
								SUM(CASE WHEN (fixedassettrans.transdate <"' . $DateFrom .'" AND fixedassettrans.fixedassettranstype="depn") THEN fixedassettrans.amount ELSE 0 END) AS bfwddepn,
								SUM(CASE WHEN (fixedassettrans.transdate >="' . $DateFrom .'"  AND fixedassettrans.transdate <="' . $DateTo . '" AND fixedassettrans.fixedassettranstype="cost") THEN fixedassettrans.amount ELSE 0 END) AS periodadditions,
								SUM(CASE WHEN fixedassettrans.transdate >="' . $DateFrom . '"  AND fixedassettrans.transdate <="' . $DateTo . '" AND fixedassettrans.fixedassettranstype="depn" THEN fixedassettrans.amount ELSE 0 END) AS perioddepn
					FROM fixedassets
					INNER JOIN fixedassetcategories ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
					INNER JOIN fixedassetlocations ON fixedassets.assetlocation=fixedassetlocations.locationid
					INNER JOIN fixedassettrans ON fixedassets.assetid=fixedassettrans.assetid
					WHERE fixedassets.assetcategoryid ' . LIKE . '"' . $_POST['AssetCategory'] . '"
					AND fixedassets.assetid ' . LIKE . '"' . $_POST['AssetID'] . '"
					GROUP BY fixedassets.assetid,
										fixedassets.description,
										fixedassets.longdescription,
										fixedassets.assetcategoryid,
										fixedassets.serialno,
										fixedassetlocations.locationdescription,
										fixedassets.datepurchased,
										fixedassetlocations.parentlocationid,
										fixedassets.assetlocation';
	$result = DB_query($sql, $db);
	if (isset($_POST['pdf'])) {
		$FontSize = 10;
		$pdf->addinfo('Title', _('Fixed Asset Register'));
		$PageNumber = 1;
		$line_height = 12;
		if ($_POST['AssetCategory']=='%') {
			$AssetCategory=_('All');
		} else {
			$CategorySQL='SELECT categorydescription FROM fixedassetcategories WHERE categoryid="'.$_POST['AssetCategory'].'"';
			$CategoryResult=DB_query($CategorySQL, $db);
			$CategoryRow=DB_fetch_array($CategoryResult);
			$AssetCategory=$CategoryRow['categorydescription'];
		}
	
		if ($_POST['AssetID']=='%') {
			$AssetDescription =_('All');
		} else {
			$AssetSQL='SELECT description FROM fixedassets WHERE assetid="'.$_POST['AssetID'].'"';
			$AssetResult=DB_query($AssetSQL, $db);
			$AssetRow=DB_fetch_array($AssetResult);
			$AssetDescription =$AssetRow['description'];
		}
		PDFPageHeader();
	} elseif (isset($_POST['csv'])) {
		$csv_output = "'Asset ID','Description','Serial Number','Location','Date Acquired','Cost B/Fwd','Period Additions','Depn B/Fwd','Period Depreciation','Cost C/Fwd', 'Accum Depn C/Fwd','NBV','Disposal Value'\n";
	} else {
		echo '<form name="RegisterForm" method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '"><table class=selection>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<br><table width=80% cellspacing="1" class=selection><tr>';
		echo '<th>' . _('Asset ID') . '</th>';
		echo '<th>' . _('Description') . '</th>';
		echo '<th>' . _('Serial Number') . '</th>';
		echo '<th>' . _('Location') . '</th>';
		echo '<th>' . _('Date Acquired') . '</th>';
		echo '<th>' . _('Cost B/fwd') . '</th>';
		echo '<th>' . _('Depn B/fwd') . '</th>';
		echo '<th>' . _('Additions') . '</th>';
		echo '<th>' . _('Depn') . '</th>';
		echo '<th>' . _('Cost C/fwd') . '</th>';
		echo '<th>' . _('Depn C/fwd') . '</th>';
		echo '<th>' . _('NBV') . '</th>';
		echo '<th>' . _('Disposal Value') . '</th></tr>';
	}
	$TotalCostBfwd =0;
	$TotalCostCfwd = 0;
	$TotalDepnBfwd = 0;
	$TotalDepnCfwd = 0;
	$TotalAdditions = 0;
	$TotalDepn = 0;
	$TotalDisposals = 0;
	$TotalNBV = 0;
	
	while ($myrow = DB_fetch_array($result)) {
		/*
		 * $Ancestors = array();
		$Ancestors[0] = $myrow['locationdescription'];
		$i = 0;
		while ($Ancestors[$i] != '') {
			$LocationSQL = 'SELECT parentlocationid from fixedassetlocations where locationdescription="' . $Ancestors[$i] . '"';
			$LocationResult = DB_query($LocationSQL, $db);
			$LocationRow = DB_fetch_array($LocationResult);
			$ParentSQL = 'SELECT locationdescription from fixedassetlocations where locationid="' . $LocationRow['parentlocationid'] . '"';
			$ParentResult = DB_query($ParentSQL, $db);
			$ParentRow = DB_fetch_array($ParentResult);
			$i++;
			$Ancestors[$i] = $ParentRow['locationdescription'];
		}
		*/
				
		if (isset($_POST['pdf'])) {
			$LeftOvers = $pdf->addTextWrap($XPos, $YPos, 30 - $Left_Margin, $FontSize, $myrow['assetid']);
			$LeftOvers = $pdf->addTextWrap($XPos + 30, $YPos, 150 - $Left_Margin, $FontSize, $myrow['description']);
			$LeftOvers = $pdf->addTextWrap($XPos + 180, $YPos, 40 - $Left_Margin, $FontSize, $myrow['serialno']);
			/*
			 * $TempYPos = $YPos;
			for ($i = 1;$i < sizeof($Ancestors) - 1;$i++) {
				for ($j = 0;$j < $i;$j++) {
					$TempYPos-= (0.8 * $line_height);
					$LeftOvers = $pdf->addTextWrap($XPos + 300, $TempYPos, 300 - $Left_Margin, $FontSize, '	');
				}
				$LeftOvers = $pdf->addTextWrap($XPos + 300, $TempYPos, 300 - $Left_Margin, $FontSize, '|_' . $Ancestors[$i]);
			}
			* */
			
			$LeftOvers = $pdf->addTextWrap($XPos + 220, $YPos, 50 - $Left_Margin, $FontSize, ConvertSQLDate($myrow['datepurchased']));
			$LeftOvers = $pdf->addTextWrap($XPos + 270, $YPos, 70, $FontSize, number_format($myrow['costbfwd'], 0), 'right');
			$LeftOvers = $pdf->addTextWrap($XPos + 340, $YPos, 70, $FontSize, number_format($myrow['depnbfwd'], 0), 'right');
			$LeftOvers = $pdf->addTextWrap($XPos + 410, $YPos, 70, $FontSize, number_format($myrow['periodadditions'], 0), 'right');
			$LeftOvers = $pdf->addTextWrap($XPos + 480, $YPos, 70, $FontSize, number_format($myrow['perioddepn'], 0), 'right');
			$LeftOvers = $pdf->addTextWrap($XPos + 550, $YPos, 70, $FontSize, number_format($myrow['periodadditions'] + $myrow['costbfwd'], 0), 'right');
			$LeftOvers = $pdf->addTextWrap($XPos + 620, $YPos, 70, $FontSize, number_format($myrow['periodepn'] + $myrow['depnbfwd'], 0), 'right');
			$LeftOvers = $pdf->addTextWrap($XPos + 690, $YPos, 70, $FontSize, number_format($myrow['periodadditions'] + $myrow['costbfwd'] - $myrow['periodepn'] - $myrow['depnbfwd'], 0), 'right');
			
			$YPos = $TempYPos - (0.8 * $line_height);
			if ($YPos < $Bottom_Margin + $line_height) {
				PDFPageHeader();
			}
		} elseif (isset($_POST['csv'])) {
			$csv_output.= $myrow['assetid'] . "," . $myrow['longdescription'] .",".$myrow['serialno'].",".$myrow['locationdescription'].",".$myrow['datepurchased'].",".$myrow['costbfwd'].",".$myrow['periodadditions']."," . $myrow['depnbfwd'] . "," .$myrow['perioddepn'].",".($myrow['costbfwd']+$myrow['periodadditions']). ", " . ($myrow['depnbfwd']+$myrow['perioddepn']) . ", " . ($myrow['costbfwd']+$myrow['periodadditions']-$myrow['depnbfwd']-$myrow['perioddepn']) . "\n";

		} else {
			echo '<tr><td style="vertical-align:top">' . $myrow['assetid'] . '</td>';
			echo '<td style="vertical-align:top">' . $myrow['longdescription'] . '</td>';
			echo '<td style="vertical-align:top">' . $myrow['serialno'] . '</td>';
			echo '<td>' . $myrow['locationdescription'] . '<br>';
			for ($i = 1;$i < sizeOf($Ancestors) - 1;$i++) {
				for ($j = 0;$j < $i;$j++) {
					echo '&nbsp;&nbsp;&nbsp;&nbsp;';
				}
				echo '|_' . $Ancestors[$i] . '<br>';
			}
			echo '</td><td style="vertical-align:top">' . ConvertSQLDate($myrow['datepurchased']) . '</td>';
			echo '<td style="vertical-align:top" class=number>' . number_format($myrow['costbfwd'], 2) . '</td>';
			echo '<td style="vertical-align:top" class=number>' . number_format($myrow['depnbfwd'], 2) . '</td>';
			echo '<td style="vertical-align:top" class=number>' . number_format($myrow['periodadditions'], 2) . '</td>';
			echo '<td style="vertical-align:top" class=number>' . number_format($myrow['perioddepn'], 2) . '</td>';
			echo '<td style="vertical-align:top" class=number>' . number_format($myrow['costbfwd']+$myrow['periodadditions'], 2) . '</td>';
			echo '<td style="vertical-align:top" class=number>' . number_format($myrow['perioddepn']+$myrow['depnbfwd'], 2) . '</td>';
			echo '<td style="vertical-align:top" class=number>' . number_format($myrow['costbfwd']+$myrow['periodadditions']-$myrow['perioddepn']-$myrow['depnbfwd'], 2) . '</td>';
			
			//echo '<td style="vertical-align:top" class=number>' . number_format($myrow['disposalvalue'], 2) . '</td></tr>';
		}
		$TotalCostBfwd +=$myrow['costbfwd'];
		$TotalCostCfwd += ($myrow['costbfwd']+$myrow['periodadditions']);
		$TotalDepnBfwd += $myrow['depnbfwd'];
		$TotalDepnCfwd += ($myrow['depnbfwd']+$myrow['perioddepn']);
		$TotalAdditions += $myrow['periodadditions'];
		$TotalDepn += $myrow['perioddepn'];
		$TotalDisposals += 0;
		$TotalNBV += ($myrow['costbfwd']+$myrow['periodadditions']- $myrow['depnbfwd']-$myrow['perioddepn']);
	}

	if (isset($_POST['pdf'])) {
		$LeftOvers = $pdf->addTextWrap($XPos, $YPos, 300 - $Left_Margin, $FontSize, _('TOTAL'));
		$LeftOvers = $pdf->addTextWrap($XPos + 270, $YPos, 70, $FontSize, number_format($TotalCostBfwd, 0), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 340, $YPos, 70, $FontSize, number_format($TotalDepnBfwd, 0), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 410, $YPos, 70, $FontSize, number_format($TotalAdditions, 0), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 480, $YPos, 70, $FontSize, number_format($TotalDepn, 0), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 550, $YPos, 70, $FontSize, number_format($TotalCostCfwd, 0), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 620, $YPos, 70, $FontSize, number_format($TotalDepnCfwd, 0), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 690, $YPos, 70, $FontSize, number_format($TotalNBV, 0), 'right');
		
		$pdf->Output($_SESSION['DatabaseName'] . '_Asset Register_' . date('Y-m-d') . '.pdf', 'I');
		exit;
	} elseif (isset($_POST['csv'])) {
		$filename =  $_SESSION['reports_dir'] . '/FixedAssetRegister_' . Date('Y-m-d') .'.csv';
		$csvfile = fopen($filename, 'w');
		$i = fwrite($csvfile, $csv_output);
		header('Location: ' .$_SESSION['reports_dir'] . '/FixedAssetRegister_' . Date('Y-m-d') .'.csv');

	} else {
		echo '<input type=hidden name=FromDate value="' . $_POST['FromDate'] . '">';
		echo '<input type=hidden name=ToDate value=' . $_POST['ToDate'] . '>';
		echo '<input type=hidden name=AssetCategory value=' . $_POST['AssetCategory'] . '>';
		echo '<input type=hidden name=AssetID value=' . $_POST['AssetID'] . '>';
		echo '<input type=hidden name=AssetLocation value=' . $_POST['AssetLocation'] . '>';
		//Total Values
		echo '<tr><th style="vertical-align:top" colspan="5">' . _('TOTAL') . '</th>';
		echo '<th style="text-align:right">' . number_format($TotalCostBfwd, 2) . '</th>';
		echo '<th style="text-align:right">' . number_format($TotalDepnBfwd, 2) . '</th>';
		echo '<th style="text-align:right">' . number_format($TotalAdditions, 2) . '</th>';
		echo '<th style="text-align:right">' . number_format($TotalDepn, 2) . '</th>';
		echo '<th style="text-align:right">' . number_format($TotalCostCfwd, 2) . '</th>';
		echo '<th style="text-align:right">' . number_format($TotalDepnCfwd, 2) . '</th>';
		echo '<th style="text-align:right">' . number_format($TotalNBV, 2) . '</th>';
		echo '<th style="text-align:right">' . number_format($TotalDisposals, 2) . '</th></tr>';
		echo '</table>';
		echo '<br><div class="centre"><input type="Submit" name="pdf" value="' . _('Print as a pdf') . '">&nbsp;';
		echo '<input type="Submit" name="csv" value="' . _('Print as CSV') . '"></div></form>';
	}
} else {
	include ('includes/header.inc');
	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . $title;
	
	$result = DB_query('SELECT categoryid,categorydescription FROM fixedassetcategories', $db);
	echo '<form name="RegisterForm" method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '"><table class=selection>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<tr><th>' . _('Asset Category') . '</th>';
	echo '<td><select name=AssetCategory>';
	echo '<option value="%">' . _('ALL') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['AssetCategory']) and $myrow['categoryid'] == $_POST['AssetCategory']) {
			echo '<option selected value=' . $myrow['categoryid'] . '>' . $myrow['categorydescription'] . '</option>';
		} else {
			echo '<option value=' . $myrow['categoryid'] . '>' . $myrow['categorydescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	$sql = 'SELECT  locationid, locationdescription FROM fixedassetlocations';
	$result = DB_query($sql, $db);
	echo '<tr><th>' . _('Asset Location') . '</th>';
	echo '<td><select name=AssetLocation>';
	echo '<option value="All">' . _('ALL') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['AssetLocation']) and $myrow['locationdescription'] == $_POST['AssetLocation']) {
			echo '<option selected value="' . $myrow['locationdescription'] . '">' . $myrow['locationdescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['locationdescription'] . '">' . $myrow['locationdescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	$sql = 'SELECT assetid, description FROM fixedassets';
	$result = DB_query($sql, $db);
	echo '<tr><th>' . _('Asset') . '</th>';
	echo '<td><select name="AssetID">';
	echo '<option value="%">' . _('ALL') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['AssetID']) and $myrow['assetid'] == $_POST['AssetID']) {
			echo '<option selected value=' . $myrow['assetid'] . '>' . $myrow['assetid'] . ' - ' . $myrow['description'] . '</option>';
		} else {
			echo '<option value=' . $myrow['assetid'] . '>'  . $myrow['assetid'] . ' - ' . $myrow['description'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	if (empty($_POST['FromDate'])) {
		$_POST['FromDate'] = date($_SESSION['DefaultDateFormat'], mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1));
	}
	if (empty($_POST['ToDate'])) {
		$_POST['ToDate'] = date($_SESSION['DefaultDateFormat']);
	}
	//FULUSI CHANGE BELOW TO ADD TIME
	echo '<tr><th>' . _(' From Date') . "</th><td><input type='text' class='date' alt='" . $_SESSION['DefaultDateFormat'] . "' name='FromDate' maxlength=10 size=11 value='" . $_POST['FromDate'] . "'></td>";
	echo '</tr>';
	echo '<tr><th>' . _('To Date ') . "</th><td><input type='text' class='date' alt='" . $_SESSION['DefaultDateFormat'] . "' name='ToDate' maxlength=10 size=11 value='" . $_POST['ToDate'] . "'></td>";
	echo '</tr>';
	//end of FULUSI STUFF
	echo '</table><br>';
	echo '<div class="centre"><input type="Submit" name="submit" value="' . _('Show Assets') . '">&nbsp;';
	echo '<input type="Submit" name="pdf" value="' . _('Print as a pdf') . '">&nbsp;';
	echo '<input type="Submit" name = "csv" value= "' . _('Print as CSV') . '"></div>';
	echo '</form>';
}
include ('includes/footer.inc');


function PDFPageHeader (){
	global $PageNumber,
				$pdf,
				$XPos,
				$YPos,
				$Page_Height,
				$Page_Width,
				$Top_Margin,
				$FontSize,
				$Left_Margin,
				$Right_Margin,
				$line_height;
				$AssetDescription;
				$AssetCategory;
				
	if ($PageNumber>1){
		$pdf->newPage();
	}
	
	$FontSize=10;
	$YPos= $Page_Height-$Top_Margin;
	$XPos=0;
	$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+20,$YPos-50,0,60);
	
	
	
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos,240,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*1),240,$FontSize, _('Asset Category ').' ' . $AssetCategory );
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*2),240,$FontSize, _('Asset Location ').' ' . $_POST['AssetLocation'] );
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*3),240,$FontSize, _('Asset ID').': ' . $AssetDescription);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*4),240,$FontSize, _('From').': ' . $_POST['FromDate']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*5),240,$FontSize, _('To').': ' . $_POST['ToDate']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*7),240,$FontSize, _('Page'). ' ' . $PageNumber);
	
	$YPos -= 60;
	
	$YPos -=2*$line_height;
	//Note, this is ok for multilang as this is the value of a Select, text in option is different
	
	$YPos -=(2*$line_height);
	
	/*Draw a rectangle to put the headings in     */
	$YTopLeft=$YPos+$line_height;
	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
	$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
	$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
	
	/*set up the headings */
	$FontSize=10;
	$XPos = $Left_Margin+1;
	$YPos -=(0.8*$line_height);
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,30,$FontSize,  _('Asset'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+30,$YPos,150,$FontSize,  _('Description'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+180,$YPos,40,$FontSize,  _('Serial No.'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+220,$YPos,50,$FontSize,  _('Purchased'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+270,$YPos,70,$FontSize,  _('Cost B/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+340,$YPos,70,$FontSize,  _('Depn B/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+410,$YPos,70,$FontSize,  _('Additions'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+480,$YPos,70,$FontSize,  _('Depreciation'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+550,$YPos,70,$FontSize,  _('Cost C/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+620,$YPos,70,$FontSize,  _('Depn C/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+690,$YPos,70,$FontSize,  _('Net Book Value'), 'centre');
	//$LeftOvers = $pdf->addTextWrap($XPos+760,$YPos,70,$FontSize,  _('Disposal Proceeds'), 'centre');
	
	$pdf->line($Left_Margin, $YTopLeft,$Page_Width-$Right_Margin, $YTopLeft);
	$pdf->line($Left_Margin, $YTopLeft,$Left_Margin, $Bottom_Margin);
	$pdf->line($Left_Margin, $Bottom_Margin,$Page_Width-$Right_Margin, $Bottom_Margin);
	$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$Page_Width-$Right_Margin, $YTopLeft);
	
	$FontSize=8;
	$YPos -= (1.5 * $line_height);
	
	$PageNumber++;
}

?>