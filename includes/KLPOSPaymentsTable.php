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
			<th colspan=3>' . __('Payments') . '</th>
		</thead>
		<tbody>';

/* Always show cash payments*/
echo '<tr>
		<th>' . __('Cash Payments') . '</th>
		<td>' . __('Cash') . ':</td>
		<td><input type="text" class="number" name="AmountPaidCash" maxlength="12" size="12" value="' . $_POST['AmountPaidCash'] . '" /></td>
	</tr>';

/* Only show the CC payments active for the retail partner (set as commission not 0) */
echo '<tr>
		<th>' . __('Credit Card Payments') . '</th>';
if ($_SESSION['ComissionCCBNI'] != 0){
	echo '<td>' . __('CC EDC BNI') . ':</td>
		<td><input type="text" class="number" name="AmountPaidCCBNI" maxlength="12" size="12" value="' . $_POST['AmountPaidCCBNI'] . '" /></td>';
}
echo '</tr>';

if ($_SESSION['ComissionCCBCA'] != 0){
	echo '<tr>
			<td></td>
			<td>' . __('CC EDC BCA') . ':</td>
			<td><input type="text" class="number" name="AmountPaidCCBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidCCBCA'] . '" /></td>
		</tr>';
}

if ($_SESSION['ComissionCCMandiri'] != 0){
	echo '<tr>
			<td></td>
			<td>' . __('CC EDC Mandiri') . ':</td>
			<td><input type="text" class="number" name="AmountPaidCCMandiri" maxlength="12" size="12" value="' . $_POST['AmountPaidCCMandiri'] . '" /></td>
		</tr>';
}

if ($_SESSION['ComissionCCBRI'] != 0){
	echo '<tr>
			<td></td>
			<td>' . __('CC EDC BRI') . ':</td>
			<td><input type="text" class="number" name="AmountPaidCCBRI" maxlength="12" size="12" value="' . $_POST['AmountPaidCCBRI'] . '" /></td>
		</tr>';
}

if ($_SESSION['ComissionCCDanamon'] != 0){
	echo '<tr>
			<td></td>
			<td>' . __('CC EDC Danamon') . ':</td>
			<td><input type="text" class="number" name="AmountPaidCCDanamon" maxlength="12" size="12" value="' . $_POST['AmountPaidCCDanamon'] . '" /></td>
		</tr>';
}

echo '<tr>
		<th>' . __('AMEX Payments') . '</th>';
if ($_SESSION['ComissionAmexBCA'] != 0){
	echo '<td>' . __('AMEX EDC BCA') . ':</td>
		<td><input type="text" class="number" name="AmountPaidAmexBCA" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexBCA'] . '" /></td>';
}
echo '</tr>';

if ($_SESSION['ComissionAmexBNI'] != 0){
	echo '<tr>
			<td></td>
			<td>' . __('AMEX EDC BNI') . ':</td>
			<td><input type="text" class="number" name="AmountPaidAmexBNI" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexBNI'] . '" /></td>
		</tr>';
}

if ($_SESSION['ComissionAmexMandiri'] != 0){
	echo '<tr>
			<td></td>
			<td>' . __('AMEX EDC Mandiri') . ':</td>
			<td><input type="text" class="number" name="AmountPaidAmexMandiri" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexMandiri'] . '" /></td>
		</tr>';
}

if ($_SESSION['ComissionAmexBRI'] != 0){
	echo '<tr>
			<td></td>
			<td>' . __('AMEX EDC BRI') . ':</td>
			<td><input type="text" class="number" name="AmountPaidAmexBRI" maxlength="12" size="12" value="' . $_POST['AmountPaidAmexBRI'] . '" /></td>
		</tr>';
}

echo '<tr>';
echo '<th>' . __('Other Payments') . '</th>';
if ($_SESSION['ComissionQRISMandiri'] != 0){
	echo '<td>' . __('QRIS Mandiri') . ':</td>
		<td><input type="text" class="number" name="AmountPaidQRIS" maxlength="12" size="12" value="' . $_POST['AmountPaidQRIS'] . '" /></td>';
}
echo '</tr>';

if ($_SESSION['ComissionQRISBRI'] != 0){
	echo '<tr>
			<td></td>
			<td>' . __('QRIS BRI') . ':</td>
			<td><input type="text" class="number" name="AmountPaidQRISBRI" maxlength="12" size="12" value="' . $_POST['AmountPaidQRISBRI'] . '" /></td>
		</tr>';
}

if ($_SESSION['ComissionWeChat'] != 0){
	echo '<tr>
			<td></td>
			<td>' . __('Alipay/WeChat') . ':</td>
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
			<th colspan=3>' . __('Others') . '</th>
		</thead>
		<tbody>';

echo '<tr>
		<th>' . __('Returned Goods') . '</th>
		<td>' . __('Amount Returned Goods') . ':</td>
		<td><input type="text" class="number" name="AmountReturnedGoods" maxlength="12" size="12" value="' . $_POST['AmountReturnedGoods'] . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . __('Invoice number') . ':</td>
		<td><input type="text" class="text" name="ReturnedGoodsOldInvoice" maxlength="12" size="12" value="' . $_POST['ReturnedGoodsOldInvoice'] . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . __('Invoice Date') . ':</td>
		<td><input type="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ReturnDate" size="10" maxlength="10" value="' . FormatDateForSQL($_POST['ReturnDate']) . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . __('Items returned') . ':</td>
		<td><input type="text" class="text" name="ReturnedGoodsItems" maxlength="40" size="12" value="' . $_POST['ReturnedGoodsItems'] . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . __('Reason of return') . ':</td>
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
		<th>' . __('Vouchers/Discounts') . '</th>
		<td>' . __('Amount Voucher/Discount') . ':</td>
		<td><input type="text" class="number" name="AmountVouchers" maxlength="12" size="12" value="' . $_POST['AmountVouchers'] . '" /></td>
	</tr>';

echo '<tr>
		<td></td>
		<td>' . __('Voucher/Discount Code') . ':</td>
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
			<th colspan=2>' . __('Comments') . ':</th>
		</tr>
		<tr>
			<td colspan=2><textarea name="Comments" cols="100" rows="3">' . stripcslashes($_SESSION['Items' . $identifier]->Comments) . '</textarea></td>
		</tr>
	</table>';

