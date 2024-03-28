<?php

/////////////////////////////////////////////////////////////////////
//  PAYMENTS Table
/////////////////////////////////////////////////////////////////////

if (!isset($_POST['AmountPaidCash'])){
	$_POST['AmountPaidCash'] =0;
}
if (!isset($_POST['AmountPaidCCDanamon'])){
	$_POST['AmountPaidCCDanamon'] =0;
}
if (!isset($_POST['AmountPaidCCBNI'])){
	$_POST['AmountPaidCCBNI'] =0;
}
if (!isset($_POST['AmountPaidAmexBCA'])){
	$_POST['AmountPaidAmexBCA'] =0;
}
if (!isset($_POST['AmountPaidAmexBNI'])){
	$_POST['AmountPaidAmexBNI'] =0;
}
if (!isset($_POST['AmountPaidCCMandiri'])){
	$_POST['AmountPaidCCMandiri'] =0;
}
if (!isset($_POST['AmountPaidCCBCA'])){
	$_POST['AmountPaidCCBCA'] =0;
}
if (!isset($_POST['AmountPaidWeChat'])){
	$_POST['AmountPaidWeChat'] =0;
}
if (!isset($_POST['AmountPaidQRIS'])){
	$_POST['AmountPaidQRIS'] =0;
}
if (!isset($_POST['AmountReturnedGoods'])){
	$_POST['AmountReturnedGoods'] =0;
}
if (!isset($_POST['ReturnedGoodsOldInvoice'])){
	$_POST['ReturnedGoodsOldInvoice'] ='';
}
if (!isset($_POST['ReturnedGoodsItems'])){
	$_POST['ReturnedGoodsItems'] ='';
}
if (!isset($_POST['ReturnedGoodsReason'])){
	$_POST['ReturnedGoodsReason'] =0;
}
if (!isset($_POST['ReturnDate'])){
	$_POST['ReturnDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AmountVouchers'])){
	$_POST['AmountVouchers'] =0;
}
if (!isset($_POST['VoucherCode'])){
	$_POST['VoucherCode'] ='';
}
if (!isset($_POST['Comments'])){
	$_POST['Comments'] ='';
}

echo '<table class="selection">';
echo '<tr>';
echo '<th colspan=2></th>'; 
echo '</tr>';

echo '<td>';
echo '<table class="selection">';
echo '<tr>';
echo '<th colspan=3>' . _('Payment') . '</th>'; 
echo '</tr>';

/* Always show cash payments*/
echo '<tr>';
echo '<th>' . _('Cash Payments') . '</th>'; 
echo '<td>' . _('Amount Paid Cash') . ':</td>
	  <td><input type="text" class="number" name="AmountPaidCash" maxlength="12" size="12" value="' . $_POST['AmountPaidCash'] . '" /></td>';
echo '</tr>';

/* Only show the CC payments active (commission not 0) for the retail partner */
echo '<tr>';
echo '<th>' . _('Credit Card Payments') . '</th>'; 
if ($_SESSION['ComissionCCBNI'] != 0){
	echo '<td>' . _('Amount Paid CC EDC BNI') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidCCBNI" maxlength="12" size="12" value="' . $_POST['AmountPaidCCBNI'] . '" /></td>';
}
echo '</tr>';

if ($_SESSION['ComissionCCBCA'] != 0){
	echo '<tr>';
	echo '<td></td>';
	echo '<td>' . _('Amount Paid CC EDC BCA') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidCCBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidCCBCA'] . '" /></td>';
	echo '</tr>';
}

if ($_SESSION['ComissionCCMandiri'] != 0){
	echo '<tr>';
	echo '<td></td>';
	echo '<td>' . _('Amount Paid CC EDC Mandiri') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidCCMandiri" maxlength="12" size="12" value="' . $_POST['AmountPaidCCMandiri'] . '" /></td>';
	echo '</tr>';
}

if ($_SESSION['ComissionCCDanamon'] != 0){
	echo '<tr>';
	echo '<td></td>';
	echo '<td>' . _('Amount Paid CC EDC Danamon') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidCCDanamon" maxlength="12" size="12" value="' . $_POST['AmountPaidCCDanamon'] . '" /></td>';
	echo '</tr>';
}

