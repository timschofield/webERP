<?php
/* $Revision: 1.24 $ */
	/*--------------------------------------------------\
	|               |               | session.inc       |
	|---------------------------------------------------|
	| Web-ERP - http://web-erp.sourceforge.net          |
	| by Logic Works Ltd                                |
	\--------------------------------------------------*/

    include('config.php');
    include('includes/ConnectDB.inc');
    if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
    }
    
    ini_set('session.gc_Maxlifetime',$SessionLifeTime);
    
    session_start();

    // Un comment to turn off attempts counter
    //$_SESSION['AttemptsCounter'] = 0;

    if (!isset($_SESSION['AttemptsCounter'])){
    	$_SESSION['AttemptsCounter'] = 0;
    }

    // 5 login attempts, show failed login screen
    if (!isset($_SESSION['AttemptsCounter'])) {
	$_SESSION['AttemptsCounter'] = 0;
    } elseif ($_SESSION['AttemptsCounter'] >= 5) {
	/*User blocked from future accesses until sysadmin releases */
	$sql = "UPDATE WWW_Users SET WWW_Users.Blocked=1 WHERE WWW_Users.UserID='" . $_POST['UserNameEntryField'] . "'";
        $Auth_Result = DB_query($sql, $db);
        die(include('includes/FailedLogin.php'));
    }

	If (isset($_POST['Theme'])) {
		$_SESSION['Theme'] = $_POST['Theme'];
		$theme = $_POST['Theme'];
	} elseif (!isset($_SESSION['Theme'])) {
		$theme = $DefaultTheme;
	} else {
		$theme = $_SESSION['Theme'];
	}
    
	
    // Not logged in
    if ((!isset($_SESSION['AccessLevel']) or $_SESSION['AccessLevel'] == '') or
    	(isset($_POST['UserNameEntryField']) and $_POST['UserNameEntryField'] != '')) {
        $_SESSION['AccessLevel'] = '';
        $_SESSION['CustomerID'] = '';
        $_SESSION['UserBranch'] = '';
        $_SESSION['Module'] = '';
        $_SESSION['PageSize'] = '';
        $_SESSION['UserStockLocation'] = '';
        $_SESSION['AttemptsCounter']++;


        // Show login screen
        if (!isset($_POST['UserNameEntryField']) or $_POST['UserNameEntryField'] == '') {
		include('includes/Login.php');
        	exit;
        }

        $sql = "SELECT WWW_Users.FullAccess,
			WWW_Users.CustomerID,
			WWW_Users.LastVisitDate,
			WWW_Users.PageSize,
			WWW_Users.DefaultLocation,
			WWW_Users.BranchCode,
			WWW_Users.ModulesAllowed,
			WWW_Users.Blocked,
			WWW_Users.RealName,
			WWW_Users.Theme,
			WWW_Users.DisplayRecordsMax,
			WWW_Users.UserID,
			WWW_Users.Language
		FROM WWW_Users
		WHERE WWW_Users.UserID='" . $_POST['UserNameEntryField'] . "' 
		AND WWW_Users.Password='" . $_POST['Password'] . "'";
        $Auth_Result = DB_query($sql, $db);

        // Populate session variables with data base results
        if (DB_num_rows($Auth_Result) > 0) {
        	$myrow = DB_fetch_row($Auth_Result);
		if ($myrow[7]==1){
	    	//the account is blocked
			die(include("includes/FailedLogin.php"));
		}
		$_SESSION['AccessLevel'] = $myrow[0];
		$_SESSION['CustomerID'] = $myrow[1];
		$_SESSION['UserBranch'] = $myrow[5];
		$_SESSION['DefaultPageSize'] = $myrow[3];
		$_SESSION['UserStockLocation'] = $myrow[4];
		$_SESSION['ModulesEnabled'] = explode(",", $myrow[6]);
		$_SESSION['UsersRealName'] = $myrow[8];
		$_SESSION['Theme'] = $myrow[9];
		$_SESSION['UserID'] = $myrow[11];
		$_SESSION['Language'] = $myrow[12];

		if ($myrow[10] > 0) {
	 		$_SESSION['DisplayRecordsMax'] = $myrow[10];
		} else {
			$_SESSION['DisplayRecordsMax'] = $DefaultDisplayRecordsMax;  // default comes from config.php
		}

		$sql = "UPDATE WWW_Users SET LastVisitDate='". date("Y-m-d H:i:s") ."' 
			WHERE WWW_Users.UserID='" . $_POST['UserNameEntryField'] . "' 
			AND WWW_Users.Password='" . $_POST['Password'] ."'";
		$Auth_Result = DB_query($sql, $db);

        } else {     // Incorrect password

		$demo_text = '<FONT SIZE="3" COLOR="red"><b>' .  _('incorrect password') . '</B></FONT><BR><B>' . _('The user/password combination') . '<BR>' . _('is not a valid user of the system') . '</B>';
		include('includes/Login.php');
        }

    }		// End of userid/password check

    /*Now users language has been set include the language setup script */
    
    include('includes/LanguageSetup.php');
    
    // Now check that the user as logged in has access to the page being called. The $PageSecurity
    // value must be set in the script before header.inc is included. $SecurityGroups is an array of
    // arrays defining access for each group of users. These definitions can be modified in config.php.



    if (!is_array($SecurityGroups[$_SESSION['AccessLevel']])) {

        echo '<BR><BR><BR><CENTER><B>';
		echo _('Security settings have not been defined for your user account') . '. ' . _('Please advise your system administrator') . '</B>';
        	exit;
    }

    if (!in_array($PageSecurity, $SecurityGroups[$_SESSION['AccessLevel']]) OR !isset($PageSecurity)) {

        include('header.inc');
	echo '<TR>
		<TD CLASS="menu_group_items">
			<TABLE WIDTH="100%" CLASS="table_index">
				<TR><TD CLASS="menu_group_item">';
	echo '<B><FONT SIZE="+1"><CENTER>' . _('The security settings on your account do not permit you to access this function') . '</FONT></B></CENTER>';

	echo '</TD>
	</TR>
	</TABLE>
	</TD>';

	include('footer.inc');
	exit;
    }
    // Run with debugging messages for the system administrator(s) but not anyone else
    if (in_array(15, $SecurityGroups[$_SESSION['AccessLevel']])) {
        $debug = 1;
    } else {
        $debug = 0;
    }
?>
