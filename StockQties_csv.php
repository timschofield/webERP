<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Produce Stock Quantities CSV');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');

function stripcomma($str) { //because we're using comma as a delimiter
	return str_replace(',', '', $str);
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory') .'" alt="" /><b>' . $Title. '</b></p>';

echo '<div class="centre">' . __('Making a comma separated values file of the current stock quantities') . '</div>';

$ErrMsg = __('The SQL to get the stock quantities failed with the message');

$SQL = "SELECT stockid, SUM(quantity) FROM locstock
			INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			GROUP BY stockid HAVING SUM(quantity)<>0";
$Result = DB_query($SQL, $ErrMsg);

if (!file_exists($_SESSION['reports_dir'])){
	$Result = mkdir('./' . $_SESSION['reports_dir']);
}

$FileName = $_SESSION['reports_dir'] . '/StockQties.csv';

$fp = fopen($FileName,'w');

if ($fp==false){

	prnMsg(__('Could not open or create the file under') . ' ' . $_SESSION['reports_dir'] . '/StockQties.csv','error');
	include('includes/footer.php');
	exit();
}

// the BOM is not used much anymore in 2025...
//fputs($fp, "\xEF\xBB\xBF"); // UTF-8 BOM
while ($MyRow = DB_fetch_row($Result)){
	$Line = stripcomma($MyRow[0]) . ', ' . stripcomma($MyRow[1]);
	fputs($fp, $Line . "\n");
}

fclose($fp);

echo '<br /><div class="centre"><a href="' . $RootPath . '/' . $_SESSION['reports_dir'] . '/StockQties.csv ">' . __('click here') . '</a> ' . __('to view the file') . '</div>';

include('includes/footer.php');
