<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Authorise Internal Stock Requests');
$ViewTopic = 'Inventory';
$BookMark = 'AuthoriseRequest';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['UpdateAll'])) {
	foreach ($_POST as $POSTVariableName => $POSTValue) {
		if (mb_substr($POSTVariableName,0,6)=='status') {
			$RequestNo=mb_substr($POSTVariableName,6);
			$SQL="UPDATE stockrequest
					SET authorised='1'
					WHERE dispatchid='" . $RequestNo . "'";
			$Result = DB_query($SQL);
		}
		if (strpos($POSTVariableName, 'cancel')!== false) {
			$CancelItems = explode('cancel', $POSTVariableName);
 			$SQL = "UPDATE stockrequestitems
 						SET completed=1
 						WHERE dispatchid='" . $CancelItems[0] . "'
 						AND dispatchitemsid='" . $CancelItems[1] . "'";
 			$Result = DB_query($SQL);
 			$Result = DB_query("SELECT stockid FROM stockrequestitems WHERE completed=0 AND dispatchid='" . $CancelItems[0] . "'");
 			if (DB_num_rows($Result) ==0){
				$Result = DB_query("UPDATE stockrequest
									SET authorised='1'
									WHERE dispatchid='" . $CancelItems[0] . "'");
			}

 		}
	}
}

/* Retrieve the requisition header information
 */
$SQL="SELECT stockrequest.dispatchid,
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
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
		INNER JOIN www_users
			ON www_users.userid=departments.authoriser
		WHERE stockrequest.authorised=0
		AND stockrequest.closed=0
		AND www_users.userid='".$_SESSION['UserID']."'";
$Result = DB_query($SQL);

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">
	<tr>
		<th>' . __('Request Number') . '</th>
		<th>' . __('Department') . '</th>
		<th>' . __('Location Of Stock') . '</th>
		<th>' . __('Requested Date') . '</th>
		<th>' . __('Narrative') . '</th>
		<th>' . __('Authorise') . '</th>
	</tr>';

while ($MyRow=DB_fetch_array($Result)) {

	echo '<tr>
			<td>' . $MyRow['dispatchid'] . '</td>
			<td>' . $MyRow['description'] . '</td>
			<td>' . $MyRow['locationname'] . '</td>
			<td>' . ConvertSQLDate($MyRow['despatchdate']) . '</td>
			<td>' . $MyRow['narrative'] . '</td>
			<td><input type="checkbox" name="status'.$MyRow['dispatchid'].'" /></td>
		</tr>';
	$LinesSQL="SELECT stockrequestitems.dispatchitemsid,
						stockrequestitems.stockid,
						stockrequestitems.decimalplaces,
						stockrequestitems.uom,
						stockmaster.description,
						stockrequestitems.quantity
				FROM stockrequestitems
				INNER JOIN stockmaster
				ON stockmaster.stockid=stockrequestitems.stockid
			WHERE dispatchid='".$MyRow['dispatchid'] . "'
			AND completed=0";
	$LineResult = DB_query($LinesSQL);

	echo '<tr>
			<td></td>
			<td colspan="5" align="left">
				<table class="selection" align="left">
				<tr>
					<th>' . __('Product') . '</th>
					<th>' . __('Quantity Required') . '</th>
					<th>' . __('Units') . '</th>
					<th>' . __('Cancel Line') . '</th>
				</tr>';

	while ($LineRow=DB_fetch_array($LineResult)) {
		echo '<tr>
				<td>' . $LineRow['description'] . '</td>
				<td class="number">' . locale_number_format($LineRow['quantity'],$LineRow['decimalplaces']) . '</td>
				<td>' . $LineRow['uom'] . '</td>
				<td><input type="checkbox" name="' . $MyRow['dispatchid'] . 'cancel' . $LineRow['dispatchitemsid'] . '" /></td
			</tr>';
	} // end while order line detail
	echo '</table>
			</td>
		</tr>';
} //end while header loop
echo '</table>';
echo '<br /><div class="centre"><input type="submit" name="UpdateAll" value="' . __('Update'). '" /></div>
      </div>
      </form>';

include('includes/footer.php');
