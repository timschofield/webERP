<?php

/********************************************************************
*
* KL RICARD: Use receipt text field and PPH21 and PPH23 retention
*            Option to show only unauthorized expenses
*			 Hide tax, tag and purpose fields
*
*********************************************************************/

include('includes/session.php');
$Title = _('Authorisation of Petty Cash Expenses');
/* webERP manual links before header.php */
$ViewTopic = 'PettyCash';
$BookMark = 'AuthorizeExpense';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
// KL RICARD
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');
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
if (isset($_POST['Process'])) {
	if ($SelectedTabs == '') {
		prnMsg(_('You Must First Select a Petty Cash Tab To Authorise'), 'error');
		unset($SelectedTabs);
	}
}
if (isset($_POST['Go'])) {
	if ($Days <= 0) {
		prnMsg(_('The number of days must be a positive number'), 'error');
		$Days = 30;
	}
}

echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', _('Petty Cash'), '" alt="" />', _('Authorisation of Petty Cash Expenses'), '
		</p>';


if (isset($SelectedTabs)) {
	echo '<form><fieldset>';
	echo '<field>
			<label>' . _('Petty Cash Tab') . ':</label>
			<fieldtext>' . $SelectedTabs . '</fieldtext>
		  </field>';
	echo '</fieldset></form>';
}
if (isset($_POST['Submit']) or isset($_POST['update']) or isset($SelectedTabs) or isset($_POST['GO'])) {
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	if (!isset($Days)) {
		$Days = 30;
	}

	//Limit expenses history to X days
	echo '<fieldset>
			<field>
				<label for="SelectedTabs">', _('Detail of Tab Movements For Last '), ':</label>
				<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />
				<input type="text" class="number" name="Days" value="', $Days, '" maxlength="3" size="4" />', _('Days'), '
				<input type="submit" name="Go" value="', _('Go'), '" />
			</field>
		</fieldset>';
	// KL RICARD add the receipt text field
	$SQL = "SELECT pcashdetails.counterindex,
				pcashdetails.tabcode,
				pcashdetails.tag,
				pcashdetails.date,
				pcashdetails.codeexpense,
				pcashdetails.amount,
				pcashdetails.authorized,
				pcashdetails.posted,
				pcashdetails.purpose,
				pcashdetails.notes,
				pcashdetails.receipt,
				pctabs.glaccountassignment,
				pctabs.glaccountpcash,
				pctabs.usercode,
				pctabs.currency,
				currencies.rate,
				currencies.decimalplaces
			FROM pcashdetails, pctabs, currencies
			WHERE pcashdetails.tabcode = pctabs.tabcode
				AND pctabs.currency = currencies.currabrev
				AND pcashdetails.tabcode = '" . $SelectedTabs . "'
				AND pcashdetails.date >= DATE_SUB(CURDATE(), INTERVAL '" . $Days . "' DAY)
				AND pcashdetails.codeexpense<>'ASSIGNCASH'";
	// KL RICARD 
	if (isset($_POST['ShowOnlyUnauthorized'])){
		$SQL .= "AND pcashdetails.authorized = '0000-00-00' ";
	}
	$SQL .=		" ORDER BY pcashdetails.date, pcashdetails.counterindex ASC";$Result = DB_query($SQL);
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">', _('Date of Expense'), '</th>
					<th class="SortedColumn">', _('Expense Code'), '</th>
					<th class="SortedColumn">', _('Amount'), '</th>
					<th>', _('Notes'), '</th>
					<th>', _('Receipt'), '</th>
					<th>', _('Receipt Attachment'), '</th>
					<th class="SortedColumn">', _('Date Authorised'), '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		$CurrDecimalPlaces = $MyRow['decimalplaces'];
		//update database if update pressed
		$PeriodNo = GetPeriod(ConvertSQLDate($MyRow['date']));
		$TagSQL = "SELECT tagdescription FROM tags WHERE tagref='" . $MyRow['tag'] . "'";
		$TagResult = DB_query($TagSQL);
		$TagRow = DB_fetch_array($TagResult);
		if ($MyRow['tag'] == 0) {
			$TagRow['tagdescription'] = _('None');
		}
		$TaxTotalSQL = "SELECT SUM(amount) as totaltax FROM pcashdetailtaxes WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
		$TaxTotalResult = DB_query($TaxTotalSQL);
		$TaxTotalRow = DB_fetch_array($TaxTotalResult);
		if ($MyRow['rate'] == 1) { // functional currency
			$GrossAmount = $MyRow['amount'];
			$NetAmount = $MyRow['amount'] - $TaxTotalRow['totaltax'];
		} else { // other currencies
			$GrossAmount = ($MyRow['amount']) / $MyRow['rate'];
			$NetAmount = ($MyRow['amount'] - $TaxTotalRow['totaltax']) / $MyRow['rate'];
		}
		$Type = 1;
		$NetAmount = -$NetAmount;
		$AccountFrom = $MyRow['glaccountpcash'];
		// KL RICARD add pph21 and pph23 in SQL
		$SQLAccExp = "SELECT glaccount,
							klretentionpph21,
							klretentionpph23,
							tag
						FROM pcexpenses
						WHERE codeexpense = '" . $MyRow['codeexpense'] . "'";
		$ResultAccExp = DB_query($SQLAccExp);
		$MyRowAccExp = DB_fetch_array($ResultAccExp);
		$AccountTo = $MyRowAccExp['glaccount'];
		$TagTo = $MyRow['tag'];
		$TagDescription = $TagTo . ' - ' . $TagRow['tagdescription'];
		// KL RICARD pph21, pph23
		if ($MyRowAccExp['klretentionpph21'] != 0){
			// gross up method
			$HutangPPH21 = round(($NetAmount / (1-($MyRowAccExp['klretentionpph21']/100)))-$NetAmount);
		}else{
			$HutangPPH21 = 0;
		}
		if ($MyRowAccExp['klretentionpph23'] != 0){
			// gross up method
			$HutangPPH23 = round(($NetAmount / (1-($MyRowAccExp['klretentionpph23']/100)))-$NetAmount);
		}else{
			$HutangPPH23 = 0;
		}
		// KL RICARD END pph21, pph23
		
		if (isset($_POST['Submit']) and $_POST['Submit'] == _('Update') and isset($_POST[$MyRow['counterindex']])) {
			//get typeno
			$TypeNo = GetNextTransNo($Type);
			//build narrative
			$Narrative = _('PettyCash') . ' - ' . $MyRow['tabcode'] . ' - ' . $MyRow['codeexpense'] . ' - ' . DB_escape_string($MyRow['notes']);
			//insert to gltrans
			DB_Txn_Begin();
			$SQLFrom = "INSERT INTO `gltrans` (`counterindex`,
											`type`,
											`typeno`,
											`chequeno`,
											`trandate`,
											`periodno`,
											`account`,
											`narrative`,
											`amount`,
											`posted`,
											`jobref`,
											`tag`)
									VALUES (NULL,
											'" . $Type . "',
											'" . $TypeNo . "',
											0,
											'" . $MyRow['date'] . "',
											'" . $PeriodNo . "',
											'" . $AccountFrom . "',
											'" . $Narrative . "',
											'" . $GrossAmount . "',
											0,
											'',
											'" . $TagTo ."')";
			$ResultFrom = DB_Query($SQLFrom, '', '', true);
			$SQLTo = "INSERT INTO `gltrans` (`counterindex`,
										`type`,
										`typeno`,
										`chequeno`,
										`trandate`,
										`periodno`,
										`account`,
										`narrative`,
										`amount`,
										`posted`,
										`jobref`,
										`tag`)
								VALUES (NULL,
										'" . $Type . "',
										'" . $TypeNo . "',
										0,
										'" . $MyRow['date'] . "',
										'" . $PeriodNo . "',
										'" . $AccountTo . "',
										'" . $Narrative . "',
										'" . ($NetAmount + $HutangPPH21 + $HutangPPH23)."',

										0,
										'',
										'" . $TagTo ."')";
			$ResultTo = DB_Query($SQLTo, '', '', true);

			// KL RICARD
			// if there's a PPH21 retention, we account for it
			if ($HutangPPH21 != 0){
				$CompanyExpenses = GLAccountBelongsTo($AccountTo);
				if ($CompanyExpenses == "PTADU"){
					$AccountPPH21 = ACCOUNT_HUTANG_PPH21_PTADU;
				}elseif ($CompanyExpenses == "PTSMH"){
					$AccountPPH21 = ACCOUNT_HUTANG_PPH21_PTSMH;
				}else{
					$AccountPPH21 = ACCOUNT_HUTANG_PPH21_PTBB;
				}
				$SQLHutangPPH21="INSERT INTO `gltrans` (`counterindex`,
												`type`,
												`typeno`,
												`chequeno`,
												`trandate`,
												`periodno`,
												`account`,
												`narrative`,
												`amount`,
												`posted`,
												`jobref`,
												`tag`)
										VALUES (NULL,
												'".$Type."',
												'".$TypeNo."',
												0,
												'".$MyRow['date']."',
												'".$PeriodNo."',
												'". $AccountPPH21 ."',
												'". $Narrative ."',
												'".-$HutangPPH21."',
												0,
												'',
												'" . $TagTo ."')";
				$ResultHutangPPH21 = DB_Query($SQLHutangPPH21,'', '', true);
			}
			
			// if there's a PPH23 retention, we account for it
			if ($HutangPPH23 != 0){
				$CompanyExpenses = GLAccountBelongsTo($AccountTo);
				if ($CompanyExpenses == "PTADU"){
					$AccountPPH23 = ACCOUNT_HUTANG_PPH23_PTADU;
				}elseif ($CompanyExpenses == "PTSMH"){
					$AccountPPH23 = ACCOUNT_HUTANG_PPH23_PTSMH;
				}else{
					$AccountPPH23 = ACCOUNT_HUTANG_PPH23_PTBB;
				}
				$SQLHutangPPH23="INSERT INTO `gltrans` (`counterindex`,
												`type`,
												`typeno`,
												`chequeno`,
												`trandate`,
												`periodno`,
												`account`,
												`narrative`,
												`amount`,
												`posted`,
												`jobref`,
												`tag`)
										VALUES (NULL,
												'".$Type."',
												'".$TypeNo."',
												0,
												'".$MyRow['date']."',
												'".$PeriodNo."',
												'". $AccountPPH23 ."',
												'". $Narrative ."',
												'".-$HutangPPH23."',
												0,
												'',
												'" . $TagTo ."')";
				$ResultHutangPPH23 = DB_Query($SQLHutangPPH23,'', '', true);
			}
			// KL RICARD END

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
				$SQLTo = "INSERT INTO `gltrans` (`counterindex`,
												`type`,
												`typeno`,
												`chequeno`,
												`trandate`,
												`periodno`,
												`account`,
												`narrative`,
												`amount`,
												`posted`,
												`jobref`,
												`tag`)
										VALUES (NULL,
												'" . $Type . "',
												'" . $TypeNo . "',
												0,
												'" . $MyRow['date'] . "',
												'" . $PeriodNo . "',
												'" . $MyTaxRow['purchtaxglaccount'] . "',
												'" . $Narrative . "',
												'" . -$MyTaxRow['amount'] . "',
												0,
												'',
												'" . $TagTo ."')";
				$ResultTax = DB_Query($SQLTo, '', '', true);
			}

			$SQL = "UPDATE pcashdetails
					SET authorized = CURRENT_DATE,
					posted = 1
					WHERE counterindex = '" . $MyRow['counterindex'] . "'";
			$Resultupdate = DB_query($SQL, '', '', true);
			DB_Txn_Commit();
			prnMsg(_('Expenses have been correctly authorised'), 'success');
		}

		$SQLDes = "SELECT description
						FROM pcexpenses
						WHERE codeexpense='" . $MyRow['codeexpense'] . "'";
		$ResultDes = DB_query($SQLDes);
		$Description = DB_fetch_array($ResultDes);
		$ExpenseCodeDes = $MyRow['codeexpense'] . ' - ' . $Description[0];

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

		//Generate download link for expense receipt, or show text if no receipt file is found.
			$ReceiptSupportedExt = array('png','jpg','jpeg','pdf','doc','docx','xls','xlsx'); //Supported file extensions
			$ReceiptDir = $PathPrefix . 'companies/' . $_SESSION['DatabaseName'] . '/expenses_receipts/'; //Receipts upload directory
			$ReceiptSQL = "SELECT hashfile,
									extension
									FROM pcreceipts
									WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
			$ReceiptResult = DB_query($ReceiptSQL);
			$ReceiptRow = DB_fetch_array($ReceiptResult);
			if (DB_num_rows($ReceiptResult) > 0) { //If receipt exists in database
				$ReceiptHash = $ReceiptRow['hashfile'];
				$ReceiptExt = $ReceiptRow['extension'];
				$ReceiptFileName = $ReceiptHash . '.' . $ReceiptExt;
				$ReceiptPath = $ReceiptDir . $ReceiptFileName;
				$ReceiptText = '<a href="' . $ReceiptPath . '" download="ExpenseReceipt-' . mb_strtolower($SelectedTabs) . '-[' . $MyRow['date'] . ']-[' . $MyRow['counterindex'] . ']">' . _('Download attachment') . '</a>';
			} else {
				$ReceiptText = _('No attachment');
			}

		echo '<tr class="striped_row">
			<td>', ConvertSQLDate($MyRow['date']), '</td>
			<td>', $ExpenseCodeDes, '</td>
			<td class="number">', locale_number_format($MyRow['amount'], $CurrDecimalPlaces), '</td>
			<td>', $MyRow['notes'], '</td>
			<td>', $MyRow['receipt'], '</td>
			<td>', $ReceiptText, '</td>';
		if (isset($_POST[$MyRow['counterindex']])) {
			echo '<td>' . ConvertSQLDate(Date('Y-m-d'));
		} else {
			//compare against raw SQL format date, then convert for display.
			if (($MyRow['authorized'] != '0000-00-00')) {
				echo '<td>', ConvertSQLDate($MyRow['authorized']);
			} else {
				echo '<td class="number"><input type="checkbox" name="', $MyRow['counterindex'], '" />';
			}
		}
		echo '<input type="hidden" name="SelectedIndex" value="', $MyRow['counterindex'], '" />
			</td>
		</tr>';
	} //end of looping
	$CurrentBalance = PettyCashTabCurrentBalance($SelectedTabs);
	echo '</tbody>
		<tfoot>
			<tr>
				<td colspan="2" class="number">', _('Current balance'), ':</td>
				<td class="number">', locale_number_format($CurrentBalance, $CurrDecimalPlaces), '</td>
			</tr>
		</tfoot>';
	// Do the postings
	include('includes/GLPostings.inc');
	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="Submit" value="', _('Update'), '" />
		</div>
	</form>';
} else {
	/*The option to submit was not hit so display form */
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	echo '<fieldset>'; //Main table
	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE authorizerexpenses LIKE '%" . $_SESSION['UserID'] . "%'
		ORDER BY tabcode";
	$Result = DB_query($SQL);
	echo '<field>
			<td>', _('Authorise expenses on petty cash tab'), ':</td>
			<td><select required="required" name="SelectedTabs">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	} //end while loop get type of tab
	echo '</select>
		</tr>';	
	// KL RICARD
	echo'	<tr>
			 <td>' . _('Show only unauthorized expenses') . '</td>
			 <td><input type="checkbox" title="' . _('Check this box to display only the expenses pending of authorization') . '" name="ShowOnlyUnauthorized" /></td>
		</tr>';
	// KL RICARD END

	echo '</fieldset>'; // close main table
	DB_free_result($Result);
	echo '<div class="centre">
			<input type="submit" name="Process" value="', _('Accept'), '" />
			<input type="submit" name="Cancel" value="', _('Cancel'), '" />
		</div>';
	echo '</form>';
}
/*end of else not submit */
include('includes/footer.php');
?>