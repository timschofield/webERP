<?php

function TypeInInventory($type){
	switch($type){
		case 11:
		case 16:
		case 25:
			return true;
			break;
		default:
			return false;
	}
	return false;
}

function TypeInInventory_InList(){

	return " (11,16,25) ";

}

//Stops WebERP in it's tracks and includes some shutdown code.
function endWEBERP(){
	include("includes/footer.inc");
	exit;
}

//simple function to mark required fields however necessary. Pulls formatting out of pages.
function markRequired($mark="*",$color="red"){

	return "<font color=\"$color\"><b>$mark</b></font>";

}

//inits SESSION variable to avoid Notice messages and give consistent values
function initSvar($name,$default=""){
        if (isset($_SESSION[$name])){
                return $_SESSION[$name];
        } else {
                return $default;
        }
}
//inits POST variable to avoid Notice messages and give consistent values
function initPvar($name,$default=""){
        if (isset($_POST[$name])){
                return $_POST[$name];
        } else {
                return $default;
        }
}
//inits GET variable to avoid Notice messages and give consistent values
function initGvar($name,$default=""){
        if (isset($_GET[$name])){
                return $_GET[$name];
        } else {
                return $default;
        }
}
//inits GET, then POST variable to avoid Notice messages and give consistent values
function initFvar($name,$default=""){
        $val = initGvar($name,$default);
        if ($val == ""){
        	$val = initPvar($name,$default);
        }
	return $val;
}


/********************************************/
/** STANDARD MESSAGE HANDLING & FORMATTING **/
/********************************************/

function prnMsg($msg,$type="info", $prefix=""){

	echo getMsg($msg, $type, $prefix);

}//prnMsg

function getMsg($msg,$type="info",$prefix=""){
	$Colour='';
	switch($type){
		case "error":
			$Colour='red';
			break;
		case "warn":
			$Colour='maroon';
			break;
		case "succ":
			$Colour='#336600';
			break;
		case "info":
		default:
			$Colour='navy';
	}
	return "<font color=" . $Colour . "><b>" . $prefix . "</b> : " .$msg . "</font>";
}//getMsg

