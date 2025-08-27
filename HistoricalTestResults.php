<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Historical Test Results');
$ViewTopic = 'QualityAssurance';
$BookMark = 'QA_HistoricalResults';
include('includes/header.php');

if (isset($_POST['FromDate'])){$_POST['FromDate'] = ConvertSQLDate($_POST['FromDate']);}
if (isset($_POST['ToDate'])){$_POST['ToDate'] = ConvertSQLDate($_POST['ToDate']);}

if (isset($_GET['KeyValue'])){
	$KeyValue =mb_strtoupper($_GET['KeyValue']);
} elseif (isset($_POST['KeyValue'])){
	$KeyValue =mb_strtoupper($_POST['KeyValue']);
} else {
	$KeyValue='';
}

if (!isset($_POST['FromDate'])){
	$_POST['FromDate']=Date(($_SESSION['DefaultDateFormat']), Mktime(0, 0, 0, Date('m'), Date('d')-180, Date('Y')));
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!Is_Date($_POST['FromDate'])) {
	$InputError = 1;
	prnMsg(__('Invalid From Date'),'error');
	$_POST['FromDate']=Date(($_SESSION['DefaultDateFormat']), Mktime(0, 0, 0, Date('m'), Date('d')-180, Date('Y')));
}
if (!Is_Date($_POST['ToDate'])) {
	$InputError = 1;
	prnMsg(__('Invalid To Date'),'error');
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}
$FromDate = FormatDateForSQL($_POST['FromDate']);
$ToDate = FormatDateForSQL($_POST['ToDate']);

$Errors = array();

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';


//prompt user for Key Value
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<fieldset>
		<legend>', __('Report Criteria'), '</legend>
		<field>
			<label for="KeyValue">' . __('Show Test Results For') .':</label>';
$SQLSpecSelect="SELECT DISTINCT(prodspeckey),
						description
					FROM qasamples LEFT OUTER JOIN stockmaster
					ON stockmaster.stockid=qasamples.prodspeckey";


$ResultSelection=DB_query($SQLSpecSelect);
echo '<select name="KeyValue">';

while ($MyRowSelection=DB_fetch_array($ResultSelection)){
	if ($MyRowSelection['prodspeckey']==$KeyValue) {
		$Selected='selected="selected" ';
	} else {
		$Selected='';
	}
	echo '<option ' . $Selected . ' value="' . $MyRowSelection['prodspeckey'] . '">' . $MyRowSelection['prodspeckey'].' - ' .htmlspecialchars($MyRowSelection['description'], ENT_QUOTES,'UTF-8', false)  . '</option>';
}
echo '</select>';
echo '</field>';

echo '<field>
		<label for="FromDate">' . __('From Sample Date') . ': </label>
		<input name="FromDate" maxlength="10" size="11" type="date" value="' . FormatDateForSQL($_POST['FromDate']) . '" />
	</field>
	<field>
		<label for="ToDate"> ' . __('To Sample Date') . ':</label>
		<input name="ToDate" maxlength="10" size="11" type="date" value="' . FormatDateForSQL($_POST['ToDate']) . '" />
	</field>
	</fieldset>
	<div>
	<input type="submit" name="PickSpec" value="' . __('Submit') . '" />
	</div>
	</form>';

//show header
$SQLSpecSelect="SELECT description
					FROM stockmaster
					WHERE stockmaster.stockid='" . $KeyValue . "'";

$ResultSelection=DB_query($SQLSpecSelect);
$MyRowSelection=DB_fetch_array($ResultSelection);
$SQLTests="SELECT sampleresults.testid,
					sampledate,
					sampleresults.sampleid,
					lotkey,
					identifier,
					cert,
					isinspec,
					testvalue,
					name
				FROM qasamples
				INNER JOIN sampleresults on sampleresults.sampleid=qasamples.sampleid
				INNER JOIN qatests
				ON qatests.testid=sampleresults.testid
				WHERE qasamples.prodspeckey='" . $KeyValue . "'
				AND sampleresults.showontestplan='1'
				AND sampledate>='" . $FromDate . "'
				AND sampledate <='" . $ToDate . "'";


$TestResult = DB_query($SQLTests);
$TestsArray=array();
$SamplesArray=array();
$AllResultsArray=array();
$TotResults=0;
while ($MyTestRow=DB_fetch_array($TestResult)) {
	$FormattedSampleID=str_pad($MyTestRow['sampleid'],10,'0',STR_PAD_LEFT);
	$TestKey=array_search ($MyTestRow['name'] , $TestsArray);
	if ($TestKey===false) {
		$TestsArray[$MyTestRow['name']]=$MyTestRow['name'];
	}

	$TestKey=array_search ($MyTestRow['sampleid'] , $SamplesArray);
	if ($TestKey===false) {
		$SamplesArray[$FormattedSampleID]=$MyTestRow;
		$TotResults++;
	}
	$AllResultsArray[$MyTestRow['name']][$FormattedSampleID]=$MyTestRow;
}

if ($TotResults>0) {
	echo '<br/>' . __('Historical Test Results for') . ' ' . $KeyValue . '-' . $MyRowSelection['description'] . '<br/>';

	echo '<div>
		<div style="overflow:auto; width:98%; padding:10px; ">
			<table width="90%" style="overflow: scroll;">
			<tr>
				<th style="white-space:nowrap;" class="number">' . __('Sample ID:') . '<br>' . __('Lot/Serial:') . '<br>' . __('Identifier:') . '<br>' . __('Sample Date:') .'</th>';
	foreach ($SamplesArray as $SampleKey => $SampleValue) {
		echo '<th>'. $SampleKey . '<br>' . $SampleValue['lotkey'] . '<br>' . $SampleValue['identifier'] . '<br>' . ConvertSQLDate($SampleValue['sampledate']).'</th>';
	}
	echo '</tr>';
	foreach ($TestsArray as $TestKey => $TestValue) {
		echo '<tr class="striped_row">
				<td class="select" style="white-space:nowrap;">'.$TestValue.'</td>';
		foreach ($SamplesArray as $SampleKey => $SampleValue) {
			if ($AllResultsArray[$TestKey][$SampleKey]['testvalue']=='' OR !isset($AllResultsArray[$TestKey][$SampleKey]['testvalue'])) {
				$AllResultsArray[$TestKey][$SampleKey]['testvalue']='&nbsp;';
			}
			echo '<td>'.$AllResultsArray[$TestKey][$SampleKey]['testvalue'].'</td>';
		}
		echo '</tr>';
	}
	echo '</tr></table></div></div>';
}

include('includes/footer.php');
