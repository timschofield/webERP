<?php
/* $Id: api_taxgroups.php 6941 2014-10-26 23:18:08Z daintree $*/

/* This function returns a list of the tax group id's
 * currently setup on webERP
 */

	function GetTaxGroupList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT taxgroupid FROM taxgroups';
		$result = DB_query($sql);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$TaxgroupList[$i]=$myrow[0];
			$i++;
		}
		return $TaxgroupList;
	}

/* This function takes as a parameter a tax group id
 * and returns an array containing the details of the selected
 * tax group.
 */

	function GetTaxGroupDetails($taxgroup, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "SELECT * FROM taxgroups WHERE taxgroupid='".$taxgroup."'";
		$result = DB_query($sql);
		return DB_fetch_array($result);
	}

	/* This function takes as a parameter a tax group id
 * and returns an array containing the taxes in the selected
 * tax group.
 */

	function GetTaxGroupTaxes($TaxGroup, $User, $Password) {
		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "SELECT taxgroupid, taxauthid, calculationorder, taxontax FROM taxgrouptaxes WHERE taxgroupid='".$TaxGroup."'";
		$result = DB_query($sql);
		$i=0;
		$Answer = array();
		while ($myrow=DB_fetch_array($result)){
			$Answer[$i]['taxgroupid'] = $myrow['taxgroupid'];
			$Answer[$i]['taxauthid'] = $myrow['taxauthid'];
			$Answer[$i]['calculationorder'] = $myrow['calculationorder'];
			$Answer[$i]['taxontax'] = $myrow['taxontax'];
			$i++;
		}
		$Errors[0]=0;
		$Errors[1]=$Answer;
		return $Errors;
	}

/* This function returns a list of the tax authority ids
 * currently setup on webERP
 */
    function GetTaxAuthorityList($User, $Password) {
		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT taxid FROM taxauthorities';
		$result = DB_query($sql);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$TaxAuthList[$i]=$myrow[0];
			$i++;
		}
		return $TaxAuthList;
	}

/* This function takes as a parameter a tax authority id
 * and returns an array containing the details of the selected
 * tax authority.
 */

	function GetTaxAuthorityDetails($TaxAuthority, $User, $Password) {
		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "SELECT * FROM taxauthorities WHERE taxid='".$TaxAuthority."'";
		$result = DB_query($sql);
		return DB_fetch_array($result);
	}

/* This function takes as a parameter a tax authority id and a tax category id
 * and returns an array containing the rate of tax for the selected
 * tax authority and tax category
 */

    function GetTaxAuthorityRates($TaxAuthority, $User, $Password) {
		$Errors = array();
		$db = db($User, $Password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = "SELECT taxcatid, dispatchtaxprovince, taxrate FROM taxauthrates WHERE taxauthority='".$TaxAuthority."'";
		$result = DB_query($sql);
		$i=0;
		$Answer = array();
		while ($myrow=DB_fetch_array($result)){
			$Answer[$i]['taxcatid'] = $myrow['taxcatid'];
			$Answer[$i]['dispatchtaxprovince'] = $myrow['dispatchtaxprovince'];
			$Answer[$i]['taxrate'] = $myrow['taxrate'];
			$i++;
		}
		$Errors[0]=0;
		$Errors[1]=$Answer;
		return $Errors;
	}

?>