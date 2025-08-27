<?php

require(__DIR__ . '/includes/session.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_GET['KeyValue']))  {
	$SelectedProdSpec=$_GET['KeyValue'];
} elseif (isset($_POST['KeyValue'])) {
	$SelectedProdSpec=$_POST['KeyValue'];
} else {
	$SelectedProdSpec='';
}
//Get Out if we have no product specification
if (!isset($SelectedProdSpec) OR $SelectedProdSpec==''){
        $Title = __('Select Product Specification To Print');
        $ViewTopic = 'QualityAssurance';
        $BookMark = '';
        include('includes/header.php');
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print')  . '" alt="" />' . ' ' . $Title . '</p>';

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<fieldset>
				<legend>', __('Select Product Specification'), '</legend>
				<field>
					<label for="KeyValue">' . __('Enter Specification Name') .':</label>
					<input type="text" name="KeyValue" size="25" maxlength="25" /></td>
				</field>
			</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="PickSpec" value="' . __('Submit') . '" />
			</div>
		</form>';

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<fieldset>
				<field>
				<label for="KeyValue">' . __('Or Select Existing Specification') .':</label>';
	$SQLSpecSelect="SELECT DISTINCT(keyval),
							description
						FROM prodspecs LEFT OUTER JOIN stockmaster
						ON stockmaster.stockid=prodspecs.keyval";


	$ResultSelection=DB_query($SQLSpecSelect);
	echo '<select name="KeyValue">';

	while ($MyRowSelection=DB_fetch_array($ResultSelection)){
		echo '<option value="' . $MyRowSelection['keyval'] . '">' . $MyRowSelection['keyval'].' - ' .htmlspecialchars($MyRowSelection['description'], ENT_QUOTES,'UTF-8', false)  . '</option>';
	}
	echo '</select>';
	echo '</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PickSpec" value="' . __('Submit') . '" />
		</div>
		</form>';
    include('includes/footer.php');
    exit();
}

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
			WHERE prodspecs.keyval='" .$SelectedProdSpec."'
			AND prodspecs.showonspec='1'
			ORDER by groupby, prodspecs.testid";

$Result = DB_query($SQL, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($Result)==0){
	$Title = __('Print Product Specification Error');
	include('includes/header.php');
	 echo '<div class="centre">
			<br />
			<br />
			<br />';
	prnMsg( __('Unable to Locate Specification') . ' : ' . $_SelectedProdSpec . ' ', 'error');
	echo '<br />
			<br />
			<br />
			<table class="table_index">
			<tr>
				<td class="menu_group_item">
					<ul><li><a href="'. $RootPath . '/PDFProdSpec.php">' . __('Product Specifications') . '</a></li></ul>
				</td>
			</tr>
			</table>
			</div>
			<br />
			<br />
			<br />';
	include('includes/footer.php');
	exit();
}
$PaperSize = 'Letter';

include('includes/PDFStarter.php');
$pdf->addInfo('Title', __('Product Specification') );
$pdf->addInfo('Subject', __('Product Specification') . ' ' . $SelectedProdSpec);
$FontSize=12;
$PageNumber = 1;
$HeaderPrinted=0;
$LineHeight=$FontSize*1.25;
$RectHeight=12;
$SectionHeading=0;
$CurSection='NULL';
$SectionTitle='';
$SectionTrailer='';

$SectionsArray=array(array('PhysicalProperty',3, __('Technical Data Sheet Properties'), __('* Data herein is typical and not to be construed as specifications.'), array(260,110,135),array(__('Physical Property'),__('Value'),__('Test Method')),array('left','center','center')),
					 array('',3, __('Header'), __('* Trailer'), array(260,110,135), array(__('Physical Property'),__('Value'),__('Test Method')),array('left','center','center')),
					 array('Processing',2, __('Injection Molding Processing Guidelines'), __('* Desicant type dryer required.'), array(240,265),array(__('Setting'),__('Value')),array('left','center')),
					 array('RegulatoryCompliance',2, __('Regulatory Compliance'), '', array(240,265),array(__('Regulatory Compliance'),__('Value')),array('left','center')));

