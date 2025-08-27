<?php

//$PageSecurity = 15;

require(__DIR__ . '/includes/session.php');

$Title = __('Upgrade webERP to version 3.10.5');
include('includes/header.php');

prnMsg(__('This script will perform any modifications to the database since v 3.10 required to allow the additional functionality in version 3.10 scripts'),'info');

if (!isset($_POST['DoUpgrade'])) {
    echo '<br /><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<div class="centre"><input type="submit" name=DoUpgrade value="' . __('Perform Upgrade') . '" /></div>';
    echo '</form>';
}

if ($_POST['DoUpgrade'] == __('Perform Upgrade')){
    echo '<table><tr><td>' . __('Inserting default Debtor type') . '</td>';
    $SQL='SELECT count(typeid)
            FROM debtortype
            WHERE typeid=1';
    $Result = DB_query($SQL);
    $MyRow=DB_fetch_array($Result);
    if ($MyRow[0]==0) {
        $SQL='INSERT INTO `debtortype` ( `typeid` , `typename` ) VALUES (1, "Default")';
        $Result = DB_query($SQL);
        if (DB_error_no()==0) {
            echo '<td>' . __('Success') . '</td></tr>';
        } else {
            echo '<td>' . __('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . __('Success') . '</td></tr>';
    }
    echo '<tr><td>' . __('Inserting default Factor company') . '</td>';
    $SQL="SELECT count(id)
            FROM factorcompanies
            WHERE coyname='None'";
    $Result = DB_query($SQL);
    $MyRow=DB_fetch_array($Result);
    if ($MyRow[0]==0) {
        $SQL='INSERT INTO `factorcompanies` ( `id` , `coyname` ) VALUES (null, "None")';
        $Result = DB_query($SQL);
        if (DB_error_no()==0) {
            echo '<td>' . __('Success') . '</td></tr>';
        } else {
            echo '<td>' . __('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . __('Success') . '</td></tr>';
    }
    echo '<tr><td>' . __('Adding quotedate to salesorders table') . '</td>';
    $SQL='DESCRIBE `salesorders` `quotedate`';
    $Result = DB_query($SQL);
    if (DB_num_rows($Result)==0) {
        $SQL='ALTER TABLE `salesorders` ADD `quotedate` date NOT NULL default "0000-00-00"';
        $Result = DB_query($SQL);
        if (DB_error_no()==0) {
            echo '<td>' . __('Success') . '</td></tr>';
        } else {
            echo '<td>' . __('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . __('Success') . '</td></tr>';
    }
    echo '<tr><td>' . __('Adding confirmeddate to salesorders table') . '</td>';
    $SQL='DESCRIBE `salesorders` `confirmeddate`';
    $Result = DB_query($SQL);
    if (DB_num_rows($Result)==0) {
        $SQL="ALTER TABLE `salesorders` ADD `confirmeddate` date NOT NULL default '0000-00-00'";
        $Result = DB_query($SQL);
        if (DB_error_no()==0) {
            echo '<td>' . __('Success') . '</td></tr>';
        } else {
            echo '<td>' . __('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . __('Success') . '</td></tr>';
    }
    echo '</table>';
}

include('includes/footer.php');
