<?php

/**
 * Adds HTML attributes to form fields.
 *
 * @param int|string|null $TabIndex The tab index value for the HTML element
 * @param bool|int|null $Required Whether the field is required or not
 * @param bool|int|null $AutoFocus Whether the field should autofocus or not
 * @return string Generated HTML attributes
 */
function AddAttributesToField(int|string|null $TabIndex, bool|int|null $Required, bool|int|null $AutoFocus): string {
	$Attributes = ' ';
	if (isset($AutoFocus) and $AutoFocus) {
		$Attributes .= 'autofocus="autofocus" ';
	}

	if (isset($Required) and $Required) {
		$Attributes .= 'required="required" ';
	}

	if (isset($TabIndex) and $TabIndex != '') {
		$Attributes .= 'tabindex="' . $TabIndex . '" ';
	}
	return $Attributes;
}

/**
 * Creates a dropdown from an array of values.
 *
 * @param array $Array
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneEntryFromArray(array $Array, string $VariableName, ?string $SelectedValue, string $Label = '', string $HelpText = '', string $Filter = '', int|string|null $TabIndex = '', bool $Required = true, bool $AutoFocus = false): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	foreach ($Array as $Entry => $Name) {
		if (isset($SelectedValue) and (strtoupper($SelectedValue) == strtoupper($Entry))) {
			$HTML .= '<option selected="selected" value="' . $Entry . '">' . $Name . '</option>';
		} else {
			$HTML .= '<option value="' . $Entry . '">' . $Name . '</option>';
		}
	}

	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting a currency.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneCurrency(string $VariableName, ?string $SelectedValue, string $Label = '', string $HelpText = '', string $Filter = '', int|string|null $TabIndex = '', bool $Required = true, bool $AutoFocus = false): string {

	$SQL = "SELECT currabrev,
				currency
			FROM currencies
			ORDER BY currency";

	$Result = DB_query($SQL);


	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) and ($MyRow['currabrev'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
		} else {
			$HTML .= '<option value="' . $MyRow['currabrev'] . '">' . $MyRow['currency'] . '</option>';
		}
	}

	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}


/**
 * Creates a dropdown for selecting customer types.
 *
 * @param string $VariableName
 * @param int|string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneCustomerType(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	$SQL = "SELECT typename,
				typeid
			FROM debtortype
			ORDER BY typename";

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($Required) {
		$HTML .= '<option value="All">' . __('All Customer Types') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="All">' . __('All Customer Types') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['typeid'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		}
		else {
			$HTML .= '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a date input field.
 *
 * @param string $VariableName
 * @param int|string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneDate(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<input type="date"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= ' name="' . $VariableName . '" size="11" maxlength="10" value="' .  FormatDateForSQL($SelectedValue) . '" />';
	if ($HelpText != '') {
		$HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a file upload input field.
 *
 * @param string $VariableName
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneFile(
	string $VariableName,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<input type="file"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= ' id="' . $VariableName . '" name="' . $VariableName . '" />';
	if ($HelpText != '') {
		$HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting one GL account.
 *
 * @param string $VariableName
 * @param int|string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneGLAccount(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	if ($Filter == 'PTADU_ALL') {
		$SuffixPT = 'ADU';
		$Where = '';
	} elseif ($Filter == 'PTADU_VIEW') {
		$SuffixPT = 'ADU';
		$Where = 'WHERE glaccountusers.canview=1';
	} elseif ($Filter == 'PTADU_UPDATE') {
		$SuffixPT = 'ADU';
		$Where = 'WHERE glaccountusers.canupd=1';
	} elseif ($Filter == 'PTSMH_ALL') {
		$SuffixPT = 'SMH';
		$Where = '';
	} elseif ($Filter == 'PTSMH_VIEW') {
		$SuffixPT = 'SMH';
		$Where = 'WHERE glaccountusers.canview=1';
	} elseif ($Filter == 'PTSMH_UPDATE') {
		$SuffixPT = 'SMH';
		$Where = 'WHERE glaccountusers.canupd=1';
	} elseif ($Filter == 'PTBB_ALL') {
		$SuffixPT = 'BB';
		$Where = '';
	} elseif ($Filter == 'PTBB_VIEW') {
		$SuffixPT = 'BB';
		$Where = 'WHERE glaccountusers.canview=1';
	} elseif ($Filter == 'PTBB_UPDATE') {
		$SuffixPT = 'BB';
		$Where = 'WHERE glaccountusers.canupd=1';
	} elseif ($Filter == 'POIK_ALL') {
		$SuffixPT = 'IK';
		$Where = '';
	} elseif ($Filter == 'POIK_VIEW') {
		$SuffixPT = 'IK';
		$Where = 'WHERE glaccountusers.canview=1';
	} elseif ($Filter == 'POIK_UPDATE') {
		$SuffixPT = 'IK';
		$Where = 'WHERE glaccountusers.canupd=1';
	} elseif ($Filter == 'POPI_ALL') {
		$SuffixPT = 'PI';
		$Where = '';
	} elseif ($Filter == 'POPI_VIEW') {
		$SuffixPT = 'PI';
		$Where = 'WHERE glaccountusers.canview=1';
	} elseif ($Filter == 'POPI_UPDATE') {
		$SuffixPT = 'PI';
		$Where = 'WHERE glaccountusers.canupd=1';
	} elseif ($Filter == 'ALL') {
		$SuffixPT = '';
		$Where = '';
	} elseif ($Filter == 'BS') {
		$SuffixPT = '';
		$Where = 'WHERE accountgroups.pandl=0';
	} elseif ($Filter == 'P&L') {
		$SuffixPT = '';
		$Where = 'WHERE accountgroups.pandl=1';
	} elseif ($Filter == 'VIEW') {
		$SuffixPT = '';
		$Where = 'WHERE glaccountusers.canview=1';
	} elseif ($Filter == 'UPDATE') {
		$SuffixPT = '';
		$Where = 'WHERE glaccountusers.canupd=1';
	} else {
		$SuffixPT = '';
		$Where = '';
	}
	
	$SQL = "SELECT chartmaster" . $SuffixPT . ".accountcode,
				chartmaster" . $SuffixPT . ".accountname
			FROM chartmaster" . $SuffixPT . " 
			INNER JOIN accountgroups
				ON chartmaster.group_=accountgroups.groupname
			INNER JOIN glaccountusers 
				ON glaccountusers.accountcode=chartmaster" . $SuffixPT . ".accountcode 
				AND glaccountusers.userid='" . $_SESSION['UserID'] . "' ". 
			$Where . " 
			ORDER BY chartmaster" . $SuffixPT . ".accountcode";

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">';
	
	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue) OR ($SelectedValue == '')) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		$TextOption = str_pad($MyRow['accountcode'], 20, ' ', STR_PAD_RIGHT) . '- ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false);
		if ($MyRow['accountcode'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $TextOption . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['accountcode'] . '">' . $TextOption . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting GL account groups.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneGLAccountGroup(
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	$SQL = "SELECT groupname
			FROM accountgroups
			ORDER BY sequenceintb ASC";

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';
	
	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['groupname'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['groupname'] . '">' . $MyRow['groupname'] . '</option>';
		}
		else {
			$HTML .= '<option value="' . $MyRow['groupname'] . '">' . $MyRow['groupname'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting a department.
 *
 * @param string $VariableName
 * @param int|string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneDepartment(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	$SQL = "SELECT departmentid,
				description
			FROM departments
			ORDER BY description ASC";

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($Required) {
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue) OR ($SelectedValue == '')) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['departmentid'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
		}
		else {
			$HTML .= '<option value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting a location.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneLocation(
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	if ($Filter == 'CANVIEW') {
		$SQL = "SELECT locations.loccode,
					locations.locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				ORDER BY locations.locationname";
	} elseif ($Filter == 'CANUPDATE') {
		$SQL = "SELECT locations.loccode,
					locations.locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" .  $_SESSION['UserID'] . "'
					AND locationusers.canupd=1
				ORDER BY locations.locationname";
	} elseif ($Filter == 'BALISHOPS') {
		$SQL = "SELECT locations.loccode,
					locations.locationname
				FROM locations
				WHERE locations.typeloc IN " . LIST_PHYSICAL_SHOPS_BY_TYPE . "
				ORDER BY locations.locationname";
	} elseif ($Filter == 'GUDANGPACKAGING') {
		$SQL = "SELECT locations.loccode,
					locations.locationname
				FROM locations
				WHERE locations.typeloc IN " . LIST_GUDANG_PACKAGING_BY_TYPE . "
				ORDER BY locations.locationname";
	} else {
		$SQL = "SELECT loccode,
					locationname
				FROM locations
				ORDER BY locationname";
	}

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue) OR ($SelectedValue == '')) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['loccode'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
		else {
			$HTML .= '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting an accounting period.
 *
 * @param string $VariableName
 * @param int|string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOnePeriod(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	/* Select One Period, with a dropdown showing the month and Year */

	if ($Filter == 'NEWER_OR_EQUAL_THAN_SELECTED') {
		$WhereSQL = " WHERE periodno >= " . $SelectedValue . " ";
	} else {
		$WhereSQL = " ";
	}

