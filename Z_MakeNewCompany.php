<?php
/* $Revision: 1.1 $ */

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

if (isset($_POST['submit']) AND isset($_POST['NewCompany'])) {
	
	if(strlen($_POST['NewCompany'])>10 
		OR ContainsIllegalCharacters($_POST['NewCompany'])){
		
		prnMsg(_('Company abbreviations must not contain spaces, \& or " or \''),'error');
	} else {
				
		$_POST['NewCompany'] = strtolower($_POST['NewCompany']);
		
		echo '<CENTER>';
		echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	
		/* check for directory existence */
		    
		if (!file_exists('./companies/' . $_POST['NewCompany']) 
				AND (isset($_FILES['LogoFile']) AND $_FILES['LogoFile']['name'] !='')) {
			
			prnMsg (_('Attempting to create the new company directories') . '.....<BR>', 'info');
			$Result = mkdir('./companies/' . $_POST['NewCompany']);
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/part_pics');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/EDI_Incoming_Orders');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/reports');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/EDI_Sent');
			$Result = mkdir('./companies/' . $_POST['NewCompany'] . '/EDI_Pending');
				
		/*OK Now upload the logo */
		
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
			
			if ($UploadTheLogo=='Yes'){
				$result  =  move_uploaded_file($_FILES['LogoFile']['tmp_name'], $filename);
				$message = ($result)?_('File url') ."<a href='". $filename ."'>" .  $filename . '</a>' : _('Something is wrong with uploading a file');
			}
			
			/* Need to read in the sql script and process the queries to initate a new DB */
			
			$result = DB_query('CREATE DATABASE ' . $_POST['NewCompany'],$db);
			
			if ($dbType=='mysql'){
				
				mysql_select_db($_POST['NewCompany'],$db);
				$SQLScriptFile = file('./sql/mysql/weberp-new.sql');
				
			} elseif($dbType=='postgres'){
				
				unset($db);
				
				$PgConnStr = 'dbname=' . $_POST['NewCompany'];

				if ( isset($host) && ($host != "")) {
					$PgConnStr = 'host='.$host.' '.$PgConnStr;
				} 

				if ( isset( $dbuser ) && ($dbuser != "") ) {
    				// if we have a user we need to use password if supplied
    					$PgConnStr .= " user=".$dbuser;
    					if ( isset( $dbpassword ) && ($dbpassword != "") ) {
						$PgConnStr .= " password=".$dbpassword;
    					}
				}
				
				$db = pg_connect( $PgConnStr );
				$SQLScriptFile = file('./sql/pg/weberp-new.psql');
			}
			
			$ScriptFileEntries = sizeof($SQLScriptFile);
			$ErrMsg = _('The script to create the new company database failed because');
			$SQL ='';
			for ($i=0; $i<=$ScriptFileEntries; $i++) {
												
				if (strstr($SQLScriptFile[$i],'--') == FALSE 
					AND strstr($SQLScriptFile[$i],addslashes('/*'))==FALSE 
					AND strlen($SQLScriptFile[$i])>1){
					
					$SQL .= ' ' . trim($SQLScriptFile[$i]);
					
					if (strpos($SQLScriptFile[$i],';')>0){
						$SQL = substr($SQL,0,strlen($SQL)-1);
						$result = DB_query($SQL,$db,$ErrMsg);
						$SQL='';
					}
				}
			}
			
			/*Now create directory for the session files to be held 
			Change required to always store session files in a company subdirectory of the default session directory*/
			
		
		
		} else {
			prnMsg(_('This language cannot be added because either it already exists or no logo is being uploaded!'),'error');
			if (isset($_FILES['LogoFile'])){
				echo '<BR>Files LogoFile is set ok';
			} else  {
				echo '<BR>_FILES[LogoFile] is not set';
			}
			if($_FILES['LogoFile']['name'] !=''){
				echo '<BR>_FILES[LogoFile][name] is not blank';
			} else  {
				echo '<BR>_FILES[LogoFile][name] is blank';
			}
			
  			echo '</FORM>';
	  		echo '</CENTER>';
			include('includes/footer.inc');
			exit;
		}
		
		$_SESSION['DatabaseName'] = $_POST['NewCompany'];
		
		unset ($_SESSION['CustomerID']);
		unset ($_SESSION['SupplierID']);
		unset ($_SESSION['StockID']);
		unset ($_SESSION['Items']);
		unset ($_SESSION['CreditItems']);
		
		$SQL ='UPDATE config SET confvalue="companies/' . $_POST['NewCompany'] . '/EDI__Sent" WHERE confname="EDI_MsgSent"';
		$result = DB_query($SQL,$db);
		$SQL ='UPDATE config SET confvalue="companies/' . $_POST['NewCompany'] . '/EDI_Incoming_Orders" WHERE confname="EDI_Incoming_Orders"';
		$result = DB_query($SQL,$db);
		$SQL ='UPDATE config SET confvalue="companies/' . $_POST['NewCompany'] . '/part_pics" WHERE confname="part_pics_dir"';
		$result = DB_query($SQL,$db);
		$SQL ='UPDATE config SET confvalue="companies/' . $_POST['NewCompany'] . '/reports" WHERE confname="reports_dir"';
		$result = DB_query($SQL,$db);
		$SQL ='UPDATE config SET confvalue="companies/' . $_POST['NewCompany'] . '/EDI_Pending" WHERE confname="EDI_MsgPending"';
		$result = DB_query($SQL,$db);
		
		$ForceConfigReload=true;
		include('includes/GetConfig.php');
		
		prnMsg (_('The new company database has been created for' . ' ' . $_POST['NewCompany'] . ' ' . _('the company details and parameters should now be set up for the new company. NB: Only a single user "demo" is defined with the password "weberp" in the new company database. A new system administrator user should be defined for the new company and this account deleted immediately.')), 'info');
		
		echo '<P><A HREF="' . $rootpath . '/CompanyPreferences.php?' . SID .'">' . _('Set Up New Company Details') . '</A>';
		echo '<P><A HREF="' . $rootpath . '/SystemParameters.php?' . SID .'">' . _('Set Up Configuration Details') . '</A>';
		echo '<P><A HREF="' . $rootpath . '/WWW_Users.php?' . SID .'">' . _('Set Up User Accounts') . '</A>';
		
		echo '</FORM>';
		echo '</CENTER>';
		include('includes/footer.inc');
		exit;
	}

}


echo '<CENTER>';
echo '<BR>';
prnMsg (_('This utility will create a new company') . '<BR><BR>' .
		_('If the company name already exists then you cannot recreate it'), 'info', _('PLEASE NOTE'));
echo '<BR>';
echo '<FORM METHOD="post" ACTION=' . $_SERVER['PHP_SELF'] . '?' . SID . ' enctype="multipart/form-data">';

echo '<TABLE><TR>';
echo '<TD>' . _('Enter 10 lower case character abbreviation for the company') . '</TD>
	<TD><INPUT TYPE="TEXT" SIZE="11" MAXLENGTH=10 NAME="NewCompany"></TD></TR>
	<TR><TD>'. _('Logo Image File (.jpg)') . ':</TD><TD><INPUT TYPE="FILE" ID="LogoFile" NAME="LogoFile"></TD></TR>
	</TABLE>';

echo '<BR><INPUT TYPE="SUBMIT" NAME="submit" VALUE="' . _('Proceed') . '">&nbsp;&nbsp;&nbsp;&nbsp;';
echo '</FORM>';
echo '</CENTER>';

include('includes/footer.inc');

?>