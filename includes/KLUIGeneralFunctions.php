<?php

/**********************************************************************************************************
 * 
 * KL RICARD: KL Specific UI functions
 * 
 *********************************************************************************************************/

function FieldToSelectSpreadSheetFormat($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = FieldToSelectFromTwoOptions('xlsx', 'Excel 2007 (xlsx)',
										'ods', 'OpenDocument (ods)',
										$VariableName, $SelectedValue, $Label, $HelpText, $Filter, $TabIndex, $Required, $AutoFocus);

	return $HTML;
}

function FieldToSelectFromTwoOptions($ValueOption1, $LabelOption1, 
									$ValueOption2, $LabelOption2, 
									$VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
		<label>' . $Label . ':</label>
		<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">';
	if($SelectedValue == $ValueOption1) {
		$HTML .= '<option selected="selected" value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>';
	}
	else {
		$HTML .= '<option selected="selected" value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>';
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectFromThreeOptions($ValueOption1, $LabelOption1, 
									$ValueOption2, $LabelOption2, 
									$ValueOption3, $LabelOption3,
									$VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
		<label>' . $Label . ':</label>
		<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">';
	if($SelectedValue == $ValueOption1) {
		$HTML .= '<option selected="selected" value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>';
	}
	else if($SelectedValue == $ValueOption2) {
		$HTML .= '<option selected="selected" value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>';
	}
	else {
		$HTML .= '<option selected="selected" value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>';
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectFromFourOptions($ValueOption1, $LabelOption1, 
									$ValueOption2, $LabelOption2, 
									$ValueOption3, $LabelOption3,
									$ValueOption4, $LabelOption4,
									$VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
		<label>' . $Label . ':</label>
		<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">';
	if($SelectedValue == $ValueOption1) {
		$HTML .= '<option selected="selected" value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>';
	}
	else if($SelectedValue == $ValueOption2) {
		$HTML .= '<option selected="selected" value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>';
	}
	else if($SelectedValue == $ValueOption3) {
		$HTML .= '<option selected="selected" value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption3 . '</option>
				<option value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>';
	}
	else {
		$HTML .= '<option selected="selected" value="' . $ValueOption4 . '">' . $LabelOption4 . '</option>
				<option value="' . $ValueOption1 . '">' . $LabelOption1 . '</option>
				<option value="' . $ValueOption2 . '">' . $LabelOption2 . '</option>
				<option value="' . $ValueOption3 . '">' . $LabelOption3 . '</option>';
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectOneBrand($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$SQL = "SELECT manufacturers.manufacturers_id, 
					manufacturers_name 
			FROM manufacturers 
			ORDER BY manufacturers_name";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';
	
	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['manufacturers_id'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['manufacturers_id'] . '">' . $MyRow['manufacturers_name'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['manufacturers_id'] . '">' . $MyRow['manufacturers_name'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectOneGLAccount($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
	if ($Filter = 'PTADU_ALL') {
		$SuffixPT = 'ADU';
		$WhereUser = '';
	}
	elseif ($Filter = 'PTADU_VIEW') {
		$SuffixPT = 'ADU';
		$WhereUser = 'AND glaccountusers.canview=1';
	}
	elseif ($Filter = 'PTADU_UPDATE') {
		$SuffixPT = 'ADU';
		$WhereUser = 'AND glaccountusers.canupd=1';
	}
	elseif ($Filter = 'PTSMH_ALL') {
		$SuffixPT = 'SMH';
		$WhereUser = '';
	}
	elseif ($Filter = 'PTSMH_VIEW') {
		$SuffixPT = 'SMH';
		$WhereUser = 'AND glaccountusers.canview=1';
	}
	elseif ($Filter = 'PTSMH_UPDATE') {
		$SuffixPT = 'SMH';
		$WhereUser = 'AND glaccountusers.canupd=1';
	}
	elseif ($Filter = 'PTBB_ALL') {
		$SuffixPT = 'BB';
		$WhereUser = '';
	}
	elseif ($Filter = 'PTBB_VIEW') {
		$SuffixPT = 'BB';
		$WhereUser = 'AND glaccountusers.canview=1';
	}
	elseif ($Filter = 'PTBB_UPDATE') {
		$SuffixPT = 'BB';
		$WhereUser = 'AND glaccountusers.canupd=1';
	}
	elseif ($Filter = 'POIK_ALL') {
		$SuffixPT = 'IK';
		$WhereUser = '';
	}
	elseif ($Filter = 'POIK_VIEW') {
		$SuffixPT = 'IK';
		$WhereUser = 'AND glaccountusers.canview=1';
	}
	elseif ($Filter = 'POIK_UPDATE') {
		$SuffixPT = 'IK';
		$WhereUser = 'AND glaccountusers.canupd=1';
	}
	elseif ($Filter = 'POPI_ALL') {
		$SuffixPT = 'PI';
		$WhereUser = '';
	}
	elseif ($Filter = 'POPI_VIEW') {
		$SuffixPT = 'PI';
		$WhereUser = 'AND glaccountusers.canview=1';
	}
	elseif ($Filter = 'POPI_UPDATE') {
		$SuffixPT = 'PI';
		$WhereUser = 'AND glaccountusers.canupd=1';
	}
	elseif ($Filter = 'ALL') {
		$SuffixPT = '';
		$WhereUser = '';
	}
	elseif ($Filter = 'VIEW') {
		$SuffixPT = '';
		$WhereUser = 'AND glaccountusers.canview=1';
	}
	elseif ($Filter = 'UPDATE') {
		$SuffixPT = '';
		$WhereUser = 'AND glaccountusers.canupd=1';
	}
	else {
		$SuffixPT = '';
		$WhereUser = '';
	}
	
	$SQL = "SELECT chartmaster" . $SuffixPT . ".accountcode,
				chartmaster" . $SuffixPT . ".accountname
			FROM chartmaster" . $SuffixPT . " 
			INNER JOIN glaccountusers 
				ON glaccountusers.accountcode=chartmaster" . $SuffixPT . ".accountcode 
				AND glaccountusers.userid='" . $_SESSION['UserID'] . "' ". 
				$WhereUser . " 
			ORDER BY chartmaster" . $SuffixPT . ".accountcode";

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';
	
	while ($MyRow = DB_fetch_array($Result)) {
		$TextOption = $MyRow['accountcode'] . ' ' . htmlspecialchars($MyRow['accountname'], ENT_QUOTES, 'UTF-8', false);
		if ($MyRow['accountcode'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['accountcode'] . '">' . $TextOption . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['accountcode'] . '">' . $TextOption . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectOneKPIConcept($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$SQL = "SELECT DISTINCT class,
				concept 
			FROM klkpi 
			ORDER BY class, concept";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if ($Required){
		$HTML .= '<option value="">' . _('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
	}

	while ($MyRow = DB_fetch_array($Result)) {
		if ($MyRow['concept'] == $SelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['concept'] . '">' . $MyRow['class'] . ' - ' . $MyRow['concept'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['concept'] . '">' . $MyRow['class'] . ' - ' . $MyRow['concept'] . '</option>';
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
		$HTML .= '<option value="">' . _('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
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


?>
