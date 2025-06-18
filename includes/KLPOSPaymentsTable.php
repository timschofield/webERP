<?php

/////////////////////////////////////////////////////////////////////
//  PAYMENTS Table
/////////////////////////////////////////////////////////////////////

/* Container table. It only contains 2 tables, one for payments and one for others */
echo '<table>
		<thead>
			<th colspan=2></th>
		</thead>';

echo '<td>';

/* First nested table, about payments */
echo '<table class="selection">
		<thead>
			<th colspan=3>' . _('Payments') . '</th>
		</thead>
		<tbody>';

/* Always show cash payments*/
echo '<tr>
		<th>' . _('Cash Payments') . '</th>
		<td>' . _('Cash') . ':</td>
		<td><input type="text" class="number" name="AmountPaidCash" maxlength="12" size="12" value="' . $_POST['AmountPaidCash'] . '" /></td>
	</tr>';

/* Only show the CC payments active for the retail partner (set as commission not 0) */
echo '<tr>
		<th>' . _('Credit Card Payments') . '</th>';
if ($_SESSION['ComissionCCBNI'] != 0){
	echo '<td>' . _('CC EDC BNI') . ':</td>
		<td><input type="text" class="number" name="AmountPaidCCBNI" maxlength="12" size="12" value="' . $_POST['AmountPaidCCBNI'] . '" /></td>';
}
echo '</tr>';

if ($_SESSION['ComissionCCBCA'] != 0){
	echo '<tr>
			<td></td>
			<td>' . _('CC EDC BCA') . ':</td>
			<td><input type="text" class="number" name="AmountPaidCCBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidCCBCA'] . '" /></td>
		</tr>';
}

if ($_SESSION['ComissionCCMandiri'] != 0){
	echo '<tr>
			<td></td>
			<td>' . _('CC EDC Mandiri') . ':</td>
			<td><input type="text" class="number" name="AmountPaidCCMandiri" maxlength="12" size="12" value="' . $_POST['AmountPaidCCMandiri'] . '" /></td>
		</tr>';
}

if ($_SESSION['ComissionCCDanamon'] != 0){
	echo '<tr>
			<td></td>
			<td>' . _('CC EDC Danamon') . ':</td>
			<td><input type="text" class="number" name="AmountPaidCCDanamon" maxlength="12" size="12" value="' . $_POST['AmountPaidCCDanamon'] . '" /></td>
		</tr>';
}

echo '<tr>
		<th>' . _('AMEX Payments') . '</th>';
if ($_SESSION['ComissionAmexBCA'] != 0){
	echo '<td>' . _('AMEX EDC BCA') . ':</td>
		<td><input type="text" class="number" name="AmountPaidAmexBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexBCA'] . '" /></td>';
}
echo '</tr>';

if ($_SESSION['ComissionAmexBNI'] != 0){
	echo '<tr>
			<td></td>
			<td>' . _('AMEX EDC BNI') . ':</td>
			<td><input type="text" class="number" name="AmountPaidAmexBNI" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexBNI'] . '" /></td>
		</tr>';
}

echo '<tr>';
echo '<th>' . _('Other Payments') . '</th>';
if ($_SESSION['ComissionQRIS'] != 0){
	echo '<td>' . _('QRIS Mandiri') . ':</td>
		<td><input type="text" class="number" name="AmountPaidQRIS" maxlength="12" size="12" value="' . $_POST['AmountPaidQRIS'] . '" /></td>';
}
echo '</tr>';

if ($_SESSION['ComissionWeChat'] != 0){
	echo '<tr>
			<td></td>
			<td>' . _('Alipay/WeChat') . ':</td>
			<td><input type="text" class="number" name="AmountPaidWeChat" maxlength="12" size="12" value="' . $_POST['AmountPaidWeChat'] . '" /></td>
		</tr>';
}

echo '</tbody>
	</table>
	</td>';

/* Second nested table, about "others"*/
echo '<td>';
echo '<table class="selection">
		<thead>
			<th colspan=3>' . _('Others') . '</th>
		</thead>
		<tbody>';

echo '<tr>
		<th>' . _('Returned Goods') . '</th>
		<td>' . _('Amount Returned Goods') . ':</td>
		<td><input type="text" class="number" name="AmountReturnedGoods" maxlength="12" size="12" value="' . $_POST['AmountReturnedGoods'] . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . _('Invoice number') . ':</td>
		<td><input type="text" class="text" name="ReturnedGoodsOldInvoice" maxlength="12" size="12" value="' . $_POST['ReturnedGoodsOldInvoice'] . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . _('Invoice Date') . ':</td>
		<td><input type="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ReturnDate" size="10" maxlength="10" value="' . FormatDateForSQL($_POST['ReturnDate']) . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . _('Items returned') . ':</td>
		<td><input type="text" class="text" name="ReturnedGoodsItems" maxlength="40" size="12" value="' . $_POST['ReturnedGoodsItems'] . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . _('Reason of return') . ':</td>
		<td><select name="ReturnedGoodsReason">';
$SQL = "SELECT reasonid,
				reasonname
		FROM returnitemreasons
		ORDER BY reasonname";
$Result = DB_query($SQL);
while ($MyRow = DB_fetch_array($Result)){
	if (isset($_POST['ReturnedGoodsReason']) && $_POST['ReturnedGoodsReason'] == $MyRow['reasonid']){
		echo '<option selected="selected" value="' . $MyRow['reasonid'] . '">' . $MyRow['reasonname'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['reasonid'] . '">' . $MyRow['reasonname'] . '</option>';
	}
}
echo '</select></td>
	</tr>';

echo '<tr>
		<th>' . _('Vouchers/Discounts') . '</th>
		<td>' . _('Amount Voucher/Discount') . ':</td>
		<td><input type="text" class="number" name="AmountVouchers" maxlength="12" size="12" value="' . $_POST['AmountVouchers'] . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . _('Voucher/Discount Code') . ':</td>
		<td><input type="text" class="text" name="VoucherCode" maxlength="40" size="12" value="' . $_POST['VoucherCode'] . '" /></td>
	</tr>';

/* Close the Others table */	
echo '</tbody>
	</table>';

/* Close the container table */
echo '</td>
	</table>';

/* Show comments section */
echo '<table class="selection">
		<tr>
			<th colspan=2>' . _('Comments') . ':</th>
		</tr>
		<tr>
			<td colspan=2><textarea name="Comments" cols="100" rows="3">' . stripcslashes($_SESSION['Items' . $identifier]->Comments) . '</textarea></td>
		</tr>
	</table>';

?>
