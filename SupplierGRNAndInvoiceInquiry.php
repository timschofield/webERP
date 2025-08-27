<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Invoice and GRN inquiry');
$ViewTopic = 'AccountsPayable';
$BookMark = '';
include('includes/header.php');

if (isset($_GET['SelectedSupplier'])) {
	$SupplierID= $_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])){
	$SupplierID = $_POST['SelectedSupplier'];
} else {
	prnMsg(__('The page must be called from suppliers selected interface, please click following link to select the supplier'),'error');
	echo '<a href="' . $RootPath . '/SelectSupplier.php">'. __('Select Supplier') . '</a>';
	include('includes/footer.php');
	exit();
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
echo '<p class="page_title_text">' . __('Supplier Invoice and Delivery Note Inquiry') . '<img src="' . $RootPath . '/css/' . $Theme . '/images/transactions.png" alt="" />' . __('Supplier') . ': ' . $SupplierName . '</p>';
echo '<div class="page_help_text">' . __('The supplier\'s delivery note is prefer to GRN No, and GRN No is preferred to Invoice No').'</div>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="SelectedSupplier" value="' . $SupplierID . '" />';

echo '<fieldset>
		<legend>', __('Inquiry Criteria'), '</legend>
		<field>
			<label>' . __('Part of Supplier\'s Delivery Note') . ':</label>
			<input type="text" name="SupplierRef" value="' . $_POST['SupplierRef'] . '" size="20" maxlength="30" >
		</field>
		<field>
			<label>' . __('GRN No') . ':</label>
			<input type="text" name="GRNBatchNo" value="' . $_POST['GRNBatchNo'] . '" size="6" maxlength="6" />
		</field>
		<field>
			<label>' . __('Invoice No') . ':</label>
			<input type="text" name="InvoiceNo" value="' . $_POST['InvoiceNo'] . '" size="11" maxlength="11" />
		</field>
	</fieldset>';
echo '<div class="centre">
		<input type="submit" name="Submit" value="' . __('Submit') . '" />
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
	$SQL = "SELECT grnbatch, grns.supplierref, suppinv,purchorderdetails.orderno
		FROM grns INNER JOIN purchorderdetails ON grns.podetailitem=purchorderdetails.podetailitem
		LEFT JOIN suppinvstogrn ON grns.grnno=suppinvstogrn.grnno
		WHERE supplierid='" . $SupplierID . "'" . $Where;
	$ErrMsg = __('Failed to retrieve supplier invoice and grn data');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($Result)>0) {
		echo '<table class="selection">
			<thead>
			<tr>
					<th class="SortedColumn">' . __('Supplier Delivery Note') . '</th>
					<th class="SortedColumn">' . __('GRN Batch No') . '</th>
					<th class="SortedColumn">' . __('PO No') . '</th>
					<th class="SortedColumn">' . __('Invoice No') . '</th>
				</tr>
			</thead>
			<tbody>';

		while ($MyRow = DB_fetch_array($Result)){
			echo '<tr class="striped_row">
				<td>' . $MyRow['supplierref'] . '</td>
				<td><a href="' . $RootPath .'/PDFGrn.php?GRNNo=' . $MyRow['grnbatch'] . '&amp;PONo=' . $MyRow['orderno'] . '">' . $MyRow['grnbatch']. '</td>
				<td>' . $MyRow['orderno'] . '</td>
				<td>' . $MyRow['suppinv'] . '</td>
				</tr>';

		}
		echo '</tbody></table><br/>';

	}

}
include('includes/footer.php');
