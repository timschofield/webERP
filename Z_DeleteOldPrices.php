<?php
/* $Id: Z_DeleteOldPrices.php 5296 2012-04-29 15:28:19Z vvs2012 $*/

include ('includes/session.inc');
$Title = _('UTILITY PAGE To Delete All Old Prices');
include('includes/header.inc');

if (isset($_POST['DeleteOldPrices'])){
	$result=DB_query("DELETE FROM prices WHERE enddate<'" . Date('Y-m-d') . "' AND enddate <>'0000-00-00'",$db);
	prnMsg(_('All old prices have been deleted'),'success');
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
	<input type="submit" name="DeleteOldPrices" value="' . _('Purge Old Prices') . '" onclick="return confirm(\'' . _('Are You Sure you wish to delete all old prices?') . '\');" />';

echo '</div>
      </form>';

include('includes/footer.inc');
?>
