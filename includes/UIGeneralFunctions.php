<?php

/**********************************************************************************************************
 * 
 * General UI functions
 * 
 *********************************************************************************************************/

function AddAttributesToField($TabIndex, $Required, $AutoFocus) {
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

function FieldToSelectOneDate($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<input type="text"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="' . $VariableName . '" size="10" maxlength="10" value="' . $SelectedValue . '" />
			</field>';
	return $HTML;
}


function FieldToSelectOneLocation($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
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
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
	}

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


function FieldToSelectOnePeriod($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
	/* Select One Period, with a dropdown showing the month and Year */
	$SQL = "SELECT periodno, 
				lastdate_in_period 
			FROM periods
			ORDER BY periodno";

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . _('Not Yet Selected') . '</option>';
	}
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND ($MyRow['periodno'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


function FieldToSelectOneSalesPerson($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if($_SESSION['SalesmanLogin'] != '') {
		/* If the user is a salesman, then the salesperson is fixed */
		$HTML .=  '<fieldtext>' . $_SESSION['UsersRealName'] . '</fieldtext>
				</field>';
	}else{

		if ($Filter == 'CURRENT') {
			$SQL = "SELECT salesmancode, 
						salesmanname 
					FROM salesman
					WHERE current = 1
					ORDER BY salesmancode";
		}
		else{
			$SQL = "SELECT salesmancode, 
						salesmanname 
					FROM salesman
					ORDER BY salesmancode";
		}
	
		$Result = DB_query($SQL);
	
		if (!isset($SelectedValue)) {
			$HTML .= '<option selected="selected" value="All">' . _('All') . '</option>';
		} 
		else {
			$HTML .= '<option value="All">' . _('All') . '</option>';
		}
	
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($SelectedValue) AND ($MyRow['salesmancode'] == $SelectedValue)) {
				$HTML .= '<option selected="selected" value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmancode'] . '-' . $MyRow['salesmanname'] . '</option>';
			} 
			else {
				$HTML .= '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmancode'] . '-' . $MyRow['salesmanname'] . '</option>';
			}
		}
		$HTML .= '</select>
				</field>';
	}
	return $HTML;
}


function FieldToSelectMultipleStockCategories($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
	$SQL = "SELECT categoryid, 
				categorydescription 
			FROM stockcategory
			ORDER BY categorydescription";
	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '[]">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'minlength="1" size="12" name="' . $VariableName . '[]" multiple="multiple">
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


function FixedField($VariableName, $SelectedValue, $Label, $HelpText) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<fieldtext>' . $SelectedValue . '</fieldtext>
			</field>';
	return $HTML;
}


function OneButtonCenteredForm($ButtonName, $ButtonValue, $TabIndex, $Required, $AutoFocus) {
	$HTML = '<div class="centre">
				<input type="submit" ';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $ButtonName . '" value="' . $ButtonValue . '" />
			</div>';
	return $HTML;
}

?>
