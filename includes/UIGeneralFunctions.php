<?php

/**********************************************************************************************************
 * 
 * General UI functions
 * 
 *********************************************************************************************************/

function DateFieldSelect($VariableName, $SelectedValue, $Label, $HelpText) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="' . $VariableName . '" size="10" maxlength="10" value="' . $SelectedValue . '" />
			</field>';
	return $HTML;
}

function FixedField($VariableName, $SelectedValue, $Label, $HelpText) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<fieldtext>' . $SelectedValue . '</fieldtext>
			</field>';
	return $HTML;
}

function LocationFieldSelectOne($VariableName, $SelectedValue, $Label, $HelpText, $Filter) {
	if ($Filter == 'ALL') {
		$SQL = "SELECT loccode,
					locationname
				FROM locations
				ORDER BY locationname";
	} 
	elseif ($Filter == 'CANVIEW') {    
		$SQL = "SELECT locations.loccode,
					locations.locationname
				FROM locations
				INNER JOIN locationusers 
					ON locationusers.loccode=locations.loccode 
					AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
					AND locationusers.canview=1
				ORDER BY locations.locationname";
	} 
	elseif ($Filter == 'CANUPDATE') {    
		$SQL = "SELECT locations.loccode,
					locations.locationname
				FROM locations
				INNER JOIN locationusers 
					ON locationusers.loccode=locations.loccode 
					AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
					AND locationusers.canupd=1
				ORDER BY locations.locationname";
	}
	elseif ($Filter == 'BALISHOPS') {    
		$SQL = "SELECT loccode,
					locationname
				FROM locations
				WHERE typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . "
				ORDER BY locationname";
	}
	else 
	{
		return '';
	}

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';
	
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['loccode'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function OneButtonCenteredForm($ButtonName, $ButtonValue) {
	$HTML = '<div class="centre">
				<input type="submit" name="' . $ButtonName . '" value="' . $ButtonValue . '" />
			</div>';
	return $HTML;
}

function StockCategoryFieldMultipleSelect($VariableName, $SelectedValue, $Label, $HelpText) {
	$SQL = "SELECT categoryid, 
				categorydescription 
			FROM stockcategory
			ORDER BY categorydescription";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '[]">' . $Label . ':</label>
				<select autofocus="autofocus" required="required" minlength="1" size="12" name="' . $VariableName . '[]" multiple="multiple">
				<fieldhelp>' . $HelpText . '</fieldhelp>';
	
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND in_array($MyRow['categoryid'], $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

?>
