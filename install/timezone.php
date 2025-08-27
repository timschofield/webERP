<?php

if (isset($_SESSION['timezone']) && mb_strlen($_SESSION['timezone']) > 0 ) {
    $ltz = $_SESSION['timezone'];
} else {
    $ltz = date_default_timezone_get();
}

/// @todo move to a function in InstallFunctions.php. Also, save timezone.csv as a php array...
//$Row = 1;
$Handle = fopen('timezone.csv', "r");
while (($data = fgetcsv($Handle, 1000, ",")) !== false) {
    $num = count($data);
    //$Row++;
    for ($c=0; $c < $num; $c++) {
		$timezone = $data[$c];
		$c++;
		if ($timezone == $ltz) {
			echo "<option selected='selected' value='".$timezone."'>".$timezone.'</option>';
		} else {
			echo "<option value='".$timezone."'>".$timezone.'</option>';
		}
    }
}
