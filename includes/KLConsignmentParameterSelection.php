<?php

/////////////////////////////////////////////////////////////////////
// Company FROM, company TO, Invocie consignment sales date
/////////////////////////////////////////////////////////////////////

	echo '<tr>
			<td>' . 'From' . ':</td>
			<td><select name="CompanyFrom">';
	if($_POST['CompanyFrom']=="PTADU") {
		echo '<option selected="selected" value="PTADU">' . 'PT ADU' . '</option>';
		echo '<option value="CASH">' . 'PT BB (temporary until end of stock)' . '</option>';
	} else {
		echo '<option selected="selected" value="CASH">' . 'PT BB (temporary until end of stock)' . '</option>';
		echo '<option value="PTADU">' . 'PT ADU' . '</option>';
	}
	echo '</select></td></tr>';	

	echo '<tr>
			<td>' . 'To' . ':</td>
			<td><select name="CompanyTo">';
	$sql="SELECT partnercode, 
				partnernameinvoice 
		FROM klretailpartners 
		WHERE partnercode != 'NORETAIL' ";
	$PartnerResult= DB_query($sql);
	while ($myrow = DB_fetch_array($PartnerResult)){
		if ($_POST['CompanyTo']==$myrow['partnercode']){
			echo '<option selected="selected" value="' . $myrow['partnercode'] . '">' . $myrow['partnernameinvoice']  . '</option>';
		}else{
			echo '<option value="' . $myrow['partnercode'] . '">' . $myrow['partnernameinvoice']  . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . _('Invoice Consignment Sales until') . '</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="EndDate" size="10" maxlength="10" value="' . $_POST['EndDate'] . '" /></td>
		</tr>';
	


?>
