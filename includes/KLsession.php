<?php
 /***************************************************************************************************
 *
 * KL RICARD: Includes functions related to session or type of webERP used
 *
 ***************************************************************************************************/

function KLDatabaseSelection(){
	// KL RICARD Select the default database depending on the code version
	if (strpos(strtoupper($_SERVER['HTTP_HOST']),"LOCAL-TEST")!== false){
		// the current script filename resides in the WAMPP localhost, we are on TEST code
		$DefaultDatabase = 'test_erp';
	} else {
		// the current script filename resides in the production server
		if (strpos(strtoupper($_SERVER['PHP_SELF']),"TEST")!== false){
			// the current script filename contains TEST, we are on TEST code
			$DefaultDatabase = 'test_erp';
		}else{
			// the current script filename does not contain TEST, we are on production code
			$DefaultDatabase = 'kurakura_kl_erp';
		}
	}
	return $DefaultDatabase;	
}

function KLThemeSelection(){
	if (strpos(strtoupper($_SERVER['HTTP_HOST']),"LOCAL-TEST")!== false){
		// the current script filename resides in the WAMPP localhost, we are on TEST code
		// loalhost development environment must go with the test DB (safest)
		$Theme = 'silverwolf'; 
	} else {
		if (strpos(strtoupper($_SERVER['HTTP_HOST']),"DEVELOPMENT")!== false){
			// we are on ptadu-development.com (development code)
			if (strpos(strtoupper($_SERVER['PHP_SELF']),"TEST")!== false){
				// development environment with the test DB (safe)
				$Theme = 'xenos'; 
			}else{
				// development environment with the production DB (risky)
				$Theme = 'professional'; 
			}
		} else {
			// we are on ptadu.com (production code)
			if (strpos(strtoupper($_SERVER['PHP_SELF']),"TEST")!== false){
				// Training staff environment: we are on production code with the test DB 
				$Theme = 'gel'; 
			}else{
				// Production environment: we are on production code with the real production DB 
				$Theme = 'aguapop'; 
			}
		}
		
	}
	return $Theme;
}

function KLwebERPScriptCalledFromTEST() {
    return (strpos(strtoupper($_SERVER['HTTP_HOST']), "LOCAL-TEST") !== false) 
        || (strpos(strtoupper($_SERVER['PHP_SELF']), "TEST") !== false);
}

?>