<?php
/* $Id$*/

include('includes/session.php');
$Title = _('Claim Petty Cash Expenses From Tab');
/* webERP manual links before header.php */
$ViewTopic = 'PettyCash';
$BookMark = 'ExpenseClaim';
include('includes/header.php');
if (isset($_POST['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])) {
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}
if (isset($_POST['SelectedIndex'])) {
	$SelectedIndex = $_POST['SelectedIndex'];
} elseif (isset($_GET['SelectedIndex'])) {
	$SelectedIndex = $_GET['SelectedIndex'];
}
if (isset($_POST['Days'])) {
	$Days = filter_number_format($_POST['Days']);
} elseif (isset($_GET['Days'])) {
	$Days = filter_number_format($_GET['Days']);
}
if (isset($_POST['Cancel'])) {
	unset($SelectedTabs);
	unset($SelectedIndex);
	unset($Days);
	unset($_POST['Amount']);
	unset($_POST['Notes']);
	unset($_POST['Receipt']);
}
if (isset($_POST['Process'])) {
	if ($_POST['SelectedTabs'] == '') {
		echo prnMsg(_('You have not selected a tab to claim the expenses on'), 'error');
		unset($SelectedTabs);
	}
}
if (isset($_POST['Go'])) {
	if ($Days <= 0) {
		prnMsg(_('The number of days must be a positive number'), 'error');
		$Days = 30;
	}
}
if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	//first off validate inputs sensible
	if ($_POST['SelectedExpense'] == '') {
		$InputError = 1;
		prnMsg(_('You have not selected an expense to claim on this tab'), 'error');
	} elseif ($_POST['Amount'] == 0) {
		$InputError = 1;
		prnMsg(_('The Amount must be greater than 0'), 'error');
	}
	if (!is_date($_POST['Date'])) {
		$InputError = 1;
		prnMsg(_('The date input is not in the correct format'), 'error');
	}
	if (isset($SelectedIndex) and $InputError != 1) {
		$SQL = "UPDATE pcashdetails
			SET date = '" . FormatDateForSQL($_POST['Date']) . "',
				tag = '" . $_POST['Tag'] . "',
				codeexpense = '" . $_POST['SelectedExpense'] . "',
				amount = '" . -filter_number_format($_POST['Amount']) . "',
				notes = '" . $_POST['Notes'] . "',
				receipt = '" . $_POST['Receipt'] . "'
			WHERE counterindex = '" . $SelectedIndex . "'";
		$Msg = _('The expense claim on tab') . ' ' . $SelectedTabs . ' ' . _('has been updated');
		$Result = DB_query($SQL);
		foreach ($_POST as $Index => $Value) {
			if (substr($Index, 0, 5) == 'index') {
				$Index = $Value;
				$SQL = "UPDATE pcashdetailtaxes SET pccashdetail='" . $_POST['PcCashDetail' . $Index] . "',
													calculationorder='" . $_POST['CalculationOrder' . $Index] . "',
													description='" . $_POST['Description' . $Index] . "',
													taxauthid='" . $_POST['TaxAuthority' . $Index] . "',
													purchtaxglaccount='" . $_POST['TaxGLAccount' . $Index] . "',
													taxontax='" . $_POST['TaxOnTax' . $Index] . "',
													taxrate='" . $_POST['TaxRate' . $Index] . "',
													amount='" . -$_POST['TaxAmount' . $Index] . "'
												WHERE counterindex='" . $Index ."'";
				$Result = DB_query($SQL);
			}
		}
		prnMsg($Msg, 'success');
	} elseif ($InputError != 1) {
		// First check the type is not being duplicated
		// Add new record on submit
		$SQL = "INSERT INTO pcashdetails (counterindex,
										tabcode,
										tag,
										date,
										codeexpense,
										amount,
										authorized,
										posted,
										notes,
										receipt)
								VALUES (NULL,
										'" . $_POST['SelectedTabs'] . "',
										'" . $_POST['Tag'] . "',
										'" . FormatDateForSQL($_POST['Date']) . "',
										'" . $_POST['SelectedExpense'] . "',
										'" . -filter_number_format($_POST['Amount']) . "',
										0,
										0,
										'" . $_POST['Notes'] . "',
										'" . $_POST['Receipt'] . "'
										)";
		$Msg = _('The expense claim on tab') . ' ' . $_POST['SelectedTabs'] . ' ' . _('has been created');
		$Result = DB_query($SQL);
		$PcCashDetail = DB_Last_Insert_ID($db, 'pcashdetails', 'counterindex');
		foreach ($_POST as $Index => $Value) {
			if (substr($Index, 0, 5) == 'index') {
				$Index = $Value;
				$SQL = "INSERT INTO pcashdetailtaxes (counterindex,
														pccashdetail,
														calculationorder,
														description,
														taxauthid,
														purchtaxglaccount,
														taxontax,
														taxrate,
														amount
												) VALUES (
														NULL,
														'" . $PcCashDetail . "',
														'" . $_POST['CalculationOrder' . $Index] . "',
														'" . $_POST['Description' . $Index] . "',
														'" . $_POST['TaxAuthority' . $Index] . "',
														'" . $_POST['TaxGLAccount' . $Index] . "',
														'" . $_POST['TaxOnTax' . $Index] . "',
														'" . $_POST['TaxRate' . $Index] . "',
														'" . -$_POST['TaxAmount' . $Index] . "'
												)";
				$Result = DB_query($SQL);
			}
		}
		prnMsg($Msg, 'success');
	}
	if ($InputError != 1) {
		unset($_POST['SelectedExpense']);
		unset($_POST['Amount']);
		unset($_POST['Tag']);
		unset($_POST['Date']);
		unset($_POST['Notes']);
		unset($_POST['Receipt']);
	}
} elseif (isset($_GET['Delete'])) {
	$SQL = "DELETE FROM pcashdetails, pcashdetailtaxes
				USING pcashdetails
				INNER JOIN pcashdetailtaxes
				ON pcashdetails.counterindex = pcashdetailtaxes.pccashdetail
				WHERE pcashdetails.counterindex = '" . $SelectedIndex . "'";
	$ErrMsg = _('Petty Cash Expense record could not be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
	prnMsg(_('Petty Cash expense record') . ' ' . $SelectedTabs . ' ' . _('has been deleted'), 'success');
	unset($_GET['Delete']);
} //end of get delete
if (!isset($SelectedTabs)) {
	/* It could still be the first time the page has been run and a record has been selected for modification - SelectedTabs will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
	then none of the above are true and the list of sales types will be displayed with
	links to delete or edit each. These will call the same page again and allow update/input
	or deletion of the records*/
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Payment Entry'), '" alt="" />', ' ', $Title, '
		</p>';
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<table class="selection">
			<tr>
				<td>', _('Clain expenses on petty cash tab'), ':</td>
				<td><select required="required" name="SelectedTabs">';
	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE usercode='" . $_SESSION['UserID'] . "'";
	$Result = DB_query($SQL);
	echo '<option value="">', _('Not Yet Selected'), '</option>';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	} //end while loop
	echo '</select>
			</td>
		</tr>';
	echo '</table>'; // close main table
	echo '<div class="centre">
			<input type="submit" name="Process" value="', _('Accept'), '" />
			<input type="submit" name="Cancel" value="', _('Cancel'), '" />
		</div>';
	echo '</form>';
} else { // isset($SelectedTabs)
	echo '<div class="centre">
			<a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">', _('Select another tab'), '</a>
		</div>';
	echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/money_add.png" title="', _('Petty Cash Claim Entry'), '" alt="" />', ' ', $Title, '
		</p>';
	if (!isset($_GET['edit']) or isset($_POST['GO'])) {
		if (!isset($Days)) {
			$Days = 30;
		}
		/* Retrieve decimal places to display */
		$SQLDecimalPlaces = "SELECT decimalplaces
					FROM currencies,pctabs
					WHERE currencies.currabrev = pctabs.currency
						AND tabcode='" . $SelectedTabs . "'";
		$Result = DB_query($SQLDecimalPlaces);
		$MyRow = DB_fetch_array($Result);
		$CurrDecimalPlaces = $MyRow['decimalplaces'];
		echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		echo '<br /><table class="selection">';
		echo '<tr>
				<td>' . _('Petty Cash Tab') . ':</td>
				<td>' . $SelectedTabs . '</td>
			  </tr>';
		echo '</table>';
		echo '<table class="selection">
				<tr>
					<th colspan="9">', _('Detail of Tab Movements For Last '), ':
						<input type="hidden" name="SelectedTabs" value="' . $SelectedTabs . '" />
						<input type="text" class="number" name="Days" value="', $Days, '" required="required" maxlength="3" size="4" /> ', _('Days'), '
						<input type="submit" name="Go" value="', _('Go'), '" />
					</th>
				</tr>';
		if (isset($_POST['Cancel'])) {
			unset($_POST['SelectedExpense']);
			unset($_POST['Amount']);
			unset($_POST['Date']);
			unset($_POST['Notes']);
			unset($_POST['Receipt']);
		}
		$SQL = "SELECT counterindex,
						tabcode,
						tag,
						date,
						codeexpense,
						amount,
						authorized,
						posted,
						notes,
						receipt
					FROM pcashdetails
					WHERE tabcode='" . $SelectedTabs . "'
						AND date >=DATE_SUB(CURDATE(), INTERVAL " . $Days . " DAY)
					ORDER BY date,
							counterindex ASC";
		$Result = DB_query($SQL);
		echo '<tr>
				<th>', _('Date of Expense'), '</th>
				<th>', _('Expense Code'), '</th>
				<th>', _('Gross Amount'), '</th>
				<th>', _('Tax'), '</th>
				<th>', _('Tax Group'), '</th>
				<th>', _('Tag'), '</th>
				<th>', _('Notes'), '</th>
				<th>', _('Receipt'), '</th>
				<th>', _('Date Authorised'), '</th>
			</tr>';
		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			$SQLDes = "SELECT description
						FROM pcexpenses
						WHERE codeexpense='" . $MyRow['codeexpense'] . "'";
			$ResultDes = DB_query($SQLDes);
			$Description = DB_fetch_array($ResultDes);
			
			if (!isset($Description[0])) {
				$ExpenseCodeDes = 'ASSIGNCASH';
			} else {
					$ExpenseCodeDes = $MyRow['codeexpense'] . ' - ' . $Description[0];
			}
			
			if ($MyRow['authorized'] == '0000-00-00') {
				$AuthorisedDate = _('Unauthorised');
			} else {
				$AuthorisedDate = ConvertSQLDate($MyRow['authorized']);
			}
			
			$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
			$TagResult = DB_query($TagSQL);
			$TagRow = DB_fetch_array($TagResult);
			if ($MyRow['tag'] == 0) {
				$TagRow['tagdescription'] = _('None');
			}
			$TagTo = $MyRow['tag'];
			$TagDescription = $TagTo . ' - ' . $TagRow['tagdescription'];
		
			$TaxesDescription = '';
			$TaxesTaxAmount = '';
			$TaxSQL = "SELECT counterindex,
								pccashdetail,
								calculationorder,
								description,
								taxauthid,
								purchtaxglaccount,
								taxontax,
								taxrate,
								amount
							FROM pcashdetailtaxes
							WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
			$TaxResult = DB_query($TaxSQL);
			while ($MyTaxRow = DB_fetch_array($TaxResult)) {
				$TaxesDescription .= $MyTaxRow['description'] . '<br />';
				$TaxesTaxAmount .= locale_number_format($MyTaxRow['amount'], $CurrDecimalPlaces) . '<br />';
			}
				if (($MyRow['authorized'] == '0000-00-00') and ($ExpenseCodeDes != 'ASSIGNCASH')) {
					// only movements NOT authorised can be modified or deleted
					echo '<td>', ConvertSQLDate($MyRow['date']), '</td>
							<td>', $ExpenseCodeDes, '</td>
							<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>
							<td class="number">', $TaxesTaxAmount, '</td>
							<td>', $TaxesDescription, '</td>
							<td>', $TagDescription, '</td>
							<td>', $MyRow['notes'], '</td>
							<td>', $MyRow['receipt'], '</td>
							<td>', $AuthorisedDate, '</td>
							<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedIndex=', $MyRow['counterindex'], '&SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;edit=yes">' . _('Edit') . '</a></td>
							<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedIndex=', $MyRow['counterindex'], '&amp;SelectedTabs=' . $SelectedTabs . '&amp;Days=' . $Days . '&amp;Delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this code and the expenses it may have set up?') . '");\'>' . _('Delete') . '</a></td>
						</tr>';
				} else {
					echo '<td>', ConvertSQLDate($MyRow['date']), '</td>
							<td>', $ExpenseCodeDes, '</td>
							<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>
							<td class="number">', $TaxesTaxAmount, '</td>
							<td>', $TaxesDescription, '</td>
							<td>', $MyRow['tag'], ' - ', $TagRow['tagdescription'], '</td>
							<td>', $MyRow['notes'], '</td>
							<td>', $MyRow['receipt'], '</td>
							<td>', $AuthorisedDate, '</td>
						</tr>';
				}
		}
		//END WHILE LIST LOOP
		$SQLAmount = "SELECT sum(amount)
					FROM pcashdetails
					WHERE tabcode='" . $SelectedTabs . "'";
		$ResultAmount = DB_query($SQLAmount);
		$Amount = DB_fetch_array($ResultAmount);
		if (!isset($Amount['0'])) {
			$Amount['0'] = 0;
		}
		echo '<tr>
				<td colspan="2" class="number">', _('Current balance'), ':</td>
				<td class="number">', locale_number_format($Amount['0'], $CurrDecimalPlaces), '</td>
			</tr>';
		echo '</table>';
		echo '</form>';
	}
	if (!isset($_GET['Delete'])) {
		echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
		echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
		if (isset($_GET['edit'])) {
			$SQL = "SELECT counterindex,
							tabcode,
							tag,
							date,
							codeexpense,
							amount,
							authorized,
							posted,
							notes,
							receipt
				FROM pcashdetails
				WHERE counterindex='" . $SelectedIndex . "'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$_POST['Date'] = ConvertSQLDate($MyRow['date']);
			$_POST['SelectedExpense'] = $MyRow['codeexpense'];
			$_POST['Amount'] = -$MyRow['amount'];
			$_POST['Notes'] = $MyRow['notes'];
			$_POST['Tag'] = $MyRow['tag'];
			$_POST['Receipt'] = $MyRow['receipt'];
			echo '<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />';
			echo '<input type="hidden" name="SelectedIndex" value="', $SelectedIndex, '" />';
			echo '<input type="hidden" name="Days" value="', $Days, '" />';
		} //end of Get Edit
		if (!isset($_POST['Date'])) {
			$_POST['Date'] = Date($_SESSION['DefaultDateFormat']);
		}
		echo '<table class="selection">
				<tr>
					<td>', _('Date of Expense'), ':</td>
					<td>
						<input type="text" class="date" alt="', $_SESSION['DefaultDateFormat'], '" name="Date" size="10" required="required" maxlength="10" value="', $_POST['Date'], '" />
					</td>
				</tr>
				<tr>
					<td>', _('Code of Expense'), ':</td>
					<td>
						<select required="required" name="SelectedExpense">';
		DB_free_result($Result);
		$SQL = "SELECT pcexpenses.codeexpense,
					pcexpenses.description,
					pctabs.defaulttag
			FROM pctabexpenses, pcexpenses, pctabs
			WHERE pctabexpenses.codeexpense = pcexpenses.codeexpense
				AND pctabexpenses.typetabcode = pctabs.typetabcode
				AND pctabs.tabcode = '" . $SelectedTabs . "'
			ORDER BY pcexpenses.codeexpense ASC";
		$Result = DB_query($SQL);
		echo '<option value="">', _('Not Yet Selected'), '</option>';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['SelectedExpense']) and $MyRow['codeexpense'] == $_POST['SelectedExpense']) {
				echo '<option selected="selected" value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], ' - ', $MyRow['description'], '</option>';
			} else {
				echo '<option value="', $MyRow['codeexpense'], '">', $MyRow['codeexpense'], ' - ', $MyRow['description'], '</option>';
			}
			$DefaultTag = $MyRow['defaulttag'];
		} //end while loop
		echo '</select>
				</td>
			</tr>';
		//Select the tag
		echo '<tr>
				<td>', _('Tag'), ':</td>
				<td><select name="Tag">';
		$SQL = "SELECT tagref,
					tagdescription
			FROM tags
			ORDER BY tagref";
		$Result = DB_query($SQL);
		if (!isset($_POST['Tag'])) {
			$_POST['Tag'] = $DefaultTag;
		}
		echo '<option value="0">0 - ', _('None'), '</option>';
		while ($MyRow = DB_fetch_array($Result)) {
			if ($_POST['Tag'] == $MyRow['tagref']) {
				echo '<option selected="selected" value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
			} else {
				echo '<option value="', $MyRow['tagref'], '">', $MyRow['tagref'], ' - ', $MyRow['tagdescription'], '</option>';
			}
		}
		echo '</select>
				</td>
			</tr>';
		// End select tag
		if (!isset($_POST['Amount'])) {
			$_POST['Amount'] = 0;
		}
		echo '<tr>
				<td>', _('Gross Amount'), ':</td>
				<td><input type="text" class="number" name="Amount" size="12" required="required" maxlength="11" value="', $_POST['Amount'], '" /></td>
			</tr>';
		if (isset($_GET['edit'])) {
			$SQL = "SELECT counterindex,
							pccashdetail,
							calculationorder,
							description,
							taxauthid,
							purchtaxglaccount,
							taxontax,
							taxrate,
							amount
						FROM pcashdetailtaxes
						WHERE pccashdetail='" . $SelectedIndex . "'";
			$TaxesResult = DB_query($SQL);
			while ($MyTaxRow = DB_fetch_array($TaxesResult)) {
				echo '<input type="hidden" name="index', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['counterindex'], '" />';
				echo '<input type="hidden" name="PcCashDetail', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['pccashdetail'], '" />';
				echo '<input type="hidden" name="CalculationOrder', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['calculationorder'], '" />';
				echo '<input type="hidden" name="Description', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['description'], '" />';
				echo '<input type="hidden" name="TaxAuthority', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['taxauthid'], '" />';
				echo '<input type="hidden" name="TaxGLAccount', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['purchtaxglaccount'], '" />';
				echo '<input type="hidden" name="TaxOnTax', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['taxontax'], '" />';
				echo '<input type="hidden" name="TaxRate', $MyTaxRow['counterindex'], '" value="', $MyTaxRow['taxrate'], '" />';
				echo '<tr>
						<td>', $MyTaxRow['description'], ' - ', ($MyTaxRow['taxrate'] * 100), '%</td>
						<td><input type="text" class="number" size="12" name="TaxAmount', $MyTaxRow['counterindex'], '" value="', -$MyTaxRow['amount'], '" /></td>
					</tr>';
			}
		} else {
			$SQL = "SELECT taxgrouptaxes.calculationorder,
							taxauthorities.description,
							taxgrouptaxes.taxauthid,
							taxauthorities.purchtaxglaccount,
							taxgrouptaxes.taxontax,
							taxauthrates.taxrate
						FROM taxauthrates
						INNER JOIN taxgrouptaxes
							ON taxauthrates.taxauthority=taxgrouptaxes.taxauthid
						INNER JOIN taxauthorities
							ON taxauthrates.taxauthority=taxauthorities.taxid
						INNER JOIN taxgroups
							ON taxgroups.taxgroupid=taxgrouptaxes.taxgroupid
						INNER JOIN pctabs
							ON pctabs.taxgroupid=taxgroups.taxgroupid
						WHERE taxauthrates.taxcatid = " . $_SESSION['DefaultTaxCategory'] . "
							AND pctabs.tabcode='" . $SelectedTabs . "'
						ORDER BY taxgrouptaxes.calculationorder";
			$TaxResult = DB_query($SQL);
			$i = 0;
			while ($MyTaxRow = DB_fetch_array($TaxResult)) {
				echo '<input type="hidden" name="index', $i, '" value="', $i, '" />';
				echo '<input type="hidden" name="CalculationOrder', $i, '" value="', $MyTaxRow['calculationorder'], '" />';
				echo '<input type="hidden" name="Description', $i, '" value="', $MyTaxRow['description'], '" />';
				echo '<input type="hidden" name="TaxAuthority', $i, '" value="', $MyTaxRow['taxauthid'], '" />';
				echo '<input type="hidden" name="TaxGLAccount', $i, '" value="', $MyTaxRow['purchtaxglaccount'], '" />';
				echo '<input type="hidden" name="TaxOnTax', $i, '" value="', $MyTaxRow['taxontax'], '" />';
				echo '<input type="hidden" name="TaxRate', $i, '" value="', $MyTaxRow['taxrate'], '" />';
				echo '<tr>
						<td>', $MyTaxRow['description'], ' - ', ($MyTaxRow['taxrate'] * 100), '%</td>
						<td><input type="text" class="number" size="12" name="TaxAmount', $i, '" value="0" /></td>
					</tr>';
				++$i;
			}
		}
		if (!isset($_POST['Notes'])) {
			$_POST['Notes'] = '';
		}
		echo '<tr>
				<td>', _('Notes'), ':</td>
				<td>
					<input type="text" name="Notes" size="50" maxlength="49" value="', $_POST['Notes'], '" />
				</td>
			</tr>';
		if (!isset($_POST['Receipt'])) {
			$_POST['Receipt'] = '';
		}
		echo '<tr>
				<td>', _('Receipt'), ':</td>
				<td><input type="text" name="Receipt" size="50" maxlength="49" value="', $_POST['Receipt'], '" /></td>
			</tr>';
		echo '</table>'; // close main table
		echo '<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />';
		echo '<input type="hidden" name="Days" value="', $Days, '" />';
		echo '<div class="centre">
				<input type="submit" name="submit" value="', _('Accept'), '" />
				<input type="submit" name="Cancel" value="', _('Cancel'), '" />
			</div>';
		echo '</form>';
	} // end if user wish to delete
}
include('includes/footer.php');
?>