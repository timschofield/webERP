<?php
/* $Revision: 1.4 $ */
include ('includes/class.pdf.php');

/* A4_Landscape */

$Page_Width=842;
$Page_Height=595;
$Top_Margin=20;
$Bottom_Margin=20;
$Left_Margin=25;
$Right_Margin=22;

$PageSize = array(0,0,$Page_Width,$Page_Height);
$pdf = & new Cpdf($PageSize);

$PageNumber = 0;

$pdf->selectFont('./fonts/Helvetica.afm');

/* Standard PDF file creation header stuff */

$pdf->addinfo('Author','WEB-ERP ' . $Version);
$pdf->addinfo('Creator','WEB-ERP http://weberp.sourceforge.net - R&OS PHP-PDF http://www.ros.co.nz');
$pdf->addinfo('Title',$ReportSpec['ReportHeading']);
$pdf->addinfo('Subject',_('Sales Analysis Report') . ' ' . $ReportSpec['ReportHeading']);

$line_height=12;

include ('includes/PDFSalesAnalPageHeader.inc');

$GrpData1='';
$GrpData2='';
$GrpData3='';

$Counter=0;

/*Make an array to hold accumulators for */
$AccumLvl1 = array();
for ($i=0;$i<=10;$i++){
$AccumLvl1[$i]=0;
}
$AccumLvl2= array();
for ($i=0;$i<=10;$i++){
$AccumLvl2[$i]=0;
}
$AccumLvl3=array();
for ($i=0;$i<=10;$i++){
$AccumLvl3[$i]=0;
}
$AccumLvl4=array();
for ($i=0;$i<=10;$i++){
$AccumLvl4[$i]=0;
}

