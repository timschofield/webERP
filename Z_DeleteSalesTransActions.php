<?php
/* $Revision: 1.8 $ */
/*Script to Delete all sales transactions*/

$PageSecurity=15;
include ('includes/session.inc');
$title = _('Delete Sales Transactions');
include('includes/header.inc');

if (isset($_POST['ProcessDeletions'])){

	if ($_POST['SalesAnalysis']=='on'){

		prnMsg(_('Deleting sales analysis records'),'info');

		$sql = 'TRUNCATE TABLE salesanalysis';
		$ErrMsg = _('The SQL to delete Sales Analysis records failed because');
		$Result = DB_query($sql,$db,$ErrMsg);
	}
	if ($_POST['DebtorTrans']=='on'){

		prnMsg(_('Deleting customer statement transactions and allocation records'),'info');

		$ErrMsg = _('The SQL to delete customer transaction records failed because');

		$Result = DB_query('TRUNCATE TABLE custallocns',$db,$ErrMsg);
		$Result = DB_query('DELETE FROM debtortrans',$db,$ErrMsg);
		$Result = DB_query('DELETE FROM stockserialmoves',$db,$ErrMsg);
		$Result = DB_query('DELETE FROM stockmoves WHERE type=10 OR type=11',$db,$ErrMsg);

		$ErrMsg = _('The SQL to update the transaction numbers for all sales transactions because');
		$sql = 'UPDATE systypes SET typeno =0 WHERE typeid =10 OR typeid=11 OR typeid=15 OR typeid=12';
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['SalesOrders']=='on'){

		prnMsg(_('Deleting all sales order records'),'info');

		$ErrMsg = _('The SQL to delete sales order detail records failed because');
		$Result = DB_query('DELETE FROM salesorderdetails',$db);

		$Result = DB_query('DELETE FROM orderdeliverydifferenceslog',$db);

		$ErrMsg = _('The SQL to delete sales order header records failed because');
		$Result = DB_query('DELETE FROM salesorders',$db,$ErrMsg);


		$sql = 'UPDATE systypes SET typeno =0 WHERE typeid =30';
		$ErrMsg = _('The SQL to update the transaction number of sales orders has failed') . ', ' . _('the SQL statement was');
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['ZeroStock']=='on'){

		prnMsg (_('Making stock for all parts and locations nil'),'info');

		$sql = 'UPDATE locstock SET quantity=0';
		$Result = DB_query($sql,$db);
		$ErrMsg = _('The SQL to make all stocks zero failed because');

	}
	if ($_POST['ZeroSalesOrders']=='on'){

		prnMsg(_('Making the quantity invoiced zero on all orders'),'info');

		$sql = 'UPDATE salesorderdetails SET qtyinvoiced=0, completed=0';
		$ErrMsg =_('The SQL to un-invoice all sales orders failed');
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['SalesGL']=='on'){

		prnMsg(_('Deleting all sales related GL Transactions'),'info');

		$sql = 'DELETE FROM gltrans WHERE type>=10 AND type <=15';
		$ErrMsg = _('The SQL to delete sales related GL Transactions failed');
		$Result = DB_query($sql,$db,$ErrMsg);
	}

	if ($_POST['StockGL']=='on'){

		prnMsg(_('Deleting all stock related GL Transactions'),'info');

		$sql = 'DELETE FROM gltrans WHERE type=25 OR type=17 OR type=26 OR type=28';
		$ErrMsg = _('The SQL to delete stock related GL Transactions failed');
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['ZeroPurchOrders']=='on'){

		prnMsg(_('Zeroing all purchase order quantities received and uncompleting all purchase orders'),'info');

		$sql = 'UPDATE purchorderdetails SET quantityrecd=0, completed=0';
		$ErrMsg = _('The SQL to zero quantity received for all purchase orders line items and uncompleted all purchase order line items because');
		$Result = DB_query($sql,$db,$ErrMsg);

	}
	if ($_POST['GRNs']=='on'){

		prnMsg(_('Deleting all GRN records'),'info');

		$ErrMsg = _('The SQL to delete Sales Analysis records failed because');
		$Result = DB_query('DELETE FROM grns',$db,$ErrMsg);

		$ErrMsg = _('The SQL to update the transaction number of stock receipts has failed because');
		$Result = DB_query('UPDATE SysTypes SET typeid =1 WHERE typeno =25',$db,$ErrMsg);
	}
	if ($_POST['PurchOrders']=='on'){

		prnMsg(_('Deleting all Purchase Orders'),'info');

		$ErrMsg = _('The SQL to delete all purchase order details failed, the SQL statement was');
		$Result = DB_query('DELETE FROM purchorderdetails',$db,$ErrMsg);

		$ErrMsg = _('The SQL to delete all purchase orders failed because');
		$Result = DB_query('DELETE FROM purchorders',$db,$ErrMsg);

		$ErrMsg = _('The SQL to update the transaction number of stock receipts has failed because');
		$Result = DB_query('UPDATE systypes SET typeno=0 WHERE typeid =18',$db,$ErrMsg);

	}
	if ($_POST['ReceiptStockMoves']=='on'){

		prnMsg (_('Deleting all stock movements for receipt of stocks'),'info');

		$ErrMsg =_('The SQL to delete all stock movements for the receipt of goods failed because');
		$Result = DB_query('DELETE FROM stockmoves WHERE type=25',$db,$ErrMsg);

		$ErrMsg = _('The SQL to reinitialise to 0 the transaction number of stock adjustments and location transfers has failed because');
		$Result = DB_query('UPDATE systypes SET typeno=0 WHERE typeid =16 OR typeid=17',$db,$ErrMsg);

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
