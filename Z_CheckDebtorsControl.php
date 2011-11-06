<?php
/* $Id$*/

include('includes/session.inc');
$title=_('Debtors Control Integrity');
include('includes/header.inc');


//
//========[ SHOW OUR FORM ]===========
//

	// Page Border
	echo '<table border=1 width=100%><tr><td bgcolor="#FFFFFF">';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	// Context Navigation and Title
	echo '<table width=100%>
			<td width=37% align=left><a href="'. $rootpath . '/index.php?&Application=AR">' . _('Back to Customers') . '</a></td>
			<td align=left><font size=4 color=blue><u><b>' . _('Debtors Control Integrity') . '</b></u></font></td>
	      </table><p>';

	echo '<table border=1>'; //Main table
	echo '<td><table>'; // First column

	$DefaultFromPeriod = ( !isset($_POST['FromPeriod']) OR $_POST['FromPeriod']=='' ) ? 1 : $_POST['FromPeriod'];

	if ( !isset($_POST['ToPeriod']) OR $_POST['ToPeriod']=='' )
	{
			$SQL = "SELECT Max(periodno) FROM periods";
			$prdResult = DB_query($SQL,$db);
			$MaxPrdrow = DB_fetch_row($prdResult);
			DB_free_result($prdResult);
			$DefaultToPeriod = $MaxPrdrow[0];
	} else {
			$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<tr>
			<td>' . _('Start Period:') . '</td>
			<td><select name="FromPeriod">';
	
	$ToSelect = '<tr><td>' . _('End Period:') .'</td>
					<td><select name="ToPeriod">';

	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno";
	$perResult = DB_query($SQL,$db);

	while ( $perRow=DB_fetch_array($perResult) ) {
		$FromSelected = ( $perRow['periodno'] == $DefaultFromPeriod ) ? 'selected' : '';
		echo '<option ' . $FromSelected . ' value="' . $perRow['periodno'] . '">' .MonthAndYearFromSQLDate($perRow['lastdate_in_period']) .'</option>';

		$ToSelected = ( $perRow['periodno'] == $DefaultToPeriod ) ? 'selected' : '';
		$ToSelect .= '<option ' . $ToSelected . ' value="' . $perRow['periodno'] . '">' . MonthAndYearFromSQLDate($perRow['lastdate_in_period']) .'</option>';
	}
	DB_free_result($perResult);
	echo '</select></td></tr>';

	echo '</table></td>'; // End First column
	echo '<td><table>'; // Start Second column

	echo $ToSelect . '</select></td></tr>';

	echo '</table></td>'; // End Second column
	echo '</table>'; //End the main table

	echo '<p><input type="submit" name="Show" value="'._('Accept').'" />';
	echo '<input type="submit" action="reset" value="' . _('Cancel') .'" />';


	if ( isset($_POST['Show']) )	{
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

		$CurPeriod = $_POST['FromPeriod'];
		$GLOpening = $invTotal = $RecTotal = $GLClosing = $CalcTotal = $DiffTotal = 0;
		$j=0;

		while ( $CurPeriod <= $_POST['ToPeriod'] ) {
			$SQL = "SELECT bfwd,
					actual
				FROM chartdetails
				WHERE period = " . $CurPeriod . "
				AND accountcode=" . $_SESSION['CompanyRecord']['debtorsact'];
			$dtResult = DB_query($SQL,$db);
			$dtRow = DB_fetch_array($dtResult);
			DB_free_result($dtResult);

			$GLOpening += $dtRow['bfwd'];
			$glMovement = $dtRow['bfwd'] + $dtRow['actual'];

			if ($j==1) {
				echo '<tr class="OddTableRows">';
				$j=0;
			} else {
				echo '<tr class="EvenTableRows">';
				$j++;
			}
			echo '<td>' . $CurPeriod . '</td>
					<td class="number">' . locale_number_format($dtRow['bfwd'],2) . '</td>';

			$SQL = "SELECT SUM((ovamount+ovgst)/rate) AS totinvnetcrds
					FROM debtortrans
					WHERE prd = '" . $CurPeriod . "'
					AND (type=10 OR type=11)";
			$invResult = DB_query($SQL,$db);
			$invRow = DB_fetch_array($invResult);
			DB_free_result($invResult);

			$invTotal += $invRow['totinvnetcrds'];

			echo '<td class="number">' . locale_number_format($invRow['totinvnetcrds'],2) . '</td>';

			$SQL = "SELECT SUM((ovamount+ovgst)/rate) AS totreceipts
					FROM debtortrans
					WHERE prd = '" . $CurPeriod . "'
					AND type=12";
			$recResult = DB_query($SQL,$db);
			$recRow = DB_fetch_array($recResult);
			DB_free_result($recResult);

			$RecTotal += $recRow['totreceipts'];
			$CalcMovement = $dtRow['bfwd'] + $invRow['totinvnetcrds'] + $recRow['totreceipts'];

			echo '<td class="number">' . locale_number_format($recRow['totreceipts'],2) . '</td>';

			$GLClosing += $glMovement;
			$CalcTotal += $CalcMovement;
			$DiffTotal += $diff;

			$diff = ( $dtRow['bfwd'] == 0 ) ? 0 : round($glMovement,2) - round($CalcMovement,2);
			$color = ( $diff == 0 OR $dtRow['bfwd'] == 0 ) ? 'green' : 'red';

			echo '<td class="number">' . locale_number_format($glMovement,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number">' . locale_number_format(($CalcMovement),$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td class="number" bgcolor="white"><font color="' . $color . '">' . locale_number_format($diff,$_SESSION['CompanyRecord']['decimalplaces']) . '</font></td>
			</tr>';
			$CurPeriod++;
		}

		$difColor = ( $DiffTotal == 0 ) ? 'green' : 'red';

		echo '<tr bgcolor=white>
				<td>' . _('Total') . '</td>
				<td class="number">' . locale_number_format($GLOpening,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($invTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($RecTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($GLClosing,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($CalcTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number"><font color="' . $difColor . '">' . locale_number_format($DiffTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</font></td>
			</tr>';
		echo '</table></form>';
	}

include('includes/footer.inc');

?>