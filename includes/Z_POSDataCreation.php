<?php

function CreatePOSDataFull ( $POSDebtorNo, $POSBranchCode, $db) {
	$result = DB_query("SELECT currcode, salestype FROM debtorsmaster WHERE debtorno='" . $POSDebtorNo . "'",$db);
	$CustomerRow = DB_fetch_array($result);
	if (DB_num_rows($result)==0){
		return 0;
	}
	$CurrCode = $CustomerRow['currcode'];
	$SalesType = $CustomerRow['salestype'];

	$FileHandle = fopen($_SESSION['reports_dir'] . '/POS.sql','w');

	fwrite($FileHandle,"DELETE FROM currencies;\n");
	$result = DB_query('SELECT currency, currabrev, country, hundredsname,decimalplaces, rate FROM currencies',$db);
	while ($CurrRow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO currencies VALUES ('" . $CurrRow['currency'] . "', '" . $CurrRow['currabrev'] . "', '" . sqlite_escape_string ($CurrRow['country']) . "', '" . sqlite_escape_string ($CurrRow['hundredsname']) . "', '" .$CurrRow['decimalplaces'] . "', '" .$CurrRow['rate'] . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM salestypes;\n");

	$result = DB_query('SELECT typeabbrev, sales_type FROM salestypes',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO salestypes VALUES ('" . $myrow['typeabbrev'] . "', '" . sqlite_escape_string ($myrow['sales_type']) . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM holdreasons;\n");

	$result = DB_query('SELECT reasoncode, reasondescription, dissallowinvoices FROM holdreasons',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO holdreasons VALUES ('" . $myrow['reasoncode'] . "', '" . sqlite_escape_string ($myrow['reasondescription']) . "', '" . $myrow['dissallowinvoices'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM paymentterms;\n");

	$result = DB_query('SELECT termsindicator, terms FROM paymentterms',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO paymentterms VALUES ('" . $myrow['termsindicator'] . "', '" . sqlite_escape_string ($myrow['terms']) . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM paymentmethods;\n");
	$result = DB_query('SELECT paymentid, paymentname,opencashdrawer FROM paymentmethods',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO paymentmethods VALUES ('" . $myrow['paymentid'] . "', '" . sqlite_escape_string ($myrow['paymentname']) . "', '" . $myrow['opencashdrawer'] . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM locations;\n");
	$result = DB_query('SELECT loccode, locationname,taxprovinceid FROM locations',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO locations VALUES ('" . $myrow['loccode'] . "', '" . sqlite_escape_string ($myrow['locationname']) . "', '" . $myrow['taxprovinceid'] . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM stockcategory;\n");
	$result = DB_query('SELECT categoryid, categorydescription FROM stockcategory',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO stockcategory VALUES ('" . $myrow['categoryid'] . "', '" . sqlite_escape_string ($myrow['categorydescription']) . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM taxgroups;\n");
	$result = DB_query('SELECT taxgroupid, taxgroupdescription FROM taxgroups',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO taxgroups VALUES ('" . $myrow['taxgroupid'] . "', '" . sqlite_escape_string ($myrow['taxgroupdescription']) . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM taxgrouptaxes;\n");
	$result = DB_query('SELECT taxgroupid, taxauthid, calculationorder, taxontax FROM taxgrouptaxes',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO taxgrouptaxes VALUES ('" . $myrow['taxgroupid'] . "', '" . $myrow['taxauthid'] . "', '" . $myrow['calculationorder'] . "', '" . $myrow['taxontax'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM taxauthorities;\n");
	$result = DB_query('SELECT taxid, description FROM taxauthorities',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO taxauthorities VALUES ('" . $myrow['taxid'] . "', '" . sqlite_escape_string ($myrow['description']) . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM taxauthrates;\n");
	$result = DB_query('SELECT taxauthority, dispatchtaxprovince, taxcatid, taxrate FROM taxauthrates',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO taxauthrates VALUES ('" . $myrow['taxauthority'] . "', '" . $myrow['dispatchtaxprovince'] . "', '" . $myrow['taxcatid'] . "', '" . $myrow['taxrate'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM stockmaster;\n");
	$result = DB_query("SELECT stockid, categoryid, description, longdescription, units, barcode, taxcatid, decimalplaces FROM stockmaster WHERE (mbflag='B' OR mbflag='M') AND discontinued=0 AND controlled=0",$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO stockmaster VALUES ('" . sqlite_escape_string ($myrow['stockid']) . "', '" . sqlite_escape_string ($myrow['categoryid']) . "', '" . sqlite_escape_string ($myrow['description']) . "', '" . sqlite_escape_string (str_replace("\n", '', $myrow['longdescription'])) . "', '" . sqlite_escape_string ($myrow['units']) . "', '" . sqlite_escape_string ($myrow['barcode']) . "', '" . $myrow['taxcatid'] . "', '" . $myrow['decimalplaces'] . "');\n");
	      $Price = GetPrice ($myrow['stockid'], $_POST['POSDebtorNo'], $_POST['POSBranchCode'], $db,0);
	      if ($Price!=0) {
		  	  fwrite($FileHandle,"INSERT INTO prices (stockid, currabrev, typeabbrev, price) VALUES('" . $myrow['stockid'] . "', '" . $CurrCode . "', '" . $SalesType . "', '" . $Price . "');\n");
  		  }

	}
	fwrite($FileHandle,"DELETE FROM debtorsmaster;\n");
	$result = DB_query("SELECT debtorno, name, currcode, salestype, holdreason, paymentterms, discount, creditlimit, discountcode FROM debtorsmaster WHERE currcode='". $CurrCode . "'",$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO debtorsmaster VALUES ('" . $myrow['debtorno'] . "', '" . sqlite_escape_string ($myrow['name']) . "', '" . $myrow['currcode'] . "', '" . $myrow['salestype'] . "', '" . $myrow['holdreason'] . "', '" . sqlite_escape_string ($myrow['paymentterms']) . "', '" . $myrow['discount'] . "', '" . $myrow['creditlimit'] . "', '" . $myrow['discountcode'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM custbranch;\n");
	$result = DB_query("SELECT branchcode, debtorsmaster.debtorno, brname, contactname, specialinstructions,taxgroupid FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno=debtorsmaster.debtorno WHERE debtorsmaster.currcode='". $CurrCode . "'",$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO custbranch VALUES ('" . $myrow['branchcode'] . "', '" . $myrow['debtorno'] . "', '" . sqlite_escape_string ($myrow['brname']) . "', '" . sqlite_escape_string ($myrow['contactname']) . "', '" . sqlite_escape_string ($myrow['specialinstructions']) . "', '" . $myrow['taxgroupid'] . "');\n");

	}

	fclose($FileHandle);
	/*Now compress to a zip archive */
	if (file_exists($_SESSION['reports_dir'] . '/POS.sql.zip')){
		unlink($_SESSION['reports_dir'] . '/POS.sql.zip');
	}
	$ZipFile = new ZipArchive();
	if ($ZipFile->open($_SESSION['reports_dir'] . '/POS.sql.zip', ZIPARCHIVE::CREATE)!==TRUE) {
		exit("cannot open <" . $_SESSION['reports_dir'] . "/POS.sql.zip\n");
		include('includes/footer.inc');
	}
	$ZipFile->addFile($_SESSION['reports_dir'] . '/POS.sql','POS.sql');
	$ZipFile->close();
}

?>