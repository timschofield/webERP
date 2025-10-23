<?php

require(__DIR__ . '/includes/session.php');
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

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
if ((!isset($SelectedCOA) || $SelectedCOA=='') && (!isset($QASampleID) || $QASampleID=='')){
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
		echo '<option value="' . $MyRowSelection['sampleid'] . '">' . str_pad($MyRowSelection['lotkey'],15, '_', STR_PAD_RIGHT). str_pad($MyRowSelection['prodspeckey'],20,'_') .htmlspecialchars($MyRowSelection['description']) . '</option>';
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

if ($QASampleID>'') {
	$MyRow=DB_fetch_array($Result);
	$SelectedCOA=$MyRow['lotkey'];
	DB_data_seek($Result,0);
}

// Prepare HTML for PDF
$HTML = '
<html>
<head>
<style>
	body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; }
	h2.title { text-align: center; background: #f0f0f0; padding: 8px; }
	table.certificate { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
	table.certificate th, table.certificate td { border: 1px solid #ccc; padding: 4px 8px; font-size: 12px; }
	table.certificate th { background: #e0e0e0; }
	.section-title { font-weight: bold; font-size: 13px; padding-bottom: 4px; }
	.section-trailer { font-size: 10px; margin-bottom: 10px; }
	.disclaimer { font-size: 10px; margin-top: 16px; }
</style>
</head>
<body>
';

$HTML .= '<h2 class="title">' . __('Certificate of Analysis') . ' ' . htmlspecialchars($SelectedCOA) . '</h2>';

// Optionally include a header file (logic from includes/PDFCOAHeader.php if needed as HTML)
// For now, add a basic header:
$HTML .= '<table style="width:100%;margin-bottom:10px;"><tr>
<td style="width:33%;">' . __('Lot:') . ' ' . htmlspecialchars($SelectedCOA) . '</td>
<td style="width:33%;text-align:center;">' . __('Date:') . ' ' . date('Y-m-d') . '</td>
<td style="width:33%;text-align:right;">' . __('Item:') . ' ' . (isset($SelectedSpec)?htmlspecialchars($SelectedSpec):'') . '</td>
</tr></table>';

$SectionsArray=[];
$result2 = DB_query("SELECT groupname, headertitle, trailertext, labels, numcols FROM prodspecgroups", $db);
while ($MyGroupRow = DB_fetch_array($result2)) {
	if ($MyGroupRow['numcols']==2) {
		$cols=array(240,265);
	} else {
		$cols=array(260,110,135);
	}
	$SectionsArray[] = array(
		$MyGroupRow['groupname'],
		$MyGroupRow['numcols'],
		$MyGroupRow['headertitle'],
		$MyGroupRow['trailertext'],
		$cols,
		explode(",",$MyGroupRow['labels'])
	);
}

$CurSection = 'NULL';
$SectionTitle = '';
$SectionTrailer = '';
$PrevTrailer = '';
$PrintTrailer = 1;
$tableOpen = false;

while ($MyRow=DB_fetch_array($Result)){
	if ($MyRow['description']=='') {
		$MyRow['description']=$MyRow['prodspeckey'];
	}
	$Spec = htmlspecialchars($MyRow['description']);
	$SampleDate = ConvertSQLDate($MyRow['sampledate']);

	foreach($SectionsArray as $Row) {
		if ($MyRow['groupby']==$Row[0]) {
			$SectionColSizes = $Row[4];
			$SectionColLabs = $Row[5];
			$SectionTitle = $Row[2];
			$SectionTrailer = $Row[3];
		}
	}

	if ($CurSection != $MyRow['groupby']) {
		if ($CurSection != 'NULL' && $PrintTrailer==1 && $PrevTrailer != '') {
			if ($tableOpen) {
				$HTML .= '</table>';
				$tableOpen = false;
			}
			$HTML .= '<div class="section-trailer">'.htmlspecialchars($PrevTrailer).'</div>';
		}
		$CurSection = $MyRow['groupby'];
		$HTML .= '<div class="section-title">'.htmlspecialchars($SectionTitle).'</div>';
		$HTML .= '<table class="certificate"><tr>';
		foreach ($SectionColLabs as $ColLabel) {
			$HTML .= '<th>'.htmlspecialchars($ColLabel).'</th>';
		}
		$HTML .= '</tr>';
		$tableOpen = true;
		$SectionHeading = 1;
		$PrevTrailer = $SectionTrailer;
	}

	$Value = '';
	if ($MyRow['testvalue'] > '') {
		$Value = $MyRow['testvalue'];
	}
	if (strtoupper($Value) !== 'NB' && strtoupper($Value) !== 'NO BREAK') {
		$Value .= ' ' . $MyRow['units'];
	}
	$rowHtml = '<tr>';
	for ($x = 0; $x < count($SectionColLabs); $x++) {
		switch ($x) {
			case 0:
				$DispValue = htmlspecialchars($MyRow['name']);
				break;
			case 1:
				$DispValue = htmlspecialchars($Value);
				break;
			case 2:
				$DispValue = htmlspecialchars($MyRow['method']);
				break;
			default:
				$DispValue = '';
		}
		$rowHtml .= '<td>'.$DispValue.'</td>';
	}
	$rowHtml .= '</tr>';
	$HTML .= $rowHtml;
}
if ($tableOpen) {
	$HTML .= '</table>';
}

if ($SectionTrailer>'') {
	$HTML .= '<div class="section-trailer">'.htmlspecialchars($SectionTrailer).'</div>';
}

// Disclaimer
$SQL = "SELECT confvalue FROM config WHERE confname='QualityCOAText'";
$Result = DB_query($SQL, $ErrMsg);
$MyRow = DB_fetch_array($Result);
$Disclaimer = $MyRow[0];
if ($Disclaimer > '') {
	$HTML .= '<div class="disclaimer">'.htmlspecialchars($Disclaimer).'</div>';
}

$HTML .= '</body></html>';

// DomPDF options and rendering
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($HTML);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();

// Output to browser
$filename = $_SESSION['DatabaseName'] . 'COA' . date('Y-m-d') . '.pdf';
$dompdf->stream($filename, array("Attachment" => false));

exit;