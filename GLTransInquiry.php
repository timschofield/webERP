<?php

/* $Revision: 1.16 $ */

$PageSecurity = 8;

include ('includes/session.inc');
$title = _('General Ledger Transaction Inquiry');
include('includes/header.inc');

// Page Border
echo '<table border=1 width=100%><tr><td bgcolor="#FFFFFF"><div class="centre">';
$menuUrl = '<a href="'. $rootpath . '/index.php?&Application=GL'. SID .'">' . _('General Ledger Menu') . '</a></div>';

if ( !isset($_GET['TypeID']) OR !isset($_GET['TransNo']) )
{
		prnMsg(_('This page requires a valid transaction type and number'),'warn');
		echo $menuUrl;
} else {
		$typeSQL = 'SELECT typename,
							typeno
					FROM systypes
					WHERE typeid = ' . $_GET['TypeID'];

		$TypeResult = DB_query($typeSQL,$db);

		if ( DB_num_rows($TypeResult) == 0 ){
				prnMsg(_('No transaction of this type with id') . ' ' . $_GET['TypeID'],'error');
				echo $menuUrl;
		} else {
				$myrow = DB_fetch_row($TypeResult);
				DB_free_result($TypeResult);
				$TransName = $myrow[0];

				// Context Navigation and Title
				echo '<table width=100%>
						<td width=40% align=left>' . $menuUrl. '</td>
						<td align=left><font size=4 color=blue><u><b>' . $TransName . ' ' . $_GET['TransNo'] . '</b></u></font></td>
				      </table><p>';

				//
				//========[ SHOW SYNOPSYS ]===========
				//
				echo '<table border=1>'; //Main table
				echo '<tr>
						<th>' . _('Date') . '</th>
						<th>' . _('Period') .'</th>
						<th>'. _('GL Account') .'</th>
						<th>'. _('Debits') .'</th>
						<th>'. _('Credits') .'</th>
						<th>' . _('Description') .'</th>
						<th>'. _('Posted') . '</th>
					</tr>';

				$SQL = "SELECT gltrans.type,
							gltrans.trandate,
							gltrans.periodno,
							gltrans.account,
							gltrans.narrative,
							gltrans.amount,
							gltrans.posted,
							chartmaster.accountname
						FROM gltrans,
							chartmaster
						WHERE gltrans.account = chartmaster.accountcode
						AND gltrans.type= " . $_GET['TypeID'] . "
						AND gltrans.typeno = " . $_GET['TransNo'] . "
						ORDER BY gltrans.counterindex";
				$TransResult = DB_query($SQL,$db);

				$Posted = _('Yes');
				$CreditTotal = $DebitTotal = 0;

				while ( $TransRow = DB_fetch_array($TransResult) ) {
					$TranDate = ConvertSQLDate($TransRow['trandate']);
					$DetailResult = false;

					if ( $TransRow['amount'] > 0) {
							$DebitAmount = number_format($TransRow['amount'],2);
							$DebitTotal += $TransRow['amount'];
							$CreditAmount = '&nbsp';
					} else {
							$CreditAmount = number_format(-$TransRow['amount'],2);
							$CreditTotal += $TransRow['amount'];
							$DebitAmount = '&nbsp';
					}

					if ( $TransRow['account'] == $_SESSION['CompanyRecord']['debtorsact'] )	{
							$URL = $rootpath . '/CustomerInquiry.php?' . SID . '&CustomerID=';
							$date = '&TransAfterDate=' . $TranDate;

							$DetailSQL = 'SELECT debtortrans.debtorno,
											debtortrans.ovamount,
											debtortrans.ovgst,
											debtortrans.rate,
											debtorsmaster.name
											FROM debtortrans,
											debtorsmaster
											WHERE debtortrans.debtorno = debtorsmaster.debtorno
											AND debtortrans.type = ' . $TransRow['type'] . '
											AND debtortrans.transno = ' . $_GET['TransNo'];
							$DetailResult = DB_query($DetailSQL,$db);
					} elseif ( $TransRow['account'] == $_SESSION['CompanyRecord']['creditorsact'] )	{
							$URL = $rootpath . '/SupplierInquiry.php?' . SID . '&SupplierID=';
							$date = '&FromDate=' . $TranDate;

							$DetailSQL = 'SELECT supptrans.supplierno,
											supptrans.ovamount,
											supptrans.ovgst,
											supptrans.rate,
											suppliers.suppname
											FROM supptrans,
											suppliers
											WHERE supptrans.supplierno = suppliers.supplierid
											AND supptrans.type = ' . $TransRow['type'] . '
											AND supptrans.transno = ' . $_GET['TransNo'];
							$DetailResult = DB_query($DetailSQL,$db);
					} else {
							$URL = $rootpath . '/GLAccountInquiry.php?' . SID . '&Account=' . $TransRow['account'];

							if( strlen($TransRow['narrative'])==0 ) {
								$TransRow['narrative'] = '&nbsp';
							}
							if ( $TransRow['posted']==0 )	{
								$Posted = _('No');
							}
							$j=0;
							if ($j==1) {
								echo '<tr class="OddTableRows">';
								$j=0;
							} else {
								echo '<tr class="EvenTableRows">';
								$j++;
							}
							echo	'<td>' . $TranDate . '</td>
									<td class=number>' . $TransRow['periodno'] . '</td>
									<td><a href="' . $URL . '">' . $TransRow['accountname'] . '</a></td>
									<td class=number>' . $DebitAmount . '</td>
									<td class=number>' . $CreditAmount . '</td>
									<td>' . $TransRow['narrative'] . '</td>
									<td>' . $Posted . '</td>
								</tr>';
					}

					if ($DetailResult) {
						while ( $DetailRow = DB_fetch_row($DetailResult) ) {
							if ( $TransRow['amount'] > 0){
									$Debit = number_format(($DetailRow[1] + $DetailRow[2]) / $DetailRow[3],2);
									$Credit = '&nbsp';
							} else {
									$Credit = number_format(-($DetailRow[1] + $DetailRow[2]) / $DetailRow[3],2);
									$Debit = '&nbsp';
							}

							if ($j==1) {
								echo '<tr class="OddTableRows">';
								$j=0;
							} else {
								echo '<tr class="EvenTableRows">';
								$j++;
							}
							echo	'<td>' . $TranDate . '</td>
									<td class=number>' . $TransRow['periodno'] . '</td>
									<td><a href="' . $URL . $DetailRow[0] . $date . '">' . $TransRow['accountname']  . ' - ' . $DetailRow[4] . '</a></td>
									<td class=number>' . $Debit . '</td>
									<td class=number>' . $Credit . '</td>
									<td>' . $TransRow['narrative'] . '</td>
									<td>' . $Posted . '</td>
								</tr>';
						}
						DB_free_result($DetailResult);
					}
				}
				DB_free_result($TransResult);

				echo '<tr bgcolor="#FFFFFF">
						<td class=number colspan=3><b>' . _('Total') . '</b></td>
						<td class=number>' . number_format(($DebitTotal),2) . '</td>
						<td class=number>' . number_format((-$CreditTotal),2) . '</td>
						<td colspan=2>&nbsp</td>
					</tr>';
				echo '</table><p>';
		}

}

echo '</td></tr></table>'; // end Page Border
include('includes/footer.inc');

?>