<?php

/**********************************************************************************************************
 * 
 * KL RICARD: KL Specific UI functions
 * 
 * Alphabetical list of functions:
 * - FieldToSelectOneBank() - Creates a dropdown for selecting a bank
 * - FieldToSelectOneDepartment() - Creates a dropdown for selecting a department
 * - FieldToSelectOneKPI() - Creates a dropdown for selecting KPI concepts
 * - FieldToSelectOneMaintenanceType() - Creates a dropdown for selecting maintenance types
 * - FieldToSelectOnePPH21Zone() - Creates a dropdown for selecting PPH21 zones
 * - FieldToSelectOneRetailPartner() - Creates a dropdown for selecting retail partners
 * - FieldToSelectOneReturnedItemReason() - Creates a dropdown for selecting returned item reasons
 * - FieldToSelectOneServiceFee() - Creates a dropdown for selecting service fees
 * - FieldToSelectOneTag() - Creates a dropdown for selecting tags
 * - FieldToSelectOneUMKZone() - Creates a dropdown for selecting UMK zones
 * - ShowTableSubTitle() - Displays a table subtitle
 * - ShowTableTitle() - Displays a table title
 * - ShowWarningTitle() - Displays a warning title
 * 
 *********************************************************************************************************/



function FieldToSelectOneBank($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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


function FieldToSelectOneDepartment($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
	
	if ($Filter == 'NOTKANTOR') {
		$SQL = "SELECT departmentid,
					description
				FROM departments
				WHERE departmentid != 1
				ORDER BY description";
	}
	else {
		$SQL = "SELECT departmentid,
					description
				FROM departments
				ORDER BY description";
	}

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
		if ($MyRow['departmentid'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['departmentid'] . '">' . $MyRow['description'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


function FieldToSelectOneKPI($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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


function FieldToSelectOneMaintenanceType($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
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



function FieldToSelectOnePPH21Zone($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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


function FieldToSelectOneRetailPartner($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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

function FieldToSelectOneReturnedItemReason($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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

function FieldToSelectOneServiceFee($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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

function FieldToSelectOneStockAdjustmentReason($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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

function FieldToSelectOneTag($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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

function FieldToSelectOneUMKZone($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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

function ShowTableTitle($Text){
	echo '<p class="page_title_text" align="center">
			<strong>' . 
				$Text . '
			</strong>
		</p>';
}

function ShowTableSubTitle($Text){
	echo '<p class="page_title_text_small" align="center">' . 
			$Text . '
		</p>';
}

function ShowWarningTitle($Text){
	echo '<p class="bad" align="center">
			<strong>' . 
				$Text . '
			</strong>
		</p>';
}

