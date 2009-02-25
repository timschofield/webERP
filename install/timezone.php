<?php

function stripcomma($str) { //because we're using comma as a delimiter
    $str = trim($str);
    $str = str_replace('"', '""', $str);
    $str = str_replace("\r", "", $str);
    $str = str_replace("\n", '\n', $str);
    if($str == "" )
        return $str;
    else
        return '"'.$str.'"';
}

function NULLToZero( &$Field ) {
    if( is_null($Field) )
        return '0';
    else
        return $Field;
}

function NULLToPrice( &$Field ) {
    if( is_null($Field) )
        return '-1';
    else
        return $Field;
}

$row = 1;
$handle = fopen('timezone.csv', "r");
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);	 
    $row++;
		for ($c=0; $c < $num; $c++) {
			$timezone=$data[$c];
			$c++;
            if ($timezone==date_default_timezone_get()) {
                echo "<OPTION selected value='".$timezone."'>".$timezone;
            } else {
                echo "<OPTION value='".$timezone."'>".$timezone;
            }
	}
}
?>