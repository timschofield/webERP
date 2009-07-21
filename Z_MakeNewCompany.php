<?php
/* $Revision: 1.13 $ */

$PageSecurity = 15;

include ('includes/session.inc');
include ('includes/SQL_CommonFunctions.inc');

/* Was the Cancel button pressed the last time through ? */

if (isset($_POST['EnterCompanyDetails'])) {

	header ('Location:' . $rootpath . '/CompanyPreferences.php?' . SID);
	exit;
}

$title = _('Make New Company Database Utility');

include('includes/header.inc');

/* Your webserver user MUST have read/write access to here,
	otherwise you'll be wasting your time */
if (! is_writeable('./companies/')){
		prnMsg(_('The web-server does not appear to be able to write to the companies directory to create the required directories for the new company and to upload the logo to. The system administrator will need to modify the permissions on your installation before a new company can be created'),'error');
		include('includes/footer.inc');
		exit;
}

 
if (isset($_POST['submit']) AND isset($_POST['NewCompany'])) {
	
	if(strlen($_POST['NewCompany'])>32 
		OR ContainsIllegalCharacters($_POST['NewCompany'])){
		prnMsg(_('Company abbreviations must not contain spaces, \& or " or \''),'error');
	} else {
				
		$_POST['NewCompany'] = strtolower($_POST['NewCompany']);
		echo '<div class="centre">';
		echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
		/* check for directory existence */
		if (!file_exists('./companies/' . $_POST['NewCompany']) 
				AND (isset($_FILES['LogoFile']) AND $_FILES['LogoFile']['name'] !='')) {
			
			$result    = $_FILES['LogoFile']['error'];
			$UploadTheLogo = 'Yes'; //Assume all is well to start off with
			$filename = './companies/' . $_POST['NewCompany'] . '/logo.jpg';
						
			//But check for the worst 
			if (strtoupper(substr(trim($_FILES['LogoFile']['name']),strlen($_FILES['LogoFile']['name'])-3))!='JPG'){
				prnMsg(_('Only jpg files are supported - a file extension of .jpg is expected'),'warn');
				$UploadTheLogo ='No';
			} elseif ( $_FILES['LogoFile']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
				prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
				$UploadTheLogo ='No';
			} elseif ( $_FILES['LogoFile']['type'] == "text/plain" ) {  //File Type Check
				prnMsg( _('Only graphics files can be uploaded'),'warn');
				$UploadTheLogo ='No';
			} elseif (file_exists($filename)){
				prnMsg(_('Attempting to overwrite an existing item image'),'warn');
				$result = unlink($filename);
				if (!$result){
					prnMsg(_('The existing image could not be removed'),'error');
					$UploadTheLogo ='No';
				}
			}
			
			if ($_POST['CreateDB']==TRUE){
				/* Need to read in the sql script and process the queries to initate a new DB */
			
				$result = DB_query('CREATE DATABASE ' . $_POST['NewCompany'],$db);
			
				if ($dbType=='postgres'){
					
					$PgConnStr = 'dbname=' . $_POST['NewCompany'];
					if ( isset($host) && ($host != "")) {
						$PgConnStr = 'host=' . $host . ' ' . $PgConnStr;
					}
	
					if (isset( $dbuser ) && ($dbuser != "")) {
						// if we have a user we need to use password if supplied
						$PgConnStr .= " user=".$dbuser;
						if ( isset( $dbpassword ) && ($dbpassword != "") ) {
							$PgConnStr .= " password=".$dbpassword;
						}
					}
					$db = pg_connect( $PgConnStr );
					$SQLScriptFile = file('./sql/pg/weberp-new.psql');
				
				} elseif ($dbType =='mysql') { //its a mysql db < 4.1
					mysql_select_db($_POST['NewCompany'],$db);
					$SQLScriptFile = file('./sql/mysql/weberp-new.sql');
				} elseif ($dbType =='mysqli') { //its a mysql db using the >4.1 library functions
					mysqli_select_db($db,$_POST['NewCompany']);
					$SQLScriptFile = file('./sql/mysql/weberp-new.sql');
				}
					
				$ScriptFileEntries = sizeof($SQLScriptFile);
				$ErrMsg = _('The script to create the new company database failed because');
				$SQL ='';
				$InAFunction = false;
				
				for ($i=0; $i<=$ScriptFileEntries; $i++) {
					
					$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);
						
					if (substr($SQLScriptFile[$i], 0, 2) != '--' 
						AND substr($SQLScriptFile[$i], 0, 3) != 'USE' 
						AND strstr($SQLScriptFile[$i],'/*')==FALSE 
						AND strlen($SQLScriptFile[$i])>1){
							
						$SQL .= ' ' . $SQLScriptFile[$i];
	
						//check if this line kicks off a function definition - pg chokes otherwise
						if (substr($SQLScriptFile[$i],0,15) == 'CREATE FUNCTION'){
							$InAFunction = true;
						}
						//check if this line completes a function definition - pg chokes otherwise
						if (substr($SQLScriptFile[$i],0,8) == 'LANGUAGE'){
							$InAFunction = false;
						}
						if (strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
							$SQL = substr($SQL,0,strlen($SQL)-1);
							$result = DB_query($SQL, $db, $ErrMsg);
							$SQL='';
						}
						
					} //end if its a valid sql line not a comment
				} //end of for loop around the lines of the sql script
			} //end if CreateDB was checked
			
			prnMsg (_('Attempting to create the new company directories') . '.....<br>', 'info');
			$Result = mkdir('./companies/' . $_POST['NewCompany']);
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/part_pics');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/EDI_Incoming_Orders');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/reports');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/EDI_Sent');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/EDI_Pending');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/reportwriter');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/pdf_append');
				
			/*OK Now upload the logo */
			if ($UploadTheLogo=='Yes'){
				$result  =  move_uploaded_file($_FILES['LogoFile']['tmp_name'], $filename);
				$message = ($result)?_('File url') ."<a href='". $filename ."'>" .  $filename . '</a>' : _('Something is wrong with uploading a file');
			}
			
		} else {
			prnMsg(_('This company cannot be added because either it already exists or no logo is being uploaded!'),'error');
			if (isset($_FILES['LogoFile'])){
				prnMsg(_('Files LogoFile is set ok'),'info');
			} else  {
				prnMsg('_FILES[LogoFile] ' ._('is not set'),'info');
			}
			if($_FILES['LogoFile']['name'] !=''){
				prnMsg( '_FILES[LogoFile][name] '  . _('is not blank'),'info');
			} else  {
				prnMsg('_FILES[LogoFile][name] ' ._('is blank'),'info');
			}
			
  			echo '</form>';
	  		echo '</div>';
			include('includes/footer.inc');
			exit;
		}
	
		$_SESSION['DatabaseName'] = $_POST['NewCompany'];
		
		unset ($_SESSION['CustomerID']);
		unset ($_SESSION['SupplierID']);
		unset ($_SESSION['StockID']);
		unset ($_SESSION['Items']);
		unset ($_SESSION['CreditItems']);
		
		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/EDI__Sent' WHERE confname='EDI_MsgSent'";
		$result = DB_query($SQL,$db);
		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/EDI_Incoming_Orders' WHERE confname='EDI_Incoming_Orders'";
		$result = DB_query($SQL,$db);
		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/part_pics' WHERE confname='part_pics_dir'";
		$result = DB_query($SQL,$db);
		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/reports' WHERE confname='reports_dir'";
		$result = DB_query($SQL,$db);
		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewCompany'] . "/EDI_Pending' WHERE confname='EDI_MsgPending'";
		$result = DB_query($SQL,$db);
		
		$ForceConfigReload=true;
		include('includes/GetConfig.php');
		
		prnMsg (_('The new company database has been created for' . ' ' . $_POST['NewCompany'] . '. ' . _('The company details and parameters should now be set up for the new company. NB: Only a single user "demo" is defined with the password "weberp" in the new company database. A new system administrator user should be defined for the new company and this account deleted immediately.')), 'info');
		
		echo '<p><a href="' . $rootpath . '/CompanyPreferences.php?' . SID .'">' . _('Set Up New Company Details') . '</a>';
		echo '<p><a href="' . $rootpath . '/SystemParameters.php?' . SID .'">' . _('Set Up Configuration Details') . '</a>';
		echo '<p><a href="' . $rootpath . '/WWW_Users.php?' . SID .'">' . _('Set Up User Accounts') . '</a>';
		
		echo '</form>';
		echo '</div>';
		include('includes/footer.inc');
		exit;
	}

}


echo '<div class="centre">';
echo '<br>';
prnMsg (_('This utility will create a new company') . '<br><br>' .
		_('If the company name already exists then you cannot recreate it'), 'info', _('PLEASE NOTE'));
echo '<br>';
echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' enctype="multipart/form-data">';

echo '<table><tr>';
echo '<td>' . _('Enter up to 32 character lower case character abbreviation for the company') . '</td>
	<td><input type="TEXT" size="33" maxlength=32 name="NewCompany"></td></tr>
	<tr><td>'. _('Logo Image File (.jpg)') . ':</td><td><input type="FILE" ID="LogoFile" name="LogoFile"></td></tr>
	<tr><td>' . _('Create Database?') . '</td><td><input type="CHECKBOX" name="CreateDB"></td></tr>
	</table>';

echo '<br><input type="SUBMIT" name="submit" VALUE="' . _('Proceed') . '">&nbsp;&nbsp;&nbsp;&nbsp;';
echo '</form>';
echo '</div>';

include('includes/footer.inc');
?>