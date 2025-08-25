<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.php');

global $DBType;

/* Was the Cancel button pressed the last time through ? */

if (isset($_POST['EnterCompanyDetails'])) {

	header ('Location:' . $RootPath . '/CompanyPreferences.php');
	exit();
}
$Title = __('Make New Company Database Utility');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');

include('includes/header.php');

/* Your webserver user MUST have read/write access to here,
	otherwise you'll be wasting your time */
if (! is_writeable('./companies/')){
		prnMsg(__('The web-server does not appear to be able to write to the companies directory to create the required directories for the new company and to upload the logo to. The system administrator will need to modify the permissions on your installation before a new company can be created'),'error');
		include('includes/footer.php');
		exit();
}

if (isset($_POST['submit']) AND isset($_POST['NewDatabase'])) {

	if(mb_strlen($_POST['NewDatabase'])>32
		OR ContainsIllegalCharacters($_POST['NewDatabase'])){
		prnMsg(__('Company database must not contain spaces illegal characters') . ' ' . '" \' - &amp; or a space','error');
	} else {
		$_POST['NewDatabase'] = strtolower($_POST['NewDatabase']);
		echo '<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '">
			<div class="centre">
			<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';

		/* check for directory existence */
		if (!file_exists('./companies/' . $_POST['NewDatabase'])
				AND (isset($_FILES['LogoFile']) AND $_FILES['LogoFile']['name'] !='')) {

			$Result    = $_FILES['LogoFile']['error'];
			$UploadTheLogo = 'Yes'; //Assume all is well to start off with
			$FileName = './companies/' . $_POST['NewDatabase'] . '/logo.jpg';

			//But check for the worst
			if (mb_strtoupper(mb_substr(trim($_FILES['LogoFile']['name']),mb_strlen($_FILES['LogoFile']['name'])-3))!='JPG'){
				prnMsg(__('Only jpg files are supported - a file extension of .jpg is expected'),'warn');
				$UploadTheLogo ='No';
			} elseif ( $_FILES['LogoFile']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
				prnMsg(__('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
				$UploadTheLogo ='No';
			} elseif ( $_FILES['LogoFile']['type'] == "text/plain" ) {  //File Type Check
				prnMsg( __('Only graphics files can be uploaded'),'warn');
				$UploadTheLogo ='No';
			} elseif (file_exists($FileName)){
				prnMsg(__('Attempting to overwrite an existing item image'),'warn');
				$Result = unlink($FileName);
				if (!$Result){
					prnMsg(__('The existing image could not be removed'),'error');
					$UploadTheLogo ='No';
				}
			}

			if ($_POST['CreateDB']==true){
				/* Need to read in the sql script and process the queries to iniate a new DB */

				$Result = DB_query('CREATE DATABASE ' . $_POST['NewDatabase']);

				if ($DBType=='postgres'){

					$PgConnStr = 'dbname=' . $_POST['NewDatabase'];
					if ( isset($Host) && ($Host != "")) {
						$PgConnStr = 'host=' . $Host . ' ' . $PgConnStr;
					}

					if (isset( $DBUser ) && ($DBUser != "")) {
						// if we have a user we need to use password if supplied
						$PgConnStr .= " user=".$DBUser;
						if ( isset( $DBPassword ) && ($DBPassword != "") ) {
							$PgConnStr .= " password=".$DBPassword;
						}
					}
					$db = pg_connect( $PgConnStr );
					$SQLScriptFile = file('./sql/pg/default.psql');

				} elseif ($DBType =='mysql') { //its a mysql db < 4.1
					mysql_select_db($_POST['NewDatabase'],$db);
					/// @todo fix - which db dump to start with ?
					$SQLScriptFile = file('./sql/mysql/country_sql/default.sql');
				} elseif ($DBType =='mysqli') { //its a mysql db using the >4.1 library functions
					mysqli_select_db($db,$_POST['NewDatabase']);
					/// @todo fix - which db dump to start with ?
					$SQLScriptFile = file('./sql/mysql/country_sql/default.sql');
				}

				$ScriptFileEntries = sizeof($SQLScriptFile);
				$ErrMsg = __('The script to create the new company database failed because');
				$SQL ='';
				$InAFunction = false;

				for ($i=0; $i<=$ScriptFileEntries; $i++) {

					$SQLScriptFile[$i] = trim($SQLScriptFile[$i]);

					if (mb_substr($SQLScriptFile[$i], 0, 2) != '--'
						AND mb_substr($SQLScriptFile[$i], 0, 3) != 'USE'
						AND mb_strstr($SQLScriptFile[$i],'/*')==false
						AND mb_strlen($SQLScriptFile[$i])>1){

						$SQL .= ' ' . $SQLScriptFile[$i];

						//check if this line kicks off a function definition - pg chokes otherwise
						if (mb_substr($SQLScriptFile[$i],0,15) == 'CREATE FUNCTION'){
							$InAFunction = true;
						}
						//check if this line completes a function definition - pg chokes otherwise
						if (mb_substr($SQLScriptFile[$i],0,8) == 'LANGUAGE'){
							$InAFunction = false;
						}
						if (mb_strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
							$SQL = mb_substr($SQL,0,mb_strlen($SQL)-1);
							$Result = DB_query($SQL, $ErrMsg);
							$SQL='';
						}

					} //end if its a valid sql line not a comment
				} //end of for loop around the lines of the sql script
			} //end if CreateDB was checked

			prnMsg(__('Attempting to create the new company directories') . '.....<br />', 'info');
			$Result = mkdir('./companies/' . $_POST['NewDatabase']);

			// Sub-directories listed alphabetically to ease referencing.
			$Result = mkdir('./companies/' . $_POST['NewDatabase'] . '/EDI_Incoming_Orders');
			$Result = mkdir('./companies/' . $_POST['NewDatabase'] . '/EDI_Pending');
			$Result = mkdir('./companies/' . $_POST['NewDatabase'] . '/EDI_Sent');
			$Result = mkdir('./companies/' . $_POST['NewDatabase'] . '/FormDesigns');
			$Result = mkdir('./companies/' . $_POST['NewDatabase'] . '/part_pics');
			$Result = mkdir('./companies/' . $_POST['NewDatabase'] . '/reports');
			$Result = mkdir('./companies/' . $_POST['NewDatabase'] . '/reportwriter');

			// XML files listed alphabetically to ease referencing.
			copy ('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/FGLabel.xml',       './companies/' . $_POST['NewDatabase'] . '/FormDesigns/FGLabel.xml');
			copy ('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/GoodsReceived.xml', './companies/' . $_POST['NewDatabase'] . '/FormDesigns/GoodsReceived.xml');
			copy ('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/Journal.xml',       './companies/' . $_POST['NewDatabase'] . '/FormDesigns/Journal.xml');
			copy ('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/PickingList.xml',   './companies/' . $_POST['NewDatabase'] . '/FormDesigns/PickingList.xml');
			copy ('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/PurchaseOrder.xml', './companies/' . $_POST['NewDatabase'] . '/FormDesigns/PurchaseOrder.xml');
			copy ('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/QALabel.xml',       './companies/' . $_POST['NewDatabase'] . '/FormDesigns/QALabel.xml');
			copy ('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/ShippingLabel.xml', './companies/' . $_POST['NewDatabase'] . '/FormDesigns/ShippingLabel.xml');
			copy ('./companies/' . $_SESSION['DatabaseName'] . '/FormDesigns/WOPaperwork.xml',   './companies/' . $_POST['NewDatabase'] . '/FormDesigns/WOPaperwork.xml');

			/*OK Now upload the logo */
			if ($UploadTheLogo=='Yes'){
				$Result  =  move_uploaded_file($_FILES['LogoFile']['tmp_name'], $FileName);
				$Message = ($Result) ? __('File url') . '<a href="' . $FileName . '">' .  $FileName . '</a>' : __('Something is wrong with uploading a file');
			}

		} else {
			prnMsg(__('This company cannot be added because either it already exists or no logo is being uploaded!'),'error');

			if (isset($_FILES['LogoFile'])){
				prnMsg('_Files[LogoFile] ' . __('is set ok'), 'info');
			} else  {
				prnMsg('_FILES[LogoFile] ' . __('is not set'), 'info');
			}

			if($_FILES['LogoFile']['name'] !=''){
				prnMsg('_FILES[LogoFile][name] ' . __('is not blank'), 'info');
			} else  {
				prnMsg('_FILES[LogoFile][name] ' . __('is blank'), 'info');
			}

			echo '</div>
				</form>';
			include('includes/footer.php');
			exit();
		}


         //now update the config.php file if using the obfuscated database login else we don't want it there
        if (isset($CompanyList) && is_array($CompanyList)) {
            $ConfigFile = './config.php';
            $config_php = join('', file($ConfigFile));
            //fix the Post var - it is being preprocessed with slashes and entity encoded which we do not want here
            $_POST['NewCompany'] =  html_entity_decode($_POST['NewCompany'],ENT_QUOTES,'UTF-8');
            $config_php = preg_replace('/\/\/End Installed companies-do not change this line/', "\$CompanyList[] = array('database'=>'".$_POST['NewDatabase']."' ,'company'=>'".$_POST['NewCompany']."');\n//End Installed companies-do not change this line", $config_php);
            if (!$fp = fopen($ConfigFile, 'wb')) {
                prnMsg(__('Cannot open the configuration file' . ': ').$ConfigFile.". Please add the following line to the end of the file:\n\$CompanyList[] = array('database'=>'".$_POST['NewDatabase']."' ,'company'=>'".htmlspecialchars($_POST['NewCompany'],ENT_QUOTES,'UTF-8').");",'error');
            } else {
                fwrite ($fp, $config_php);
                fclose ($fp);
            }
        }

		$_SESSION['DatabaseName'] = $_POST['NewDatabase'];

		unset ($_SESSION['CustomerID']);
		unset ($_SESSION['SupplierID']);
		unset ($_SESSION['StockID']);
		unset ($_SESSION['Items']);
		unset ($_SESSION['CreditItems']);

		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewDatabase'] . "/EDI_Incoming_Orders' WHERE confname='EDI_Incoming_Orders'";
		$Result = DB_query($SQL);
		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewDatabase'] . "/EDI_Pending' WHERE confname='EDI_MsgPending'";
		$Result = DB_query($SQL);
		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewDatabase'] . "/EDI_Sent' WHERE confname='EDI_MsgSent'";
		$Result = DB_query($SQL);
		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewDatabase'] . "/part_pics' WHERE confname='part_pics_dir'";
		$Result = DB_query($SQL);
		$SQL ="UPDATE config SET confvalue='companies/" . $_POST['NewDatabase'] . "/reports' WHERE confname='reports_dir'";
		$Result = DB_query($SQL);

		//add new company
		$SQL = "UPDATE companies SET coyname='" . $_POST['NewCompany'] . "' WHERE coycode = 1";
		$Result = DB_query($SQL);

		$ForceConfigReload=true;
		include('includes/GetConfig.php');
		$ForceConfigReload=false;

		prnMsg(__('The new company database has been created for' . ' ' . htmlspecialchars($_POST['NewCompany'], ENT_QUOTES, 'UTF-8') . '. ' . __('The company details and parameters should now be set up for the new company. NB: Only a single user admin is defined with the password weberp in the new company database. A new system administrator user should be defined for the new company and this account deleted immediately.')), 'info');

		echo '<p><a href="', $RootPath, '/CompanyPreferences.php">', __('Set Up New Company Details'), '</a></p>
			<p><a href="', $RootPath, '/SystemParameters.php">', __('Set Up Configuration Details'), '</a></p>
			<p><a href="', $RootPath, '/WWW_Users.php">', __('Set Up User Accounts'), '</a></p>
			</div>
		</form>';
		include('includes/footer.php');
		exit();
	}

}


prnMsg(__('This utility will create a new company') . '. ' .
		__('If the company name already exists then you cannot recreate it') . '.', 'info', __('PLEASE NOTE'));

echo '<br /><br />
	<form method="post" action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" enctype="multipart/form-data">
	<div class="centre">
		<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />
		<table>
			<tr>
				<td>' . __('Enter the name of the database used for the company up to 32 characters in lower case') . ':</td>
				<td><input type="text" size="33" maxlength="32" name="NewDatabase" /></td>
			</tr>
			<tr>
				<td>' . __('Enter a unique name for the company of up to 50 characters') . ':</td>
				<td><input type="text" size="33" maxlength="32" name="NewCompany" /></td>
			</tr>
			<tr>
				<td>' .  __('Logo Image File (.jpg)') . ':</td>
				<td><input type="file" required="true" id="LogoFile" name="LogoFile" /></td>
			</tr>
			<tr>
				<td>' . __('Create Database?') . '</td>
				<td><input type="checkbox" name="CreateDB" /></td>
			</tr>
		</table>
		<br />
		<input type="submit" name="submit" value="', __('Proceed'), '" />
	</div>
	</form>';

include('includes/footer.php');
