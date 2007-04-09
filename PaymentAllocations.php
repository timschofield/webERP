<?php

/* $Revision: 1.3 $ */

/*
	This page is called from SupplierInquiry.php when the 'view payments' button is selected
*/


$PageSecurity = 5;

include('includes/session.inc');

$title = _('Payment Allocations');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

	if (!isset($_GET['SuppID'])){
		prnMsg( _('Supplier ID Number is not Set, can not display result'),'warn');
        	include('includes/footer.inc');
        	exit;
	}

	if (!isset($_GET['InvID'])){
		prnMsg( _('Invoice Number is not Set, can not display result'),'warn');
		include('includes/footer.inc');
		exit;
	}
$SuppID = $_GET['SuppID'];
$InvID = $_GET['InvID'];

echo "<BR><CENTER><FONT SIZE=4 COLOR=BLUE>Payment Allocation for Supplier: '$SuppID' and Invoice: '$InvID'</FONT>";

//	$_SESSION['SuppID'] = new SupplierID;
//	$_SESSION['InvID'] = new InvoiceID;

$SQL= "SELECT supptrans.supplierno,
		supptrans.suppreference,
		supptrans.trandate,
		supptrans.alloc
	FROM supptrans
	WHERE supptrans.id IN (SELECT suppallocs.transid_allocfrom
				FROM supptrans, suppallocs
				WHERE supptrans.supplierno = '$SuppID'
				AND supptrans.suppreference = '$InvID'
				AND supptrans.id = suppallocs.transid_allocto)";

/*
Might be a way of doing this query without a subquery

$SQL= "SELECT supptrans.supplierno,
		supptrans.suppreference,
		supptrans.trandate,
		supptrans.alloc
	FROM supptrans INNER JOIN suppallocs ON supptrans.id=suppallocs.transid_allocfrom
	WHERE supptrans.supplierno = '$SuppID'
	AND supptrans.suppreference = '$InvID'
*/

$Result = DB_query($SQL, $db);
if (DB_num_rows($Result) == 0){
	prnMsg(_('There may be a problem retrieving the information. No data is returned'),'warn');
	echo '<BR><A HREF ="javascript:history.back()">' . _('Go back') . '</A>';
	include('includes/foooter.inc');
	exit;
}

echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100% BORDER=0>';
$TableHeader = "<TR>
<TD CLASS='tableheader'>" . _('Supplier Number') . '<BR>' . _('Reference') . "</TD>
<TD CLASS='tableheader'>" . _('Payment') .'<BR>' . _('Reference') . "</TD>
<TD CLASS='tableheader'>" . _('Payment') . '<BR>' . _('Date') . "</TD>
<TD CLASS='tableheader'>" . _('Total Payment') . '<BR>' . _('Amount') .	'</TD></TR>';

echo $TableHeader;

$j=1;
$k=0; //row colour counter
  while ($myrow = DB_fetch_array($Result)) {
	if ($k == 1){
		echo '<TR BGCOLOR="#CCCCCC">';
		$k = 0;
	} else {
		echo '<TR BGCOLOR="#EEEEEE">';
		$k++;
	}

	printf('<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		<TD>%s</TD>
		</TR>',
		$myrow['supplierno'],
		$myrow['suppreference'],
		ConvertSQLDate($myrow['trandate']),
		$myrow['alloc']	);

		$j++;
		If ($j == 18){
                $j=1;
                echo $TableHeader;
       }

}
  echo '</TABLE></CENTER>';

include('includes/footer.inc');
?>