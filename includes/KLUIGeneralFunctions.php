<?php

/**
 * Creates a dropdown for selecting a bank.
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
function FieldToSelectOneBank(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT bankcode,
				bankname
			FROM banks
			ORDER BY bankname";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['bankcode'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['bankcode'] . '">' . $MyRow['bankname'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['bankcode'] . '">' . $MyRow['bankname'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


/**
 * Creates a dropdown for selecting KPI concepts.
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
function FieldToSelectOneKPI(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT kpicode,
				kpidescription 
			FROM klkpidescriptions
			ORDER BY kpidescription";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['kpidescription'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['kpicode'] . '">' . $MyRow['kpidescription'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['kpicode'] . '">' . $MyRow['kpidescription'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


/**
 * Creates a dropdown for selecting maintenance types.
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
function FieldToSelectOneMaintenanceType(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {
	$SQL = "SELECT maintenancetype,
				description
			FROM klmaintenancetypes 
			ORDER BY description";

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['maintenancetype'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['maintenancetype'] . '">' . $MyRow['description'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['maintenancetype'] . '">' . $MyRow['description'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}



function FieldToSelectOnePPH21Zone(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT pph21zoneid,
				pph21zonename
			FROM hrpph21zones
			ORDER BY pph21zonename";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['pph21zoneid'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['pph21zoneid'] . '">' . $MyRow['pph21zonename'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['pph21zoneid'] . '">' . $MyRow['pph21zonename'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


/**
 * Creates a dropdown for selecting retail partners.
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
function FieldToSelectOneRetailPartner(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

$SQL = "SELECT partnercode, 
				partnernameinvoice 
			FROM klretailpartners
			WHERE partnercode != 'NORETAIL'
			ORDER BY partnername";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';
	
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['partnercode'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['partnercode'] . '">' . $MyRow['partnernameinvoice'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['partnercode'] . '">' . $MyRow['partnernameinvoice'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


/**
 * Creates a dropdown for selecting returned item reasons.
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
function FieldToSelectOneReturnedItemReason(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT reasonname,
				reasonid
			FROM returnitemreasons
			ORDER BY reasonname";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';
	
	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['reasonid'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['reasonid'] . '">' . $MyRow['reasonname'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['reasonid'] . '">' . $MyRow['reasonname'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


/**
 * Creates a dropdown for selecting service fees.
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
function FieldToSelectOneServiceFee(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT servicecode,
				servicedescription
			FROM klservicetypes
			ORDER BY servicedescription";

	$Result = DB_query($SQL);


	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['servicecode'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['servicecode'] . '">' . $MyRow['servicedescription'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['servicecode'] . '">' . $MyRow['servicedescription'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


/**
 * Creates a dropdown for selecting stock adjustment reasons.
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
function FieldToSelectOneStockAdjustmentReason(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT reasonid,
				reasonname
			FROM stockadjustmentreasons
			ORDER BY reasonname";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';
	
	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['reasonid'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['reasonid'] . '">' . $MyRow['reasonname'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['reasonid'] . '">' . $MyRow['reasonname'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


/**
 * Creates a dropdown for selecting tags.
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
function FieldToSelectOneTag(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT tagref,
				tagdescription
			FROM tags
			ORDER BY tagref";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';
	
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['tagref'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['tagref'] . '">' . $MyRow['tagdescription'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['tagref'] . '">' . $MyRow['tagdescription'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


/**
 * Creates a dropdown for selecting UMK zones.
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
function FieldToSelectOneUMKZone(
	string $VariableName,
	int|string|null $SelectedValue,
	string $Label = '',
	string $HelpText = '',
	string $Filter = '',
	int|string|null $TabIndex = '',
	bool $Required = true,
	bool $AutoFocus = false
): string {

	$SQL = "SELECT umkzoneid,
				umkzonename
			FROM hrumkzones
			ORDER BY umkzonename";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['umkzoneid'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['umkzoneid'] . '">' . $MyRow['umkzonename'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['umkzoneid'] . '">' . $MyRow['umkzonename'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function ShowTableTitle(string $Text): void {
	echo '<p class="page_title_text" align="center">
			<strong>' . 
				$Text . '
			</strong>
		</p>';
}

function ShowTableSubTitle(string $Text): void {
	echo '<p class="page_title_text_small" align="center">' . 
			$Text . '
		</p>';
}

function ShowWarningTitle(string $Text): void {
	echo '<p class="bad" align="center">
			<strong>' . 
				$Text . '
			</strong>
		</p>';
}

