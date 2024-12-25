<?php

/////////////////////////////////////////////////////////////////////
// Company FROM, company TO, Invocie consignment sales date
/////////////////////////////////////////////////////////////////////

	echo '<tr>
			<td>' . 'From' . ':</td>
			<td><select name="CompanyFrom">';
	if($_POST['CompanyFrom']=="PTADU") {
		echo '<option selected="selected" value="PTADU">' . 'PT ADU' . '</option>';
//		echo '<option value="CASH">' . 'PT BB (temporary until end of stock)' . '</option>';
	} else {
//		echo '<option selected="selected" value="CASH">' . 'PT BB (temporary until end of stock)' . '</option>';
		echo '<option value="PTADU">' . 'PT ADU' . '</option>';
	}
	echo '</select></td></tr>';	

	echo '<tr>
			<td>' . 'To' . ':</td>
			<td><select name="CompanyTo">';
	$SQL="SELECT partnercode, 
				partnernameinvoice 
		FROM klretailpartners 
		WHERE partnercode != 'NORETAIL' ";
	$PartnerResult= DB_query($SQL);
	while ($MyRow = DB_fetch_array($PartnerResult)){
		if ($_POST['CompanyTo']==$MyRow['partnercode']){
			echo '<option selected="selected" value="' . $MyRow['partnercode'] . '">' . $MyRow['partnernameinvoice']  . '</option>';
		}else{
			echo '<option value="' . $MyRow['partnercode'] . '">' . $MyRow['partnernameinvoice']  . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . _('Invoice Consignment Sales until') . '</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="EndDate" size="10" maxlength="10" value="' . $_POST['EndDate'] . '" /></td>
		</tr>';
	
	echo '<tr>
			<td>' . 'Draft or Invoice' . ':</td>
			<td><select name="DraftOrInvoice">';
	if($_POST['DraftOrInvoice']=="DRAFT") {
		echo '<option selected="selected" value="DRAFT">' . 'Draft' . '</option>';
		echo '<option value="INVOICE">' . 'Invoice' . '</option>';
	} else {
		echo '<option selected="selected" value="INVOICE">' . 'Invoice' . '</option>';
		echo '<option value="DRAFT">' . 'Draft' . '</option>';
	}
	echo '</select></td></tr>';	


?>
