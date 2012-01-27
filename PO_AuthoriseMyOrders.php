<?php

/* $Id$*/

include('includes/session.inc');

$title = _('Authorise Purchase Orders');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . $title .
	 '" alt="" />' . ' ' . $title . '</p>';

$EmailSQL="SELECT email FROM www_users WHERE userid='".$_SESSION['UserID']."'";
$EmailResult=DB_query($EmailSQL, $db);
$EmailRow=DB_fetch_array($EmailResult);

if (isset($_POST['UpdateAll'])) {
	foreach ($_POST as $key => $value) {
		if (mb_substr($key,0,6)=='status') {
			$OrderNo=mb_substr($key,6);
			$Status=$_POST['status'.$OrderNo];
			$Comment=date($_SESSION['DefaultDateFormat']).' - '._('Authorised by').' <a href="mailto:' . $EmailRow['email'].'">'.$_SESSION['UserID'].'</a><br />' . $_POST['comment'];
			$sql="UPDATE purchorders
					SET status='".$Status."',
						stat_comment='".$Comment."',
						allowprint=1
					WHERE orderno='". $OrderNo."'";
			$result=DB_query($sql, $db);
		}
	}
}

/* Retrieve the purchase order header information
 */
$sql="SELECT purchorders.*,
			suppliers.suppname,
			suppliers.currcode,
			www_users.realname,
			www_users.email,
			currencies.decimalplaces AS currdecimalplaces
		FROM purchorders INNER JOIN suppliers
			ON suppliers.supplierid=purchorders.supplierno
		INNER JOIN currencies 
			ON suppliers.currcode=currencies.currabrev
		INNER JOIN www_users
			ON www_users.userid=purchorders.initiator
	WHERE status='Pending'";
$result=DB_query($sql, $db);

echo '<form method=post action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

/* Create the table for the purchase order header */
echo '<tr>
		<th>'._('Order Number').'</th>
		<th>'._('Supplier').'</th>
		<th>'._('Date Ordered').'</th>
		<th>'._('Initiator').'</th>
		<th>'._('Delivery Date').'</th>
		<th>'._('Status').'</th>
	</tr>';

while ($myrow=DB_fetch_array($result)) {

	$AuthSQL="SELECT authlevel FROM purchorderauth
				WHERE userid='".$_SESSION['UserID']."'
				AND currabrev='".$myrow['currcode']."'";

	$AuthResult=DB_query($AuthSQL, $db);
	$myauthrow=DB_fetch_array($AuthResult);
	$AuthLevel=$myauthrow['authlevel'];

	$OrderValueSQL="SELECT sum(unitprice*quantityord) as ordervalue
		           	FROM purchorderdetails
			        WHERE orderno='".$myrow['orderno'] . "'";

	$OrderValueResult=DB_query($OrderValueSQL, $db);
	$MyOrderValueRow=DB_fetch_array($OrderValueResult);
	$OrderValue=$MyOrderValueRow['ordervalue'];

	if ($AuthLevel>=$OrderValue) {
		echo '<tr>
				<td>'.$myrow['orderno'].'</td>
				<td>'.$myrow['suppname'].'</td>
				<td>'.ConvertSQLDate($myrow['orddate']).'</td>
				<td><a href="mailto:'.$myrow['email'].'">'.$myrow['realname'].'</td>
				<td>'.ConvertSQLDate($myrow['deliverydate']).'</td>
				<td><select name="status'.$myrow['orderno'].'">
					<option selected="selected" value="Pending">'._('Pending').'</option>
					<option value="Authorised">'._('Authorised').'</option>
					<option value="Rejected">'._('Rejected').'</option>
					<option value="Cancelled">'._('Cancelled').'</option>
					</select></td>
			</tr>';
		echo '<input type="hidden" name="comment" value="' . htmlentities($myrow['stat_comment'], ENT_QUOTES,'UTF-8') . '" />';
		$LineSQL="SELECT purchorderdetails.*,
					stockmaster.description,
					stockmaster.decimalplaces
				FROM purchorderdetails
				LEFT JOIN stockmaster
				ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE orderno='".$myrow['orderno'] . "'";
		$LineResult=DB_query($LineSQL, $db);

		echo '<tr>
				<td></td>
				<td colspan="5" align="left">
					<table class="selection" align="left">
					<tr>
						<th>'._('Product').'</th>
						<th>'._('Quantity Ordered').'</th>
						<th>'._('Currency').'</th>
						<th>'._('Price').'</th>
						<th>'._('Line Total').'</th>
					</tr>';

		while ($LineRow=DB_fetch_array($LineResult)) {
			if ($LineRow['decimalplaces']!=NULL){
				$DecimalPlaces = $LineRow['decimalplaces'];
			}else {
				$DecimalPlaces = 2;
			}
			echo '<tr>
					<td>'.$LineRow['description'].'</td>
					<td class="number">'.locale_number_format($LineRow['quantityord'],$DecimalPlaces).'</td>
					<td>'.$myrow['currcode'].'</td>
					<td class="number">'.locale_number_format($LineRow['unitprice'],$myrow['currdecimalplaces']).'</td>
					<td class="number">'.locale_number_format($LineRow['unitprice']*$LineRow['quantityord'],$myrow['currdecimalplaces']).'</td>
				</tr>';
		} // end while order line detail
		echo '</table>
			</td>
			</tr>';
	}
} //end while header loop
echo '</table>';
echo '<br />
		<div class="centre">
			<input type="submit" name="UpdateAll" value="' . _('Update'). '" />
		</div>
		</form>';
include('includes/footer.inc');
?>