<?php

function Create_POS_Data_Full ($POSDebtorNo, $POSBranchCode, $db) {
	
	$result = DB_query("SELECT currcode, salestype FROM debtorsmaster WHERE debtorno='" . $POSDebtorNo . "'",$db);
	$CustomerRow = DB_fetch_array($result);
	if (DB_num_rows($result)==0){
		echo 'customer not found';
		return 0;
	}
	$CurrCode = $CustomerRow['currcode'];
	$SalesType = $CustomerRow['salestype'];

	$FileHandle = fopen($_SESSION['reports_dir'] . '/POS.sql','w');

	if ($FileHandle == false){
		echo 'cant open file';
		return 0;
	}

	fwrite($FileHandle,"DELETE FROM currencies;\n");
	$result = DB_query('SELECT currency, currabrev, country, hundredsname,decimalplaces, rate FROM currencies',$db);
	while ($CurrRow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO currencies VALUES ('" . $CurrRow['currency'] . "', '" . $CurrRow['currabrev'] . "', '" . SQLite_Escape ($CurrRow['country']) . "', '" . SQLite_Escape ($CurrRow['hundredsname']) . "', '" .$CurrRow['decimalplaces'] . "', '" .$CurrRow['rate'] . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM salestypes;\n");

	$result = DB_query('SELECT typeabbrev, sales_type FROM salestypes',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO salestypes VALUES ('" . $myrow['typeabbrev'] . "', '" . SQLite_Escape ($myrow['sales_type']) . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM holdreasons;\n");

	$result = DB_query('SELECT reasoncode, reasondescription, dissallowinvoices FROM holdreasons',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO holdreasons VALUES ('" . $myrow['reasoncode'] . "', '" . SQLite_Escape ($myrow['reasondescription']) . "', '" . $myrow['dissallowinvoices'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM paymentterms;\n");

	$result = DB_query('SELECT termsindicator, terms FROM paymentterms',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO paymentterms VALUES ('" . $myrow['termsindicator'] . "', '" . SQLite_Escape ($myrow['terms']) . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM paymentmethods;\n");
	$result = DB_query('SELECT paymentid, paymentname,opencashdrawer FROM paymentmethods',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO paymentmethods VALUES ('" . $myrow['paymentid'] . "', '" . SQLite_Escape ($myrow['paymentname']) . "', '" . $myrow['opencashdrawer'] . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM locations;\n");
	$result = DB_query('SELECT loccode, locationname,taxprovinceid FROM locations',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO locations VALUES ('" . $myrow['loccode'] . "', '" . SQLite_Escape ($myrow['locationname']) . "', '" . $myrow['taxprovinceid'] . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM stockcategory;\n");
	$result = DB_query('SELECT categoryid, categorydescription FROM stockcategory',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO stockcategory VALUES ('" . $myrow['categoryid'] . "', '" . SQLite_Escape ($myrow['categorydescription']) . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM taxgroups;\n");
	$result = DB_query('SELECT taxgroupid, taxgroupdescription FROM taxgroups',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO taxgroups VALUES ('" . $myrow['taxgroupid'] . "', '" . SQLite_Escape ($myrow['taxgroupdescription']) . "');\n");

	}

	fwrite($FileHandle,"DELETE FROM taxgrouptaxes;\n");
	$result = DB_query('SELECT taxgroupid, taxauthid, calculationorder, taxontax FROM taxgrouptaxes',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO taxgrouptaxes VALUES ('" . $myrow['taxgroupid'] . "', '" . $myrow['taxauthid'] . "', '" . $myrow['calculationorder'] . "', '" . $myrow['taxontax'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM taxauthorities;\n");
	$result = DB_query('SELECT taxid, description FROM taxauthorities',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO taxauthorities VALUES ('" . $myrow['taxid'] . "', '" . SQLite_Escape ($myrow['description']) . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM taxauthrates;\n");
	$result = DB_query('SELECT taxauthority, dispatchtaxprovince, taxcatid, taxrate FROM taxauthrates',$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO taxauthrates VALUES ('" . $myrow['taxauthority'] . "', '" . $myrow['dispatchtaxprovince'] . "', '" . $myrow['taxcatid'] . "', '" . $myrow['taxrate'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM stockmaster;\n");
	$result = DB_query("SELECT stockid, categoryid, description, longdescription, units, barcode, taxcatid, decimalplaces FROM stockmaster WHERE (mbflag='B' OR mbflag='M') AND discontinued=0 AND controlled=0",$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO stockmaster VALUES ('" . SQLite_Escape ($myrow['stockid']) . "', '" . SQLite_Escape ($myrow['categoryid']) . "', '" . SQLite_Escape ($myrow['description']) . "', '" . SQLite_Escape (str_replace("\n", '', $myrow['longdescription'])) . "', '" . SQLite_Escape ($myrow['units']) . "', '" . SQLite_Escape ($myrow['barcode']) . "', '" . $myrow['taxcatid'] . "', '" . $myrow['decimalplaces'] . "');\n");
	      $Price = GetPriceQuick ($myrow['stockid'], $_POST['POSDebtorNo'], $_POST['POSBranchCode'], $db);
	      if ($Price!=0) {
		  	  fwrite($FileHandle,"INSERT INTO prices (stockid, currabrev, typeabbrev, price) VALUES('" . $myrow['stockid'] . "', '" . $CurrCode . "', '" . $SalesType . "', '" . $Price . "');\n");
  		  }

	}
	fwrite($FileHandle,"DELETE FROM debtorsmaster;\n");
	$result = DB_query("SELECT debtorno, name, currcode, salestype, holdreason, paymentterms, discount, creditlimit, discountcode FROM debtorsmaster WHERE currcode='". $CurrCode . "'",$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO debtorsmaster VALUES ('" . $myrow['debtorno'] . "', '" . SQLite_Escape ($myrow['name']) . "', '" . $myrow['currcode'] . "', '" . $myrow['salestype'] . "', '" . $myrow['holdreason'] . "', '" . SQLite_Escape ($myrow['paymentterms']) . "', '" . $myrow['discount'] . "', '" . $myrow['creditlimit'] . "', '" . $myrow['discountcode'] . "');\n");

	}
	fwrite($FileHandle,"DELETE FROM custbranch;\n");
	$result = DB_query("SELECT branchcode, debtorsmaster.debtorno, brname, contactname, specialinstructions,taxgroupid FROM custbranch INNER JOIN debtorsmaster ON custbranch.debtorno=debtorsmaster.debtorno WHERE debtorsmaster.currcode='". $CurrCode . "'",$db);
	while ($myrow = DB_fetch_array($result)) {

		  fwrite($FileHandle,"INSERT INTO custbranch VALUES ('" . $myrow['branchcode'] . "', '" . $myrow['debtorno'] . "', '" . SQLite_Escape ($myrow['brname']) . "', '" . SQLite_Escape ($myrow['contactname']) . "', '" . SQLite_Escape ($myrow['specialinstructions']) . "', '" . $myrow['taxgroupid'] . "');\n");

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
	//delete the original big sql file as we now have the zip for transferring
	unlink($_SESSION['reports_dir'] . '/POS.sql');
	return 1;
}

function SQLite_Escape($String) {
  $SearchCharacters  = array('&', '"', "'",'<', '>',"\n","\r"    );
  $ReplaceWith = array('&amp;', '""', "''", '&lt;', '&gt;', '', '&#13;');

  $String = str_replace($SearchCharacters, $ReplaceWith, $String);
  return $String;
}
function Delete_POS_Data(){
	$Success = true;
	if (file_exists($_SESSION['reports_dir'] . '/POS.sql.zip')){
		$Success = unlink($_SESSION['reports_dir'] . '/POS.sql');
	}
	if (file_exists($_SESSION['reports_dir'] . '/POS.sql')){
		$Success = unlink($_SESSION['reports_dir'] . '/POS.sql');
	}
	if ($Success){
		return 1;
	} else {
		return 0;
	}
}
?>