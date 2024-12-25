<?php

include ('includes/session.php');
$Title = _('KL Set Reorder Level zero for a location');// Screen identificator.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="',// Icon image.
	$Title, '" /> ',// Icon title.
	$Title, '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');
include('includes/KLGeneralFunctions.php');

if(isset($_POST['ProcessCopyAuthority'])) {

	$InputError =0;

	if($InputError ==0) {// no input errors
		$Result = DB_Txn_Begin();

		echo '<br />' . _('Setting ZERO to all Reorder Levels of location ') . ' ' .  $_POST['FromLocationID'];
		$SQL = "UPDATE locstock SET reorderlevel = 0 WHERE loccode = '" . $_POST['FromLocationID'] . "'";
		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to set RL = 0 at location failed');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');
		
		$Result = DB_Txn_Commit();

	}//only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table>';
echo ' <tr>
		<td>' . _('Select Location to set Reorder Levels to ZERO') . ':</td>
		<td><select name="FromLocationID">';
$Result = DB_query("SELECT loccode,
							locationname
					FROM locations
					ORDER BY locationname");

echo '<option selected value="">' . _('Not Yet Selected') . '</option>';
while ($MyRow = DB_fetch_array($Result)) {
	echo '<option value="';
	echo $MyRow['loccode'] . '">' . $MyRow['loccode'] . ' - ' . $MyRow['locationname'] . '</option>';
} //end while loop
echo '</select></td></tr>';
echo '</table>';
echo '<input type="submit" name="ProcessCopyAuthority" value="' . _('Set Reorder Levels Zero') . '" />
	</div>
	</form>';

include('includes/footer.php');
?>