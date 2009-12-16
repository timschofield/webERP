<?php

/* $Id$*/
$PageSecurity = 11;

include('includes/session.inc');
$title = _('Fixed Asset Locations');
include('includes/header.inc');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' .
	 _('Search') . '" alt="">' . ' ' . $title;

if (isset($_POST['submit'])) {
	$InputError=0;
	if (!isset($_POST['locationid']) or strlen($_POST['locationid'])<1) {
		prnMsg(_('You must enter at least one character in the location ID'),'error');
		$InputError=1;
	}
	if (!isset($_POST['locdesc']) or strlen($_POST['locdesc'])<1) {
		prnMsg(_('You must enter at least one character in the location description'),'error');
		$InputError=1;
	}
	if ($InputError==0) {
		$sql='INSERT INTO fixedassetlocations
			VALUES (
				"'.$_POST['locationid'].'",
				"'.$_POST['locdesc'].'",
				"'.$_POST['parentlocationid'].'")';
		$result=DB_query($sql, $db);
	}
}
if (isset($_GET['SelectedLocation'])) {
	$sql='SELECT * FROM fixedassetlocations WHERE locationid="'.$_GET['SelectedLocation'].'"';
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	$locationid=$myrow['locationid'];
	$locdesc=$myrow['locationdescription'];
	$parentlocationid=$myrow['parentlocationid'];

} else {
	$locationid='';
	$locdesc='';	
}
//Batman: Attempting to update fields 

if (isset($_POST['update'])) {
        $InputError=0;
        /*Batman: Removing the ID
	if (!isset($_POST['locationid']) or strlen($_POST['locationid'])<1) {
                prnMsg(_('You must enter at least one character in the location ID'),'error');
                $InputError=1;
        }*/
        if (!isset($_POST['locdesc']) or strlen($_POST['locdesc'])<1) {
                prnMsg(_('You must enter at least one character in the location description'),'error');
                $InputError=1;
        }
        if ($InputError==0) {
                		 $sql='UPDATE fixedassetlocations SET 
								locationdescription="'.$_POST['locdesc'].'", 
								parentlocationid="'.$_POST['parentlocationid'].'"
                                          		  WHERE locationid         ="'.$_POST['locationid'].'"';
		                 $result=DB_query($sql,$db);
//Batman: Testing leaking sql echo $sql;
				 echo '<meta http-equiv="Refresh" content="0; url="'.$_SERVER['PHP_SELF'].'">';
				}
} 

$sql='SELECT * FROM fixedassetlocations';
$result=DB_query($sql, $db);

echo '<table><tr>';
echo '<th>'._('Location ID').'</th><th>'._('Location Description').'</th><th>'._('Parent Location').'</th></tr>';

while ($myrow=DB_fetch_array($result)) {
	$parentsql='select locationdescription from fixedassetlocations where locationid="'.$myrow['parentlocationid'].'"';
	$parentresult=DB_query($parentsql, $db);
	$parentrow=DB_fetch_array($parentresult);
	echo '<tr><td>'.$myrow['locationid'].'</td>';
	echo '<td>'.$myrow['locationdescription'].'</td>';
	echo '<td>'.$parentrow['locationdescription'].'</td>';
	echo '<td><a href="'.$_SERVER['PHP_SELF'] . '?' . SID.'SelectedLocation='.$myrow['locationid'].'">' .
		 _('Edit') . '</td>'; //Batman: added '; and duplicated line as below
	echo '<td><a href="'.$_SERVER['PHP_SELF'] . '?' . SID.'SelectedLocation='.$myrow['locationid'].'">' .
                 _('Delete') . '</td></tr>';
//Batman: Just hashed this out</tr>';
}
//Batman: Captureing the location ID before the update process
//echo $loc = "'.$_POST['locationid'].'";

echo '</table><br>';
echo '<form name="LocationForm" method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '"><table>';
echo '<tr><th style="text-align:left">'._('Location ID').'</th>';
if (isset($_GET['SelectedLocation']))
	echo '<td><b><input type=text name=locationid size=6 value="'.$locationid.'"></b></td>';
else
	echo '<td><input type=text name=locationid size=6 value="'.$locationid.'"></td></tr>';

echo '<tr><th style="text-align:left">'._('Location Description').'</th>';
echo '<td><input type=text name=locdesc size=20 value="'.$locdesc.'"></td></tr>';

echo '<tr><th style="text-align:left">'._('Parent Location').'</th>';
echo '<td><select name=parentlocationid>';

$sql='SELECT * FROM fixedassetlocations';
$result=DB_query($sql, $db);

echo '<option value=""></option>';
while ($row=DB_fetch_array($result)) {
	if ($row['locationid']==$parentlocationid) {
		echo '<option selected value="'.$row['locationid'].'">'.$row['locationdescription'].'</option>';
	} else {
		echo '<option value="'.$row['locationid'].'">'.$row['locationdescription'].'</option>';
	}
}
echo '</select>';
//Batman: Collecting all ParentLocations

echo '</td></tr>';
echo '</table><br>';


//Batman: parentlocationid checking the location ID 
//echo $locationid;

echo '<div class="centre">';
if (isset($_GET['SelectedLocation'])) 
  		echo '<input type="Submit" name="update" value="' . _('Update Information') . '">';
else		echo '<input type="submit" name="submit" value="' . _('Enter Information') . '">';
echo '</div>';
echo '</form>';

include('includes/footer.inc');
?>
