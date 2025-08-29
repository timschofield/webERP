<?php

include('includes/class.cpdf.php');

/* A4_Landscape */
$DocumentPaper = 'A4';
$DocumentOrientation ='L';
$Page_Width=842;
$Page_Height=595;
$Top_Margin=30;
$Bottom_Margin=30;
$Left_Margin=40;
$Right_Margin=30;

$pdf = new Cpdf($DocumentOrientation, 'pt', $DocumentPaper);
$pdf->SetAutoPageBreak(true, 0);
$pdf->SetPrintHeader(false);
$pdf->AddPage();
$pdf->cMargin = 0;

/* Standard PDF file creation header stuff */

$pdf->addInfo('Author','webERP ' . $Version);
$pdf->addInfo('Creator','webERP https://www.weberp.org');
$pdf->addInfo('Title',$ReportSpec['reportheading']);
$pdf->addInfo('Subject',__('Sales Analysis Report') . ' ' . $ReportSpec['reportheading']);

$PageNumber = 0;
$LineHeight=12;

include('includes/PDFSalesAnalPageHeader.php');

$GrpData1='';
$GrpData2='';
$GrpData3='';

$Counter=0;

/*Make an array to hold accumulators for */
$AccumLvl1 = array();
$AccumLvl2 = array();
$AccumLvl3 = array();
$AccumLvl4 = array();

for ($i=0;$i<=10;$i++){
	$AccumLvl1[$i]=0;
	$AccumLvl2[$i]=0;
	$AccumLvl3[$i]=0;
	$AccumLvl4[$i]=0;
}

