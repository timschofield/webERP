<?php
/* $Revision: 1.6 $ */
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('Delete Sales Transactions');
include('includes/header.inc');

if (isset($_POST['ProcessDeletions'])){

	if ($_POST['SalesAnalysis']=='on'){

		prnMsg(_('Deleting sales analysis records'),'info');

		$sql = 'TRUNCATE TABLE SalesAnalysis';
		$ErrMsg = _('The SQL to delete Sales Analysis records failed because');
		$Result = DB_query($sql,$db,$ErrMsg);
	}
	if ($_POST['DebtorTrans']=='on'){

		prnMsg(_('Deleting customer statement transactions and allocation records'),'info');

		$ErrMsg = _('The SQL to delete customer transaction records failed because');

		$Result = DB_query('TRUNCATE TABLE CustAllocns',$db,$ErrMsg);
		$Result = DB_query('TRUNCATE TABLE DebtorTrans',$db,$ErrMsg);
		$Result = DB_query('TRUNCATE TABLE StockSerialMoves',$db,$ErrMsg);
		$Result = DB_query('DELETE FROM StockMoves WHERE Type=10 OR Type=11',$db,$ErrMsg);

		$ErrMsg = _('The SQL to update the transaction numbers for all sales transactions because');
		$sql = 'UPDATE SysTypes SET TypeNo =0 WHERE TypeID =10 OR TypeID=11 OR TypeID=15 OR TypeID=12';
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['SalesOrders']=='on'){

		prnMsg(_('Deleting all sales order records'),'info');

		$ErrMsg = _('The SQL to delete sales order detail records failed because');
		$Result = DB_query('TRUNCATE TABLE SalesOrderDetails',$db);

		$Result = DB_query('TRUNCATE TABLE OrderDeliveryDifferencesLog',$db);

		$ErrMsg = _('The SQL to delete sales order header records failed because');
		$Result = DB_query('TRUNCATE TABLE SalesOrders',$db,$ErrMsg);


		$sql = 'UPDATE SysTypes SET TypeNo =0 WHERE TypeID =30';
		$ErrMsg = _('The SQL to update the transaction number of sales orders has failed') . ', ' . _('the SQL statement was');
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['ZeroStock']=='on'){

		prnMsg (_('Making stock for all parts and locations nil'),'info');

		$sql = 'UPDATE LocStock SET Quantity=0';
		$Result = DB_query($sql,$db);
		$ErrMsg = _('The SQL to make all stocks zero failed because');

	}
	if ($_POST['ZeroSalesOrders']=='on'){

		prnMsg(_('Making the quantity invoiced zero on all orders'),'info');

		$sql = 'UPDATE SalesOrderDetails SET QtyInvoiced=0, Completed=0';
		$ErrMsg =_('The SQL to un-invoice all sales orders failed');
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['SalesGL']=='on'){

		prnMsg(_('Deleting all sales related GL Transactions'),'info');

		$sql = 'DELETE FROM GLTrans WHERE Type>=10 and Type <=15';
		$ErrMsg = _('The SQL to delete sales related GL Transactions failed');
		$Result = DB_query($sql,$db,$ErrMsg);
	}

	if ($_POST['StockGL']=='on'){

		prnMsg(_('Deleting all stock related GL Transactions'),'info');

		$sql = 'DELETE FROM GLTrans WHERE Type=25 OR Type=17 OR Type=26 OR Type=28';
		$ErrMsg = _('The SQL to delete stock related GL Transactions failed');
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['ZeroPurchOrders']=='on'){

		prnMsg(_('Zeroing all purchase order quantities received and uncompleting all purchase orders'),'info');

		$sql = 'UPDATE PurchOrderDetails SET QuantityRecd=0, Completed=0';
		$ErrMsg = _('The SQL to zero quantity received for all purchase orders line items and uncompleted all purchase order line items because');
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['GRNs']=='on'){

		prnMsg(_('Deleting all GRN records'),'info');

		$ErrMsg = _('The SQL to delete Sales Analysis records failed because');
		$Result = DB_query('TRUNCATE TABLE GRNs',$db,$ErrMsg);

		$ErrMsg = _('The SQL to update the transaction number of stock receipts has failed because');
		$Result = DB_query('UPDATE SysTypes SET TypeID =1 WHERE TypeNo =25',$db,$ErrMsg);
	}
	if ($_POST['PurchOrders']=='on'){

		prnMsg(_('Deleting all Purchase Orders'),'info');

		$ErrMsg = _('The SQL to delete all purchase order details failed, the SQL statement was');
		$Result = DB_query('DELETE FROM PurchOrderDetails',$db,$ErrMsg);

		$ErrMsg = _('The SQL to delete all purchase orders failed because');
		$Result = DB_query('DELETE FROM PurchOrders',$db,$ErrMsg);

		$ErrMsg = _('The SQL to update the transaction number of stock receipts has failed because');
		$Result = DB_query('UPDATE SysTypes SET TypeNo=0 WHERE TypeID =18',$db,$ErrMsg);

	}
	if ($_POST['ReceiptStockMoves']=='on'){

		prnMsg (_('Deleting all stock movements for receipt of stocks'),'info');

		$ErrMsg =_('The SQL to delete all stock movements for the receipt of goods failed because');
		$Result = DB_query('DELETE FROM StockMoves WHERE Type=25',$db,$ErrMsg);

		$ErrMsg = _('The SQL to reinitialise to 0 the transaction number of stock adjustments and location transfers has failed because');
		$Result = DB_query('UPDATE SysTypes SET TypeNo=0 WHERE TypeID =16 OR TypeID=17',$db,$ErrMsg);

	}

}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?=' . $SID . "' METHOD=POST>";

echo '<P><CENTER><TABLE>
	<TR><TD>' . _('Delete All Sales Analysis') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=SalesAnalysis></TD>
	</TR>
	<TR><TD>' . _('Delete All statement transactions') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=DebtorTrans></TD>
	</TR>
	<TR><TD>' . _('Zero All stock balances') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=ZeroStock></TD>
	</TR>
	<TR><TD>' . _('Make Invoiced Qty Of All Sales Orders Nil') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=ZeroSalesOrders></TD>
	</TR>
	<TR><TD>' . _('Delete All Sales Orders') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=SalesOrders></TD>
	</TR>
	<TR><TD>' . _('Zero Received Qty of all purchase orders') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=ZeroPurchOrders></TD>
	</TR>
	<TR><TD>' . _('Delete All Purchase Orders') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=PurchOrders></TD>
	</TR>
	<TR><TD>' . _('Delete All Sales related stock movements') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=SalesStockMoves></TD>
	</TR>
	<TR><TD>' . _('Delete All Stock Receipt stock movements') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=ReceiptStockMoves></TD>
	</TR>
	<TR><TD>' . _('Delete All Sales GL Transactions') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=SalesGL></TD>
	</TR>
	<TR><TD>' . _('Delete All Stock GL Transactions') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=StockGL></TD>
	</TR>
	<TR><TD>' . _('Delete All PO Goods Received (GRNs)') . '</TD>
		<TD><INPUT TYPE=checkbox NAME=GRNs></TD>
	</TR>
</TABLE>';

echo '<INPUT TYPE=SUBMIT NAME=ProcessDeletions VALUE=' . _('Process') . '>';

echo '</FORM>';

include('includes/footer.inc');
?>