$SQL = "SELECT periodno,
				lastdate_in_period
			FROM periods" .
			$WhereSQL . "
			ORDER BY periodno";

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue) OR ($SelectedValue == '')) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['periodno'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
		else {
			$HTML .= '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting a sales area.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneSalesArea(
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT areacode,
				areadescription
			FROM areas
			ORDER BY areadescription";

	$Result = DB_query($SQL);


	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue) OR ($SelectedValue == '')) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['areacode'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		}
		else {
			$HTML .= '<option value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting a sales person.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param bool $AllowAll
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneSalesPerson(
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	bool $AllowAll = false,
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($_SESSION['SalesmanLogin'] != '') {
		/* If the user is a salesman, then the salesperson is fixed */
		$HTML .=  '<fieldtext>' . $_SESSION['UsersRealName'] . '</fieldtext>
				</field>';
	} else {

	if ($Filter == 'CURRENT') {
			$SQL = "SELECT salesmancode,
						salesmanname
					FROM salesman
					WHERE current = 1
					ORDER BY salesmancode";
		}
		else {
			$SQL = "SELECT salesmancode,
						salesmanname
					FROM salesman
					ORDER BY salesmancode";
		}

		$Result = DB_query($SQL);

		if ($AllowAll) {
			if (!isset($SelectedValue)) {
				$HTML .= '<option selected="selected" value="All">' . __('All Sales Persons') . '</option>';
			}
			else {
				$HTML .= '<option value="All">' . __('All Sales Persons') . '</option>';
			}
		}
		else {
			$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
		}

		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($SelectedValue) AND ($MyRow['salesmancode'] == $SelectedValue)) {
				$HTML .= '<option selected="selected" value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmancode'] . '-' . $MyRow['salesmanname'] . '</option>';
			}
			else {
				$HTML .= '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmancode'] . '-' . $MyRow['salesmanname'] . '</option>';
			}
		}
		$HTML .= '</select>';
		if ($HelpText != '') {
			$HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
		}
		$HTML .= '</field>';
	}
	return $HTML;
}

