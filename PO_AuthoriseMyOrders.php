<?php
$PageSecurity = 4;
include('includes/session.inc');

$title = _('Authorise Purchase Orders');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . $title .
	 '" alt="">' . ' ' . $title . '</p>';

$emailsql='SELECT email FROM www_users WHERE userid="'.$_SESSION['UserID'].'"';
$emailresult=DB_query($emailsql, $db);
$emailrow=DB_fetch_array($emailresult);

if (isset($_POST['updateall'])) {
	foreach ($_POST as $key => $value) {
		if (substr($key,0,6)=='status') {
			$orderno=substr($key,6);
			$status=$_POST['status'.$orderno];
			$comment=date($_SESSION['DefaultDateFormat']).' - '._('Authorised by').' '.'<a href="mailto:'.
				$emailrow['email'].'">'.$_SESSION['UserID'].'</a><br>'.$_POST['comment'];
			$sql="UPDATE purchorders 
				SET status='".$status."',
				stat_comment='".$comment."'
				WHERE orderno=".$orderno;
			$result=DB_query($sql, $db);
		}
	}
}

/* Retrieve the purchase order header information
 */
$sql='SELECT purchorders.*, 
			suppliers.suppname,
			suppliers.currcode,
			www_users.realname,
			www_users.email
			FROM purchorders
		LEFT JOIN suppliers 
			ON suppliers.supplierid=purchorders.supplierno
		LEFT JOIN www_users
			ON www_users.userid=purchorders.initiator
	WHERE status="'. _('Pending'). '"';
$result=DB_query($sql, $db);

echo '<form method=post action="' . $_SERVER['PHP_SELF'] . '">';
echo '<table><tr>';

/* Create the table for the purchase order header */
echo '<th>'._('Order Number').'</th>';
echo '<th>'._('Supplier').'</th>';
echo '<th>'._('Date Ordered').'</th>';
echo '<th>'._('Initiator').'</th>';
echo '<th>'._('Delivery Date').'</th>';
echo '<th>'._('Status').'</th>';
echo '</tr>';

while ($myrow=DB_fetch_array($result)) {
	
	$authsql='SELECT authlevel FROM purchorderauth
				WHERE userid="'.$_SESSION['UserID'].'"
				AND currabrev="'.$myrow['currcode'].'"';

	$authresult=DB_query($authsql, $db);
	$myauthrow=DB_fetch_array($authresult);
	$authlevel=$myauthrow['authlevel'];
	
	$ordervaluesql='SELECT sum(unitprice*quantityord) as ordervalue
			FROM purchorderdetails 
			WHERE orderno='.$myrow['orderno'];

	$ordervalueresult=DB_query($ordervaluesql, $db);
	$myordervaluerow=DB_fetch_array($ordervalueresult);
	$ordervalue=$myordervaluerow['ordervalue'];
	
	if ($authlevel>=$ordervalue) {
		echo '<tr>';
		echo '<td>'.$myrow['orderno'].'</td>';
		echo '<td>'.$myrow['suppname'].'</td>';
		echo '<td>'.ConvertSQLDate($myrow['orddate']).'</td>';
		echo '<td><a href="mailto:'.$myrow['email'].'">'.$myrow['realname'].'</td>';
		echo '<td>'.ConvertSQLDate($myrow['deliverydate']).'</td>';
		echo '<td><select name=status'.$myrow['orderno'].'>';
		echo '<option selected value="'._('Pending').'">'._('Pending').'</option>';
		echo '<option value="'._('Authorised').'">'._('Authorised').'</option>';
		echo '<option value="'._('Rejected').'">'._('Rejected').'</option>';
		echo '<option value="'._('Cancelled').'">'._('Cancelled').'</option>';
		echo '</select></td>';
		echo '</tr>';
		echo "<input type='hidden' name='comment' value='".$myrow['stat_comment']."'>";
		$linesql='SELECT purchorderdetails.*,
					stockmaster.description
				FROM purchorderdetails
				LEFT JOIN stockmaster
				ON stockmaster.stockid=purchorderdetails.itemcode
			WHERE orderno='.$myrow['orderno'];
		$lineresult=DB_query($linesql, $db);

		echo '<tr><td></td><td colspan=5 align=left><table align=left>';
		echo '<th>'._('Product').'</th>';
		echo '<th>'._('Quantity Ordered').'</th>';
		echo '<th>'._('Currency').'</th>';
		echo '<th>'._('Price').'</th>';
		echo '<th>'._('Line Total').'</th>';
		echo '</tr>';

		while ($linerow=DB_fetch_array($lineresult)) {
			echo '<tr>';
			echo '<td>'.$linerow['description'].'</td>';
			echo '<td class="number">'.number_format($linerow['quantityord'],2).'</td>';
			echo '<td>'.$myrow['currcode'].'</td>';
			echo '<td class="number">'.number_format($linerow['unitprice'],2).'</td>';
			echo '<td class="number">'.number_format($linerow['unitprice']*$linerow['quantityord'],2).'</td>';
			echo '</tr>';
		} // end while order line detail
		echo '</table></td></tr>';
	}
} //end while header loop
echo '</table>';
echo '<br><div class="centre"><input type="submit" name="updateall" value="' . _('Update'). '"></form>';

include('includes/footer.inc');
?>