while ($MyRow = DB_fetch_array($Result)){

/*First off check that at least one of the columns of data has some none zero amounts */
	DB_data_seek($ColsResult,0); /*go back to the beginning */
	$ThisLineHasOutput=false;   /*assume no output to start with */
	while ($Cols = DB_fetch_array($ColsResult)){
		$ColumnNo ='col' . ((int) $Cols['colno'] + 8);
		if (abs($MyRow[$ColumnNo])>0.5){
			$ThisLineHasOutput = true;
		}
	}
	if ($ThisLineHasOutput==true){

		if ($MyRow['col5']!=$GrpData3 && $MyRow['col5']!='0' && $MyRow['col7']!='0'){
			/*Totals only relevant to GrpByLevel 3 if GrpByLevel 4 also used */
			if ($Counter > 0){ /*Dont want to print totals if this is the first record */
				$TotalText = mb_substr(__('TOTAL') . ' ' . $LastLine['col5'] . ' - ' . $LastLine['col6'],0,33);
				$LeftOvers = $pdf->addTextWrap(40,$Ypos,180,$FontSize,$TotalText);

				DB_data_seek($ColsResult,0);
				while ($Cols = DB_fetch_array($ColsResult)){
					$Xpos = 160 + $Cols['colno']*60;
					if ($Cols['calculation']==0){
						$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, locale_number_format($AccumLvl3[$Cols['colno']],0),'right');
					} else { /* its a calculation need to re-perform on the totals*/

						switch ($Cols['calcoperator']) {
							case '/':
								if ($AccumLvl3[$Cols['coldenominator']]==0){
									$TotalCalculation = 0;
								} else {
									$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] / $AccumLvl3[$Cols['coldenominator']];
								}
								break;
							case '+':
								$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] + $AccumLvl3[$Cols['coldenominator']];
								break;
							case '-':
								$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] + $AccumLvl3[$Cols['coldenominator']];
								break;
							case '*':
								$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] * $Cols['constant'];
								break;
							case 'C':
								if ($Cols['constant']==0){
									$TotalCalculation = 0;
								} else {
									$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] / $Cols['constant'];
								}
								break;
						} /*end of switch stmt block*/
						if ($Cols['valformat']=='P'){
							$TotalCalculation = locale_number_format($TotalCalculation * 100,1) . '%';
						} else {
							$TotalCalculation = locale_number_format($TotalCalculation,0);
						}
						$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
					}
				}
				$Ypos -=(2*$LineHeight);
				/*reset the accumulators to 0 */
				for ($i=0;$i<=10;$i++){
					$AccumLvl3[$i]=0;
				}
			}
		}

		if ($MyRow['col3']!=$GrpData2 AND $MyRow['col3']!='0' AND $MyRow['col5']!='0'){
		/*Totals only relevant to GrpByLevel 2 if GrpByLevel 3 also used */
			if ($Counter > 0){ /*Dont want to print totals if this is the first record */
				$TotalText = mb_substr(__('TOTAL') . ' ' . $LastLine['col3'] . ' - ' . $LastLine['col4'],0,43);
				$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$TotalText);
				DB_data_seek($ColsResult,0);
				while ($Cols = DB_fetch_array($ColsResult)){
					$Xpos = 160 + $Cols['colno']*60;
					if ($Cols['calculation']==0){
						$LeftOvers = $pdf->addTextWrap($Xpos, $Ypos,60,$FontSize, locale_number_format($AccumLvl2[$Cols['colno']],0),'right');
					} else { /* its a calculation need to re-perform on the totals*/

						switch ($Cols['calcoperator']) {
							case '/':
								if ($AccumLvl2[$Cols['coldenominator']]==0){
									$TotalCalculation = 0;
								} else {
									$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] / $AccumLvl2[$Cols['coldenominator']];
								}
								break;
							case '+':
								$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] + $AccumLvl2[$Cols['coldenominator']];
								break;
							case '-':
								$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] + $AccumLvl2[$Cols['coldenominator']];
								break;
							case '*':
								$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] * $Cols['constant'];
								break;
							case 'C':
								if ($Cols['constant']==0){
									$TotalCalculation = 0;
								} else {
									$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] / $Cols['constant'];
								}
								break;
						} /*end of switch stmt block*/
						if ($Cols['valformat']=='P'){
							$TotalCalculation = locale_number_format($TotalCalculation * 100,1) . '%';
						} else {
							$TotalCalculation = locale_number_format($TotalCalculation,0);
						}
						$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
					}/*end if not a calculation column*/
				}/*end while loop through column definitions */
				$Ypos -=(2*$LineHeight);
				/*reset the accumulators to 0 */
				for ($i=0;$i<=10;$i++){
					$AccumLvl2[$i]=0;
				}
			}/*end if Counter > 0 */
		}

		if ($MyRow['col1']!=$GrpData1  && $MyRow['col3']!='0'){
			/*Totals only relevant to GrpByLevel 1 if GrpByLevel 2 also used */
			if ($Counter > 0){ /*Dont want to print totals if this is the first record */
				$TotalText = mb_substr(__('TOTAL') . ' ' . $LastLine['col1'] . ' - ' . $LastLine['col2'],0,46);
				$LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,$TotalText);
				DB_data_seek($ColsResult,0);
				while ($Cols = DB_fetch_array($ColsResult)){
					$Xpos = 160 + $Cols['colno']*60;
					if ($Cols['calculation']==0){
						$LeftOvers = $pdf->addTextWrap($Xpos, $Ypos,60,$FontSize, locale_number_format($AccumLvl1[$Cols['colno']],0),'right');
					} else { /* its a calculation need to re-perform on the totals*/

						switch ($Cols['calcoperator']) {
							case '/':
								if ($AccumLvl1[$Cols['coldenominator']]==0){
									$TotalCalculation = 0;
								} else {
									$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] / $AccumLvl1[$Cols['coldenominator']];
								}
								break;
							case '+':
								$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] + $AccumLvl1[$Cols['coldenominator']];
								break;
							case '-':
								$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] + $AccumLvl1[$Cols['coldenominator']];
								break;
							case '*':
								$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] * $Cols['constant'];
								break;
							case 'C':
								if ($Cols['constant']==0){
									$TotalCalculation = 0;
								} else {
									$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] / $Cols['constant'];
								}
								break;
						} /*end of switch stmt block*/
						if ($Cols['valformat']=='P'){
							$TotalCalculation = locale_number_format($TotalCalculation * 100,1) . '%';
						} else {
							$TotalCalculation = locale_number_format($TotalCalculation,0);
						}
						$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
					}/*end if its not a calculation */
				} /* end loop around column defintions */
				$Ypos -=(2*$LineHeight);

				/*reset the accumulators to 0 */
				for ($i=0;$i<=10;$i++){
					$AccumLvl1[$i]=0;
				}
			}/* end if counter>0 */
		}

		$NewHeading =0;

		if ($MyRow['col1']!=$GrpData1){ /*Need a new heading for Level 1 */
			$NewHeading = 1;
			if ($ReportSpec['newpageafter1']==1){
				include('includes/PDFSalesAnalPageHeader.php');
			}
			$GroupHeadingText = mb_substr($MyRow['col1'] . ' - ' . $MyRow['col2'],0,50);
			$LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,$GroupHeadingText);

			if ($MyRow['col3']!='0'){
				$Ypos-=$LineHeight;
			}
		}

		if (($MyRow['col3']!=$GrpData2  OR $NewHeading ==1) AND $MyRow['col3']!='0'){
			/*Need a new heading for Level 2 */
			$NewHeading = 1;
			if ($ReportSpec['newpageafter2']==1){
				include('includes/PDFSalesAnalPageHeader.php');
			}
			$GroupHeadingText = mb_substr($MyRow['col3'] . ' - ' . $MyRow['col4'],0,46);
			$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$GroupHeadingText);

			if ($MyRow['col5']!='0'){
				$Ypos-=$LineHeight;
			}
		}
		if (($MyRow['col5']!=$GrpData3  OR $NewHeading ==1) AND $MyRow['col5']!='0'){
			/*Need a new heading for Level 3 */

			if ($ReportSpec['newpageafter3']==1){
				include('includes/PDFSalesAnalPageHeader.php');
			}
			$GroupHeadingText = mb_substr($MyRow['col5'] . ' - ' . $MyRow['col6'],0,46);
			$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$GroupHeadingText);

			if ($MyRow['col7']!='0'){
				$Ypos-=$LineHeight;
			}
		}

		if ($MyRow['col7']!='0'){
			/*show titles */
			$GroupHeadingText = mb_substr($MyRow['col7'] . ' - ' . $MyRow['col8'], 0, 40);
			$LeftOvers = $pdf->addTextWrap(55,$Ypos,135,$FontSize,$GroupHeadingText);

		}

		/*NOW SHOW THE LINE OF DATA */
		DB_data_seek($ColsResult,0);
		while ($Cols = DB_fetch_array($ColsResult)){
			$Xpos = 160 + ($Cols['colno']*60);
			$ColumnNo = 'col' . (string) (($Cols['colno']) +8);
			if ($Cols['valformat']=='P'){
				$DisplayValue = locale_number_format($MyRow[$ColumnNo] *100,1) . '%';
			} else {
				$DisplayValue = locale_number_format($MyRow[$ColumnNo],0);
			}
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize,$DisplayValue, 'right');

			$AccumLvl1[$Cols['colno']] += $MyRow[$ColumnNo];
			$AccumLvl2[$Cols['colno']] += $MyRow[$ColumnNo];
			$AccumLvl3[$Cols['colno']] += $MyRow[$ColumnNo];
			$AccumLvl4[$Cols['colno']] += $MyRow[$ColumnNo];
		}

		$Ypos -=$LineHeight;

		if ($Ypos - (2*$LineHeight) < $Bottom_Margin){
			include('includes/PDFSalesAnalPageHeader.php');
		}//end if need a new page headed up
		$GrpData1 = $MyRow['col1'];
		$GrpData2 = $MyRow['col3'];
		$GrpData3 = $MyRow['col5'];
		$Counter++;
		$LastLine = $MyRow; /*remember the last line that had some output in an array called last line*/
	} /*The line has some positive amount on it */
} /*end of the data loop to print lines */

