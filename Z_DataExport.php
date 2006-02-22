<?php

/* $Revision: 1.5 $ */

$PageSecurity=15;

include('includes/session.inc');

function stripcomma($str) { //because we're using comma as a delimiter
    $str = trim($str);
    $str = str_replace('"', '""', $str);
    $str = str_replace("\r", "", $str);
    $str = str_replace("\n", '\n', $str);
    if($str == "" )
        return $str;
    else
        return '"'.$str.'"';
}

function NULLToZero( &$Field ) {
    if( is_null($Field) )
        return '0';
    else
        return $Field;
}

function NULLToPrice( &$Field ) {
    if( is_null($Field) )
        return '-1';
    else
        return $Field;
}


// EXPORT FOR PRICE LIST
if ( isset($_POST['pricelist']) ) {
	
		$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'";
		$SalesTypeResult = DB_query($SQL,$db);
		$SalesTypeRow = DB_fetch_row($SalesTypeResult);
		$SalesTypeName = $SalesTypeRow[0];

		$SQL = "SELECT prices.typeabbrev,
				prices.stockid,
				stockmaster.description,
				prices.currabrev,
				prices.price,
				stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost 
					as standardcost,
				stockmaster.categoryid,
				stockcategory.categorydescription,
				stockmaster.barcode,
				stockmaster.units,
				stockmaster.mbflag,
				stockmaster.taxcatid,
				stockmaster.discontinued
			FROM prices,
				stockmaster,
				stockcategory
			WHERE stockmaster.stockid=prices.stockid
			AND stockmaster.categoryid=stockcategory.categoryid
			AND prices.typeabbrev='" . $_POST['SalesType'] . "'
			AND ( (prices.debtorno='') OR (prices.debtorno IS NULL))
			ORDER BY prices.currabrev,
				stockmaster.categoryid,
				stockmaster.stockid";
	$PricesResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Price List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Price List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
	$CSVContent = stripcomma('stockid') . ',' . 
			stripcomma('description') . ',' .
			stripcomma('barcode') . ',' .
			stripcomma('units') . ',' .
			stripcomma('mbflag') . ',' .
			stripcomma('taxcatid') . ',' .
			stripcomma('discontinued') . ',' .
			stripcomma('price') . ',' . 
			stripcomma('qty') . ',' . 
			stripcomma('categoryid') . ',' . 
			stripcomma('categorydescription') . "\n";
			
	While ($PriceList = DB_fetch_array($PricesResult,$db)){
		$Qty = 0;
		$sqlQty = "SELECT newqoh
			FROM stockmoves 
			WHERE stockid = '".$PriceList['stockid']."'
			AND loccode = '".$_POST['Location']."'
			ORDER BY stkmoveno DESC LIMIT 1";
		$resultQty = DB_query($sqlQty, $db, $ErrMsg);
		if ( $resultQty ) {
			if( DB_num_rows($resultQty) > 0 ) {
				$Row = DB_fetch_row($resultQty);
				$Qty = $Row[0];
			}
			DB_free_result($resultQty);
		}
		
		$DisplayUnitPrice = $PriceList['price'];
				
		$CSVContent .= (stripcomma($PriceList['stockid']) . ',' . 
			stripcomma($PriceList['description']) . ',' . 
			stripcomma($PriceList['barcode']) . ',' .
			stripcomma($PriceList['units']) . ',' .
			stripcomma($PriceList['mbflag']) . ',' .
			stripcomma($PriceList['taxcatid']) . ',' .
			stripcomma($PriceList['discontinued']) . ',' .
			stripcomma($DisplayUnitPrice) . ',' . 
			stripcomma($Qty) . ',' .
			stripcomma($PriceList['categoryid']) . ',' .
			stripcomma($PriceList['categorydescription']) . "\n"
			);
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=PriceList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;

} elseif ( isset($_POST['custlist']) ) {
	$SQL = "SELECT debtorsmaster.debtorno, 
			custbranch.branchcode,
			debtorsmaster.name,
			custbranch.contactname,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			debtorsmaster.currcode,
			debtorsmaster.clientsince,
			debtorsmaster.creditlimit,
			debtorsmaster.taxref,
			custbranch.braddress1,
			custbranch.braddress2,
			custbranch.braddress3,
			custbranch.braddress4,
			custbranch.braddress5,
			custbranch.braddress6,
			custbranch.disabletrans,
			custbranch.phoneno,
			custbranch.faxno,
			custbranch.email
		FROM debtorsmaster,
			custbranch
		WHERE debtorsmaster.debtorno=custbranch.debtorno
		AND ((defaultlocation = '".$_POST['Location']."') OR (defaultlocation = '') OR (defaultlocation IS NULL))";

	$CustResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Customer List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Customer List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent = stripcomma('debtorno') . ',' . 
			stripcomma('branchcode') . ',' .
			stripcomma('name') . ',' .
			stripcomma('contactname') . ',' .
			stripcomma('address1') . ',' .
			stripcomma('address2') . ',' .
			stripcomma('address3') . ',' .
			stripcomma('address4') . ',' .
			stripcomma('address5') . ',' .
			stripcomma('address6') . ',' .
			stripcomma('phoneno') . ',' .
			stripcomma('faxno') . ',' .
			stripcomma('email') . ',' .
			stripcomma('currcode') . ',' .
			stripcomma('clientsince') . ',' . 
			stripcomma('creditlimit') . ',' . 
			stripcomma('taxref') . ',' . 
			stripcomma('disabletrans') . "\n";
	
	
	While ($CustList = DB_fetch_array($CustResult,$db)){
		
		$CreditLimit = $CustList['creditlimit'];
		if ( strlen($CustList['braddress1']) <= 3 ) {
			$Address1 = $CustList['address1'];
			$Address2 = $CustList['address2'];
			$Address3 = $CustList['address3'];
			$Address4 = $CustList['address4'];
			$Address5 = $CustList['address5'];
			$Address6 = $CustList['address6'];
		} else {
			$Address1 = $CustList['braddress1'];
			$Address2 = $CustList['braddress2'];
			$Address3 = $CustList['braddress3'];
			$Address4 = $CustList['braddress4'];
			$Address5 = $CustList['braddress5'];
			$Address6 = $CustList['braddress6'];
		}

		$CSVContent .= (stripcomma($CustList['debtorno']) . ',' . 
			stripcomma($CustList['branchcode']) . ',' . 
			stripcomma($CustList['name']) . ',' .
			stripcomma($CustList['contactname']) . ',' .
			stripcomma($Address1) . ',' .
			stripcomma($Address2) . ',' .
			stripcomma($Address3) . ',' .
			stripcomma($Address4) . ',' .
			stripcomma($Address5) . ',' .
			stripcomma($Address6) . ',' .
			stripcomma($CustList['phoneno']) . ',' .
			stripcomma($CustList['faxno']) . ',' .
			stripcomma($CustList['email']) . ',' .
			stripcomma($CustList['currcode']) . ',' .
			stripcomma($CustList['clientsince']) . ',' .
			stripcomma($CreditLimit) . ',' .
			stripcomma($CustList['taxref']) . ',' .
			stripcomma($CustList['disabletrans']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=CustList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;

} elseif ( isset($_POST['salesmanlist']) ) {
	$SQL = "SELECT salesmancode, 
			salesmanname,
			smantel,
			smanfax,
			commissionrate1,
			breakpoint,
			commissionrate2
		FROM salesman";

	$SalesManResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Salesman List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Salesman List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
	$CSVContent = stripcomma('salesmancode') . ',' . 
			stripcomma('salesmanname') . ',' .
			stripcomma('smantel') . ',' .
			stripcomma('smanfax') . ',' .
			stripcomma('commissionrate1') . ',' .
			stripcomma('breakpoint') . ',' .
			stripcomma('commissionrate2') . "\n";
	
	
	While ($SalesManList = DB_fetch_array($SalesManResult,$db)){
		
		$CommissionRate1 = $SalesManList['commissionrate1'];
		$BreakPoint 	 = $SalesManList['breakpoint'];
		$CommissionRate2 = $SalesManList['commissionrate2'];

		$CSVContent .= (stripcomma($SalesManList['salesmancode']) . ',' . 
			stripcomma($SalesManList['salesmanname']) . ',' . 
			stripcomma($SalesManList['smantel']) . ',' .
			stripcomma($SalesManList['smanfax']) . ',' .
			stripcomma($CommissionRate1) . ',' .
			stripcomma($BreakPoint) . ',' .
			stripcomma($CommissionRate2) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SalesmanList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['imagelist']) ) {
	$SQL = "SELECT stockid
		FROM stockmaster
		ORDER BY stockid";
	$ImageResult = DB_query($SQL,$db,'','',false,false);
	
	if (DB_error_no($db) !=0) {
		$title = _('Security Token List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Image List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$CSVContent = stripcomma('stockid') . ','. 
				  stripcomma('filename') . ','. 
				  stripcomma('url') . "\n";
	$baseurl = 'http://'. $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . 'getstockimg.php?automake=1&stockid=%s.png';
	While ($ImageList = DB_fetch_array($ImageResult,$db)){
		$url = sprintf($baseurl, urlencode($ImageList['stockid']));
		$CSVContent .= (
			stripcomma($ImageList['stockid']) . ',' . 
			stripcomma($ImageList['stockid'] . '.png') . ',' . 
			stripcomma($url) . "\n");
	}
	
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=ImageList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;	
} elseif ( isset($_POST['sectokenlist']) ) {
	$SQL = "SELECT tokenid, 
			tokenname
		FROM securitytokens";

	$SecTokenResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Security Token List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Security Token List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
	$CSVContent = stripcomma('tokenid') . ',' . 
			stripcomma('tokenname') . "\n";
	
	
	While ($SecTokenList = DB_fetch_array($SecTokenResult,$db)){

		$CSVContent .= (stripcomma($SecTokenList['tokenid']) . ',' . 
			stripcomma($SecTokenList['tokenname']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecTokenList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['secrolelist']) ) {
	$SQL = "SELECT secroleid, 
			secrolename
		FROM securityroles";

	$SecRoleResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Security Role List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Security Role List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
	$CSVContent = stripcomma('secroleid') . ',' . 
			stripcomma('secrolename') . "\n";
	
	
	While ($SecRoleList = DB_fetch_array($SecRoleResult,$db)){

		$CSVContent .= (stripcomma($SecRoleList['secroleid']) . ',' . 
			stripcomma($SecRoleList['secrolename']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecRoleList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['secgrouplist']) ) {
	$SQL = "SELECT secroleid, 
			tokenid
		FROM securitygroups";

	$SecGroupResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Security Group List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Security Group List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
	$CSVContent = stripcomma('secroleid') . ',' . 
			stripcomma('tokenid') . "\n";
	
	
	While ($SecGroupList = DB_fetch_array($SecGroupResult,$db)){

		$CSVContent .= (stripcomma($SecGroupList['secroleid']) . ',' . 
			stripcomma($SecGroupList['tokenid']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecGroupList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['secuserlist']) ) {
	$SQL = "SELECT userid, 
			password,
			realname,
			customerid,
			phone,
			email,
			defaultlocation,
			fullaccess,
			lastvisitdate,
			branchcode,
			pagesize,
			modulesallowed,
			blocked,
			displayrecordsmax,
			theme,
			language,
			pinno,
			swipecard
		FROM www_users 
		WHERE (customerid <> '') OR 
			(NOT customerid IS NULL)";

	$SecUserResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Security User List Export Problem ....');
		include('includes/header.inc');
		prnMsg( _('The Security User List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
	$CSVContent = stripcomma('userid') . ',' . 
			stripcomma('password') . ','.
			stripcomma('realname') . ','.
			stripcomma('customerid') . ','.
			stripcomma('phone') . ','.
			stripcomma('email') . ','.
			stripcomma('defaultlocation') . ','.
			stripcomma('fullaccess') . ','.
			stripcomma('lastvisitdate') . ','.
			stripcomma('branchcode') . ','.
			stripcomma('pagesize') . ','.
			stripcomma('modulesallowed') . ','.
			stripcomma('blocked') . ','.
			stripcomma('displayrecordsmax') . ','.
			stripcomma('theme') . ','.
			stripcomma('language') . ','.
			stripcomma('pinno') . ','.
			stripcomma('swipecard') . "\n";
	
	
	While ($SecUserList = DB_fetch_array($SecUserResult,$db)){

		$CSVContent .= (stripcomma($SecUserList['userid']) . ',' . 
			stripcomma($SecUserList['password']) . ',' .
			stripcomma($SecUserList['realname']) . ',' .
			stripcomma($SecUserList['customerid']) . ',' .
			stripcomma($SecUserList['phone']) . ',' .
			stripcomma($SecUserList['email']) . ',' .
			stripcomma($SecUserList['defaultlocation']) . ',' .
			stripcomma($SecUserList['fullaccess']) . ',' .
			stripcomma($SecUserList['lastvisitdate']) . ',' .
			stripcomma($SecUserList['branchcode']) . ',' .
			stripcomma($SecUserList['pagesize']) . ',' .
			stripcomma($SecUserList['modulesallowed']) . ',' .
			stripcomma($SecUserList['blocked']) . ',' .
			stripcomma($SecUserList['displayrecordsmax']) . ',' .
			stripcomma($SecUserList['theme']) . ',' .
			stripcomma($SecUserList['language']) . ',' .
			stripcomma($SecUserList['pinno']) . ',' .
			stripcomma($SecUserList['swipecard']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecUserList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} else {
	$title = _('Data Exports');
	include('includes/header.inc');
		
	// SELECT EXPORT FOR PRICE LIST
	
	echo "<BR><CENTER>";
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo '<CENTER><TABLE>';
	echo '<TR><TD colspan=2 class="tableheader" >' . _('Price List Export') . '</TD></TR>';
	$sql = 'SELECT sales_type, typeabbrev FROM salestypes';
	$SalesTypesResult=DB_query($sql,$db);
	echo '<TR><TD>' . _('For Sales Type/Price List') . ':</TD>';
	echo '<TD><SELECT name="SalesType">';
	while ($myrow=DB_fetch_array($SalesTypesResult)){
	          echo '<OPTION Value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'];
	}
	echo '</SELECT></TD></TR>';
	
	$sql = 'SELECT loccode, locationname FROM locations';
	$SalesTypesResult=DB_query($sql,$db);
	echo '<TR><TD>' . _('For Location') . ':</TD>';
	echo '<TD><SELECT name="Location">';
	while ($myrow=DB_fetch_array($SalesTypesResult)){
	          echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
	echo '</SELECT></TD></TR>';
	echo "</TABLE>";
	echo "<CENTER><input type='Submit' name='pricelist' value='" . _('Export') . "'></CENTER>";
	echo "</FORM></CENTER><BR>";

	// SELECT EXPORT FOR CUSTOMER LIST
	
	
	echo "<BR><CENTER>";
	// Export Stock For Location
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo '<CENTER><TABLE>';
	echo '<TR><TD colspan=2 class="tableheader" >' . _('Customer List Export') . '</TD></TR>';
	
	$sql = 'SELECT loccode, locationname FROM locations';
	$SalesTypesResult=DB_query($sql,$db);
	echo '<TR><TD>' . _('For Location') . ':</TD>';
	echo '<TD><SELECT name="Location">';
	while ($myrow=DB_fetch_array($SalesTypesResult)){
	          echo '<OPTION Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];
	}
	echo '</SELECT></TD></TR>';
	echo "</TABLE>";
	echo "<CENTER><input type='Submit' name='custlist' value='" . _('Export') . "'></CENTER>";
	echo "</FORM></CENTER><BR>";

	// SELECT EXPORT FOR SALES MAN
	
	echo "<BR><CENTER>";
	// Export Stock For Location
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo '<CENTER><TABLE>';
	echo '<TR><TD colspan=2 class="tableheader" >' . _('Salesman List Export') . '</TD></TR>';
	echo "</TABLE>";
	echo "<CENTER><input type='Submit' name='salesmanlist' value='" . _('Export') . "'></CENTER>";
	echo "</FORM></CENTER><BR>";
	
	// SELECT EXPORT FOR IMAGES
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo '<CENTER><TABLE>';
	echo '<TR><TD colspan=2 class="tableheader" >' . _('Image List Export') . '</TD></TR>';
	echo "</TABLE>";
	echo "<CENTER><input type='Submit' name='imagelist' value='" . _('Export') . "'></CENTER>";
	echo "</FORM></CENTER><BR>";

	// SELECT EXPORT SECURITY TOKENS
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo '<CENTER><TABLE>';
	echo '<TR><TD colspan=2 class="tableheader" >' . _('Security Token List Export') . '</TD></TR>';
	echo "</TABLE>";
	echo "<CENTER><input type='Submit' name='sectokenlist' value='" . _('Export') . "'></CENTER>";
	echo "</FORM></CENTER><BR>";
	
	// SELECT EXPORT SECURITY ROLES
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo '<CENTER><TABLE>';
	echo '<TR><TD colspan=2 class="tableheader" >' . _('Security Role List Export') . '</TD></TR>';
	echo "</TABLE>";
	echo "<CENTER><input type='Submit' name='secrolelist' value='" . _('Export') . "'></CENTER>";
	echo "</FORM></CENTER><BR>";

	// SELECT EXPORT SECURITY GROUPS
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo '<CENTER><TABLE>';
	echo '<TR><TD colspan=2 class="tableheader" >' . _('Security Group List Export') . '</TD></TR>';
	echo "</TABLE>";
	echo "<CENTER><input type='Submit' name='secgrouplist' value='" . _('Export') . "'></CENTER>";
	echo "</FORM></CENTER><BR>";

	// SELECT EXPORT SECURITY USERS
	echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
	echo '<CENTER><TABLE>';
	echo '<TR><TD colspan=2 class="tableheader" >' . _('Security User List Export') . '</TD></TR>';
	echo "</TABLE>";
	echo "<CENTER><input type='Submit' name='secuserlist' value='" . _('Export') . "'></CENTER>";
	echo "</FORM></CENTER><BR>";
	

	include('includes/footer.inc');
}
?>