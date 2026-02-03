<?php

/**********************************************************************************************************
 * 
 * General UI functions
 * 
 * Functions included in this file:
 * - AddAttributesToField() - Adds HTML attributes to form fields
 * - FieldToSelectMultipleLocations() - Creates a multiple selection dropdown for locations
 * - FieldToSelectMultiplePeriods() - Creates a multiple selection dropdown for accounting periods
 * - FieldToSelectMultipleStockCategories() - Creates a multiple selection dropdown for stock categories
 * - FieldToSelectOneCustomerType() - Creates a dropdown for selecting customer types
 * - FieldToSelectOneDate() - Creates a date input field
 * - FieldToSelectOneEntryFromArray() - Creates a dropdown from an array of values
 * - FieldToSelectOneFile() - Creates a file upload input field
 * - FieldToSelectOneGLAccountGroup() - Creates a dropdown for selecting GL account groups
 * - FieldToSelectOneLocation() - Creates a dropdown for selecting a location
 * - FieldToSelectOnePassword() - Creates a password input field
 * - FieldToSelectOnePeriod() - Creates a dropdown for selecting an accounting period
 * - FieldToSelectOneSalesArea() - Creates a dropdown for selecting a sales area
 * - FieldToSelectOneSalesPerson() - Creates a dropdown for selecting a sales person
 * - FieldToSelectOneStockCategory() - Creates a dropdown for selecting a stock category
 * - FieldToSelectOneSysType() - Creates a dropdown for selecting a system type
 * - FieldToSelectOneText() - Creates a text input field
 * - FieldToSelectOneTextArea() - Creates a text area input field
 * - FieldToSelectOneEmail() - Creates an email input field
 * - FixedField() - Creates a read-only field displaying a value
 * - OneButtonCenteredForm() - Creates a centered form with one submit button
 * - TwoButtonsCenteredForm() - Creates a centered form with submit and reset buttons
 * - FieldToSelectFromFiveOptions() - Creates a dropdown with five options
 * - FieldToSelectFromFourOptions() - Creates a dropdown with four options
 * - FieldToSelectFromThreeOptions() - Creates a dropdown with three options
 * - FieldToSelectFromTwoOptions() - Creates a dropdown with two options
 * - FieldToSelectOneBrand() - Creates a dropdown for selecting a brand
 * - FieldToSelectSpreadSheetFormat() - Creates a dropdown for selecting spreadsheet formats
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

function FieldToSelectOneEntryFromArray($Array, $VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	foreach ($Array as $Entry => $Name){
		if (isset($SelectedValue) AND (strtoupper($SelectedValue) == strtoupper($Entry))){
			$HTML .=  '<option selected="selected" value="' . $Entry . '">' . $Name  . '</option>';
		} else {
			$HTML .=  '<option value="' . $Entry . '">' . $Name  . '</option>';
		}
	}

	$HTML .= '</select>
			</field>';
	return $HTML;
}


function FieldToSelectOneCustomerType($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
	$SQL = "SELECT typename,
				typeid
			FROM debtortype
			ORDER BY typename";

	$Result = DB_query($SQL);

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if ($Required){
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
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectOneDate($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<input type="date"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= ' name="' . $VariableName . '" size="11" maxlength="10" value="' .  FormatDateForSQL($SelectedValue) . '" />
			</field>';
	return $HTML;
}

function FieldToSelectOneFile($VariableName, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<input type="file"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= ' id="' . $VariableName . '" name="' . $VariableName . '" />
			</field>';
	return $HTML;
}

function FieldToSelectOneGLAccountGroup($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
	$SQL = "SELECT groupname
			FROM accountgroups
			ORDER BY sequenceintb ASC";

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
		if (isset($SelectedValue) AND ($MyRow['groupname'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['groupname'] . '">' . $MyRow['groupname'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['groupname'] . '">' . $MyRow['groupname'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectOneLocation($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
	
	if ($Filter == 'CANVIEW') {    
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
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

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
	$HTML .= '</select>
			</field>';
	return $HTML;
}


function FieldToSelectOnePeriod($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
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
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	if (!isset($SelectedValue)) {
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
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectOneSalesArea($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$SQL = "SELECT areacode,
				areadescription
			FROM areas
			ORDER BY areadescription";

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
		if (isset($SelectedValue) AND ($MyRow['areacode'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


function FieldToSelectOneSalesPerson($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $AllowAll = false, $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

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
		$HTML .= '</select>
				</field>';
	}
	return $HTML;
}

function FieldToSelectOneStockCategory($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $AllowAll = false, $TabIndex = '', $Required = true, $AutoFocus = false) {

	$SQL = "SELECT categoryid, 
				categorydescription
			FROM stockcategory
			ORDER BY categorydescription";

	$Result = DB_query($SQL);


	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

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
		} 
		else {
			$HTML .= '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}


function FieldToSelectOneSysType($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$SQL = "SELECT typeid, 
				typename 
			FROM systypes 
			ORDER BY typename";

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
		if (isset($SelectedValue) AND ($MyRow['typeid'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['typeid'] . '">' . $MyRow['typename'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectOnePassword($VariableName, $SelectedValue, $Size, $MaxLength, $Label = '', $HelpText = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<input type="password" pattern=".{5,}"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= '" name="' . $VariableName . '"  placeholder="'.__('At least 5 characters').'" size="' . $Size . '" maxlength="' . $MaxLength . '" value="' . $SelectedValue . '" />
			</field>';
	return $HTML;
}


function FieldToSelectOneText($VariableName, $SelectedValue, $Size, $MaxLength, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<input type="text"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= '" name="' . $VariableName . '" size="' . $Size . '" maxlength="' . $MaxLength . '" value="' . $SelectedValue . '" />
			</field>';
	return $HTML;
}

function FieldToSelectOneEmail($VariableName, $SelectedValue, $Size, $MaxLength, $Label = '', $HelpText = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<input type="email" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= ' name="' . $VariableName . '" size="' . $Size . '" maxlength="' . $MaxLength . '" value="' . $SelectedValue . '" />
			</field>';
	return $HTML;
}

function FieldToSelectOneNumber($VariableName, $SelectedValue, $Size, $MaxLength, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<input type="text" class="number" pattern="[0-9]*"';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= '" name="' . $VariableName . '" size="' . $Size . '" maxlength="' . $MaxLength . '" value="' . $SelectedValue . '" />
			</field>';
	return $HTML;
}



function FieldToSelectOneTextArea($VariableName, $SelectedValue, $Cols, $Rows, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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

function FieldToSelectOneUser($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $AllowAll = false, $TabIndex = '', $Required = true, $AutoFocus = false) {

	$SQL = "SELECT userid, 
				realname 
			FROM www_users";

	$Result = DB_query($SQL);


	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<select';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $VariableName . '">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

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
		if (isset($SelectedValue) AND ($MyRow['userid'] == $SelectedValue)) {
			$HTML .= '<option selected="selected" value="' . $MyRow['userid'] . '">' . $MyRow['userid'] . '</option>';
		} 
		else {
			$HTML .= '<option value="' . $MyRow['userid'] . '">' . $MyRow['userid'] . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectMultipleLocations($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {
	if ($Filter == 'CANVIEW') {    
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
	$HTML .= 'minlength="1" size="12" name="' . $VariableName . '[]" multiple="multiple">
				<fieldhelp>' . $HelpText . '</fieldhelp>';
	
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($SelectedValue) AND in_array($MyRow['loccode'], $SelectedValue)) {
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


function FieldToSelectMultiplePeriods($VariableName, $FirstSelectedValue, $LastSelectedValue, $Label = '', $HelpText = '', $Filter = 'ASC', $TabIndex = '', $Required = true, $AutoFocus = false) {
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
	$HTML .= 'minlength="1" size="12" name="' . $VariableName . '[]" multiple="multiple">
				<fieldhelp>' . $HelpText . '</fieldhelp>';

	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($FirstSelectedValue) AND $MyRow['periodno'] >= $FirstSelectedValue AND $MyRow['periodno'] <= $LastSelectedValue) {
			$HTML .= '<option selected="selected" value="' . $MyRow['periodno'] . '">' . __(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
		}
		else {
			$HTML .= '<option value="' . $MyRow['periodno'] . '">' . __(MonthAndYearFromSQLDate($MyRow['lastdate_in_period'])) . '</option>';
		}
	}
	$HTML .= '</select>
			</field>';
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


function FixedField($VariableName, $SelectedValue, $Label = '', $HelpText = '') {

	$HTML = '<field>
				<label for="' . $VariableName . '">' . $Label . ':</label>
				<fieldhelp>' . $HelpText . '</fieldhelp>
				<fieldtext>' . $SelectedValue . '</fieldtext>
			</field>';
	return $HTML;
}


function OneButtonCenteredForm($ButtonName, $ButtonValue, $TabIndex = '', $Required = true, $AutoFocus = false) {
	$HTML = '<div class="centre">
				<input type="submit" ';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $ButtonName . '" value="' . $ButtonValue . '" />
			</div>';
	return $HTML;
}

function TwoButtonsCenteredForm($ButtonName1, $ButtonValue1, $ButtonName2, $ButtonValue2, $TabIndex = '', $Required = true, $AutoFocus = false) {
	$HTML = '<div class="centre">
				<input type="submit" ';
	$HTML .= AddAttributesToField($TabIndex, $Required, $AutoFocus);	
	$HTML .= 'name="' . $ButtonName1 . '" value="' . $ButtonValue1 . '" />
				<input type="reset" ';
	$HTML .= 'name="' . $ButtonName2 . '" value="' . $ButtonValue2 . '" />
			</div>';
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
	if ($SelectedValue == $ValueOption1) {
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
	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectFromFiveOptions($ValueOption1, $LabelOption1, 
									$ValueOption2, $LabelOption2, 
									$ValueOption3, $LabelOption3,
									$ValueOption4, $LabelOption4,
									$ValueOption5, $LabelOption5,
									$VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

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

	$HTML .= '</select>
			</field>';
	return $HTML;
}

function FieldToSelectSpreadSheetFormat($VariableName, $SelectedValue, $Label = '', $HelpText = '', $Filter = '', $TabIndex = '', $Required = true, $AutoFocus = false) {

	$HTML = FieldToSelectFromTwoOptions('xlsx', 'Excel 2007 (xlsx)',
										'ods', 'OpenDocument (ods)',
										$VariableName, $SelectedValue, $Label, $HelpText, $Filter, $TabIndex, $Required, $AutoFocus);

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
	
	if ($Required){
		$HTML .= '<option value="">' . __('Not Yet Selected') . '</option>';
	} elseif (!isset($SelectedValue)) {
		$HTML .= '<option selected="selected" value="">' . __('Not Yet Selected') . '</option>';
	}

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