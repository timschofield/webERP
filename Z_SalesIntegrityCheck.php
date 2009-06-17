<?php

/* $Revision: 1.3 $ */

// Script to do some Sales Integrity checks
// No SQL updates or Inserts - so safe to run

$PageSecurity=15;

include ('includes/session.inc');
$title = _('Sales Integrity');
include('includes/header.inc');


echo '<div class="centre"><font size=4 color=blue><U><b>' . _('Sales Integrity Check') . '</b></U></font></div>';

echo '<br><br>'._('Check every Invoice has a Sales Order').'<br>';
echo '<br><br>'._('Check every Invoice has a Tax Entry').'<br>';
echo '<br><br>'._('Check every Invoice has a GL Entry').'<br>';
$SQL = 'SELECT id, transno, order_, trandate FROM debtortrans WHERE type = 10';
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = 'SELECT orderno, orddate FROM salesorders WHERE orderno = ' . $myrow['order_'];
	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
		echo '<br>'._('Invoice '). ' '. $myrow['transno'] . ' : ';
		echo '<font color=RED>' . _('No Sales Order') . '</font>';
	}

	$SQL3 = 'SELECT debtortransid FROM debtortranstaxes WHERE debtortransid = ' . $myrow['id'];
	$Result3 = DB_query($SQL3,$db);

	if ( DB_num_rows($Result3) == 0) {
		echo '<br>'. _('Invoice '). ' ' . $myrow['transno'] . ' : ';
		echo '<font color=RED>' . _('Has no Tax Entry') . '</font>';
	}

	$SQL4 = 'SELECT typeno 
				FROM gltrans 
				WHERE type = 10 
				AND typeno = ' . $myrow['transno'];
	$Result4 = DB_query($SQL4,$db);

	if ( DB_num_rows($Result4) == 0) {
		echo '<br>' . _('Invoice') . ' ' . $myrow['transno'] . ' : ';
		echo '<font color=RED>' . _('has no GL Entry') . '</font>';
	}
}


echo '<br><br>'._('Check for orphan GL Entries').'<br>';
$SQL = 'SELECT DISTINCT typeno, counterindex FROM gltrans WHERE type = 10';
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = 'SELECT id, 
					transno, 
					trandate 
				FROM debtortrans 
				WHERE type = 10 
				AND transno = ' . $myrow['typeno'];
	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
			echo "<br>"._('GL Entry ') . $myrow['counterindex'] . " : ";
			echo ', <font color=RED>'._('Invoice ') . $myrow['typeno'] . _(' could not be found').'</font>';
	}
}

echo '<br><br>'._('Check Receipt totals').'<br>';
$SQL = 'SELECT typeno, 
				amount 
		FROM gltrans 
		WHERE type = 12 
		AND account = ' . $_SESSION['CompanyRecord']['debtorsact'];
		
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = 'SELECT SUM((ovamount+ovgst)/rate) 
			FROM debtortrans 
			WHERE type = 12 
			AND transno = ' . $myrow['typeno'];
			
	$Result2 = DB_query($SQL2,$db);
	$myrow2 = DB_fetch_row($Result2);

	if ( $myrow2[0] + $myrow['amount'] == 0 ) {
			echo '<br>'._('Receipt') . ' ' . $myrow['typeno'] . " : ";
			echo '<font color=RED>' . $myrow['amount']. ' ' . _('in GL but found'). ' ' . $myrow2[0] . ' ' . _('in debtorstrans').'</font>';
	}
}

echo '<br><br>'._('Check for orphan Receipts')."<br>";
$SQL = 'SELECT transno FROM debtortrans WHERE type = 12';
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = 'SELECT amount FROM gltrans WHERE type = 12 AND typeno = ' . $myrow['transno'];
	$Result2 = DB_query($SQL2,$db);
	$myrow2 = DB_fetch_row($Result2);

	if ( !$myrow2[0] ) {
		echo '<br>'._('Receipt') . ' ' . $myrow['transno'] . " : ";
		echo '<font color=RED>' . $myrow['transno'] . ' ' ._('not found in GL')."</font>";
	}
}


echo '<br><br>'._('Check for orphan Sales Orders').'<br>';
$SQL = 'SELECT orderno, orddate FROM salesorders';
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = 'SELECT transno, 
					order_, 
					trandate 
				FROM debtortrans 
				WHERE type = 10 
				AND order_ = ' . $myrow['orderno'];
				
	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
		echo '<br>'._('Sales Order') . ' ' . $myrow['orderno'] . ' : ';
		echo '<font color=RED>'._('Has no Invoice').'</font>';
	}
}

echo '<br><br>'._('Check for orphan Order Items').'<br>';
echo '<br><br>'._('Check Order Item Amounts').'<br>';
$SQL = 'SELECT orderno FROM salesorderdetails';
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = 'SELECT orderno, orddate FROM salesorders WHERE orderno = ' . $myrow['orderno'];
	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
			echo '<br>'._('Order Item') . ' ' . $myrow['orderno'] . ' : ';
			echo ', <font color=RED>'._('Has no Sales Order').'</font>';
	}

	$sumsql = 'SELECT SUM( qtyinvoiced * unitprice ) AS InvoiceTotal 
				FROM salesorderdetails
				WHERE orderno = ' . $myrow['orderno'];
	$sumresult = DB_query($sumsql,$db);

	if ($sumrow = DB_fetch_array($sumresult)) {
		$invSQL = 'SELECT transno, 
							type, 
							trandate, 
							settled, 
							rate, 
							ovamount, 
							ovgst
				 	FROM debtortrans WHERE order_ = ' . $myrow['orderno'];
		$invResult = DB_query($invSQL,$db);

		while( $invrow = DB_fetch_array($invResult) ) {
			// Ignore credit notes
			if ( $invrow['type'] != 11 ) {
					// Do an integrity check on sales order items
					if ( $sumrow['InvoiceTotal'] != $invrow['ovamount'] ) {
						echo '<br><font color=red>' . _('Debtors trans') . ' ' . $invrow['ovamount'] . ' ' . _('differ from salesorderdetails') . ' ' . $sumrow['InvoiceTotal'] . '</font>';
					}
			}
		}
	}
}


echo '<br><br>'._('Check for orphan Stock Moves').'<br>';
$SQL = 'SELECT stkmoveno, transno FROM stockmoves';
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = 'SELECT transno, 
					order_, 
					trandate 
				FROM debtortrans 
				WHERE type BETWEEN 10 AND 11 
				AND transno = ' . $myrow['transno'];
				
	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
			echo '<br>'._('Stock Move') . ' ' . $myrow['stkmoveno'] . ' : ';
			echo ', <font color=RED>'._('Has no Invoice').'</font>';
	}
}


echo '<br><br>'._('Check for orphan Tax Entries').'<br>';
$SQL = 'SELECT debtortransid FROM debtortranstaxes';
$Result = DB_query($SQL,$db);

while ($myrow = DB_fetch_array($Result)) {
	$SQL2 = 'SELECT id, transno, trandate FROM debtortrans WHERE type BETWEEN 10 AND 11 AND id = ' . $myrow['debtortransid'];
	$Result2 = DB_query($SQL2,$db);

	if ( DB_num_rows($Result2) == 0) {
			echo '<br>'._('Tax Entry') . ' ' . $myrow['debtortransid'] . ' : ';
			echo ', <font color=RED>'._('Has no Invoice').'</font>';
	}
}

echo '<br><br>'._('Sales Integrity Check completed.').'<br><br>';

prnMsg(_('Sales Integrity Check completed.'),'info');

include('includes/footer.inc');
?>