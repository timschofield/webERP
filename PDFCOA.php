<?php

require(__DIR__ . '/includes/session.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_GET['LotKey']))  {
	$SelectedCOA=$_GET['LotKey'];
} elseif (isset($_POST['LotKey'])) {
	$SelectedCOA=$_POST['LotKey'];
}
if (isset($_GET['ProdSpec']))  {
	$SelectedSpec=$_GET['ProdSpec'];
} elseif (isset($_POST['ProdSpec'])) {
	$SelectedSpec=$_POST['ProdSpec'];
}

if (isset($_GET['QASampleID']))  {
	$QASampleID=$_GET['QASampleID'];
} elseif (isset($_POST['QASampleID'])) {
	$QASampleID=$_POST['QASampleID'];
}

//Get Out if we have no Certificate of Analysis
If ((!isset($SelectedCOA) || $SelectedCOA=='') AND (!isset($QASampleID) OR $QASampleID=='')){
	$ViewTopic = 'QualityAssurance';
	$BookMark = '';
        $Title = __('Select Certificate of Analysis To Print');
        include('includes/header.php');
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . __('Print')  . '" alt="" />' . ' ' . $Title . '</p>';
        echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
		<legend>', __('Report Criteria'), '</legend>
		<field>
			<label for="ProdSpec">' . __('Enter Item') .':</label>
			<input type="text" name="ProdSpec" size="25" maxlength="25" />
		</field>
		<field>
			<label for="LotKey">' . __('Enter Lot') .':</label>
			<input type="text" name="LotKey" size="25" maxlength="25" />
		</field>
		</fieldset>
		<div>
			<input type="submit" name="PickSpec" value="' . __('Submit') . '" />
		</div>
		</form>
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<fieldset>
		<field>
			<label for="QASampleID">' . __('Or Select Existing Lot') .':</label>';
	$SQLSpecSelect="SELECT sampleid,
							lotkey,
							prodspeckey,
							description
						FROM qasamples LEFT OUTER JOIN stockmaster
						ON stockmaster.stockid=qasamples.prodspeckey
						WHERE cert='1'
						ORDER BY lotkey";


	$ResultSelection=DB_query($SQLSpecSelect);
	echo '<select name="QASampleID" style="font-family: monospace; white-space:pre;">';
	echo '<option value="">' . str_pad(__('Lot/Serial'),15,'_'). str_pad(__('Item'),20, '_', STR_PAD_RIGHT). str_pad(__('Description'),20,'_') . '</option>';
	while ($MyRowSelection=DB_fetch_array($ResultSelection)){
		echo '<option value="' . $MyRowSelection['sampleid'] . '">' . str_pad($MyRowSelection['lotkey'],15, '_', STR_PAD_RIGHT). str_pad($MyRowSelection['prodspeckey'],20,'_') .htmlspecialchars($MyRowSelection['description'], ENT_QUOTES,'UTF-8', false)  . '</option>';
	}
	echo '</select>';
	echo '</field>
		</fieldset>
		<div>
		<input type="submit" name="PickSpec" value="' . __('Submit') . '" />
		</div>
		</form>';
    include('includes/footer.php');
    exit();
}


$ErrMsg = __('There was a problem retrieving the Lot Information') . ' ' .$SelectedCOA . ' ' . __('from the database');
if (isset($SelectedCOA)) {
	$SQL = "SELECT lotkey,
					description,
					name,
					method,
					qatests.units,
					type,
					testvalue,
					sampledate,
					prodspeckey,
					groupby
				FROM qasamples INNER JOIN sampleresults
				ON sampleresults.sampleid=qasamples.sampleid
				INNER JOIN qatests
				ON qatests.testid=sampleresults.testid
				LEFT OUTER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
				WHERE qasamples.lotkey='" .$SelectedCOA."'
				AND qasamples.prodspeckey='" .$SelectedSpec."'
				AND qasamples.cert='1'
				AND sampleresults.showoncert='1'
				ORDER by groupby, sampleresults.testid";
} else {
	$SQL = "SELECT lotkey,
					description,
					name,
					method,
					qatests.units,
					type,
					testvalue,
					sampledate,
					prodspeckey,
					groupby
				FROM qasamples INNER JOIN sampleresults
				ON sampleresults.sampleid=qasamples.sampleid
				INNER JOIN qatests
				ON qatests.testid=sampleresults.testid
				LEFT OUTER JOIN stockmaster on stockmaster.stockid=qasamples.prodspeckey
				LEFT OUTER JOIN prodspecgroups on prodspecgroups.groupname=qatests.groupby
				WHERE qasamples.sampleid='" .$QASampleID."'
				AND qasamples.cert='1'
				AND sampleresults.showoncert='1'
				ORDER by groupbyNo, sampleresults.testid";
}
$Result = DB_query($SQL, $ErrMsg);

