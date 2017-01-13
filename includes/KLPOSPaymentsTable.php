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
if (!isset($_POST['AmountPaidAmexBCA'])){
	$_POST['AmountPaidAmexBCA'] =0;
}
if (!isset($_POST['AmountPaidCCMandiri'])){
	$_POST['AmountPaidCCMandiri'] =0;
}
if (!isset($_POST['AmountPaidCCBCA'])){
	$_POST['AmountPaidCCBCA'] =0;
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
echo '<th colspan=4>' . _('Payment details') . '</th>'; 
echo '</tr>';

echo '<tr>';
echo '<th>' . _('Cash Payments') . '</th>'; 
echo '<td></td>';
echo '<td>' . _('Amount Paid Cash') . ':</td>
	  <td><input type="text" class="number" name="AmountPaidCash" maxlength="12" size="12" value="' . $_POST['AmountPaidCash'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<th>' . _('Credit Card Payments') . '</th>'; 
echo '<td></td>';
echo '<td>' . _('Amount Paid CC EDC Danamon') . ':</td>
	  <td><input type="text" class="number" name="AmountPaidCCDanamon" maxlength="12" size="12" value="' . $_POST['AmountPaidCCDanamon'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>'; 
echo '<td></td>';
echo '<td>' . _('Amount Paid CC EDC Mandiri') . ':</td>
	  <td><input type="text" class="number" name="AmountPaidCCMandiri" maxlength="12" size="12" value="' . $_POST['AmountPaidCCMandiri'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>'; 
echo '<td></td>';
echo '<td>' . _('Amount Paid CC EDC BCA') . ':</td>
	  <td><input type="text" class="number" name="AmountPaidCCBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidCCBCA'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>'; 
echo '<td></td>';
echo '<td>' . _('Amount Paid AMEX EDC BCA') . ':</td>
	  <td><input type="text" class="number" name="AmountPaidAmexBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexBCA'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<th>' . _('Returned Goods') . '</th>'; 
echo '<td></td>';
echo '<td>' . _('Amount Returned Goods') . ':</td>
	  <td><input type="text" class="number" name="AmountReturnedGoods" maxlength="12" size="12" value="' . $_POST['AmountReturnedGoods'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>'; 
echo '<td></td>';
echo '<td>' . _('Invoice number') . ':</td>
	  <td><input type="text" class="number" name="ReturnedGoodsOldInvoice" maxlength="12" size="12" value="' . $_POST['ReturnedGoodsOldInvoice'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>'; 
echo '<td></td>';
echo '<td>' . _('Items returned') . ':</td>
	  <td><input type="text" class="number" name="ReturnedGoodsItems" maxlength="40" size="12" value="' . $_POST['ReturnedGoodsItems'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<th>' . _('Vouchers/Discounts') . '</th>'; 
echo '<td></td>';
echo '<td>' . _('Amount Voucher/Discount') . ':</td>
	  <td><input type="text" class="number" name="AmountVouchers" maxlength="12" size="12" value="' . $_POST['AmountVouchers'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<td></td>'; 
echo '<td></td>';
echo '<td>' . _('Voucher/Discount Code') . ':</td>
	  <td><input type="text" class="number" name="VoucherCode" maxlength="40" size="12" value="' . $_POST['VoucherCode'] . '" /></td>';
echo '</tr>';

echo '<tr>';
echo '<th>'. _('Comments') .':</th>
	  <td colspan= 3><textarea name="Comments" cols="40" rows="3">' . stripcslashes($_SESSION['Items'.$identifier]->Comments) .'</textarea></td>';
echo '</tr>';

echo '</table>';


?>