if ($LastLine['col5']!='0' && $LastLine['col7']!='0'){
/* if GrpBY3 and GrpBy4 are both set need to show totals for GrpBy3 */
	if ($Counter>0){ /*Dont want to print totals if this is the first record */
		$TotalText = mb_substr(__('TOTAL') . ' ' . $LastLine['col5'] . ' - ' . $LastLine['col6'],0,33);
		$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$TotalText);

		DB_data_seek($ColsResult,0);
		while ($Cols = DB_fetch_array($ColsResult)){
			$Xpos = 160 + $Cols['colno']*60;
			if ($Cols['calculation']==0){
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, locale_number_format($AccumLvl3[$Cols['colno']],0),'right');

			} else { /* its a calculation need to re-perform on the totals*/

				switch ($Cols['calcoperator']) {
					case '/':
						if ($AccumLvl3[$Cols['coldenominator']]==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] / $AccumLvl3[$Cols['coldenominator']];
						}
						break;
					case '+':
						$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] + $AccumLvl3[$Cols['coldenominator']];
						break;
					case '-':
						$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] + $AccumLvl3[$Cols['coldenominator']];
						break;
					case '*':
						$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] * $Cols['constant'];
						break;
					case 'C':
						if ($Cols['constant']==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl3[$Cols['colnumerator']] / $Cols['constant'];
						}
						break;
				} /*end of switch stmt block*/
				if ($Cols['valformat']=='P'){
					$TotalCalculation = locale_number_format($TotalCalculation * 100,1) . '%';
				} else {
					$TotalCalculation = locale_number_format($TotalCalculation,0);
				}
				$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
			}

		}
		$Ypos -=$LineHeight;
	}
}