While ($myrow = DB_fetch_array($result)){

/*First off check that at least one of the columns of data has some none zero amounts */
DB_data_seek($ColsResult,0); /*go back to the beginning */
$ThisLineHasOutput=False;   /*assume no output to start with */
while ($Cols = DB_fetch_array($ColsResult)){
	$ColumnNo ='Col' . ((int) $Cols['ColNo'] + 8);
	if (abs($myrow[$ColumnNo])>0.5){
		$ThisLineHasOutput = True;
	}
}
if ($ThisLineHasOutput==True){


	if ($myrow['Col5']!=$GrpData3 && $myrow['Col5']!='0' && $myrow['Col7']!='0'){
		/*Totals only relevant to GrpByLevel 3 if GrpByLevel 4 also used */
		if ($Counter > 0){ /*Dont want to print totals if this is the first record */
			$TotalText = substr(_('TOTAL') . ' ' . $LastLine['Col5'] . ' - ' . $LastLine['Col6'],0,33);
			$LeftOvers = $pdf->addTextWrap(40,$Ypos,180,$FontSize,$TotalText);

			DB_data_seek($ColsResult,0);
			while ($Cols = DB_fetch_array($ColsResult)){
				$Xpos = 160 + $Cols['ColNo']*60;
				if ($Cols['Calculation']==0){
				$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl3[$Cols['ColNo']]),'right');
				} else { /* its a calculation need to re-perform on the totals*/

					switch ($Cols['CalcOperator']) {
					Case '/':
						if ($AccumLvl3[$Cols['ColDenominator']]==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] / $AccumLvl3[$Cols['ColDenominator']];
						}
						break;
					Case '+':
						$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] + $AccumLvl3[$Cols['ColDenominator']];
						break;
					Case '-':
						$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] + $AccumLvl3[$Cols['ColDenominator']];
						break;
					Case '*':
						$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] * $Cols['Constant'];
						break;
					Case 'C':
						if ($Cols['Constant']==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] / $Cols['Constant'];
						}
						break;
					} /*end of switch stmt block*/
					if ($Cols['ValFormat']=='P'){
						$TotalCalculation = number_format($TotalCalculation * 100) . '%';
					} else {
						$TotalCalculation = number_format($TotalCalculation);
					}
					$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
				}
			}
			$Ypos -=(2*$line_height);
			/*reset the accumulators to 0 */
			for ($i=0;$i<=10;$i++){
				$AccumLvl3[$i]=0;
			}
		}
	}


	if ($myrow['Col3']!=$GrpData2 && $myrow['Col3']!='0' && $myrow['Col5']!='0'){
		/*Totals only relevant to GrpByLevel 2 if GrpByLevel 3 also used */
		if ($Counter > 0){ /*Dont want to print totals if this is the first record */
			$TotalText = substr(_('TOTAL') . ' ' . $LastLine['Col3'] . ' - ' . $LastLine['Col4'],0,43);
			$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$TotalText);
			DB_data_seek($ColsResult,0);
			while ($Cols = DB_fetch_array($ColsResult)){
				$Xpos = 160 + $Cols['ColNo']*60;
				if ($Cols['Calculation']==0){
				$LeftOvers = $pdf->addTextWrap($Xpos, $Ypos,60,$FontSize, number_format($AccumLvl2[$Cols['ColNo']]),'right');
				} else { /* its a calculation need to re-perform on the totals*/

					switch ($Cols['CalcOperator']) {
					Case '/':
						if ($AccumLvl2[$Cols['ColDenominator']]==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] / $AccumLvl2[$Cols['ColDenominator']];
						}
						break;
					Case '+':
						$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] + $AccumLvl2[$Cols['ColDenominator']];
						break;
					Case '-':
						$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] + $AccumLvl2[$Cols['ColDenominator']];
						break;
					Case '*':
						$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] * $Cols['Constant'];
						break;
					Case 'C':
						if ($Cols['Constant']==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] / $Cols['Constant'];
						}
						break;
					} /*end of switch stmt block*/
					if ($Cols['ValFormat']=='P'){
						$TotalCalculation = number_format($TotalCalculation * 100) . '%';
					} else {
						$TotalCalculation = number_format($TotalCalculation);
					}
					$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
				}

			}
			$Ypos -=(2*$line_height);
			/*reset the accumulators to 0 */
			for ($i=0;$i<=10;$i++){
				$AccumLvl2[$i]=0;
			}
		}
	}

	if ($myrow['Col1']!=$GrpData1  && $myrow['Col3']!='0'){
		/*Totals only relevant to GrpByLevel 1 if GrpByLevel 2 also used */
		if ($Counter > 0){ /*Dont want to print totals if this is the first record */
			$TotalText = substr(_('TOTAL') . ' ' . $LastLine['Col1'] . ' - ' . $LastLine['Col2'],0,46);
			$LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,$TotalText);
			DB_data_seek($ColsResult,0);
			while ($Cols = DB_fetch_array($ColsResult)){
				$Xpos = 160 + $Cols['ColNo']*60;
				if ($Cols['Calculation']==0){
				$LeftOvers = $pdf->addTextWrap($Xpos, $Ypos,60,$FontSize, number_format($AccumLvl1[$Cols['ColNo']]),'right');
				} else { /* its a calculation need to re-perform on the totals*/

					switch ($Cols['CalcOperator']) {
					Case '/':
						if ($AccumLvl1[$Cols['ColDenominator']]==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] / $AccumLvl1[$Cols['ColDenominator']];
						}
						break;
					Case '+':
						$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] + $AccumLvl1[$Cols['ColDenominator']];
						break;
					Case '-':
						$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] + $AccumLvl1[$Cols['ColDenominator']];
						break;
					Case '*':
						$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] * $Cols['Constant'];
						break;
					Case 'C':
						if ($Cols['Constant']==0){
							$TotalCalculation = 0;
						} else {
							$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] / $Cols['Constant'];
						}
						break;
					} /*end of switch stmt block*/
					if ($Cols['ValFormat']=='P'){
						$TotalCalculation = number_format($TotalCalculation * 100) . '%';
					} else {
						$TotalCalculation = number_format($TotalCalculation);
					}
					$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
				}

			}
			$Ypos -=(2*$line_height);

			/*reset the accumulators to 0 */
			for ($i=0;$i<=10;$i++){
				$AccumLvl1[$i]=0;
			}
		}
	}

	$NewHeading =0;

	if ($myrow['Col1']!=$GrpData1){ /*Need a new heading for Level 1 */
		$NewHeading = 1;
		if ($ReportSpec['NewPageAfter1']==1){
			include ('includes/PDFSalesAnalPageHeader.inc');
		}
		$GroupHeadingText = substr($myrow['Col1'] . ' - ' . $myrow['Col2'],0,50);
		$LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,$GroupHeadingText);

		if ($myrow['Col3']!='0'){
				$Ypos-=$line_height;
		}
	}

	if (($myrow['Col3']!=$GrpData2  OR $NewHeading ==1) AND $myrow['Col3']!='0'){
			/*Need a new heading for Level 2 */
		$NewHeading = 1;
		if ($ReportSpec['NewPageAfter2']==1){
			include ('includes/PDFSalesAnalPageHeader.inc');
		}
		$GroupHeadingText = substr($myrow['Col3'] . ' - ' . $myrow['Col4'],0,46);
		$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$GroupHeadingText);

		if ($myrow['Col5']!='0'){
			$Ypos-=$line_height;
		}
	}
	if (($myrow['Col5']!=$GrpData3  OR $NewHeading ==1) AND $myrow['Col5']!='0'){
			/*Need a new heading for Level 3 */

		if ($ReportSpec['NewPageAfter3']==1){
			include ('includes/PDFSalesAnalPageHeader.inc');
		}
		$GroupHeadingText = substr($myrow['Col5'] . ' - ' . $myrow['Col6'],0,46);
		$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$GroupHeadingText);

		if ($myrow['Col7']!='0'){
			$Ypos-=$line_height;
		}
	}

	if ($myrow['Col7']!='0'){
		/*show titles */
		$GroupHeadingText = substr($myrow['Col7'] . ' - ' . $myrow['Col8'], 0, 40);
		$LeftOvers = $pdf->addTextWrap(55,$Ypos,135,$FontSize,$GroupHeadingText);

	}

	/*NOW SHOW THE LINE OF DATA */
	DB_data_seek($ColsResult,0);
	while ($Cols = DB_fetch_array($ColsResult)){
		$Xpos = 160 + ($Cols['ColNo']*60);
		$ColumnNo = 'Col' . (string) (($Cols['ColNo']) +8);
		if ($Cols['ValFormat']=='P'){
			$DisplayValue = number_format($myrow[$ColumnNo] *100) . '%';
		} else {
			$DisplayValue = number_format($myrow[$ColumnNo]);
		}
		$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize,$DisplayValue, 'right');

		$AccumLvl1[$Cols['ColNo']] += $myrow[$ColumnNo];
		$AccumLvl2[$Cols['ColNo']] += $myrow[$ColumnNo];
		$AccumLvl3[$Cols['ColNo']] += $myrow[$ColumnNo];
		$AccumLvl4[$Cols['ColNo']] += $myrow[$ColumnNo];
	}

	$Ypos -=$line_height;

	if ($Ypos - (2*$line_height) < $Bottom_Margin){
		include ('includes/PDFSalesAnalPageHeader.inc');
	}//end if need a new page headed up
	$GrpData1 = $myrow['Col1'];
	$GrpData2 = $myrow['Col3'];
	$GrpData3 = $myrow['Col5'];
	$Counter++;
	$LastLine = $myrow; /*remember the last line that had some output in an array called last line*/
	} /*The line has some positive amount on it */

} /*end of the data loop to print lines */

