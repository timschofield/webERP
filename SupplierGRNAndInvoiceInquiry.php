<?php
include('includes/session.php');
$Title = _('Supplier Invoice and GRN inquiry');
include('includes/header.php');
if (isset($_GET['SelectedSupplier'])) {
	$SupplierID= $_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])){
	$SupplierID = $_POST['SelectedSupplier'];
} else {
	prnMsg(_('The page must be called from suppliers selected interface, please click following link to select the supplier'),'error');
	echo '<a href="' . $RootPath . '/SelectSupplier.php">'. _('Select Supplier') . '</a>';
	include('includes/footer.php');
	exit;
}
if (isset($_GET['SupplierName'])) {
	$SupplierName = $_GET['SupplierName'];
} 
if (!isset($_POST['SupplierRef']) OR trim($_POST['SupplierRef'])=='') {
	$_POST['SupplierRef'] = '';
	if (empty($_POST['GRNBatchNo']) AND empty($_POST['InvoiceNo'])) {
		$_POST['GRNBatchNo'] = '';
		$_POST['InvoiceNo'] = '';
	} elseif (!empty($_POST['GRNBatchNo']) AND !empty($_POST['InvoiceNo'])) {
		$_POST['InvoiceNo'] = '';
	}
} elseif (isset($_POST['GRNBatchNo']) OR isset($_POST['InvoiceNo'])) {
	$_POST['GRNBatchNo'] = '';
	$_POST['InvoiceNo'] = '';
}
echo '<p class="page_title_text">' . _('Supplier Invoice and Delivery Note Inquiry') . '<img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" alt="" />' . _('Supplier') . ': ' . $SupplierName . '</p>';
echo '<div class="page_help_text">' . _('The supplier\'s delivery note is prefer to GRN No, and GRN No is prefered to Invoice No').'</div>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="SelectedSupplier" value="' . $SupplierID . '" />';
	
echo '<table class="selection">
	<tr>
		<td class="label">' . _('Part of Supplier\'s Delivery Note') . ':</td>
		<td><input type="text" name="SupplierRef" value="' . $_POST['SupplierRef'] . '" size="20" maxlength="30" ></td>
		<td class="label">' . _('GRN No') . ':</td><td><input type="text" name="GRNBatchNo" value="' . $_POST['GRNBatchNo'] . '" size="6" maxlength="6" /></td>
		<td class="label">' . _('Invoice No') . ':</td><td><input type="text" name="InvoiceNo" value="' . $_POST['InvoiceNo'] . '" size="11" maxlength="11" /></td>
	
	</tr>
	</table>';
echo '<div class="center">
		<input type="submit" name="Submit" value="' . _('Submit') . '" />
	</div>';
if (isset($_POST['Submit'])) {
	$Where = '';
	if (isset($_POST['SupplierRef']) AND trim($_POST['SupplierRef']) != '') {
		$SupplierRef = trim($_POST['SupplierRef']);
		$WhereSupplierRef = " AND grns.supplierref LIKE '%" . $SupplierRef . "%'";
		$Where .= $WhereSupplierRef;
	} elseif (isset($_POST['GRNBatchNo']) AND trim($_POST['GRNBatchNo']) != '') {
		$GRNBatchNo = trim($_POST['GRNBatchNo']);
		$WhereGRN = " AND grnbatch LIKE '%" . $GRNBatchNo . "%'";
		$Where .= $WhereGRN;
	} elseif (isset($_POST['InvoiceNo']) AND (trim($_POST['InvoiceNo']) != '')) {
		$InvoiceNo = trim($_POST['InvoiceNo']);
		$WhereInvoiceNo = " AND suppinv LIKE '%" . $InvoiceNo . "%'";
		$Where .= $WhereInvoiceNo;
	}
	$sql = "SELECT grnbatch, grns.supplierref, suppinv,purchorderdetails.orderno 
		FROM grns INNER JOIN purchorderdetails ON grns.podetailitem=purchorderdetails.podetailitem 
		LEFT JOIN suppinvstogrn ON grns.grnno=suppinvstogrn.grnno 
		WHERE supplierid='" . $SupplierID . "'" . $Where;
	$ErrMsg = _('Failed to retrieve supplier invoice and grn data');
	$result = DB_query($sql,$ErrMsg);
	if (DB_num_rows($result)>0) {
		echo '<table class="selection">
			<tr>
				<th>' . _('Supplier Delivery Note') . '</th>
				<th>' . _('GRN Batch No') . '</th>
				<th>' . _('PO No') . '</th>
				<th>' . _('Invoice No') . '</th>
			</tr>';
		$k = 0;
		while ($myrow = DB_fetch_array($result)){
			if ($k == 0) {
				echo '<tr class="EvenTableRows">';
				$k = 1;
			} else{
				echo '<tr class="OddTableRows">';
				$k = 0;
			}
				echo '<td class="ascending">' . $myrow['supplierref'] . '</td>
					<td class="ascending"><a href="' . $RootPath .'/PDFGrn.php?GRNNo=' . $myrow['grnbatch'] . '&amp;PONo=' . $myrow['orderno'] . '">' . $myrow['grnbatch']. '</td>
					<td class="ascending">' . $myrow['orderno'] . '</td>
					<td class="ascending">' . $myrow['suppinv'] . '</td>
				</tr>';

		}
		echo '</table><br/>';

	}

}
include('includes/footer.php');
?>
