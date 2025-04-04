<?php
/******************************************************************************
*
* KL RICARD: Specific configuration for PTADU installations
*			- Runs after config.php to overwrite the variables with the proper variables needed for any scenario
*
*******************************************************************************/
/* KL RICARD Configuration file for specific KL code */
$KLCodeVersion = "023";

// let's setup all the variables depending on the environment
if (URLWithoutScriptNameContains("LOCAL-TEST")){
	// the current script filename resides in the WAMPP localhost, we are on TEST code
	// localhost development environment must go with the remote test DB (safest) in Exabytes
	$webERPType = 'TEST';
	$ErrorReportingType = 'DEBUGGING'; 
	$Theme = 'silverwolf';
	$Host = '202.157.184.151';
	$OpenCartDBHost = '202.157.184.151';
	$OldDataDBHost = '202.157.184.151';
	$SessionSavePath = '';
} else {
	// we are in the hosted environment in Exabytes, the DB is local to the code
	$Host = 'localhost';
	$OpenCartDBHost = 'localhost';
	$OldDataDBHost = 'localhost';
	if (URLWithoutScriptNameContains("DEVELOPMENT")){
		// we are on ptadu-development.com (development code)
		if (URLWithoutScriptNameContains("TEST")){
			// development environment with the test DB (safe)
			$webERPType = 'TEST';
			$ErrorReportingType = 'DEBUGGING';
			$Theme = 'xenos'; 
			$SessionSavePath = '/var/www/vhosts/kapal-laut.com/.sessions_weberp/ptadu-development.com/TEST/';
		}else{
			// development environment with the production DB (risky)
			$webERPType = 'PRODUCTION';
			$ErrorReportingType = 'DEVELOPMENT';
			$Theme = 'professional'; 
			$SessionSavePath = '/var/www/vhosts/kapal-laut.com/.sessions_weberp/ptadu-development.com/';
		}
	} else {
		// we are on ptadu.com (production code)
		if (URLWithoutScriptNameContains("TEST")){
			// Training staff environment: we are on production code with the test DB 
			$webERPType = 'TEST';
			$ErrorReportingType = 'DEVELOPMENT';
			$Theme = 'gel'; 
			$SessionSavePath = '/var/www/vhosts/kapal-laut.com/.sessions_weberp/ptadu.com/TEST/';
		}else{
			// Production environment: we are on production code with the real production DB 
			$webERPType = 'PRODUCTION';
			$ErrorReportingType = 'PRODUCTION';
			$Theme = 'aguapop'; 
			$SessionSavePath = '/var/www/vhosts/kapal-laut.com/.sessions_weberp/ptadu.com/';
		}
	}
}

if ($webERPType == 'PRODUCTION'){
	// use the production DB
	$DBUser = 'kurakura_kl_0001';
	$DBPassword = 'KXGrwKrlKduQTSdqnLZc';
	$DefaultDatabase = 'kurakura_kl_erp';

	// use the production company folder
	$DefaultCompany = 'kurakura_kl_erp';
	$CompanyList[0] = array('database'=>'kurakura_kl_erp' ,'company'=>'Kapal-Laut' );
	
	// use the production Opencart DB
	$OpenCartDBUser = 'DBU_kl_shop';
	$OpenCartDBPassword = '2e549bf390a028a9fRR55.2afd';
	$OpenCartDBName = 'kl_online_shop';
	
	//Use the production old data DB
	$OldDataDBUser = 'kurakura_kl_0002';
	$OldDataDBPassword = '60af008cdf563c86cab75f66aa4c68ef';
	$OldDataDBName = 'kurakura_kl_erpolddata';

}else{
	// use the TEST DB
	$DBUser = 'DBU_ptadu_test';
	$DBPassword = 'LTq%w@.KkJcZ$@!^HBz';
	$DefaultDatabase = 'test_erp';
	
	// use the TEST company folder
	$DefaultCompany  = 'test_erp';
	$CompanyList[0] = array('database'=>'test_erp' ,'company'=>'Kapal-Laut TEST' );
	
	// use the TEST Opencart DB
	$OpenCartDBUser = 'DBU_ptadu_test';
	$OpenCartDBPassword = 'LTq%w@.KkJcZ$@!^HBz';
	$OpenCartDBName = 'test_online_shop';
	
	//Use the TEST old data DB
	$OldDataDBUser = 'kurakura_kl_0006';
	$OldDataDBPassword = '7187cd531a6f94ad56b0aad';
	$OldDataDBName = 'kurakura_kl_test_erpolddata';
}

if ($ErrorReportingType == 'PRODUCTION'){
	// reportonly errors
	// error_reporting (-1);
	// error_reporting (E_ALL);
	// error_reporting (E_ALL & ~E_NOTICE);
	// error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING);
	error_reporting (E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
}elseif ($ErrorReportingType == 'DEVELOPMENT'){
	// reportonly errors
	// error_reporting (-1);
	// error_reporting (E_ALL);
	// error_reporting (E_ALL & ~E_NOTICE);
	error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING);
	// error_reporting (E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING & ~E_DEPRECATED);
}elseif ($ErrorReportingType == 'DEBUGGING'){
	// report everything, or almost
	error_reporting (-1);
	// error_reporting (E_ALL);
	// error_reporting (E_ALL & ~E_NOTICE);
	// error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING);
	// error_reporting (E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
}

?>