if ($LastLine['Col5']!='0' && $LastLine['Col7']!='0'){
/* if GrpBY3 and GrpBy4 are both set need to show totals for GrpBy3 */
if ($Counter>0){ /*Dont want to print totals if this is the first record */
	$TotalText = substr(_('TOTAL') . ' ' . $LastLine['Col5'] . ' - ' . $LastLine['Col6'],0,33);
	$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$TotalText);

	DB_data_seek($ColsResult,0);
	while ($Cols = DB_fetch_array($ColsResult)){
		$Xpos = 160 + $Cols['ColNo']*60;
		if ($Cols['Calculation']==0){
		$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl3[$Cols['ColNo']]),'right');

		} else { /* its a calculation need to re-perform on the totals*/

			switch ($Cols['CalcOperator']) {
				Case '/':
					if ($AccumLvl3[$Cols['ColDenominator']]==0){
						$TotalCalculation = 0;
					} else {
						$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] / $AccumLvl3[$Cols['ColDenominator']];
					}
					break;
				Case '+':
					$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] + $AccumLvl3[$Cols['ColDenominator']];
					break;
				Case '-':
					$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] + $AccumLvl3[$Cols['ColDenominator']];
					break;
				Case '*':
					$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] * $Cols['Constant'];
					break;
				Case 'C':
					if ($Cols['Constant']==0){
						$TotalCalculation = 0;
					} else {
						$TotalCalculation = $AccumLvl3[$Cols['ColNumerator']] / $Cols['Constant'];
					}
					break;
			} /*end of switch stmt block*/
			if ($Cols['ValFormat']=='P'){
				$TotalCalculation = number_format($TotalCalculation * 100) . '%';
			} else {
				$TotalCalculation = number_format($TotalCalculation);
			}
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
		}

	}
	$Ypos -=$line_height;
}
}

