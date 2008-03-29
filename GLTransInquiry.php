<?php

/* $Revision: 1.11 $ */

$PageSecurity = 8;

include ('includes/session.inc');
$title = _('General Ledger Transaction Inquiry');
include('includes/header.inc');

// Page Border
echo '<table border=1 width=100%><tr><td bgcolor="#FFFFFF"><center>';
$menuUrl = '<a href="'. $rootpath . '/index.php?&Application=GL'. SID .'">' . _('General Ledger Menu') . '</a>';

if ( !isset($_GET['TypeID']) OR !isset($_GET['TransNo']) )
{
		prnMsg(_('This page requires a valid transaction type and number'),'warn');
		echo $menuUrl;
} else {
		$typeSQL = "SELECT typename,
					typeno
				FROM systypes
				WHERE typeid = " . $_GET['TypeID'];
		$TypeResult = DB_query($typeSQL,$db);

		if ( DB_num_rows($TypeResult) == 0 )
		{
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
						<th>'. _('--- Debits ---') .'</th>
						<th>'. _('--- Credits ---') .'</th>
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
				$transResult = DB_query($SQL,$db);

				$Posted = _('Yes');
				$CreditTotal = $DebitTotal = 0;

				while ( $transRow = DB_fetch_array($transResult) )
				{
					$tranDate = ConvertSQLDate($transRow["trandate"]);
					$detailResult = false;

					if ( $transRow['amount'] > 0)
					{
							$DebitAmount = number_format($transRow['amount'],2);
							$DebitTotal += $transRow['amount'];
							$CreditAmount = '&nbsp';
					} else {
							$CreditAmount = number_format(-$transRow['amount'],2);
							$CreditTotal += $transRow['amount'];
							$DebitAmount = '&nbsp';
					}

					if ( $transRow['account'] == $_SESSION['CompanyRecord']['debtorsact'] )
					{
							$URL = $rootpath . '/CustomerInquiry.php?' . SID . '&CustomerID=';
							$date = '&TransAfterDate=' . $tranDate;

							$detailSQL = "SELECT debtortrans.debtorno,
											debtortrans.ovamount,
											debtorsmaster.name
										FROM debtortrans,
											debtorsmaster
										WHERE debtortrans.debtorno = debtorsmaster.debtorno
										AND debtortrans.type = 12
										AND debtortrans.transno = " . $_GET['TransNo'];
							$detailResult = DB_query($detailSQL,$db);
					}
					elseif ( $transRow['account'] == $_SESSION['CompanyRecord']['creditorsact'] )
					{
							$URL = $rootpath . '/SupplierInquiry.php?' . SID . '&SupplierID=';
							$date = '&FromDate=' . $tranDate;

							$detailSQL = "SELECT supptrans.supplierno,
											supptrans.ovamount,
											suppliers.suppname
										FROM supptrans,
											suppliers
										WHERE supptrans.supplierno = suppliers.supplierid
										AND supptrans.type = 22
										AND supptrans.transno = " . $_GET['TransNo'];
							$detailResult = DB_query($detailSQL,$db);
					} else {
							$URL = $rootpath . '/GLAccountInquiry.php?' . SID . '&Account=' . $transRow['account'];

							if( !$transRow['narrative'] )
							{
								$transRow['narrative'] = '&nbsp';
							}
							if ( !$transRow['posted'] )
							{
								$Posted = _('No');
							}

							echo '<tr>
									<td>' . $tranDate . '</td>
									<td align=right>' . $transRow['periodno'] . '</td>
									<td><a href="' . $URL . '">' . $transRow['accountname'] . '</a></td>
									<td align=right>' . $DebitAmount . '</td>
									<td align=right>' . $CreditAmount . '</td>
									<td>' . $transRow['narrative'] . '</td>
									<td>' . $Posted . '</td>
								</tr>';
					}

					if ($detailResult)
					{
						while ( $detailRow = DB_fetch_row($detailResult) )
						{
							if ( $transRow['amount'] > 0)
							{
									$Debit = number_format($detailRow[1],2);
									$Credit = '&nbsp';
							} else {
									$Credit = number_format(-$detailRow[1],2);
									$Debit = '&nbsp';
							}

							echo '<tr>
									<td>' . $tranDate . '</td>
									<td align=right>' . $transRow['periodno'] . '</td>
									<td><a href="' . $URL . $detailRow[0] . $date . '">' . $transRow['accountname']  . ' - ' . $detailRow[2] . '</a></td>
									<td align=right>' . $Debit . '</td>
									<td align=right>' . $Credit . '</td>
									<td>' . $transRow['narrative'] . '</td>
									<td>' . $Posted . '</td>
								</tr>';
						}
						DB_free_result($detailResult);
					}
				}
				DB_free_result($transResult);

				echo '<tr bgcolor="#FFFFFF">
						<td align=right colspan=3><b>' . _('Total') . '</b></td>
						<td align=right>' . number_format(($DebitTotal),2) . '</td>
						<td align=right>' . number_format((-$CreditTotal),2) . '</td>
						<td colspan=2>&nbsp</td>
					</tr>';
				echo '</table><p>';
		}

}

echo '</center></td></tr></table>'; // end Page Border
include('includes/footer.inc');

?>