<?php

/////////////////////////////////////////////////////////////////////
// Company FROM, company TO, Invocie consignment sales date
/////////////////////////////////////////////////////////////////////

echo FixedField("CompanyFrom", "PTADU", 'From', 'Select the company issuing the Faktur Pajak');	
echo FieldToSelectOneRetailPartner("CompanyTo", $_POST['CompanyTo'], _('To'), 'Select the company receiving the Faktur Pajak');
echo FieldToSelectOneDate('EndDate', $_POST['EndDate'], _('Invoice Consignment Sales until'), '');

echo '<field>
		<label>' . 'Draft or Invoice' . ':</label>
		<select name="DraftOrInvoice">';
if($_POST['DraftOrInvoice']=="DRAFT") {
	echo '<option selected="selected" value="DRAFT">' . 'Draft' . '</option>';
	echo '<option value="INVOICE">' . 'Invoice' . '</option>';
}
else {
	echo '<option selected="selected" value="INVOICE">' . 'Invoice' . '</option>';
	echo '<option value="DRAFT">' . 'Draft' . '</option>';
}
echo '</select>
	</field>';


?>
