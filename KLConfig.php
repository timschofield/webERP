<?php
/******************************************************************************
*
* KL RICARD: Specific configuration for PTADU installations
*			- Runs after config.php to overwrite the variables with the proper variables needed for any scenario
*
*******************************************************************************/
/* KL RICARD Configuration file for specific KL code */
$KLCodeVersion = "068";

// KL RICARD look for the secret values of sensitive variables and credentials $PTADU...
include('KLConfig/KLCredentials.php');
// END KL RICARD look for the secret values of sensitive variables and credentials

// The real path to the symlinked part_pics directory, to prevent DomPDF from being unable to access images
// $SymlinkImageDir = ''; // if no symlink is used.
// because symlink is used.
$SymlinkImageDir = $PTADUSymlinkImageDir;

// let's setup all the variables depending on the environment
if (URLWithoutScriptNameContains(".LOCAL")) {
	// the current script filename resides in the WAMPP localhost, we are on TEST code
	// localhost development environment must go with the remote test DB (safest) in Exabytes
	$webERPType = 'TEST';
	$ErrorReportingType = 'DEBUGGING'; 
	$Theme = 'silverwolf';
	$Host = $PTADURemoteHostIP;
	$OpenCartDBHost = $PTADURemoteHostIP;
	$ArchiveDBHost = $PTADURemoteHostIP;
	$SessionSavePath = '';
	$SessionName = 'PHPSESSIDwebERPLocal';
} else {
	// we are in the hosted environment in Exabytes, the DB is local to the code
	$Host = $PTADULocalHostIP;
	$OpenCartDBHost = $PTADULocalHostIP;
	$ArchiveDBHost = $PTADULocalHostIP;
	if (URLWithoutScriptNameContains("DEVELOPMENT")) {
		// we are on ptadu-development.com (development code)
		if (URLWithoutScriptNameContains("/TEST")) {
			// development environment with the test DB (safe)
			$webERPType = 'TEST';
			$ErrorReportingType = 'DEBUGGING';
			$Theme = 'xenos'; 
			$SessionSavePath = $PTADUTestDBDevelopmentCodeSessionSavePath;
			$SessionName = $PTADUTestDBSessionName;
		} else {
			// development environment with the production DB (risky)
			$webERPType = 'PRODUCTION';
			$ErrorReportingType = 'DEVELOPMENT';
			$Theme = 'professional'; 
			$SessionSavePath = $PTADUProductionDBDevelopmentCodeSessionSavePath;
			$SessionName = $PTADUProductionDBSessionName;
		}
	} else {
		// we are on ptadu.com (production code)
		if (URLWithoutScriptNameContains("/TEST")) {
			// Training staff environment: we are on production code with the test DB 
			$webERPType = 'TEST';
			$ErrorReportingType = 'DEVELOPMENT';
			$Theme = 'gel'; 
			$SessionSavePath = $PTADUTestDBProductionCodeSessionSavePath;
			$SessionName = $PTADUTestDBSessionName;
		} else {
			// Production environment: we are on production code with the real production DB
			$webERPType = 'PRODUCTION';
			$ErrorReportingType = 'PRODUCTION';
			$Theme = 'aguapop'; 
			$SessionSavePath = $PTADUProductionDBProductionCodeSessionSavePath;
			$SessionName = $PTADUProductionDBSessionName;
		}
	}
}

/* 
 * DB Selection depending on the type $webERPType (production DB or test DB)
 */
if ($webERPType == 'PRODUCTION') {
	// use the production DB
	$DefaultDatabase = $PTADUProductionERPDBName;
	$DBUser = $PTADUProductionERPDBUser;
	$DBPassword = $PTADUProductionERPDBPassword;

	// use the production company folder
	$DefaultCompany = $PTADUProductionERPDBName;
	
	// use the production Opencart DB
	$OpenCartDBName = $PTADUProductionOpenCartDBName;
	$OpenCartDBUser = $PTADUProductionOpenCartDBUser;
	$OpenCartDBPassword = $PTADUProductionOpenCartDBPassword;
	
	// Use the production archive DB
	$ArchiveDBName = $PTADUProductionArchiveDBName;
	$ArchiveDBUser = $PTADUProductionArchiveDBUser;
	$ArchiveDBPassword = $PTADUProductionArchiveDBPassword;
} else {
	// use the TEST DB
	$DefaultDatabase = $PTADUTestERPDBName;
	$DBUser = $PTADUTestERPDBUser;
	$DBPassword = $PTADUTestERPDBPassword;
	
	// use the TEST company folder
	$DefaultCompany  = $PTADUTestERPDBName;
	
	// use the TEST Opencart DB
	$OpenCartDBName = $PTADUTestOpenCartDBName;
	$OpenCartDBUser = $PTADUTestOpenCartDBUser;
	$OpenCartDBPassword = $PTADUTestOpenCartDBPassword;
	
	//Use the TEST archive DB
	$ArchiveDBName = $PTADUTestArchiveDBName;
	$ArchiveDBUser = $PTADUTestArchiveDBUser;
	$ArchiveDBPassword = $PTADUTestArchiveDBPassword;
}

/* 
 * Error and Debugging reporting selection depending on the environment
 */
if ($ErrorReportingType == 'PRODUCTION') {
	// report only errors
	// error_reporting (-1);
	// error_reporting (E_ALL);
	// error_reporting (E_ALL & ~E_NOTICE);
	// error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING);
	error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
	
	// Disable debug
	$Debug = 0;

} elseif ($ErrorReportingType == 'DEVELOPMENT') {
	// report some errors
	error_reporting (-1);
	// error_reporting (E_ALL);
	// error_reporting (E_ALL & ~E_NOTICE);
	// error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING);
	// error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

	// Enable debug (1 = simple, 2= more detailed)
	$Debug = 2;

} elseif ($ErrorReportingType == 'DEBUGGING') {
	// report everything, or almost
	error_reporting (-1);
	// error_reporting (E_ALL);
	// error_reporting (E_ALL & ~E_NOTICE);
	// error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING);
	// error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

	// Enable debug (1 = simple, 2= more detailed)
	$Debug = 2;
}
