<?php
/* $Id$*/

if (!isset($PathPrefix)) {
	$PathPrefix='';
}
if (!file_exists($PathPrefix . 'config.php')){
	$rootpath = dirname($_SERVER['PHP_SELF']);
	if ($rootpath == '/' OR $rootpath == "\\") {
		$rootpath = '';
	}
	header('Location:' . $rootpath . '/install/index.php');
}
include($PathPrefix . 'config.php');

if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
}

ini_set('session.gc_Maxlifetime',$SessionLifeTime);
ini_set('max_execution_time',$MaximumExecutionTime);

session_name('webERPapi');
session_start();

include($PathPrefix . 'includes/LanguageSetup.php');
//  Establish a DB connection, if possible.   NOTE that this connection
//  may not have the same 'value' as any previous connection, so
//  save the new one in the session variable.
if (isset($_SESSION['DatabaseName']) AND $_SESSION['DatabaseName'] != '' ) {
	include($PathPrefix . 'includes/ConnectDB.inc');
	$_SESSION['db'] = $db;
}
include($PathPrefix . 'includes/DateFunctions.inc');

// Un comment to turn off attempts counter
//$_SESSION['AttemptsCounter'] = 0;

if (!isset($_SESSION['AttemptsCounter'])){
	$_SESSION['AttemptsCounter'] = 0;
}

/*User is logged in so get configuration parameters  - save in session*/
if (isset($_SESSION['db']) AND $_SESSION['db'] != '' ) {
	//include($PathPrefix . 'includes/GetConfig.php');
}

if(isset($_SESSION['DB_Maintenance'])){
	if ($_SESSION['DB_Maintenance']!=0)  {
		if (DateDiff(Date($_SESSION['DefaultDateFormat']),
				ConvertSQLDate($_SESSION['DB_Maintenance_LastRun'])
				,'d')	> 	$_SESSION['DB_Maintenance']){

			/*Do the DB maintenance routing for the DB_type selected */
			DB_Maintenance($db);
			//purge the audit trail if necessary
			if (isset($_SESSION['MonthsAuditTrail'])){
				 $sql = "DELETE FROM audittrail
						WHERE  transactiondate <= '" . Date('Y-m-d', mktime(0,0,0, Date('m')-$_SESSION['MonthsAuditTrail'])) . "'";
				$ErrMsg = _('There was a problem deleting expired audit-trail history');
				$result = DB_query($sql,$db);
			}
			$_SESSION['DB_Maintenance_LastRun'] = Date('Y-m-d');
		}
	}
}

/*Check to see if currency rates need to be updated */
if (isset($_SESSION['UpdateCurrencyRatesDaily'])){
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0)  {
		if (DateDiff(Date($_SESSION['DefaultDateFormat']),
				ConvertSQLDate($_SESSION['UpdateCurrencyRatesDaily'])
				,'d')> 0){

			$CurrencyRates = GetECBCurrencyRates(); // gets rates from ECB see includes/MiscFunctions.php
			/*Loop around the defined currencies and get the rate from ECB */
			$CurrenciesResult = DB_query('SELECT currabrev FROM currencies',$db);
			while ($CurrencyRow = DB_fetch_row($CurrenciesResult)){
				if ($CurrencyRow[0]!=$_SESSION['CompanyRecord']['currencydefault']){
					$UpdateCurrRateResult = DB_query('UPDATE currencies SET
											rate=' . GetCurrencyRate ($CurrencyRow[0],$CurrencyRates) . "
											WHERE currabrev='" . $CurrencyRow[0] . "'",$db);
				}
			}
			$_SESSION['UpdateCurrencyRatesDaily'] = Date('Y-m-d');
			$UpdateConfigResult = DB_query("UPDATE config SET confvalue = '" . Date('Y-m-d') . "' WHERE confname='UpdateCurrencyRatesDaily'",$db);
		}
	}
}
// include('../includes/GetConfig.php');
if ($_SESSION['HTTPS_Only']==1){
	if ($_SERVER['HTTPS']!='on'){
		prnMsg(_('webERP is configured to allow only secure socket connections. Pages must be called with https://') . ' .....','error');
		exit;
	}
}

// Now check that the user as logged in has access to the page being called. The $PageSecurity
// value must be set in the script before header.inc is included. $SecurityGroups is an array of
// arrays defining access for each group of users. These definitions can be modified by a system admin under setup


if (!is_array($_SESSION['AllowedPageSecurityTokens']) AND !isset($AllowAnyone)) {
/*  NO HTML output - but may need an XMLRPC style error message here.
	Lindsay: 12Jan10
	$title = _('Account Error Report');
	include($PathPrefix . 'includes/header.inc');
	echo '<br /><br /><br />';
	prnMsg(_('Security settings have not been defined for your user account. Please advise your system administrator. It could also be that there is a session problem with your PHP web server'),'error');
	include($PathPrefix . 'includes/footer.inc');
 */
	exit;
}

if (!isset($AllowAnyone)){
	if ((!in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {
/* NO HTML output - but need to return an appropriate error message
	Lindsay: 12Jan10
		$title = _('Security Permissions Problem');
		include($PathPrefix . 'includes/header.inc');
		echo '<tr>
			<td class="menu_group_items">
				<table width="100%" class="table_index">
					<tr><td class="menu_group_item">';
		echo '<b><font style="size:+1; text-align:center;">' . _('The security settings on your account do not permit you to access this function') . '</font></b>';

		echo '</td>
		</tr>
		</table>
		</td>';

		include($PathPrefix . 'includes/footer.inc');
 */
		exit;
	}
 }

function CryptPass( $Password ) {
	global $CryptFunction;
	if ( $CryptFunction == 'sha1' ) {
		return sha1($Password);
	} elseif ( $CryptFunction == 'md5' ) {
		return md5($Password);
	} else {
		return $Password;
	}
 }
?>