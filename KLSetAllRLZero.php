<?php

include('includes/session.php');
$Title = __('KL Set Reorder Level zero for a location');// Screen identificator.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/maintenance.png" title="',// Icon image.
	$Title, '" /> ',// Icon title.
	$Title, '</p>';// Page title.

include('includes/SQL_CommonFunctions.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

if (!isset($_POST['FromLocationID'])){
	$_POST['FromLocationID'] = '';
}

if(isset($_POST['ProcessReorderLevelZero'])) {

	$InputError =0;

	if($InputError ==0) {// no input errors
		DB_Txn_Begin();

		$SQL = "UPDATE locstock SET reorderlevel = 0 WHERE loccode = '" . $_POST['FromLocationID'] . "'";
		$ErrMsg =__('The SQL to set RL = 0 at location failed');
		$Result = DB_query($SQL, $ErrMsg, '', true);
		prnMsg(__('Setting ZERO to all Reorder Levels of location ') . ' ' .  $_POST['FromLocationID'] . ' ' . __('completed'),'success');
		
		DB_Txn_Commit();

	}//only do the stuff above if  $InputError==0
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
        <legend>' . __('Location Selection') . '</legend>';
echo FieldToSelectOneLocation('FromLocationID', $_POST['FromLocationID'], __('Select Location to set Reorder Levels to ZERO'), '', '', '', true, false);
echo '</fieldset>';

echo OneButtonCenteredForm('ProcessReorderLevelZero', __('Set Reorder Levels Zero'));

echo '</div>
      </form>';

include('includes/footer.php');