if ($LastLine['Col3']!='0' && $LastLine['Col5']!='0'){
/* if GrpBY2 and GrpBy3 are both set need to show totals for GrpBy2 */
if ($Counter>0){ /*Dont want to print totals if this is the first record */
	$TotalText = substr(_('TOTAL') . ' ' . $LastLine['Col3'] . ' - ' . $LastLine['Col4'],0,33);
	$LeftOvers = $pdf->addTextWrap(30,$Ypos,190,$FontSize,$TotalText);
	DB_data_seek($ColsResult,0);
	while ($Cols = DB_fetch_array($ColsResult)){
		$Xpos = 160 + $Cols['ColNo']*60;
		if ($Cols['Calculation']==0){
		$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl2[$Cols['ColNo']]),'right');

		} else { /* its a calculation need to re-perform on the totals*/

			switch ($Cols['CalcOperator']) {
				Case '/':
					if ($AccumLvl2[$Cols['ColDenominator']]==0){
						$TotalCalculation = 0;
					} else {
						$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] / $AccumLvl2[$Cols['ColDenominator']];
					}
					break;
				Case '+':
					$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] + $AccumLvl2[$Cols['ColDenominator']];
					break;
				Case '-':
					$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] + $AccumLvl2[$Cols['ColDenominator']];
					break;
				Case '*':
					$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] * $Cols['Constant'];
					break;
				Case 'C':
					if ($Cols['Constant']==0){
						$TotalCalculation = 0;
					} else {
						$TotalCalculation = $AccumLvl2[$Cols['ColNumerator']] / $Cols['Constant'];
					}
					break;
			} /*end of switch stmt block*/
			if ($Cols['ValFormat']=='P'){
				$TotalCalculation = number_format($TotalCalculation * 100) . '%';
			} else {
				$TotalCalculation = number_format($TotalCalculation);
			}
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
		}

	}
	$Ypos -=$line_height;
}
}
if ($LastLine['Col3']!='0'){
/* GrpBY1 must always be set but if GrpBy2 is also set need to show totals for GrpBy2 */
if ($Counter>1){ /*Dont want to print totals if this is the first record */
	$TotalText = substr(_('TOTAL') .  ' ' . $LastLine['Col1'] . ' - ' . $LastLine['Col2'],0,30);
	$LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,$TotalText);
	DB_data_seek($ColsResult,0);
	while ($Cols = DB_fetch_array($ColsResult)){
		$Xpos =160 + $Cols['ColNo']*60;
		if ($Cols['Calculation']==0){
		$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl1[$Cols['ColNo']]),'right');
		} else { /* its a calculation need to re-perform on the totals*/

			switch ($Cols['CalcOperator']) {
			Case '/':
				if ($AccumLvl1[$Cols['ColDenominator']]==0){
					$TotalCalculation = 0;
				} else {
					$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] / $AccumLvl1[$Cols['ColDenominator']];
				}
				break;
			Case '+':
				$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] + $AccumLvl1[$Cols['ColDenominator']];
				break;
			Case '-':
				$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] + $AccumLvl1[$Cols['ColDenominator']];
				break;
			Case '*':
				$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] * $Cols['Constant'];
				break;
			Case 'C':
				if ($Cols['Constant']==0){
					$TotalCalculation = 0;
				} else {
					$TotalCalculation = $AccumLvl1[$Cols['ColNumerator']] / $Cols['Constant'];
				}
				break;
			} /*end of switch stmt block*/
			if ($Cols['ValFormat']=='P'){
				$TotalCalculation = number_format($TotalCalculation * 100) . '%';
			} else {
				$TotalCalculation = number_format($TotalCalculation);
			}
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
		}

	}
	$Ypos -=(2*$line_height);
}
}
if ($Counter>0){
	$LeftOvers = $pdf->addTextWrap(15,$Ypos,205,$FontSize,_('GRAND TOTAL'));

	DB_data_seek($ColsResult,0);
	while ($Cols = DB_fetch_array($ColsResult)){
		$Xpos =160 + $Cols['ColNo']*60;
		if ($Cols['Calculation']==0){
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, number_format($AccumLvl4[$Cols['ColNo']]),'right');
		} else { /* its a calculation need to re-perform on the totals*/

			switch ($Cols['CalcOperator']) {
			Case '/':
				if ($AccumLvl4[$Cols['ColDenominator']]==0){
					$TotalCalculation = 0;
				} else {
					$TotalCalculation = $AccumLvl4[$Cols['ColNumerator']] / $AccumLvl4[$Cols['ColDenominator']];
				}
				break;
			Case '+':
				$TotalCalculation = $AccumLvl4[$Cols['ColNumerator']] + $AccumLvl4[$Cols['ColDenominator']];
				break;
			Case '-':
				$TotalCalculation = $AccumLvl4[$Cols['ColNumerator']] + $AccumLvl4[$Cols['ColDenominator']];
				break;
			Case '*':
				$TotalCalculation = $AccumLvl4[$Cols['ColNumerator']] * $Cols['Constant'];
				break;
			Case 'C':
				if ($Cols['Constant']==0){
					$TotalCalculation = 0;
				} else {
					$TotalCalculation = $AccumLvl4[$Cols['ColNumerator']] / $Cols['Constant'];
				}
				break;
			} /*end of switch stmt block*/
			if ($Cols['ValFormat']=='P'){
				$TotalCalculation = number_format($TotalCalculation * 100) . '%';
			} else {
				$TotalCalculation = number_format($TotalCalculation);
			}
			$LeftOvers = $pdf->addTextWrap($Xpos,$Ypos,60,$FontSize, $TotalCalculation,'right');
		}

	}
	$Ypos -=$line_height;
}




