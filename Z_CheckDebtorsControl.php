<?php

include('includes/session.php');
$Title=__('Debtors Control Integrity');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');


//
//========[ SHOW OUR FORM ]===========
//

    // Context Navigation and Title
    echo '<a href="'. $RootPath . '/index.php?&amp;Application=AR">' . __('Back to Customers') . '</a>';
    echo '<div class="centre"><h3>' . $Title . '</h3></div>';

	// Page Border
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';

	$DefaultFromPeriod = ( !isset($_POST['FromPeriod']) OR $_POST['FromPeriod']=='' ) ? 1 : $_POST['FromPeriod'];

	if ( !isset($_POST['ToPeriod']) OR $_POST['ToPeriod']=='' )
	{
			$SQL = "SELECT Max(periodno) FROM periods";
			$prdResult = DB_query($SQL);
			$MaxPrdrow = DB_fetch_row($prdResult);
			DB_free_result($prdResult);
			$DefaultToPeriod = $MaxPrdrow[0];
	} else {
			$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<tr>
			<td>' . __('Start Period:') . '</td>
			<td><select name="FromPeriod">';

	$ToSelect = '<tr><td>' . __('End Period:')  . '</td>
					<td><select name="ToPeriod">';

	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno";
	$perResult = DB_query($SQL);

	while ( $perRow=DB_fetch_array($perResult) ) {
		$FromSelected = ( $perRow['periodno'] == $DefaultFromPeriod ) ? 'selected="selected"' : '';
		echo '<option ' . $FromSelected . ' value="' . $perRow['periodno'] . '">' .MonthAndYearFromSQLDate($perRow['lastdate_in_period'])  . '</option>';

		$ToSelected = ( $perRow['periodno'] == $DefaultToPeriod ) ? 'selected="selected"' : '';
		$ToSelect .= '<option ' . $ToSelected . ' value="' . $perRow['periodno'] . '">' . MonthAndYearFromSQLDate($perRow['lastdate_in_period'])  . '</option>';
	}
	DB_free_result($perResult);
	echo '</select></td></tr>';

	echo $ToSelect . '</select></td></tr>';

	echo '</table>';

	echo '<br /><input type="submit" name="Show" value="'.__('Accept').'" />';
	echo '<input type="submit" value="' . __('Cancel') .'" />';


	if ( isset($_POST['Show']) )	{
		//
		//========[ SHOW SYNOPSYS ]===========
		//
		echo '<br /><table border="1">';
		echo '<tr>
				<th>' . __('Period') . '</th>
				<th>' . __('Bal B/F in GL') . '</th>
				<th>' . __('Invoices') . '</th>
				<th>' . __('Receipts') . '</th>
				<th>' . __('Bal C/F in GL') . '</th>
				<th>' . __('Calculated') . '</th>
				<th>' . __('Difference') . '</th>
			</tr>';

		$CurPeriod = $_POST['FromPeriod'];
		$GLOpening = $invTotal = $RecTotal = $GLClosing = $CalcTotal = $DiffTotal = 0;

		while ( $CurPeriod <= $_POST['ToPeriod'] ) {
			$SQL = "SELECT bfwd,
					actual
				FROM chartdetails
				WHERE period = " . $CurPeriod . "
				AND accountcode=" . $_SESSION['CompanyRecord']['debtorsact'];
			$dtResult = DB_query($SQL);
			$dtRow = DB_fetch_array($dtResult);
			DB_free_result($dtResult);

			$GLOpening += $dtRow['bfwd'];
			$glMovement = $dtRow['bfwd'] + $dtRow['actual'];

			echo '<tr class="striped_row">
					<td>' . $CurPeriod . '</td>
					<td class="number">' . locale_number_format($dtRow['bfwd'],2) . '</td>';

			$SQL = "SELECT SUM((ovamount+ovgst+ovfreight+ovdiscount)/rate) AS totinvnetcrds
					FROM debtortrans
					WHERE prd = '" . $CurPeriod . "'
					AND (type=10 OR type=11)";
			$invResult = DB_query($SQL);
			$invRow = DB_fetch_array($invResult);
			DB_free_result($invResult);

			$invTotal += $invRow['totinvnetcrds'];

			echo '<td class="number">' . locale_number_format($invRow['totinvnetcrds'],2) . '</td>';

			$SQL = "SELECT SUM((ovamount+ovgst+ovfreight+ovdiscount)/rate) AS totreceipts
					FROM debtortrans
					WHERE prd = '" . $CurPeriod . "'
					AND type=12";
			$recResult = DB_query($SQL);
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
					<td class="number" style="background-color:white;color:' . $color . '">' . locale_number_format($diff,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
			$CurPeriod++;
		}

		$difColor = ( $DiffTotal == 0 ) ? 'green' : 'red';

		echo '<tr style="bgcolor:white">
				<td>' . __('Total') . '</td>
				<td class="number">' . locale_number_format($GLOpening,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($invTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($RecTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($GLClosing,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($CalcTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
				<td class="number" style="color=' . $difColor . '">' . locale_number_format($DiffTotal,$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
			</tr>';
		echo '</table>';
	}
    echo '</div>
          </form>';

include('includes/footer.php');
