<?php
/* $Revision: 1.9 $ */
$PageSecurity=15;

include('includes/session.inc');
$title=_('Debtors Control Integrity');
include('includes/header.inc');


//
//========[ SHOW OUR FORM ]===========
//

	// Page Border
	echo '<table border=1 width=100%><tr><td bgcolor="#FFFFFF">';
	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	// Context Navigation and Title
	echo '<table width=100%>
			<td width=37% align=left><a href="'. $rootpath . '/index.php?&Application=AR'. SID .'">' . _('Back to Customers') . '</a></td>
			<td align=left><font size=4 color=blue><u><b>' . _('Debtors Control Integrity') . '</b></u></font></td>
	      </table><p>';

	echo '<table border=1>'; //Main table
	echo '<td><table>'; // First column

	$DefaultFromPeriod = ( !isset($_POST['FromPeriod']) OR $_POST['FromPeriod']=='' ) ? 1 : $_POST['FromPeriod'];

	if ( !isset($_POST['ToPeriod']) OR $_POST['ToPeriod']=='' )
	{
			$SQL = 'SELECT Max(periodno) FROM periods';
			$prdResult = DB_query($SQL,$db);
			$MaxPrdrow = DB_fetch_row($prdResult);
			DB_free_result($prdResult);
			$DefaultToPeriod = $MaxPrdrow[0];
	} else {
			$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<tr><td>' . _('Start Period:') . '</td><td><select name="FromPeriod">';
	$toSelect = '<tr><td>' . _('End Period:') .'</td><td><select name="ToPeriod">';

	$SQL = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno';
	$perResult = DB_query($SQL,$db);

	while ( $perRow=DB_fetch_array($perResult) )
	{
		$fromSelected = ( $perRow['periodno'] == $DefaultFromPeriod ) ? 'selected' : '';
		echo '<option ' . $fromSelected . ' value="' . $perRow['periodno'] . '">' .MonthAndYearFromSQLDate($perRow['lastdate_in_period']);

		$toSelected = ( $perRow['periodno'] == $DefaultToPeriod ) ? 'selected' : '';
		$toSelect .= '<option ' . $toSelected . ' value="' . $perRow['periodno'] . '">' . MonthAndYearFromSQLDate($perRow['lastdate_in_period']);
	}
	DB_free_result($perResult);
	echo '</select></td></tr>';

	echo '</table></td>'; // End First column
	echo '<td><table>'; // Start Second column

	echo $toSelect . '</select></td></tr>';

	echo '</table></td>'; // End Second column
	echo '</table>'; //End the main table

	echo "<p><input type=submit name='Show' value='"._('Accept')."'>";
	echo '<input type=submit action=reset value="' . _('Cancel') .'">';


	if ( isset($_POST['Show']) )
	{
		//
		//========[ SHOW SYNOPSYS ]===========
		//
		echo '<p><table border=1>';
		echo '<tr>
				<th>' . _('Period') . '</th>
				<th>' . _('Bal B/F in GL') . '</th>
				<th>' . _('Invoices') . '</th>
				<th>' . _('Receipts') . '</th>
				<th>' . _('Bal C/F in GL') . '</th>
				<th>' . _('Calculated') . '</th>
				<th>' . _('Difference') . '</th>
			</tr>';

		$curPeriod = $_POST['FromPeriod'];
		$glOpening = $invTotal = $recTotal = $glClosing = $calcTotal = $difTotal = 0;
		$j=0;

		while ( $curPeriod <= $_POST['ToPeriod'] )
		{
			$SQL = "SELECT bfwd,
						actual
					FROM chartdetails
					WHERE period = " . $curPeriod . "
					AND accountcode=" . $_SESSION['CompanyRecord']['debtorsact'];
			$dtResult = DB_query($SQL,$db);
			$dtRow = DB_fetch_array($dtResult);
			DB_free_result($dtResult);

			$glOpening += $dtRow['bfwd'];
			$glMovement = $dtRow['bfwd'] + $dtRow['actual'];

			if ($j==1) {
				echo '<tr class="OddTableRows">';
				$j=0;
			} else {
				echo '<tr class="EvenTableRows">';
				$j++;
			}
			echo "<td>" . $curPeriod . "</td>
					<td align=right>" . number_format($dtRow['bfwd'],2) . "</td>";

			$SQL = "SELECT SUM((ovamount+ovgst)/rate) AS totinvnetcrds
					FROM debtortrans
					WHERE prd = " . $curPeriod . "
					AND (type=10 OR type=11)";
			$invResult = DB_query($SQL,$db);
			$invRow = DB_fetch_array($invResult);
			DB_free_result($invResult);

			$invTotal += $invRow['totinvnetcrds'];

			echo '<td align=right>' . number_format($invRow['totinvnetcrds'],2) . '</td>';

			$SQL = "SELECT SUM((ovamount+ovgst)/rate) AS totreceipts
					FROM debtortrans
					WHERE prd = " . $curPeriod . "
					AND type=12";
			$recResult = DB_query($SQL,$db);
			$recRow = DB_fetch_array($recResult);
			DB_free_result($recResult);

			$recTotal += $recRow['totreceipts'];
			$calcMovement = $dtRow['bfwd'] + $invRow['totinvnetcrds'] + $recRow['totreceipts'];

			echo '<td align=right>' . number_format($recRow['totreceipts'],2) . '</td>';

			$glClosing += $glMovement;
			$calcTotal += $calcMovement;
			$difTotal += $diff;

			$diff = ( $dtRow['bfwd'] == 0 ) ? 0 : round($glMovement,2) - round($calcMovement,2);
			$color = ( $diff == 0 OR $dtRow['bfwd'] == 0 ) ? 'green' : 'red';

			echo '<td align=right>' . number_format($glMovement,2) . '</td>
					<td align=right>' . number_format(($calcMovement),2) . '</td>
					<td align=right bgcolor=white><font color="' . $color . '">' . number_format($diff,2) . '</font></td>
			</tr>';
			$curPeriod++;
		}

		$difColor = ( $difTotal == 0 ) ? 'green' : 'red';

		echo '<tr bgcolor=white>
				<td>' . _('Total') . '</td>
				<td align=right>' . number_format($glOpening,2) . '</td>
				<td align=right>' . number_format($invTotal,2) . '</td>
				<td align=right>' . number_format($recTotal,2) . '</td>
				<td align=right>' . number_format($glClosing,2) . '</td>
				<td align=right>' . number_format($calcTotal,2) . '</td>
				<td align=right><font color="' . $difColor . '">' . number_format($difTotal,2) . '</font></td>
			</tr>';
		echo '</table></form>';
	}

echo '</td></tr></table>'; // end Page Border
include('includes/footer.inc');

?>