echo '<tr>';
echo '<th>' . _('AMEX Payments') . '</th>'; 
if ($_SESSION['ComissionAmexBCA'] != 0){
	echo '<td>' . _('Amount Paid AMEX EDC BCA') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidAmexBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexBCA'] . '" /></td>';
}
echo '</tr>';

if ($_SESSION['ComissionAmexBNI'] != 0){
	echo '<tr>';
	echo '<td></td>';
	echo '<td>' . _('Amount Paid AMEX EDC BNI') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidAmexBNI" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexBNI'] . '" /></td>';
	echo '</tr>';
}

echo '<tr>';
echo '<th>' . _('Other Payments') . '</th>'; 
if ($_SESSION['ComissionQRIS'] != 0){
	echo '<td>' . _('Amount Paid QRIS Mandiri') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidQRIS" maxlength="12" size="12" value="' . $_POST['AmountPaidQRIS'] . '" /></td>';
}
echo '</tr>';

if ($_SESSION['ComissionWeChat'] != 0){
	echo '<tr>';
	echo '<td></td>';
	echo '<td>' . _('Amount Paid WeChat/Alipay') . ':</td>
		  <td><input type="text" class="number" name="AmountPaidWeChat" maxlength="12" size="12" value="' . $_POST['AmountPaidWeChat'] . '" /></td>';
	echo '</tr>';
}

echo '</table>';
echo '</td>';

/* Second nested table, about "others"*/

echo '<td>';
echo '<table class="selection">';
echo '<tr>';
echo '<th colspan=3>' . _('Others') . '</th>'; 
echo '</tr>';

echo '<tr>';
echo '<th>' . _('Returned Goods') . '</th>'; 
echo '<td>' . _('Amount Returned Goods') . ':</td>
	  <td><input type="text" class="number" name="AmountReturnedGoods" maxlength="12" size="12" value="' . $_POST['AmountReturnedGoods'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>';
echo '<td>' . _('Invoice number') . ':</td>
	  <td><input type="text" class="text" name="ReturnedGoodsOldInvoice" maxlength="12" size="12" value="' . $_POST['ReturnedGoodsOldInvoice'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>';
echo '<td>' . _('Invoice Date') . ':</td>';
echo '<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="ReturnDate" size="10" maxlength="10" value="' . $_POST['ReturnDate'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>';
echo '<td>' . _('Items returned') . ':</td>
	  <td><input type="text" class="text" name="ReturnedGoodsItems" maxlength="40" size="12" value="' . $_POST['ReturnedGoodsItems'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>';
echo '<td>' . _('Reason of return') . ':</td>
	  <td><select name="ReturnedGoodsReason">';
$SQL = "SELECT reasonid,
				reasonname
		FROM returnitemreasons
		ORDER BY reasonname";
$result=DB_query($SQL);
while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['ReturnedGoodsReason']) and $_POST['ReturnedGoodsReason']==$myrow['reasonid']){
		echo '<option selected="selected" value="' . $myrow['reasonid'] . '">' . $myrow['reasonname'] . '</option>';
	} else {
		echo '<option value="' . $myrow['reasonid'] . '">' . $myrow['reasonname'] . '</option>';
	}
}
echo '</select></td>';
echo '</tr>';

echo '<tr>';
echo '<th>' . _('Vouchers/Discounts') . '</th>'; 
echo '<td>' . _('Amount Voucher/Discount') . ':</td>
	  <td><input type="text" class="number" name="AmountVouchers" maxlength="12" size="12" value="' . $_POST['AmountVouchers'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>';
echo '<td>' . _('Voucher/Discount Code') . ':</td>
	  <td><input type="text" class="text" name="VoucherCode" maxlength="40" size="12" value="' . $_POST['VoucherCode'] . '" /></td>';
echo '</tr>';

echo '</table>';
echo '</td>';

echo '</table>';


echo '<table class="selection">';
echo '<tr>';
echo '<th colspan=2>'. _('Comments') .':</th>';
echo '</tr>';
echo '<tr>';
echo '<td colspan=2><textarea name="Comments" cols="100" rows="3">' . stripcslashes($_SESSION['Items'.$identifier]->Comments) .'</textarea></td>';
echo '</tr>';
echo '</table>';

?>