/**
 * Creates a dropdown for selecting a stock category.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param bool $AllowAll
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneStockCategory(
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	bool $AllowAll = false,
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";

	$Result = DB_query($SQL);


	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($AllowAll) {
		if (!isset($SelectedValue)) {
			$HTML .= '<option selected="selected" value="All">' . __('All Stock Categories') . '</option>';
		}
		else {
			$HTML .= '<option value="All">' . __('All Stock Categories') . '</option>';
		}
	}
	else {
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['categoryid'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		} else {
			$HTML .= '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting a system type.
 *
 * @param string $VariableName
 * @param int|string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneSysType(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT typeid,
				typename
			FROM systypes
			ORDER BY typename";

	$Result = DB_query($SQL);


	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue) OR ($SelectedValue == '')) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['typeid'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		} else {
			$HTML .= '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a password input field.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param int|string $Size
 * @param int|string $MaxLength
 * @param string $Label
 * @param string $HelpText
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOnePassword(
	string $VariableName,
	?string $SelectedValue,
	int|string $Size,
	int|string $MaxLength,
	string $Label = '',
	string $HelpText = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<input type="password" pattern=".{5,}"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= '" name="' . $VariableName . '"  placeholder="'.__('At least 5 characters').'" size="' . $Size . '" maxlength="' . $MaxLength . '" value="' . $SelectedValue . '" />';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a telephone number input field.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param int|string $Size
 * @param int|string $MaxLength
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneTelephoneNumber(
	string $VariableName,
	?string $SelectedValue,
	int|string $Size,
	int|string $MaxLength,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<input type="text" pattern="[0-9+\s]*"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= '" name="' . $VariableName . '" size="' . $Size . '" maxlength="' . $MaxLength . '" value="' . $SelectedValue . '" />';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a text input field.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param int|string $Size
 * @param int|string $MaxLength
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneText(
	string $VariableName,
	?string $SelectedValue,
	int|string $Size,
	int|string $MaxLength,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<input type="text"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= '" name="' . $VariableName . '" size="' . $Size . '" maxlength="' . $MaxLength . '" value="' . $SelectedValue . '" />';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates an email input field.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param int|string $Size
 * @param int|string $MaxLength
 * @param string $Label
 * @param string $HelpText
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneEmail(
	string $VariableName,
	?string $SelectedValue,
	int|string $Size,
	int|string $MaxLength,
	string $Label = '',
	string $HelpText = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= ' name="' . $VariableName . '" size="' . $Size . '" maxlength="' . $MaxLength . '" value="' . $SelectedValue . '" placeholder="accounts@example.com" />';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a number input field.
 *
 * @param string $VariableName
 * @param int|string|float|null $SelectedValue
 * @param int|string $Size
 * @param int|string $MaxLength
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneNumber(
	string $VariableName,
	int|string|float|null $SelectedValue,
	int|string $Size,
	int|string $MaxLength,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<input type="text" class="number" pattern="[0-9]*\.?[0-9]*"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= '" name="' . $VariableName . '" size="' . $Size . '" maxlength="' . $MaxLength . '" value="' . $SelectedValue . '" />';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a text area input field.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param int|string $Cols
 * @param int|string $Rows
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneTextArea(
	string $VariableName,
	?string $SelectedValue,
	int|string $Cols,
	int|string $Rows,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>';
	if ($HTML != '') {
	    $HTML .= '<label for="' . $VariableName . '">' . $Label . ':</label>';
	}
	$HTML .= '<textarea name="' . $VariableName . '" cols="' . $Cols . '" rows="' . $Rows . '">' . htmlspecialchars($SelectedValue) . '</textarea>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting a user.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param bool $AllowAll
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneUser(
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	bool $AllowAll = false,
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT userid,
				realname
			FROM www_users";

	$Result = DB_query($SQL);


	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($AllowAll) {
		if (!isset($SelectedValue)) {
			$HTML .= '<option selected="selected" value="All">' . __('All Users') . '</option>';
		}
		else {
			$HTML .= '<option value="All">' . __('All Users') . '</option>';
		}
	}
	else {
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) and ($MyRow['userid'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['userid'] . '">' . $MyRow['userid'] . '</option>';
		} else {
			$HTML .= '<option value="' . $MyRow['userid'] . '">' . $MyRow['userid'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a multiple selection dropdown for locations.
 *
 * @param string $VariableName
 * @param array|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectMultipleLocations(
	string $VariableName,
	?array $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	if ($Filter == 'CANVIEW') {
		$SQL = "SELECT locations.loccode,
					locations.locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canview=1
				ORDER BY locations.locationname";
	} elseif ($Filter == 'CANUPDATE') {
		$SQL = "SELECT locations.loccode,
					locations.locationname
				FROM locations
				INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
					AND locationusers.userid='" . $_SESSION['UserID'] . "'
					AND locationusers.canupd=1
				ORDER BY locations.locationname";
	} else {
		$SQL = "SELECT loccode,
					locationname
				FROM locations
				ORDER BY locationname";
	}

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '[]">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'minlength="1" size="12" name="' . $VariableName . '[]" multiple="multiple">';

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) and in_array($MyRow['loccode'], $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} else {
			$HTML .= '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a multiple selection dropdown for accounting periods.
 *
 * @param string $VariableName
 * @param int|string|null $FirstSelectedValue
 * @param int|string|null $LastSelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectMultiplePeriods(
	string $VariableName,
	int|string|null $FirstSelectedValue,
	int|string|null $LastSelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = 'ASC',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	/* Select a range of Periods, showing the month and Year */
	if ($Filter == 'ASC') {
		$OrderSQL = " ORDER BY periodno";
	}
	else {
		$OrderSQL = " ORDER BY periodno DESC";
	}

	$SQL = "SELECT periodno,
				lastdate_in_period
			FROM periods"
			. $OrderSQL;

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '[]">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'minlength="1" size="12" name="' . $VariableName . '[]" multiple="multiple">';

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($FirstSelectedValue) and $MyRow['periodno'] >= $FirstSelectedValue and $MyRow['periodno'] <= $LastSelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['periodno'] . '">' . __(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
		} else {
			$HTML .= '<option value="' . $MyRow['periodno'] . '">' . __(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a multiple selection dropdown for stock categories.
 *
 * @param string $VariableName
 * @param array|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectMultipleStockCategories(
	string $VariableName,
	?array $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	$SQL = "SELECT categoryid,
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '[]">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'minlength="1" size="12" name="' . $VariableName . '[]" multiple="multiple">';

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) and in_array($MyRow['categoryid'], $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		} else {
			$HTML .= '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a read-only field displaying a value.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @return string Generated HTML field
 */
function FixedField(
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = ''
): string {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<fieldtext>' . $SelectedValue . '</fieldtext>
			</field>';
	return $HTML;
}

/**
 * Creates a centered form with one submit button.
 *
 * @param string $ButtonName
 * @param string $ButtonValue
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML
 */
function OneButtonCenteredForm(
	string $ButtonName,
	string $ButtonValue,
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	$HTML = '<div class="centre">
				<input type="submit" ';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $ButtonName . '" value="' . $ButtonValue . '" />
			</div>';
	return $HTML;
}

/**
 * Creates a centered form with submit and reset buttons.
 *
 * @param string $ButtonName1
 * @param string $ButtonValue1
 * @param string $ButtonName2
 * @param string $ButtonValue2
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML
 */
function TwoButtonsCenteredForm(
	string $ButtonName1,
	string $ButtonValue1,
	string $ButtonName2,
	string $ButtonValue2,
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	$HTML = '<div class="centre">
				<input type="submit" ';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $ButtonName1 . '" value="' . $ButtonValue1 . '" />
				<input type="reset" ';
	$HTML .= 'name="' . $ButtonName2 . '" value="' . $ButtonValue2 . '" />
			</div>';
	return $HTML;
}

/**
 * Creates a dropdown with two options.
 *
 * @param string $ValueOption1
 * @param string $LabelOption1
 * @param string $ValueOption2
 * @param string $LabelOption2
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectFromTwoOptions(
	string $ValueOption1,
	string $LabelOption1,
	string $ValueOption2,
	string $LabelOption2,
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
		<label>' . $Label . ':</label>
		<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';
	if ($SelectedValue == $ValueOption1) {
		$HTML .= '<option selected="selected" value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>';
	}
	else {
		$HTML .= '<option selected="selected" value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>';
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown with three options.
 *
 * @param string $ValueOption1
 * @param string $LabelOption1
 * @param string $ValueOption2
 * @param string $LabelOption2
 * @param string $ValueOption3
 * @param string $LabelOption3
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectFromThreeOptions(
	string $ValueOption1,
	string $LabelOption1,
	string $ValueOption2,
	string $LabelOption2,
	string $ValueOption3,
	string $LabelOption3,
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
		<label>' . $Label . ':</label>
		<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';
	if ($SelectedValue == $ValueOption1) {
		$HTML .= '<option selected="selected" value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>';
	}
	elseif ($SelectedValue == $ValueOption2) {
		$HTML .= '<option selected="selected" value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>';
	}
	else {
		$HTML .= '<option selected="selected" value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>';
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown with four options.
 *
 * @param string $ValueOption1
 * @param string $LabelOption1
 * @param string $ValueOption2
 * @param string $LabelOption2
 * @param string $ValueOption3
 * @param string $LabelOption3
 * @param string $ValueOption4
 * @param string $LabelOption4
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectFromFourOptions(
	string $ValueOption1,
	string $LabelOption1,
	string $ValueOption2,
	string $LabelOption2,
	string $ValueOption3,
	string $LabelOption3,
	string $ValueOption4,
	string $LabelOption4,
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
		<label>' . $Label . ':</label>
		<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';
	if ($SelectedValue == $ValueOption1) {
		$HTML .= '<option selected="selected" value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>';
	}
	elseif ($SelectedValue == $ValueOption2) {
		$HTML .= '<option selected="selected" value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>';
	}
	elseif ($SelectedValue == $ValueOption3) {
		$HTML .= '<option selected="selected" value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>';
	}
	else {
		$HTML .= '<option selected="selected" value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>';
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown with five options.
 *
 * @param string $ValueOption1
 * @param string $LabelOption1
 * @param string $ValueOption2
 * @param string $LabelOption2
 * @param string $ValueOption3
 * @param string $LabelOption3
 * @param string $ValueOption4
 * @param string $LabelOption4
 * @param string $ValueOption5
 * @param string $LabelOption5
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectFromFiveOptions(
	string $ValueOption1,
	string $LabelOption1,
	string $ValueOption2,
	string $LabelOption2,
	string $ValueOption3,
	string $LabelOption3,
	string $ValueOption4,
	string $LabelOption4,
	string $ValueOption5,
	string $LabelOption5,
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = '<field>
		<label>' . $Label . ':</label>
		<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';
	if ($SelectedValue == $ValueOption1) {
		$HTML .= '<option selected="selected" value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>
				<option value="' . $ValueOption5 . '">' . $LabelOption5 . '</option>';
	}
	elseif ($SelectedValue == $ValueOption2) {
		$HTML .= '<option selected="selected" value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>
				<option value="' . $ValueOption5 . '">' . $LabelOption5 . '</option>';
	}
	elseif ($SelectedValue == $ValueOption3) {
		$HTML .= '<option selected="selected" value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>
				<option value="' . $ValueOption5 . '">' . $LabelOption5 . '</option>';
	}
	elseif ($SelectedValue == $ValueOption4) {
		$HTML .= '<option selected="selected" value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption5 . '">' . $LabelOption5 . '</option>';
	}
	else {
		$HTML .= '<option selected="selected" value="' . $ValueOption5 . '">' . $LabelOption5 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>';
	}

	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}

/**
 * Creates a dropdown for selecting spreadsheet formats.
 *
 * @param string $VariableName
 * @param string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectSpreadSheetFormat(
	string $VariableName,
	?string $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$HTML = FieldToSelectFromTwoOptions('xlsx', 'Excel 2007 (xlsx)',
										'ods', 'OpenDocument (ods)',
										$VariableName, $SelectedValue, $Label, $HelpText, $Filter, $TabIndex, $Required, $AutoFocus);

	return $HTML;
}

/**
 * Creates a dropdown for selecting a brand.
 *
 * @param string $VariableName
 * @param int|string|null $SelectedValue
 * @param string $Label
 * @param string $HelpText
 * @param string $Filter
 * @param int|string|null $TabIndex
 * @param bool $Required
 * @param bool $AutoFocus
 * @return string Generated HTML field
 */
function FieldToSelectOneBrand(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT brands.brands_id,
					brands_name
			FROM brands
			ORDER BY brands_name";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);
	$HTML .= 'name="' . $VariableName . '">';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['brands_id'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['brands_id'] . '">' . $MyRow['brands_name'] . '</option>';
		}
		else {
			$HTML .= '<option value="' . $MyRow['brands_id'] . '">' . $MyRow['brands_name'] . '</option>';
		}
	}
	$HTML .= '</select>';
	if ($HelpText != '') {
	    $HTML .= '<fieldhelp>' . $HelpText . '</fieldhelp>';
	}
	$HTML .= '</field>';
	return $HTML;
}