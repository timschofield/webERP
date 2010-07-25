<?php

/* $Id:  $ */

$PageSecurity = 4;

include('includes/DefineContractClass.php');

include('includes/session.inc');
$title = _('Contract Other Requirements');

$identifier=$_GET['identifier'];

/* If a contract header doesn't exist, then go to
 * Contracts.php to create one
 */

if (!isset($_SESSION['Contract'.$identifier])){
	header('Location:' . $rootpath . '/Contracts.php?' . SID);
	exit;
} 
include('includes/header.inc');


if (isset($_POST['UpdateLines']) OR isset($_POST['BackToHeader'])) {
	if($_SESSION['Contract'.$identifier]->Status!=2){ //dont do anything if the customer has committed to the contract
		foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $ContractComponentID => $ContractRequirementItem) {
			
			if ($_POST['Qty'.$ContractComponentID]==0){
				//this is the same as deleting the line - so delete it
				$_SESSION['Contract'.$identifier]->Remove_ContractRequirement($ContractComponentID);
			} else {
				$_SESSION['Contract'.$identifier]->ContractReqts[$ContractComponentID]->Quantity=$_POST['Qty'.$ContractComponentID];
				$_SESSION['Contract'.$identifier]->ContractReqts[$ContractComponentID]->CostPerUnit=$_POST['CostPerUnit'.$ContractComponentID];
				$_SESSION['Contract'.$identifier]->ContractReqts[$ContractComponentID]->Requirement=$_POST['Requirement'.$ContractComponentID];
			} 
		} // end loop around the items on the contract requirements array
	} // end if the contract is not currently committed to by the customer
}// end if the user has hit the update lines or back to header buttons


if (isset($_POST['BackToHeader'])){
	echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/Contracts.php?' . SID . 'identifier='.$identifier. '">';
	echo '<p>';
	prnMsg(_('You should automatically be forwarded to the Contract page. If this does not happen perhaps the browser does not support META Refresh') .	'<a href="' . $rootpath . '/Contracts.php?' . SID. 'identifier='.$identifier . '">' . _('click here') . '</a> ' . _('to continue'),'info');
	include('includes/footer.inc');
	exit;
}


if(isset($_GET['Delete'])){
	if($_SESSION['Contract'.$identifier]->Status!=2){
		$_SESSION['Contract'.$identifier]->Remove_ContractRequirement($_GET['Delete']);
	} else {
		prnMsg( _('The other contract requirements cannot be altered because the customer has already placed the order'),'warn');
	}
}
if (isset($_POST['EnterNewRequirement'])){
	$InputError = false;
	if (!is_numeric($_POST['Quantity'])){
		prnMsg(_('The quantity of the new requirement is expected to be numeric'),'error');
		$InputError = true;
	}
	if (!is_numeric($_POST['CostPerUnit'])){
		prnMsg(_('The cost per unit of the new requirement is expected to be numeric'),'error');
		$InputError = true;
	}
	if (!$InputError){
		$_SESSION['Contract'.$identifier]->Add_To_ContractRequirements ($_POST['RequirementDescription'],
																		$_POST['Quantity'],
																		$_POST['CostPerUnit']);	
		unset($_POST['RequirementDescription']);
		unset($_POST['Quantity']);
		unset($_POST['CostPerUnit']);
	}
}

/* This is where the other requirement as entered/modified should be displayed reflecting any deletions or insertions*/

echo '<form name="ContractReqtsForm" action="' . $_SERVER['PHP_SELF'] . '?' . SID . 'identifier='.$identifier. '" method="post">';
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/contract.png" title="' .
		_('Contract Other Requirements') . '" alt="">  ' . _('Contract Other Requirements') . ' - ' . $_SESSION['Contract'.$identifier]->CustomerName;
		
if (count($_SESSION['Contract'.$identifier]->ContractReqts)>0){
	

	if (isset($_SESSION['Contract'.$identifier]->ContractRef)) {
		echo  ' ' . _('Contract Reference:') .' '. $_SESSION['Contract'.$identifier]->ContractRef;
	}
	
	echo '<table cellpadding=2 colspan=7 border=1>';
	echo '<tr>
		<th>' . _('Description') . '</th>
		<th>' . _('Quantity') . '</th>
		<th>' . _('Unit Cost') .  '</th>
		<th>' . _('Sub-total') . '</th>
		</tr>';

	$_SESSION['Contract'.$identifier]->total = 0;
	$k = 0;  //row colour counter
	$TotalCost =0;
	foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $ContractReqtID => $ContractComponent) {

		$LineTotal = $ContractComponent->Quantity * $ContractComponent->CostPerUnit;
			
		$DisplayLineTotal = number_format($LineTotal,2);
		
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		
		echo '<td><textarea name="Requirement' . $ContractReqtID . '" cols="30" rows="3">' . $ContractComponent->Requirement . '</textarea></td>
			  <td><input type=text class="number" name="Qty' . $ContractReqtID . '" size="11" value="' . $ContractComponent->Quantity  . '"></td>
			  <td><input type=text class="number" name="CostPerUnit' . $ContractReqtID . '" size="11" value="' . $ContractComponent->CostPerUnit . '"></td>
			  <td class="number">' . $DisplayLineTotal . '</td>
			  <td><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . 'identifier='.$identifier. '&Delete=' . $ContractReqtID . '">' . _('Delete') . '</a></td></tr>';
		$TotalCost += $LineTotal;
	}

	$DisplayTotal = number_format($TotalCost,2);
	echo '<tr><td colspan="4" class="number">' . _('Total Other Requirements Cost') . '</td><td class="number"><b>' . $DisplayTotal . '</b></td></tr></table>';
	echo '<br><div class="centre"><input type="submit" name="UpdateLines" value="' . _('Update Other Requirements Lines') . '">';
	echo ' <input type="submit" name="BackToHeader" value="' . _('Back To Contract Header') . '">';
	
} /*Only display the contract other requirements lines if there are any !! */

echo '<hr>';
/*Now show  form to add new requirements to the contract */
echo '<table>
		<tr><th colspan="2">' . _('Enter New Requirements') . '</th></tr>
		<tr><td>' . _('Requirement Description') . '</td>
		<td><textarea name="RequirementDescription" cols="30" rows="3">' . $_POST['RequirementDescription'] . '</textarea></td></tr>';
echo '<tr><td>' . _('Quantity Required') . ':</td><td><input type="text" name="Quantity" size=10	maxlength=10 value="' . $_POST['Quantity'] . '"></td></tr>';
echo '<tr><td>' . _('Cost Per Unit') . ':</td><td><input type="text" name="CostPerUnit" size=10	maxlength=10 value="' . $_POST['CostPerUnit'] . '"></td></tr>';
echo '</table>';

echo '<div class="centre"><input type="submit" name="EnterNewRequirement" value="' . _('Enter New Contract Requirement') . '"></div>';

echo '</form>';
include('includes/footer.inc');
?>