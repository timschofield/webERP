<?php
/* $Id$*/

if (isset($_SESSION['timezone']) && strlen($_SESSION['timezone']) > 0 ) {
    $ltz = $_SESSION['timezone'];
} else {
    $ltz = date_default_timezone_get();
}

$row = 1;
$handle = fopen('timezone.csv', "r");
while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);	 
    $row++;
    for ($c=0; $c < $num; $c++) {
	$timezone=$data[$c];
	$c++;
	if ($timezone==$ltz) {
	    echo "<OPTION selected value='".$timezone."'>".$timezone;
	} else {
	    echo "<OPTION value='".$timezone."'>".$timezone;
	}
    }
}
?>