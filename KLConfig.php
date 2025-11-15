<?php
/******************************************************************************
*
* KL RICARD: Specific configuration for PTADU installations
*			- Runs after config.php to overwrite the variables with the proper variables needed for any scenario
*
*******************************************************************************/
/* KL RICARD Configuration file for specific KL code */
$KLCodeVersion = "053";

// let's setup all the variables depending on the environment
if (URLWithoutScriptNameContains(".LOCAL")) {
	// the current script filename resides in the WAMPP localhost, we are on TEST code
	// localhost development environment must go with the remote test DB (safest) in Exabytes
	$webERPType = 'TEST';
	$ErrorReportingType = 'DEBUGGING'; 
	$Theme = 'silverwolf';
	$Host = '202.157.184.151';
	$OpenCartDBHost = '202.157.184.151';
	$ArchiveDBHost = '202.157.184.151';
	$SessionSavePath = '';
	$SessionName = 'PHPSESSIDwebERPLocal';
} else {
	// we are in the hosted environment in Exabytes, the DB is local to the code
	$Host = 'localhost';
	$OpenCartDBHost = 'localhost';
	$ArchiveDBHost = 'localhost';
	if (URLWithoutScriptNameContains("DEVELOPMENT")) {
		// we are on ptadu-development.com (development code)
		if (URLWithoutScriptNameContains("/TEST")) {
			// development environment with the test DB (safe)
			$webERPType = 'TEST';
			$ErrorReportingType = 'DEBUGGING';
			$Theme = 'xenos'; 
			$SessionSavePath = '/var/www/vhosts/kapal-laut.com/.sessions_weberp/ptadu-development.com/TEST/';
			$SessionName = 'PHPSESSIDwebERPTest';
		} else {
			// development environment with the production DB (risky)
			$webERPType = 'PRODUCTION';
			$ErrorReportingType = 'DEVELOPMENT';
			$Theme = 'professional'; 
			$SessionSavePath = '/var/www/vhosts/kapal-laut.com/.sessions_weberp/ptadu-development.com/';
			$SessionName = 'PHPSESSIDwebERPProduction';
		}
	} else {
		// we are on ptadu.com (production code)
		if (URLWithoutScriptNameContains("/TEST")) {
			// Training staff environment: we are on production code with the test DB 
			$webERPType = 'TEST';
			$ErrorReportingType = 'DEVELOPMENT';
			$Theme = 'gel'; 
			$SessionSavePath = '/var/www/vhosts/kapal-laut.com/.sessions_weberp/ptadu.com/TEST/';
			$SessionName = 'PHPSESSIDwebERPTest';
		} else {
			// Production environment: we are on production code with the real production DB
			$webERPType = 'PRODUCTION';
			$ErrorReportingType = 'PRODUCTION';
			$Theme = 'aguapop'; 
			$SessionSavePath = '/var/www/vhosts/kapal-laut.com/.sessions_weberp/ptadu.com/';
			$SessionName = 'PHPSESSIDwebERPProduction';
		}
	}
}

/* 
 * DB Selection depending on the type $webERPType (production DB or test DB)
 */
if ($webERPType == 'PRODUCTION') {
	// use the production DB
	$DBUser = 'DBU_kl_erp';
	$DBPassword = 'KXGrwKrlKduQTSdqnLZc';
	$DefaultDatabase = 'kl_erp';

	// use the production company folder
	$DefaultCompany = 'kl_erp';
	
	// use the production Opencart DB
	$OpenCartDBUser = 'DBU_kl_online_shop';
	$OpenCartDBPassword = '2e549bf390a028a9fRR55.2afd';
	$OpenCartDBName = 'kl_online_shop';
	
	// Use the production archive DB
	$ArchiveDBUser = 'DBU_kl_erp_archive';
	$ArchiveDBPassword = '60af008cdf563c86cab75f66aa4c68ef';
	$ArchiveDBName = 'kl_erp_archive';
} else {
	// use the TEST DB
	$DBUser = 'DBU_test_erp';
	$DBPassword = 'JKhxyAfJvkrr2nm0xrXJ';
	$DefaultDatabase = 'test_erp';
	
	// use the TEST company folder
	$DefaultCompany  = 'test_erp';
	
	// use the TEST Opencart DB
	$OpenCartDBUser = 'DBU_test_online_shop';
	$OpenCartDBPassword = 'V@3,hlAhPTF.yr\o=iz?xF5Q:';
	$OpenCartDBName = 'test_online_shop';
	
	//Use the TEST archive DB
	$ArchiveDBUser = 'DBU_test_erp_archive';
	$ArchiveDBPassword = '7187cd531a6f94ad56b0aad';
	$ArchiveDBName = 'test_erp_archive';
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
	// error_reporting (-1);
	// error_reporting (E_ALL);
	// error_reporting (E_ALL & ~E_NOTICE);
	error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING);
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
