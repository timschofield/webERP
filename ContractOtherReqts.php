<?php

/// @todo move to after session.php inclusion, unless there are side effects
include('includes/DefineContractClass.php');

require(__DIR__ . '/includes/session.php');

$identifier = $_GET['identifier'];

/* If a contract header doesn't exist, then go to
 * Contracts.php to create one
 */
if (!isset($_SESSION['Contract'.$identifier])){
	header('Location:' . htmlspecialchars_decode($RootPath) . '/Contracts.php');
	exit();
}

$Title = __('Contract Other Requirements');
$ViewTopic = 'Contracts';
$BookMark = 'AddToContract';
include('includes/header.php');

if (isset($_POST['UpdateLines']) OR isset($_POST['BackToHeader'])) {
	if($_SESSION['Contract'.$identifier]->Status!=2){ //dont do anything if the customer has committed to the contract
		foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $ContractComponentID => $ContractRequirementItem) {

			if (filter_number_format($_POST['Qty'.$ContractComponentID])==0){
				//this is the same as deleting the line - so delete it
				$_SESSION['Contract'.$identifier]->Remove_ContractRequirement($ContractComponentID);
			} else {
				$_SESSION['Contract'.$identifier]->ContractReqts[$ContractComponentID]->Quantity=filter_number_format($_POST['Qty'.$ContractComponentID]);
				$_SESSION['Contract'.$identifier]->ContractReqts[$ContractComponentID]->CostPerUnit=filter_number_format($_POST['CostPerUnit'.$ContractComponentID]);
				$_SESSION['Contract'.$identifier]->ContractReqts[$ContractComponentID]->Requirement=$_POST['Requirement'.$ContractComponentID];
			}
		} // end loop around the items on the contract requirements array
	} // end if the contract is not currently committed to by the customer
}// end if the user has hit the update lines or back to header buttons


if (isset($_POST['BackToHeader'])){
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/Contracts.php?identifier='.$identifier. '" />';
	echo '<br />';
	prnMsg(__('You should automatically be forwarded to the Contract page. If this does not happen perhaps the browser does not support META Refresh') .	'<a href="' . $RootPath . '/Contracts.php?identifier='.$identifier . '">' . __('click here') . '</a> ' . __('to continue'),'info');
	include('includes/footer.php');
	exit();
}


if(isset($_GET['Delete'])){
	if($_SESSION['Contract'.$identifier]->Status!=2){
		$_SESSION['Contract'.$identifier]->Remove_ContractRequirement($_GET['Delete']);
	} else {
		prnMsg( __('The other contract requirements cannot be altered because the customer has already placed the order'),'warn');
	}
}
if (isset($_POST['EnterNewRequirement'])){
	$InputError = false;
	if (!is_numeric(filter_number_format($_POST['Quantity']))){
		prnMsg(__('The quantity of the new requirement is expected to be numeric'),'error');
		$InputError = true;
	}
	if (!is_numeric(filter_number_format($_POST['CostPerUnit']))){
		prnMsg(__('The cost per unit of the new requirement is expected to be numeric'),'error');
		$InputError = true;
	}
	if (!$InputError){
		$_SESSION['Contract'.$identifier]->Add_To_ContractRequirements ($_POST['RequirementDescription'],
																		filter_number_format($_POST['Quantity']),
																		filter_number_format($_POST['CostPerUnit']));
		unset($_POST['RequirementDescription']);
		unset($_POST['Quantity']);
		unset($_POST['CostPerUnit']);
	}
}

/* This is where the other requirement as entered/modified should be displayed reflecting any deletions or insertions*/

echo '<form name="ContractReqtsForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . urlencode($identifier) . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/contract.png" title="' . __('Contract Other Requirements') . '" alt="" />  ' . __('Contract Other Requirements') . ' - ' . $_SESSION['Contract'.$identifier]->CustomerName . '</p>';

