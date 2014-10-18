<?php
/* $Id: timezone.php 5768 2012-12-20 08:38:22Z daintree $*/

if (isset($_SESSION['timezone']) && mb_strlen($_SESSION['timezone']) > 0 ) {
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
	    echo "<option selected='selected' value='".$timezone."'>".$timezone.'</option>';
	} else {
	    echo "<option value='".$timezone."'>".$timezone.'</option>';
	}
    }
}
?>