if ($_GET['ProduceCVSFile']==True){

	function stripcomma($str) { //because we're using comma as a delimiter
		return str_replace(',','',$str);
	}

	$fp = fopen( $reports_dir . '/SalesAnalysis.csv', 'w');


	While ($myrow = DB_fetch_row($result)){

	/*First off check that at least one of the columns of data has some none zero amounts */
	      $ThisLineHasOutput=False;   /*assume no output to start with */
	      $NumberOfFields = DB_num_rows($ColsResult);

	      for ($i=3; $i<=$NumberOfFields+7; $i++) {
		     if (abs($myrow[$i])>0.009){
			 $ThisLineHasOutput = True;
		     }
	      }
	      if ($ThisLineHasOutput==True){
	      		$line='';
			for ($i=0;$i<=$NumberOfFields+7;$i++){
				if (isset($myrow[$i])){
					if ($i>0){
						$line.=',';
					}
					$line.=stripcomma($myrow[$i]);
				}
			}
			fputs($fp, $line.'\n');
	      }
	 }
	 $title = _('Sales Analysis Comma Seperated File (CSV) Generation');
	include('includes/header.inc');

	 echo 'http://' . getenv(SERVER_NAME) . $rootpath . '/' . $reports_dir .  '/SalesAnalysis.csv';
	 echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=http://' . getenv(SERVER_NAME) . $rootpath . '/' . $reports_dir .  '/SalesAnalysis.csv'>";

	 echo '<P>' . _('You should automatically be forwarded to the CSV Sales Analysis file when it is ready') . '. ' . _('If this does not happen') . ' <a href="' . $rootpath . '/' . $reports_dir . '/SalesAnalysis.csv">' . _('click here') . '</a> ' . _('to continue') .'<br>';
	 include('includes/footer.inc');
}


?>