if (count($_SESSION['Contract'.$identifier]->ContractReqts)>0){

	echo '<table class="selection">';

	if (isset($_SESSION['Contract'.$identifier]->ContractRef)) {
		echo  '<tr>
				<th colspan="5">' . __('Contract Reference') . ': '. $_SESSION['Contract'.$identifier]->ContractRef . '</th>
			</tr>';
	}

	echo '<tr>
			<th>' . __('Description') . '</th>
			<th>' . __('Quantity') . '</th>
			<th>' . __('Unit Cost') .  '</th>
			<th>' . __('Sub-total') . '</th>
		</tr>';

	$_SESSION['Contract'.$identifier]->total = 0;

	$TotalCost =0;
	foreach ($_SESSION['Contract'.$identifier]->ContractReqts as $ContractReqtID => $ContractComponent) {

		$LineTotal = $ContractComponent->Quantity * $ContractComponent->CostPerUnit;
		$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['CompanyRecord']['decimalplaces']);

		echo '<tr class="striped_row">
				<td><textarea name="Requirement' . $ContractReqtID . '" cols="30" rows="3" required="required" title="' . __('Enter a description of this requirement for the contract') . '" >' . $ContractComponent->Requirement . '</textarea></td>
			  <td><input type="text" class="number" required="required" title="' . __('Enter the quantity of this requirement for the contract') . '" name="Qty' . $ContractReqtID . '" size="11" value="' . locale_number_format($ContractComponent->Quantity,'Variable')  . '" /></td>
			  <td><input type="text" class="number" name="CostPerUnit' . $ContractReqtID . '" size="11" required="required" value="' . locale_number_format($ContractComponent->CostPerUnit,$_SESSION['CompanyRecord']['decimalplaces']) . '" /></td>
			  <td class="number">' . $DisplayLineTotal . '</td>
			  <td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier. '&amp;Delete=' . $ContractReqtID . '" onclick="return confirm(\'' . __('Are you sure you wish to delete this contract requirement?') . '\');">' . __('Delete') . '</a></td>
			  </tr>';
		$TotalCost += $LineTotal;
	}

	$DisplayTotal = locale_number_format($TotalCost,$_SESSION['CompanyRecord']['decimalplaces']);
	echo '<tr>
			<td colspan="4" class="number">' . __('Total Other Requirements Cost') . '</td>
			<td class="number"><b>' . $DisplayTotal . '</b></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="UpdateLines" value="' . __('Update Other Requirements Lines') . '" />
			<input type="submit" name="BackToHeader" value="' . __('Back To Contract Header') . '" />
		</div>';

} /*Only display the contract other requirements lines if there are any !! */

echo '<br />';
/*Now show  form to add new requirements to the contract */
if (!isset($_POST['RequirementDescription'])) {
	$_POST['RequirementDescription']='';
	$_POST['Quantity']=0;
	$_POST['CostPerUnit']=0;
}
echo '<table class="selection">
		<tr>
			<th colspan="2">' . __('Enter New Requirements') . '</th>
		</tr>
		<tr>
			<td>' . __('Requirement Description') . '</td>
			<td><textarea name="RequirementDescription" cols="30" rows="3" minlength="5" title="' . __('Enter a description of this requirement for the contract') . '" >' . $_POST['RequirementDescription'] . '</textarea></td>
		</tr>
		<tr>
			<td>' . __('Quantity Required') . ':</td>
			<td><input type="text" class="number" name="Quantity" required="required" title="' . __('Enter the quantity of this requirement for the contract') . '" size="10"	maxlength="10" value="' . $_POST['Quantity'] . '" /></td>
		</tr>
		<tr>
			<td>' . __('Cost Per Unit') . ':</td>
			<td><input type="text" class="number" name="CostPerUnit" size="10" required="required" title="' . __('Enter the cost per unit of this requirement') . '" maxlength="10" value="' . $_POST['CostPerUnit'] . '" /></td>
		</tr>

		</table>

		<br />
		<div class="centre">
			<input type="submit" name="EnterNewRequirement" value="' . __('Enter New Contract Requirement') . '" />
		</div>
		</div>
		</form>';

include('includes/footer.php');
