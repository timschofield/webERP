<?php

/* $Revision: 1.9 $ */

$PageSecurity = 8;

include ('includes/session.inc');
$title = _('General Ledger Transaction Inquiry');
include('includes/header.inc');

if (!isset($_GET['TypeID']) OR !isset($_GET['TransNo'])) { /*Script was not passed the correct parameters */

	prnMsg(_('The script must be called with a valid transaction type and transaction number to review the general ledger postings for'),'warn');
	echo "<P><A HREF='$rootpath/index.php?". SID ."'>" . _('Back to the menu') . '</A>';
	exit;
}


$SQL = "SELECT typename, typeno FROM systypes WHERE typeid=" . $_GET['TypeID'];

$ErrMsg =_('The transaction type') . ' ' . $_GET['TypeID'] . ' ' . _('could not be retrieved');
$TypeResult = DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($TypeResult)==0){
        prnMsg(_('No transaction type is defined for type') . ' ' . $_GET['TypeID'],'error');
	include('includes/footer.inc');
	exit;
}


$myrow = DB_fetch_row($TypeResult);
$TransName = $myrow[0];
if ($myrow[1]<$_GET['TransNo']){
	prnMsg(_('The transaction number the script was called with is requesting a') . ' ' . $TransName . ' ' . _('beyond the last one entered'),'error');
	include('includes/footer.inc');
	exit;
}

echo '<BR><CENTER><FONT SIZE=4 COLOR=BLUE>'.$TransName.' ' . $_GET['TransNo'] . '</FONT>';


$SQL = "SELECT trandate,
		account,
		periodno,
		accountname,
		narrative,
		amount,
		posted
	FROM gltrans INNER JOIN chartmaster
	ON gltrans.account = chartmaster.accountcode
	WHERE gltrans.type= " . $_GET['TypeID'] . "
	AND gltrans.typeno = " . $_GET['TransNo'] . "
	ORDER BY counterindex";

$ErrMsg = _('The transactions for') . ' ' . $TransName . ' ' . _('number') . ' ' .  $_GET['TransNo'] . ' '. _('could not be retrieved');
$TransResult = DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($TransResult)==0){
        prnMsg(_('No general ledger transactions have been created for') . ' ' . $TransName . ' ' . _('number') . ' ' . $_GET['TransNo'],'info');
	include('includes/footer.inc');
	exit;
}

/*show a table of the transactions returned by the SQL */

echo '<CENTER><TABLE CELLPADDING=2 width=100%>';

$TableHeader = '<TR><TD class="tableheader">' . _('Date') . '</TD>
			<TD class="tableheader">' . _('Period') .'</TD>
			<TD class="tableheader">'. _('Account') .'</TD>
			<TD class="tableheader">'. _('Amount') .'</TD>
			<TD class="tableheader">' . _('Narrative') .'</TD>
			<TD class="tableheader">'. _('Posted') . '</TD></TR>';

echo $TableHeader;

$j = 1;
$k=0; //row colour counter
while ($myrow=DB_fetch_array($TransResult)) {
       if ($k==1){
              echo '<tr bgcolor="#CCCCCC">';
              $k=0;
       } else {
              echo '<tr bgcolor="#EEEEEE">';
              $k++;
       }

       if ($myrow['posted']==0){
       		$Posted = _('No');
	} else {
		$Posted = _('Yes');
	}
       $FormatedTranDate = ConvertSQLDate($myrow["trandate"]);
       printf('<td>%s</td>
       		<td ALIGN=RIGHT>%s</td>
		<td>%s - %s</td>
		<td ALIGN=RIGHT>%s</td>
		<td>%s</td>
		<td>%s</td>
		</tr>',
		$FormatedTranDate,
		$myrow['periodno'],
		$myrow['account'],
		$myrow['accountname'],
		number_format($myrow['amount'],2),
		$myrow['narrative'],
		$Posted);

       $j++;
       If ($j == 18){
		$j=1;
		echo $TableHeader;
       }
}
//end of while loop

echo '</TABLE></CENTER>';

include('includes/footer.inc');

?>