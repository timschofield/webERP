<?php

/// @todo drop usage of SQLite3::escapeString, to avoid relying on the SQLite3 extension.

function Create_POS_Data_Full ($POSDebtorNo, $POSBranchCode, $PathPrefix) {

	set_time_limit(1800);
	ini_set('max_execution_time',1800);

	$Result = DB_query("SELECT confvalue FROM config WHERE confname='reports_dir'");
	$ReportDirRow = DB_fetch_row($Result);
	$ReportDir = $ReportDirRow[0];

	$Result = DB_query("SELECT confvalue FROM config WHERE confname='DefaultPriceList'");
	$DefaultPriceListRow = DB_fetch_row($Result);
	$DefaultPriceList= $DefaultPriceListRow[0];

	$Result = DB_query("SELECT confvalue FROM config WHERE confname='DefaultDateFormat'");
	$DefaultDateFormatRow = DB_fetch_row($Result);
	$DefaultDateFormat= $DefaultDateFormatRow[0];

	$Result = DB_query("SELECT currcode, salestype FROM debtorsmaster WHERE debtorno='" . $POSDebtorNo . "'");
	$CustomerRow = DB_fetch_array($Result);
	if (DB_num_rows($Result)==0){
		return 0;
	}
	$CurrCode = $CustomerRow['currcode'];
	$SalesType = $CustomerRow['salestype'];

	$FileHandle = fopen($PathPrefix . $ReportDir . '/POS.sql','w');

	if ($FileHandle == false){
		return 'Cannot open file ' . $PathPrefix . $ReportDir . '/POS.sql';
	}

	fwrite($FileHandle,"UPDATE config SET configvalue='" . $DefaultDateFormat . "' WHERE configname='DefaultDateFormat';\n");

	fwrite($FileHandle,"DELETE FROM currencies;\n");
	$Result = DB_query('SELECT currency, currabrev, country, hundredsname,decimalplaces, rate FROM currencies');
	while ($CurrRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO currencies VALUES ('" . $CurrRow['currency'] . "', '" . $CurrRow['currabrev'] . "', '" . SQLite3::escapeString ($CurrRow['country']) . "', '" . SQLite3::escapeString ($CurrRow['hundredsname']) . "', '" .$CurrRow['decimalplaces'] . "', '" .$CurrRow['rate'] . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM salestypes;\n");

	$Result = DB_query("SELECT typeabbrev, sales_type FROM salestypes");
	while ($MyRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO salestypes VALUES ('" . $MyRow['typeabbrev'] . "', '" . SQLite3::escapeString ($MyRow['sales_type']) . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM holdreasons;\n");

	$Result = DB_query("SELECT reasoncode, reasondescription, dissallowinvoices FROM holdreasons");
	while ($MyRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO holdreasons VALUES ('" . $MyRow['reasoncode'] . "', '" . SQLite3::escapeString ($MyRow['reasondescription']) . "', '" . $MyRow['dissallowinvoices'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM paymentterms;\n");

	$Result = DB_query("SELECT termsindicator, terms FROM paymentterms");
	while ($MyRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO paymentterms VALUES ('" . $MyRow['termsindicator'] . "', '" . SQLite3::escapeString ($MyRow['terms']) . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM paymentmethods;\n");
	$Result = DB_query("SELECT paymentid, paymentname,opencashdrawer FROM paymentmethods");
	while ($MyRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO paymentmethods VALUES ('" . $MyRow['paymentid'] . "', '" . SQLite3::escapeString ($MyRow['paymentname']) . "', '" . $MyRow['opencashdrawer'] . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM locations;\n");
	$Result = DB_query("SELECT loccode, locationname,taxprovinceid FROM locations");
	while ($MyRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO locations VALUES ('" . $MyRow['loccode'] . "', '" . SQLite3::escapeString ($MyRow['locationname']) . "', '" . $MyRow['taxprovinceid'] . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM stockcategory;\n");
	$Result = DB_query("SELECT categoryid, categorydescription FROM stockcategory");
	while ($MyRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO stockcategory VALUES ('" . $MyRow['categoryid'] . "', '" . SQLite3::escapeString ($MyRow['categorydescription']) . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM taxgroups;\n");
	$Result = DB_query("SELECT taxgroupid, taxgroupdescription FROM taxgroups");
	while ($MyRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO taxgroups VALUES ('" . $MyRow['taxgroupid'] . "', '" . SQLite3::escapeString ($MyRow['taxgroupdescription']) . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM taxgrouptaxes;\n");
	$Result = DB_query("SELECT taxgroupid, taxauthid, calculationorder, taxontax FROM taxgrouptaxes");
	while ($MyRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO taxgrouptaxes VALUES ('" . $MyRow['taxgroupid'] . "', '" . $MyRow['taxauthid'] . "', '" . $MyRow['calculationorder'] . "', '" . $MyRow['taxontax'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM taxauthorities;\n");
	$Result = DB_query("SELECT taxid, description FROM taxauthorities");
	while ($MyRow = DB_fetch_array($Result)) {

		  fwrite($FileHandle,"INSERT INTO taxauthorities VALUES ('" . $MyRow['taxid'] . "', '" . SQLite3::escapeString ($MyRow['description']) . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM taxauthrates;\n");
	$Result = DB_query("SELECT taxauthority, dispatchtaxprovince, taxcatid, taxrate FROM taxauthrates");
	while ($MyRow = DB_fetch_array($Result)) {
		  fwrite($FileHandle,"INSERT INTO taxauthrates VALUES ('" . $MyRow['taxauthority'] . "', '" . $MyRow['dispatchtaxprovince'] . "', '" . $MyRow['taxcatid'] . "', '" . $MyRow['taxrate'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM stockmaster;\n");


	$Result = DB_query("SELECT stockid, categoryid, description, longdescription, units, barcode, taxcatid,  decimalplaces, discountcategory FROM stockmaster WHERE (mbflag='B' OR mbflag='M' OR mbflag='D' OR mbflag='A') AND discontinued=0 AND controlled=0");

	while ($MyRow = DB_fetch_array($Result)) {

		fwrite($FileHandle,"INSERT INTO stockmaster VALUES ('" . SQLite3::escapeString($MyRow['stockid']) . "', '" . SQLite3::escapeString($MyRow['categoryid']) . "', '" . SQLite3::escapeString ($MyRow['description']) . "', '" . SQLite3::escapeString(str_replace("\n", '', $MyRow['longdescription'])) . "', '" . SQLite3::escapeString($MyRow['units']) . "', '" . SQLite3::escapeString ($MyRow['barcode']) . "', '" . $MyRow['taxcatid'] . "', '" . $MyRow['decimalplaces'] . "', '" . SQLite3::escapeString($MyRow['discountcategory']) . "' );\n");
	}
	fwrite($FileHandle,"DELETE FROM prices;\n");

	$Result = DB_query("SELECT prices.stockid,
								prices.typeabbrev,
								prices.currabrev,
								prices.debtorno,
								MIN(prices.price) AS lowestprice
							FROM prices INNER JOIN stockmaster
								ON prices.stockid=stockmaster.stockid
							WHERE (mbflag='B' OR mbflag='M')
							AND discontinued=0
							AND controlled=0
							AND prices.branchcode=''
							AND prices.currabrev='" . $CurrCode . "'
							AND prices.startdate <= CURRENT_DATE
							AND prices.enddate >= CURRENT_DATE
							GROUP BY prices.stockid,
									prices.typeabbrev,
									prices.currabrev,
									prices.debtorno");
	while ($MyRow = DB_fetch_array($Result)) {
		fwrite($FileHandle,"INSERT INTO prices VALUES ('" . SQLite3::escapeString($MyRow['stockid']) . "', '" . SQLite3::escapeString($MyRow['typeabbrev']) . "', '" . SQLite3::escapeString($MyRow['currabrev']) . "', '" . SQLite3::escapeString($MyRow['debtorno']) . "', '" . $MyRow['lowestprice'] . "', '');\n");
	}

	fwrite($FileHandle,"DELETE FROM discountmatrix;\n");
	$Result = DB_query("SELECT salestype, discountcategory, quantitybreak, discountrate FROM discountmatrix");
	while ($MyRow = DB_fetch_array($Result)) {
		  fwrite($FileHandle,"INSERT INTO discountmatrix VALUES ('" . SQLite3::escapeString($MyRow['salestype']) . "', '" . SQLite3::escapeString($MyRow['discountcategory']) . "', '" . $MyRow['quantitybreak'] . "', '" . $MyRow['discountrate'] . "');\n");
	}

	fwrite($FileHandle,"DELETE FROM debtorsmaster;\n");
	$Result = DB_query("SELECT debtorno, name, currcode, salestype, holdreason, paymentterms, discount, creditlimit, discountcode FROM debtorsmaster WHERE currcode='". $CurrCode . "'");
	while ($MyRow = DB_fetch_array($Result)) {
		  fwrite($FileHandle,"INSERT INTO debtorsmaster VALUES ('" . $MyRow['debtorno'] . "', '" . SQLite3::escapeString ($MyRow['name']) . "', '" . $MyRow['currcode'] . "', '" . $MyRow['salestype'] . "', '" . $MyRow['holdreason'] . "', '" . SQLite3::escapeString ($MyRow['paymentterms']) . "', '" . $MyRow['discount'] . "', '" . $MyRow['creditlimit'] . "', '" . $MyRow['discountcode'] . "');\n");
	}
	fwrite($FileHandle,"DELETE FROM custbranch;\n");
	$Result = DB_query("SELECT branchcode, debtorsmaster.debtorno, brname, contactname, specialinstructions,taxgroupid FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno=debtorsmaster.debtorno WHERE debtorsmaster.currcode='". $CurrCode . "'");
	while ($MyRow = DB_fetch_array($Result)) {
		  fwrite($FileHandle,"INSERT INTO custbranch VALUES ('" . $MyRow['branchcode'] . "', '" . $MyRow['debtorno'] . "', '" . SQLite3::escapeString ($MyRow['brname']) . "', '" . SQLite3::escapeString ($MyRow['contactname']) . "', '" . SQLite3::escapeString ($MyRow['specialinstructions']) . "', '" . $MyRow['taxgroupid'] . "');\n");
	}
	fclose($FileHandle);
	/*Now compress to a zip archive */
	if (file_exists($PathPrefix . $ReportDir . '/POS.sql.zip')){
		unlink($PathPrefix . $ReportDir . '/POS.sql.zip');
	}
	$ZipFile = new ZipArchive();
	if ($ZipFile->open($PathPrefix . $ReportDir . '/POS.sql.zip', ZIPARCHIVE::CREATE)!==true) {
		return 'couldnt open zip file ' . $PathPrefix . $ReportDir . '/POS.sql.zip';
	}
	$ZipFile->addFile($PathPrefix . $ReportDir . '/POS.sql','POS.sql');
	$ZipFile->close();
	//delete the original big sql file as we now have the zip for transferring
	unlink($PathPrefix . $ReportDir . '/POS.sql');
	set_time_limit($MaximumExecutionTime);
	ini_set('max_execution_time',$MaximumExecutionTime);
	return 1;
}

/// @todo 1. only replace characters which break SQL strings, ie. no &, <, >. Stop conflating sql-injection
///          prevention with html-escaping. Possibly use db-specific escapes for \n, \r (not html entities!)
/// @todo 2. the correct string to be used to escape single quotes might be database-dependent. Check $DBType global var
function escapeString($String) {
  $SearchCharacters  = array('&', '"', "'",'<', '>',"\n","\r");
  $ReplaceWith = array('&amp;', '""', "''", '&lt;', '&gt;', '', '&#13;');

  $String = str_replace($SearchCharacters, $ReplaceWith, $String);
  return $String;
}

function Delete_POS_Data($PathPrefix){

	$Result = DB_query("SELECT confvalue FROM config WHERE confname='reports_dir'");
	$ReportDirRow = DB_fetch_row($Result);
	$ReportDir = $ReportDirRow[0];


	$Success = true;
	if (file_exists($PathPrefix . $ReportDir . '/POS.sql.zip')){
		$Success = unlink($PathPrefix . $ReportDir . '/POS.sql.zip');
	}
	if (file_exists($PathPrefix . $ReportDir . '/POS.sql')){
		$Success = unlink($PathPrefix . $ReportDir . '/POS.sql');
	}
	if ($Success){
		return 1;
	} else {
		return 0;
	}
}