if ($LastLine['col3']!='0' AND $LastLine['col5']!='0'){
/* if GrpBY2 and GrpBy3 are both set need to show totals for GrpBy2 */
	if ($Counter>0){ /*Dont want to print totals if this is the first record */
		$TotalText = mb_substr(__('TOTAL') . ' ' . $LastLine['col3'] . ' - ' . $LastLine['col4'],0,33);
		$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$TotalText);
		DB_data_seek($ColsResult,0);
		while ($Cols = DB_fetch_array($ColsResult)){
			$Xpos = 160 + $Cols['colno']*60;
			if ($Cols['calculation']==0){
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, locale_number_format($AccumLvl2[$Cols['colno']],0),'right');

			} else { /* its a calculation need to re-perform on the totals*/

				switch ($Cols['calcoperator']) {
					case '/':
						if ($AccumLvl2[$Cols['coldenominator']]==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] / $AccumLvl2[$Cols['coldenominator']];
						}
						break;
					case '+':
						$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] + $AccumLvl2[$Cols['coldenominator']];
						break;
					case '-':
						$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] + $AccumLvl2[$Cols['coldenominator']];
						break;
					case '*':
						$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] * $Cols['constant'];
						break;
					case 'C':
						if ($Cols['constant']==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl2[$Cols['colnumerator']] / $Cols['constant'];
						}
						break;
				} /*end of switch stmt block*/
				if ($Cols['valformat']=='P'){
					$TotalCalculation = locale_number_format($TotalCalculation * 100,1) . '%';
				} else {
					$TotalCalculation = locale_number_format($TotalCalculation,0);
				}
				$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
			}

		}
		$Ypos -=$LineHeight;
	}
}
if ($LastLine['col3']!='0'){
/* GrpBY1 must always be set but if GrpBy2 is also set need to show totals for GrpBy2 */
	if ($Counter>1){ /*Dont want to print totals if this is the first record */
		$TotalText = mb_substr(__('TOTAL') .  ' ' . $LastLine['col1'] . ' - ' . $LastLine['col2'],0,30);
		$LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,$TotalText);
		DB_data_seek($ColsResult,0);
		while ($Cols = DB_fetch_array($ColsResult)){
			$Xpos =160 + $Cols['colno']*60;
			if ($Cols['calculation']==0){
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, locale_number_format($AccumLvl1[$Cols['colno']],0),'right');
			} else { /* its a calculation need to re-perform on the totals*/

				switch ($Cols['calcoperator']) {
				case '/':
					if ($AccumLvl1[$Cols['coldenominator']]==0){
						$TotalCalculation = 0;
					} else {
						$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] / $AccumLvl1[$Cols['coldenominator']];
					}
					break;
				case '+':
					$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] + $AccumLvl1[$Cols['coldenominator']];
					break;
				case '-':
					$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] + $AccumLvl1[$Cols['coldenominator']];
					break;
				case '*':
					$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] * $Cols['constant'];
					break;
				case 'C':
					if ($Cols['constant']==0){
						$TotalCalculation = 0;
					} else {
						$TotalCalculation = $AccumLvl1[$Cols['colnumerator']] / $Cols['constant'];
					}
					break;
				} /*end of switch stmt block*/
				if ($Cols['valformat']=='P'){
					$TotalCalculation = locale_number_format($TotalCalculation * 100,1) . '%';
				} else {
					$TotalCalculation = locale_number_format($TotalCalculation,0);
				}
				$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
			}

		}
		$Ypos -=(2*$LineHeight);
	}
}
if ($Counter>0){
	$LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,__('GRAND TOTAL'));

	DB_data_seek($ColsResult,0);
	while ($Cols = DB_fetch_array($ColsResult)){
		$Xpos =160 + $Cols['colno']*60;
		if ($Cols['calculation']==0){
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, locale_number_format($AccumLvl4[$Cols['colno']],0),'right');
		} else { /* its a calculation need to re-perform on the totals*/

			switch ($Cols['calcoperator']) {
			case '/':
				if ($AccumLvl4[$Cols['coldenominator']]==0){
					$TotalCalculation = 0;
				} else {
					$TotalCalculation = $AccumLvl4[$Cols['colnumerator']] / $AccumLvl4[$Cols['coldenominator']];
				}
				break;
			case '+':
				$TotalCalculation = $AccumLvl4[$Cols['colnumerator']] + $AccumLvl4[$Cols['coldenominator']];
				break;
			case '-':
				$TotalCalculation = $AccumLvl4[$Cols['colnumerator']] + $AccumLvl4[$Cols['coldenominator']];
				break;
			case '*':
				$TotalCalculation = $AccumLvl4[$Cols['colnumerator']] * $Cols['constant'];
				break;
			case 'C':
				if ($Cols['constant']==0){
					$TotalCalculation = 0;
				} else {
					$TotalCalculation = $AccumLvl4[$Cols['colnumerator']] / $Cols['constant'];
				}
				break;
			} /*end of switch stmt block*/
			if ($Cols['valformat']=='P'){
				$TotalCalculation = locale_number_format($TotalCalculation * 100,1) . '%';
			} else {
				$TotalCalculation = locale_number_format($TotalCalculation,0);
			}
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
		}

	}
	$Ypos -=$LineHeight;
}


