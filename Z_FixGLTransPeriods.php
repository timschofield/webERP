<?php

include('includes/session.php');
$Title = __('Fix General Ledger Transaction Periods');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Fix GL Trans Periods') . '" /></p>';


$GLTrans = DB_query("SELECT counterindex,trandate,periodno FROM gltrans");
if (DB_num_rows($GLTrans)>0){
	while ($GLTransRow = DB_fetch_array($GLTrans)){
		if ($GLTransRow['periodno']==0) {
			$Period = GetPeriod($GLTransRow['trandate']);
			if ($Period!=0) {
				$UpdateGLTran = DB_query("UPDATE gltrans SET periodno='" . $Period . "' WHERE counterindex='" . $GLTransRow['counterindex'] . "'");
			}
		}
	}
}

include('includes/footer.php');
