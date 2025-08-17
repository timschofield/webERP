<?php

/********************************************************************
*
* KL RICARD: Use receipt text field and PPH21 and PPH23 retention
*			Multiple assigners, authorizers, etc.				
*            Option to show only unauthorized expenses
*			 Hide tax, tag and purpose fields
*
*********************************************************************/

include('includes/session.php');
$Title = __('Authorisation of Petty Cash Expenses');
/* webERP manual links before header.php */
$ViewTopic = 'PettyCash';
$BookMark = 'AuthorizeExpense';
include('includes/header.php');
include('includes/SQL_CommonFunctions.php');
include('includes/GLFunctions.php');

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
		prnMsg(__('You Must First Select a Petty Cash Tab To Authorise'), 'error');
		unset($SelectedTabs);
	}
}
if (isset($_POST['Go'])) {
	if ($Days <= 0) {
		prnMsg(__('The number of days must be a positive number'), 'error');
		$Days = 30;
	}
}

echo '<p class="page_title_text">
			<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/magnifier.png" title="', __('Petty Cash'), '" alt="" />', $Title, '
		</p>';


if (isset($SelectedTabs)) {
	echo '<form><fieldset>';
	echo '<field>
			<label>' . __('Petty Cash Tab') . ':</label>
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
	$SucessfullyAuthorized = 0;

	//Limit expenses history to X days
	echo '<fieldset>
			<field>
				<label for="SelectedTabs">', __('Detail of tab expenses for the last '), ':</label>
				<input type="hidden" name="SelectedTabs" value="', $SelectedTabs, '" />
				<input type="text" class="number" name="Days" value="', $Days, '" maxlength="3" size="4" />', __('Days'), '
				<input type="submit" name="Go" value="', __('Go'), '" />
			</field>
		</fieldset>';
	// KL RICARD add the receipt text field
	$SQL = "SELECT pcashdetails.counterindex,
				pcashdetails.tabcode,
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
		$SQL .= "AND pcashdetails.authorized = '1000-01-01' ";
	}
	$SQL .=		" ORDER BY pcashdetails.date, pcashdetails.counterindex ASC";
	$Result = DB_query($SQL);
	echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">', __('Date of Expense'), '</th>
					<th class="SortedColumn">', __('Expense Code'), '</th>
					<th class="SortedColumn">', __('Amount'), '</th>
					<th>', __('Notes'), '</th>
					<th>', __('Receipt'), '</th>
					<th>', __('Receipt Attachment'), '</th>
					<th class="SortedColumn">', __('Date Authorised'), '</th>
				</tr>
			</thead>
			<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		$CurrDecimalPlaces = $MyRow['decimalplaces'];
		//update database if update pressed
		$PeriodNo = GetPeriod(ConvertSQLDate($MyRow['date']));
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
		// it is not an ASSIGNCASH, as it has been moved to PCAuthorizeCash.php, it is always an expense
		$Type = 1;
		$NetAmount = -$NetAmount;
		$AccountFrom = $MyRow['glaccountpcash'];
		// KL RICARD add pph21 and pph23 in SQL
		$SQLAccExp = "SELECT glaccount,
							klretentionpph21,
							klretentionpph23
						FROM pcexpenses
						WHERE codeexpense = '" . $MyRow['codeexpense'] . "'";
		$ResultAccExp = DB_query($SQLAccExp);
		$MyRowAccExp = DB_fetch_array($ResultAccExp);
		$AccountTo = $MyRowAccExp['glaccount'];

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
		
		$TagSQL = "SELECT tagref, tagdescription FROM tags INNER JOIN pctags ON tags.tagref=pctags.tag WHERE pctags.pccashdetail='" . $MyRow['counterindex'] . "'";
		$TagResult = DB_query($TagSQL);
		$TagDescription = '';
		while ($TagRow = DB_fetch_array($TagResult)) {
			if ($TagRow['tagref'] == 0) {
				$TagRow['tagdescription'] = __('None');
			}
			$TagDescription .= $TagRow['tagref'] . ' - ' . $TagRow['tagdescription'] . '</br>';
		}

		if (isset($_POST['Submit']) and $_POST['Submit'] == __('Update') and isset($_POST[$MyRow['counterindex']])) {
			//get typeno
			$TypeNo = GetNextTransNo($Type);

			$TagsSQL = "SELECT tag FROM pctags WHERE pccashdetail='" . $MyRow['counterindex'] . "'";
			$TagsResult = DB_query($TagsSQL);
			while ($TagRow = DB_fetch_array($TagsResult)) {
				$Tags[] = $TagRow['tag'];
			}

			//build narrative
			$Narrative = __('PettyCash') . ' - ' . $MyRow['tabcode'] . ' - ' . $MyRow['codeexpense'] . ' - ' . DB_escape_string($MyRow['receipt']) . ' - ' . DB_escape_string($MyRow['notes']);
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
											`jobref`)
									VALUES (NULL,
											'" . $Type . "',
											'" . $TypeNo . "',
											0,
											'" . $MyRow['date'] . "',
											'" . $PeriodNo . "',
											'" . $AccountFrom . "',
											'" . $Narrative . "',
											'" . $GrossAmount . "',
											'')";
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
										`jobref`)
								VALUES (NULL,
										'" . $Type . "',
										'" . $TypeNo . "',
										0,
										'" . $MyRow['date'] . "',
										'" . $PeriodNo . "',
										'" . $AccountTo . "',
										'" . $Narrative . "',
										'" . ($NetAmount + $HutangPPH21 + $HutangPPH23)."',

										'')";
			$ResultTo = DB_Query($SQLTo, '', '', true);
			InsertGLTags($Tags);
			
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
												`jobref`)
										VALUES (NULL,
												'".$Type."',
												'".$TypeNo."',
												0,
												'".$MyRow['date']."',
												'".$PeriodNo."',
												'". $AccountPPH21 ."',
												'". $Narrative ."',
												'".-$HutangPPH21."',
												'')";
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
												`jobref`)
										VALUES (NULL,
												'".$Type."',
												'".$TypeNo."',
												0,
												'".$MyRow['date']."',
												'".$PeriodNo."',
												'". $AccountPPH23 ."',
												'". $Narrative ."',
												'".-$HutangPPH23."',
												'')";
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
												`jobref`)
										VALUES (NULL,
												'" . $Type . "',
												'" . $TypeNo . "',
												0,
												'" . $MyRow['date'] . "',
												'" . $PeriodNo . "',
												'" . $MyTaxRow['purchtaxglaccount'] . "',
												'" . $Narrative . "',
												'" . -$MyTaxRow['amount'] . "',
												'')";
				$ResultTax = DB_Query($SQLTo, '', '', true);
			}

			$SQL = "UPDATE pcashdetails
					SET authorized = CURRENT_DATE,
					posted = 1
					WHERE counterindex = '" . $MyRow['counterindex'] . "'";
			$Resultupdate = DB_query($SQL, '', '', true);
			DB_Txn_Commit();
			$SucessfullyAuthorized++;
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
				$ReceiptText = '<a href="' . $ReceiptPath . '" download="ExpenseReceipt-' . mb_strtolower($SelectedTabs) . '-[' . $MyRow['date'] . ']-[' . $MyRow['counterindex'] . ']">' . __('Download attachment') . '</a>';
			} else {
				$ReceiptText = __('No attachment');
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
			if (($MyRow['authorized'] != '1000-01-01')) {
				echo '<td>', ConvertSQLDate($MyRow['authorized']);
			} else {
				echo '<td><input type="checkbox" name="', $MyRow['counterindex'], '" />';
			}
		}
		echo '<input type="hidden" name="SelectedIndex" value="', $MyRow['counterindex'], '" />
			</td>
		</tr>';
	} //end of looping
	$CurrentBalance = PettyCashTabCurrentBalance($SelectedTabs);
	echo '</tbody>
		<tfoot>
			<tr class="total_row">
				<td colspan="2" class="number">', __('Current balance'), ':</td>
				<td class="number">', locale_number_format($CurrentBalance, $CurrDecimalPlaces), '</td>
				<td colspan="7"></td>
			</tr>
		</tfoot>';

	// show the success message
	if($SucessfullyAuthorized > 0) {
		prnMsg($SucessfullyAuthorized . ' ' . __('Expenses have been correctly authorised'), 'success');
	} else {
		prnMsg(__('No expenses were authorised'), 'warning');
	}

	echo '</table>';
	echo '<div class="centre">
			<input type="submit" name="Submit" value="', __('Update'), '" />
		</div>
	</form>';
} else {
	/*The option to submit was not hit so display form */
	echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">';
	echo '<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	$SQL = "SELECT tabcode
		FROM pctabs
		WHERE authorizerexpenses LIKE '%" . $_SESSION['UserID'] . "%'
		ORDER BY tabcode";
	$Result = DB_query($SQL);
	echo '<fieldset>
			<field>
				<label>', __('Authorise expenses on petty cash tab'), '</label>
				<select required="required" name="SelectedTabs">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['SelectTabs']) and $MyRow['tabcode'] == $_POST['SelectTabs']) {
			echo '<option selected="selected" value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		} else {
			echo '<option value="', $MyRow['tabcode'], '">', $MyRow['tabcode'], '</option>';
		}
	}
	echo '</select>
			</field>';
	
	// KL RICARD
	echo '<field>
			<label>', __('Show only unauthorized expenses'), '</label>
			<input type="checkbox" title="', __('Check this box to display only the expenses pending of authorization'), '" name="ShowOnlyUnauthorized" />
		</field>';
	// KL RICARD END
	echo '</fieldset>';
	DB_free_result($Result);
	echo '<div class="centre">
			<input type="submit" name="Process" value="', __('Accept'), '" />
			<input type="reset" name="Cancel" value="', __('Cancel'), '" />
		</div>';
	echo '</form>';
}
/*end of else not submit */
include('includes/footer.php');
