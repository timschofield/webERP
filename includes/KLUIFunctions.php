<?php

/**********************************************************************************************************
 * 
 * KL RICARD: KL Specific UI functions
 * 
 *********************************************************************************************************/

function FieldToSelectOneRetailPartner($VariableName, $SelectedValue, $Label, $HelpText) {

    $SQL = "SELECT partnercode, 
                partnernameinvoice 
            FROM klretailpartners
            WHERE partnercode != 'NORETAIL'
            ORDER BY partnername";
    $Result = DB_query($SQL);

    $HTML = '<field>
                <label for="' . $VariableName . '">' . $Label . ':</label>
                <select name="' . $VariableName . '">
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

?>