//If there are no rows, there's a problem.
if (DB_num_rows($Result)==0){
	$Title = __('Print Certificate of Analysis Error');
	include('includes/header.php');
	echo '<div class="centre">
			<br />
			<br />
			<br />';
	prnMsg( __('Unable to Locate Lot') . ' : ' . $SelectedCOA . ' ', 'error');
	echo '<br />
			<br />
			<br />
			<table class="table_index">
			<tr>
				<td class="menu_group_item">
					<ul><li><a href="'. $RootPath . '/PDFCOA.php">' . __('Certificate of Analysis') . '</a></li></ul>
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
if ($QASampleID>'') {
	$MyRow=DB_fetch_array($Result);
	$SelectedCOA=$MyRow['lotkey'];
	DB_data_seek($Result,0);
}
include('includes/PDFStarter.php');
$pdf->addInfo('Title', __('Certificate of Analysis') );
$pdf->addInfo('Subject', __('Certificate of Analysis') . ' ' . $SelectedCOA);
$FontSize=12;
$PageNumber = 1;
$HeaderPrinted=0;
$LineHeight=$FontSize*1.25;
$RectHeight=12;
$SectionHeading=0;
$CurSection='NULL';
$SectionTitle='';
$SectionTrailer='';

$SectionsArray=[];
$result2 = DB_query("SELECT groupname, headertitle, trailertext, labels, numcols FROM prodspecgroups", $db);
while ($MyGroupRow = DB_fetch_array($result2)) {
	//echo $MyGroupRow['groupname'] . '&nbsp;' . $MyGroupRow['headertitle'] . '&nbsp;' . $MyGroupRow['trailertext'] . '&nbsp;' . $MyGroupRow['labels'] . '&nbsp;' . $MyGroupRow['numcols'] . '&nbsp;' ; 
	//echo'<br/>';
	if ($MyGroupRow['numcols']==2) {
		$align=array('left','center');
		$cols=array(240,265);
	} else {
		$align=array('left','center','center');
		$cols=array(260,110,135);
	}
	$SectionsArray[] = array($MyGroupRow['groupname'], $MyGroupRow['numcols'], $MyGroupRow['headertitle'],$MyGroupRow['trailertext'],$cols,explode(",",$MyGroupRow['labels']), $align);
} //end while loop
DB_data_seek($result2, 0);

while ($MyRow=DB_fetch_array($Result)){
	if ($MyRow['description']=='') {
		$MyRow['description']=$MyRow['prodspeckey'];
	}
	$Spec=$MyRow['description'];
	$SampleDate=ConvertSQLDate($MyRow['sampledate']);

	foreach($SectionsArray as $Row) {
		if ($MyRow['groupby']==$Row[0]) {
			$SectionColSizes=$Row[4];
			$SectionColLabs=$Row[5];
			$SectionAlign=$Row[6]; 
		}
	}
	$TrailerPrinted=1;
	if ($HeaderPrinted==0) {
		include('includes/PDFCOAHeader.php');
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
			include('includes/PDFCOAHeader.php');
		}
		$YPos -= $LineHeight; //added
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
	if ($MyRow['testvalue'] > '') {
		$Value=$MyRow['testvalue'];
	} //elseif ($MyRow['rangemin'] > '') {
	//	$Value=$MyRow['rangemin'] . ' - ' . $MyRow['rangemax'];
	//}
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
		include('includes/PDFCOAHeader.php');
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
	include('includes/PDFCOAHeader.php');
}

$FontSize=8;
$LineHeight=$FontSize*1.25;
$YPos -= $LineHeight;
$YPos -= $LineHeight;
$SQL = "SELECT confvalue
			FROM config
			WHERE confname='QualityCOAText'";

$Result = DB_query($SQL, $ErrMsg);
$MyRow=DB_fetch_array($Result);
$Disclaimer=$MyRow[0];
$LeftOvers = $pdf->addTextWrap($XPos+5,$YPos,500,$FontSize,$Disclaimer);
while (mb_strlen($LeftOvers) > 1) {
	$YPos -= $LineHeight;
	$LeftOvers = $pdf->addTextWrap($XPos+5,$YPos,500,$FontSize, $LeftOvers, 'left');
}

$pdf->OutputI($_SESSION['DatabaseName'] . 'COA' . date('Y-m-d') . '.pdf');
$pdf->__destruct();
