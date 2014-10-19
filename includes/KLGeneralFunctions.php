<?php

/**************************************************************************************************
			GENERAL KL FUNCTIONS
**************************************************************************************************/

function ListToArray($List, $Separator){
	$CleanUp = array("(", ")", "'");
	return explode($Separator, str_replace($CleanUp, "", $List));
}

function time_start(){
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$begintime = $time;
	return $begintime;
}

function time_finish($begintime){
	$time = microtime();
	$time = explode(" ", $time);
	$time = $time[1] + $time[0];
	$endtime = $time;
	$totaltime = ($endtime - $begintime);
	prnMsg('Script execution time: ' . locale_number_format($totaltime,3) . ' seconds.','success');
}

function CodeModel($stockid){
	return (substr($stockid, 0,6));
}

function isBead($stockid){
	return (substr($stockid, 2,2) == "BE");
}

function isRing($stockid){
	return (substr($stockid, 2,2) == "AN");
}

function isToeRing($stockid){
	return (substr($stockid, 2,2) == "TR");
}

function isBracelet($stockid){
	return ((substr($stockid, 2,2) == "PU") OR (substr($stockid, 2,2) == "BR"));
}

function isAnklet($stockid){
	return (substr($stockid, 2,2) == "AK");
}

function isPendant($stockid){
	return (substr($stockid, 2,2) == "PE");
}

function isNecklace($stockid){
	return ((substr($stockid, 2,2) == "NE") OR (substr($stockid, 0,4) == "ALCL"));
}

function isEarring($stockid){
	return (substr($stockid, 2,2) == "AR");
}

function isPlasticBag($stockid){
	return (substr($stockid, 0,4) == "BAPL");
}

function isTali($stockid){
	return ((substr($stockid, 0,3) == "TM-") 
		OR (substr($stockid, 0,4) == "TA15"));
}


function isFamily($stockid, $Family){
	return (substr($stockid, 0,2) == $Family);
}

function CodeModelRing($stockid){
	if (strlen($stockid) == 6){
		$CodeModel = $stockid;
	}else{
		if((substr($stockid, -2,1) == "0") 
			OR (substr($stockid, -2,1) == "1")
			OR (substr($stockid, -2,1) == "2")){
			// ring with sizes! We need to cut the 3 last characters -XX
			$CodeModel = (substr($stockid, 0,strlen($stockid)-3));
		}else{
			$CodeModel = $stockid;
		}
	}
	return $CodeModel;
}

function locale_number_format_zero_blank($num,$dec){
	if($num == 0){
		return '';
	}else{
		return locale_number_format($num,$dec);
	}
}

function StartEvenOrOddRow($k){
	if ($k == 1) {
		echo '<tr class="EvenTableRows">';
		$k = 0;
	} else {
		echo '<tr class="OddTableRows">';
		$k = 1;
	}
	return $k;
}

function getDirectoryTree( $outerDir , $x){ 
    $dirs = array_diff( scandir( $outerDir ), Array( ".", ".." ) ); 
    return $dirs; 
} 

function ItemInList($Item, $List){
	// http://www.php.net/manual/en/function.strpos.php for details on ===	
	if (strpos($List, $Item) === FALSE){
		return false;
	}else{
		return true;
	}
}

?>
