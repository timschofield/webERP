<?php

/* $Revision: 1.13 $ */

$PageSecurity=15;

include ('includes/session.inc');
$title = _('Recalculation of GL Balances in Chart Details Table');
include('includes/header.inc');

echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

if (!isset($_POST['FromPeriod'])){

/*Show a form to allow input of criteria for TB to show */
	echo '<table>
                             <tr>
                                 <td>' . _('Select Period From') . ":</td>
                                 <td><select Name='FromPeriod'>";

	$sql = 'SELECT periodno,
                       lastdate_in_period
                FROM periods ORDER BY periodno';
	$Periods = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($Periods,$db)){
		echo '<option VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
	}

	echo '</select></td>
             </tr>
             </table>';

	echo "<div class='centre'><input type=submit Name='recalc' Value='" . _('Do the Recalculation') . "' 
	  onclick=\"return confirm('" . _('Are you sure you wish to re-post all general ledger transactions since the selected period
	   .... this can take some time?') . '\');"></div></form>';

} else {  /*OK do the updates */

	/* Make the posted flag on all GL entries including and after the period selected = 0 */
	$sql = 'UPDATE gltrans SET posted=0 WHERE periodno >='. $_POST['FromPeriod'];
	$UpdGLTransPostedFlag = DB_query($sql,$db);

	/* Now make all the actuals 0 for all periods including and after the period from */
	$sql = 'UPDATE chartdetails SET actual =0 WHERE period >= ' . $_POST['FromPeriod'];
	$UpdActualChartDetails = DB_query($sql,$db);

	$ChartDetailBFwdResult = DB_query('SELECT accountcode, bfwd FROM chartdetails WHERE period=' . $_POST['FromPeriod'],$db);	
	while ($ChartRow=DB_fetch_array($ChartDetailBFwdResult)){
		$sql = 'UPDATE chartdetails SET bfwd =' . $ChartRow['bfwd'] . ' WHERE period > ' . $_POST['FromPeriod'] . ' AND accountcode=' . $ChartRow['accountcode'];
		$UpdActualChartDetails = DB_query($sql,$db);
	}
	
	/*Now repost the lot */

	include('includes/GLPostings.inc');
	
	prnMsg(_('All general ledger postings have been reposted from period') . ' ' . $_POST['FromPeriod'],'success');
}
include('includes/footer.inc');
?>