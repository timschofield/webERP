<?php
include('includes/GetPrice.inc');
/* $Id: Z_POS_Data.php 3843 2010-09-30 14:49:45Z daintree $*/
$PageSecurity = 9;

/* Note: For really large databases need to change config.php MaxExecutionTime = 1000; or similar */

include('includes/session.inc');

$title = _('Create POS Data Upload File');

include('includes/header.inc');

if (isset($_GET['Delete'])){
	unlink($_SESSION['reports_dir'] . '/POS.sql');
	unlink($_SESSION['reports_dir'] . '/POS.sql.zip');
	prnMsg(_('Old POS upload files deleted'),'info');
}


if (!isset($_GET['POSDebtorNo']) AND !isset($_GET['POSBranchCode'])){
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Create POS Data File') . '" alt="">' . ' ' . $title.'<br />';
	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '><br>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	//Need to get POS customer as need to know currency for prices and the sale type for prices
	if (!isset($_POST['POSDebtorNo'])){
		$_POST['POSDebtorNo']='';
	}
	if (!isset($_POST['POSBranchCode'])){
		$_POST['POSBranchCode']='';
	}
	echo '<table class="selection">
				<tr>
					<td>' . _('POS Customer Code') . ':</td>
					<td><input tabindex="1"  type="text" name="POSDebtorNo" value="' . $_POST['POSDebtorNo'] . '" size="12" maxlength="12" /></td>
				</tr>
				<tr>
					<td>' . _('POS Branch Code') . ':</td>
					<td><input tabindex="2" type="text" name="POSBranchCode" value="' . $_POST['POSBranchCode'] .'" size="12" maxlength="12" /></td>
			</tr>';

		echo '<tr><td colspan=2><div class="centre"><input tabindex="3" type="Submit" name="CreatePOSDataFile" value=' . _('Create POS Data File') .'></div></td></tr>';
		echo '</table></form>';
} else {
  $_POST['POSDebtorNo'] = $_GET['POSDebtorNo'];
  $_POST['POSBranchCode'] = $_GET['POSBranchCode'];
  $_POST['CreatePOSDataFile'] = 'Yes Please';
}

if (isset($_POST['CreatePOSDataFile'])){

	$InputError =0;

	if (!isset($_POST['POSDebtorNo']) OR $_POST['POSDebtorNo'] == ''){
		prnMsg(_('Cannot create POS Data file without the POS Customer Code'),'error');
		$InputError =1;
	} elseif(!isset($_POST['POSBranchCode']) OR $_POST['POSBranchCode']==''){
	    prnMsg(_('Cannot create POS Data file without the POS Customer Branch Code'),'error');
		$InputError =1;
	}

	if ($InputError ==0) {

		include('includes/Z_POSDataCreation.php');
		if (Create_POS_Data_Full($_POST['POSDebtorNo'],$_POST['POSBranchCode'],'./', $db) == 1 ){
			echo '<br />
			 	  <br />
			 	  <a href="' . $_SESSION['reports_dir'] . '/POS.sql.zip">' . _('Download POS Upload File') . '</a>';
		} else {
			prnMsg(_('Unable to create POS Data file - perhaps the POS Customer Code or Branch Code do not exist'),'error');
		}

	} // end if no input errors
} //hit create POSDataFile

if (file_exists($_SESSION['reports_dir'] . '/POS.sql.zip')){
	prnMsg(_('It is important to delete the POS Data file after it has been retrieved - use the link below to delete it'),'warn');
	echo '<p><a href="' . $_SERVER['PHP_SELF'] . '?Delete=Yes">' . _('Delete the POS Upload File') . '</a></p>';
}
include('includes/footer.inc');
?>
