<?php

/* $Id: InternalStockRequestAuthorisation.php 4576 2011-05-27 10:59:20Z daintree $*/

include('includes/session.inc');

$Title = _('Authorise Internal Stock Requests');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['UpdateAll'])) {
	foreach ($_POST as $key => $value) {
		if (mb_substr($key,0,6)=='status') {
			$RequestNo=mb_substr($key,6);
			$sql="UPDATE stockrequest
					SET authorised='1'
					WHERE dispatchid='".$RequestNo."'";
			$result=DB_query($sql, $db);
		}
	}
}

/* Retrieve the requisition header information
 */
$sql="SELECT stockrequest.dispatchid,
			locations.locationname,
			stockrequest.despatchdate,
			stockrequest.narrative,
			departments.description,
			www_users.realname,
			www_users.email
		FROM stockrequest INNER JOIN departments
			ON stockrequest.departmentid=departments.departmentid
		INNER JOIN locations
			ON stockrequest.loccode=locations.loccode
		INNER JOIN www_users
			ON www_users.userid=departments.authoriser
		WHERE stockrequest.authorised=0
		AND stockrequest.closed=0
		AND www_users.userid='".$_SESSION['UserID']."'";
$result=DB_query($sql, $db);

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

/* Create the table for the purchase order header */
echo '<tr>
		<th>' . _('Request Number') . '</th>
		<th>' . _('Department') . '</th>
		<th>' . _('Location Of Stock') . '</th>
		<th>' . _('Requested Date') . '</th>
		<th>' . _('Narrative') . '</th>
		<th>' . _('Authorise') . '</th>
	</tr>';

while ($myrow=DB_fetch_array($result)) {

	echo '<tr>
			<td>' . $myrow['dispatchid'] . '</td>
			<td>' . $myrow['description'] . '</td>
			<td>' . $myrow['locationname'] . '</td>
			<td>' . ConvertSQLDate($myrow['despatchdate']) . '</td>
			<td>' . $myrow['narrative'] . '</td>
			<td><input type="checkbox" name="status'.$myrow['dispatchid'].'" /></td>
		</tr>';
	$linesql="SELECT stockrequestitems.dispatchitemsid,
						stockrequestitems.stockid,
						stockrequestitems.decimalplaces,
						stockrequestitems.uom,
						stockmaster.description,
						stockrequestitems.quantity
				FROM stockrequestitems
				INNER JOIN stockmaster
				ON stockmaster.stockid=stockrequestitems.stockid
			WHERE dispatchid='".$myrow['dispatchid'] . "'";
	$lineresult=DB_query($linesql, $db);

	echo '<tr>
			<td></td>
			<td colspan="5" align="left">
				<table class="selection" align="left">
				<tr>
					<th>'._('Product').'</th>
					<th>'._('Quantity Required').'</th>
					<th>'._('Units').'</th>
				</tr>';

	while ($linerow=DB_fetch_array($lineresult)) {
		echo '<tr>
				<td>'.$linerow['description'].'</td>
				<td class="number">'.locale_number_format($linerow['quantity'],$linerow['decimalplaces']).'</td>
				<td>'.$linerow['uom'].'</td>
			</tr>';
	} // end while order line detail
	echo '</table>
			</td>
		</tr>';
} //end while header loop
echo '</table>';
echo '<br /><div class="centre"><input type="submit" name="UpdateAll" value="' . _('Update'). '" /></div>
      </div>
      </form>';

include('includes/footer.inc');
?>