while ($MyRow=DB_fetch_array($Result)){
	if ($MyRow['description']=='') {
		$MyRow['description']=$MyRow['keyval'];
	}
	$Spec=$MyRow['description'];
	$SpecDesc=$MyRow['longdescription'];
	foreach($SectionsArray as $Row) {
		if ($MyRow['groupby']==$Row[0]) {
			$SectionColSizes=$Row[4];
			$SectionColLabs=$Row[5];
			$SectionAlign=$Row[6];
		}
	}
	$TrailerPrinted=1;
	if ($HeaderPrinted==0) {
		include('includes/PDFProdSpecHeader.php');
		$HeaderPrinted=1;
	}

	if ($CurSection!=$MyRow['groupby']) {
		$SectionHeading=0;
		if ($CurSection!='NULL' AND $PrintTrailer==1) {
			$pdf->line($XPos+1, $YPos+$RectHeight,$XPos+506, $YPos+$RectHeight);
		}
		$PrevTrailer=$SectionTrailer;
		$CurSection=$MyRow['groupby'];
		foreach($SectionsArray as $Row) {
			if ($MyRow['groupby']==$Row[0]) {
				$SectionTitle=$Row[2];
				$SectionTrailer=$Row[3];
			}
		}
	}

	if ($SectionHeading==0) {
		$XPos=65;
		if ($PrevTrailer>'' AND $PrintTrailer==1) {
			$PrevFontSize=$FontSize;
			$FontSize=8;
			$LineHeight=$FontSize*1.25;
			$LeftOvers = $pdf->addTextWrap($XPos+5,$YPos,500,$FontSize,$PrevTrailer,'left');
			$FontSize=$PrevFontSize;
			$LineHeight=$FontSize*1.25;
			$YPos -= $LineHeight;
			$YPos -= $LineHeight;
		}
		if ($YPos < ($Bottom_Margin + 90)){ // Begins new page
			$PrintTrailer=0;
			$PageNumber++;
			include('includes/PDFProdSpecHeader.php');
		}
		$LeftOvers = $pdf->addTextWrap($XPos,$YPos,500,$FontSize,$SectionTitle,'center');
		$YPos -= $LineHeight;
		$pdf->setFont('','B');
		$pdf->SetFillColor(200,200,200);
		$x=0;
		foreach($SectionColLabs as $CurColLab) {
			$ColLabel=$CurColLab;
			$ColWidth=$SectionColSizes[$x];
			$x++;
			$LeftOvers = $pdf->addTextWrap($XPos+1,$YPos,$ColWidth,$FontSize,$ColLabel,'center',1,'fill');
			$XPos+=$ColWidth;
		}
		$SectionHeading=1;
		$YPos -= $LineHeight;
		$pdf->setFont('','');
	} //$SectionHeading==0
	$XPos=65;
	$Value='';
	if ($MyRow['targetvalue'] > '') {
		$Value=$MyRow['targetvalue'];
	} elseif ($MyRow['rangemin'] > '' OR $MyRow['rangemax'] > '') {
		if ($MyRow['rangemin'] > '' AND $MyRow['rangemax'] == '') {
			$Value='> ' . $MyRow['rangemin'];
		} elseif ($MyRow['rangemin']== '' AND $MyRow['rangemax'] > '') {
			$Value='< ' . $MyRow['rangemax'];
		} else {
			$Value=$MyRow['rangemin'] . ' - ' . $MyRow['rangemax'];
		}
	}
	if (strtoupper($Value) <> 'NB' AND strtoupper($Value) <> 'NO BREAK') {
		$Value.= ' ' . $MyRow['units'];
	}
	$x=0;

	foreach($SectionColLabs as $CurColLab) {
		$ColLabel=$CurColLab;
		$ColWidth=$SectionColSizes[$x];
		$ColAlign=$SectionAlign[$x];
		switch ($x) {
			case 0;
				$DispValue=$MyRow['name'];
				break;
			case 1;
				$DispValue=$Value;
				break;
			case 2;
				$DispValue=$MyRow['method'];
				break;
		}
		$LeftOvers = $pdf->addTextWrap($XPos+1,$YPos,$ColWidth,$FontSize,$DispValue,$ColAlign,1);
		$XPos+=$ColWidth;
		$x++;
	}
	$YPos -= $LineHeight;
	$XPos=65;
	$PrintTrailer=1;
	if ($YPos < ($Bottom_Margin + 80)){ // Begins new page
		$pdf->line($XPos+1, $YPos+$RectHeight,$XPos+506, $YPos+$RectHeight);
		$PrintTrailer=0;
		$PageNumber++;
		include('includes/PDFProdSpecHeader.php');
	}
	//echo 'PrintTrailer'.$PrintTrailer.' '.$PrevTrailer.'<br>' ;
} //while loop

$pdf->line($XPos+1, $YPos+$RectHeight,$XPos+506, $YPos+$RectHeight);
if ($SectionTrailer>'') {
	$PrevFontSize=$FontSize;
	$FontSize=8;
	$LineHeight=$FontSize*1.25;
	$LeftOvers = $pdf->addTextWrap($XPos+5,$YPos,500,$FontSize,$SectionTrailer,'left');
	$FontSize=$PrevFontSize;
	$LineHeight=$FontSize*1.25;
	$YPos -= $LineHeight;
	$YPos -= $LineHeight;
}
if ($YPos < ($Bottom_Margin + 85)){ // Begins new page
	$PageNumber++;
	include('includes/PDFProdSpecHeader.php');
}
$Disclaimer= __('The information provided on this datasheet should only be used as a guideline. Actual lot to lot values will vary.');
$FontSize=8;
$LineHeight=$FontSize*1.25;
$YPos -= $LineHeight;
$LeftOvers = $pdf->addTextWrap($XPos+5,$YPos,500,$FontSize,$Disclaimer);
$YPos -= $LineHeight;
$YPos -= $LineHeight;
$SQL = "SELECT confvalue
			FROM config
			WHERE confname='QualityProdSpecText'";

$Result = DB_query($SQL, $ErrMsg);
$MyRow=DB_fetch_array($Result);
$Disclaimer=$MyRow[0];
$LeftOvers = $pdf->addTextWrap($XPos+5,$YPos,500,$FontSize,$Disclaimer);
while (mb_strlen($LeftOvers) > 1) {
	$YPos -= $LineHeight;
	$LeftOvers = $pdf->addTextWrap($XPos+5,$YPos,500,$FontSize, $LeftOvers, 'left');
}

$pdf->OutputI($_SESSION['DatabaseName'] . '_ProductSpecification_' . date('Y-m-d') . '.pdf');
$pdf->__destruct();
