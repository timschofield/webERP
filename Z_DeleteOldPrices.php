<?php
/* $Id: Z_DeleteOldPrices.php 5296 2012-04-29 15:28:19Z vvs2012 $*/

include ('includes/session.inc');
$Title = _('UTILITY PAGE To Delete All Old Prices');
include('includes/header.inc');

if (isset($_POST['DeleteOldPrices'])){
	DB_Txn_Begin();
	$result=DB_query("DELETE FROM prices WHERE enddate<'" . Date('Y-m-d') . "' AND enddate <>'0000-00-00'",'','',true);
	$result=DB_query("SELECT stockid,
							typeabbrev,
							currabrev,
							debtorno,
							branchcode,
							MAX(startdate) as lateststart
					FROM prices
					WHERE startdate<='" . Date('Y-m-d') . "'
					AND enddate ='0000-00-00'
					GROUP BY stockid,
							typeabbrev,
							currabrev,
							debtorno,
							branchcode");

	while ($myrow = DB_fetch_array($result)){
		$DelResult = DB_query("DELETE FROM prices WHERE stockid='" . $myrow['stockid'] . "'
													AND debtorno='" . $myrow['debtorno'] . "'
													AND branchcode='" . $myrow['branchcode'] . "'
													AND currabrev='" . $myrow['currabrev'] . "'
													AND typeabbrev='" . $myrow['typeabbrev'] . "'
													AND enddate='0000-00-00'
													AND startdate<'" . $myrow['lateststart'] . "'",'','',true);
	}
	prnMsg(_('All old prices have been deleted'),'success');
	DB_Txn_Commit();
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div class="centre">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<br />
	<input type="submit" name="DeleteOldPrices" value="' . _('Purge Old Prices') . '" onclick="return confirm(\'' . _('Are You Sure you wish to delete all old prices?') . '\');" />
	</div>
      </form>';

include('includes/footer.inc');
?>