if (isset($_GET['ProduceCVSFile'])){

	function stripcomma($str) { //because we're using comma as a delimiter
		return str_replace(',','',$str);
	}

	$fp = fopen( $_SESSION['reports_dir'] . '/SalesAnalysis.csv', 'w');


	while ($MyRow = DB_fetch_row($Result)){

	/*First off check that at least one of the columns of data has some none zero amounts */
	      $ThisLineHasOutput=false;   /*assume no output to start with */
	      $NumberOfFields = DB_num_rows($ColsResult);

	      for ($i=3; $i<=$NumberOfFields+7; $i++) {
		     if (abs($MyRow[$i])>0.009){
			 $ThisLineHasOutput = true;
		     }
	      }
	      if ($ThisLineHasOutput==true){
	      		$Line='';
			for ($i=0;$i<=$NumberOfFields+7;$i++){
				if (isset($MyRow[$i])){
					if ($i>0){
						$Line.=',';
					}
					$Line.=stripcomma($MyRow[$i]);
				}
			}
			fputs($fp, $Line."\n");
	      }
	 }
	 $Title = __('Sales Analysis Comma Separated File (CSV) Generation');
	include('includes/header.php');

	// gg: what was this line supposed to do ?
	//echo '//' . getenv('SERVER_NAME') . $RootPath . '/' . $_SESSION['reports_dir'] .  '/SalesAnalysis.csv';
	/// @todo this meta tag should be moved into the HTML HEAD, and thus be outputted within `header.php`
	echo "<meta http-equiv='Refresh' content='0; url=" . $RootPath . '/' . $_SESSION['reports_dir'] .  "/SalesAnalysis.csv'>";

	 echo '<p>' . __('You should automatically be forwarded to the CSV Sales Analysis file when it is ready') . '. ' . __('If this does not happen') . ' <a href="' . $RootPath . '/' . $_SESSION['reports_dir'] . '/SalesAnalysis.csv">' . __('click here') . '</a> ' . __('to continue')  . '<br />';
	 include('includes/footer.php');
}
