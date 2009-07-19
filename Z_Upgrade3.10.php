<?php
$PageSecurity = 15;
include('includes/session.inc');
$title = _('Upgrade webERP to version 3.10.5');
include('includes/header.inc');


prnMsg(_('This script will perform any modifications to the database since v 3.10 required to allow the additional functionality in version 3.10 scripts'),'info');

if (!isset($_POST['DoUpgrade'])) {
    echo "<br><form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";
    echo '<div class="centre"><input type=submit name=DoUpgrade value="' . _('Perform Upgrade') . '"></div>';
    echo '</form';
}

if ($_POST['DoUpgrade'] == _('Perform Upgrade')){
    echo '<table><tr><td>' . _('Inserting default Debtor type') . '</td>';
    $sql='SELECT count(typeid)
            FROM debtortype
            WHERE typeid=1';
    $result=DB_query($sql, $db);
    $myrow=DB_fetch_array($result);
    if ($myrow[0]==0) {
        $sql='INSERT INTO `debtortype` ( `typeid` , `typename` ) VALUES (1, "Default")';
        $result=DB_query($sql, $db);
        if (DB_error_no($db)==0) {
            echo '<td>' . _('Success') . '</td></tr>';
        } else {
            echo '<td>' . _('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . _('Success') . '</td></tr>';
    }
    echo '<tr><td>' . _('Inserting default Factor company') . '</td>';
    $sql='SELECT count(id)
            FROM factorcompanies
            WHERE coyname="None"';
    $result=DB_query($sql, $db);
    $myrow=DB_fetch_array($result);
    if ($myrow[0]==0) {
        $sql='INSERT INTO `factorcompanies` ( `id` , `coyname` ) VALUES (null, "None")';
        $result=DB_query($sql, $db);
        if (DB_error_no($db)==0) {
            echo '<td>' . _('Success') . '</td></tr>';
        } else {
            echo '<td>' . _('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . _('Success') . '</td></tr>';
    }
    echo '<tr><td>' . _('Adding quotedate to salesorders table') . '</td>';
    $sql='DESCRIBE `salesorders` `quotedate`';
    $result=DB_query($sql, $db);
    if (DB_num_rows($result)==0) {
        $sql='ALTER TABLE `salesorders` ADD `quotedate` date NOT NULL default "0000-00-00"';
        $result=DB_query($sql, $db);
        if (DB_error_no($db)==0) {
            echo '<td>' . _('Success') . '</td></tr>';
        } else {
            echo '<td>' . _('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . _('Success') . '</td></tr>';
    }
    echo '<tr><td>' . _('Adding confirmeddate to salesorders table') . '</td>';
    $sql='DESCRIBE `salesorders` `confirmeddate`';
    $result=DB_query($sql, $db);
    if (DB_num_rows($result)==0) {
        $sql='ALTER TABLE `salesorders` ADD `confirmeddate` date NOT NULL default "0000-00-00"';
        $result=DB_query($sql, $db);
        if (DB_error_no($db)==0) {
            echo '<td>' . _('Success') . '</td></tr>';
        } else {
            echo '<td>' . _('Failed') . '</td></tr>';
        }
    } else {
        echo '<td>' . _('Success') . '</td></tr>';
    }
    echo '</table>';
}

include('includes/footer.inc');
?>
