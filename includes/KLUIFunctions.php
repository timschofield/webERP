<?php

/**********************************************************************************************************
 * 
 * KL RICARD: KL Specific UI functions
 * 
 *********************************************************************************************************/

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
