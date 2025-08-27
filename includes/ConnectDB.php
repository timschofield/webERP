<?php

require_once($PathPrefix .'includes/MiscFunctions.php');

if (!isset($_SESSION['DatabaseName'])) { //need to get the database name from the file structure
	if (isset($_POST['CompanyNameField'])) {
		if (ContainsIllegalCharacters($_POST['CompanyNameField'])) {
			prnMsg(__('The company database being logged into cannot contain any of the illegal characters'), 'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to login page') . '</a>';
			exit();
		}
		if (is_dir('companies/' . $_POST['CompanyNameField']) and $_POST['CompanyNameField'] != '..') {
			$_SESSION['DatabaseName'] = $_POST['CompanyNameField'];
			include_once($PathPrefix . 'includes/ConnectDB_' . $DBType . '.php');
		} else {
			prnMsg(__('The company name entered' . ' (' . $_POST['CompanyNameField'] . ') ' . 'is not configured for use with this installation of KwaMoja. Check that a directory named ' . $_POST['CompanyNameField'] . ' is set up under the companies sub-directory.'), 'error');
			prnMsg(__('Check the company name entered' . ' (' . $_POST['CompanyNameField'] . ') ' . 'is the same as the database name.'), 'error');
			prnMsg(__('The company name abbreviation entered at login must also have a company directory defined. See your system administrator'), 'error');
		}
	} elseif (isset($DatabaseName)) {
		/* Scripts that do not require a login must have the $DatabaseName variable set in hard code */
		$_SESSION['DatabaseName'] = $DatabaseName;
		include_once($PathPrefix . 'includes/ConnectDB_' . $DBType . '.php');
	}
	/// @todo handle this situation - error out?
} else {
	include_once($PathPrefix . 'includes/ConnectDB_' . $DBType . '.php');
}
