<?php
/* $Revision: 1.17 $ */
	/*--------------------------------------------------\
	|               |               | session.inc       |
	|---------------------------------------------------|
	| Web-ERP - http://web-erp.sourceforge.net          |
	| by Logic Works Ltd                                |
	\--------------------------------------------------*/

    include("config.php");
    include("includes/ConnectDB.inc");
    if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
    }
    session_start();

    include('includes/LanguageSetup.php');

    // Un comment to turn off attempts counter
    //$_SESSION["AttemptsCounter"] = 0;

    if (!isset($_SESSION["AttemptsCounter"])){
    	$_SESSION["AttemptsCounter"] =0;
    }

    // 5 login attempts, show failed login screen
    if (!isset($_SESSION["AttemptsCounter"])) {
	$_SESSION["AttemptsCounter"]=0;
    } elseif ($_SESSION["AttemptsCounter"] >= 5) {
	/*User blocked from future accesses until sysadmin releases */
	$sql = "UPDATE WWW_Users SET Blocked=1 WHERE UserID='" . $_POST["UserNameEntryField"] . "'";
        $Auth_Result = DB_query($sql, $db);
        die(include("includes/FailedLogin.php"));
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
    if ((!isset($_SESSION["AccessLevel"]) or $_SESSION["AccessLevel"] == "") or
    	(isset($_POST["UserNameEntryField"]) and $_POST["UserNameEntryField"] != "")) {
        $_SESSION["AccessLevel"] = "";
        $_SESSION["CustomerID"] = "";
        $_SESSION["UserBranch"] = "";
        $_SESSION["Module"] = "";
        $_SESSION["PageSize"] = "";
        $_SESSION["UserStockLocation"] = "";
        $_SESSION["AttemptsCounter"]++;


        // Show login screen
        if (!isset($_POST["UserNameEntryField"]) or $_POST["UserNameEntryField"] == "") {
	        include("includes/Login.php");
            exit;
        }

        $sql = "SELECT FullAccess,
			CustomerID,
			LastVisitDate,
			PageSize,
			DefaultLocation,
			BranchCode,
			ModulesAllowed,
			Blocked,
			RealName,
			Theme,
			DisplayRecordsMax,
			UserID,
			Language
		FROM WWW_Users
		WHERE UserID=\"" . $_POST["UserNameEntryField"] . "\"
		AND Password=\"" . $_POST["Password"] . "\"";
        $Auth_Result = DB_query($sql, $db);

        // Determine data base error
        if (DB_error_no($db) != 0 AND $debug == 1) {
            echo "<br />" . _("The SQL to retrieve the user details failed because :") . DB_error_msg($db) .
            "<br />" . _("The SQL used was :") . "<br />" . $sql;
        }

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

	    if ($myrow[10] <> 0) {
	    	$_SESSION['DisplayRecordsMax'] = $myrow[10];
	    } else {
		$_SESSION['DisplayRecordsMax'] = $DefaultDisplayRecordsMax;  // default comes from config.php
	    }

            $sql = "UPDATE WWW_Users SET LastVisitDate=\"". date("Y-m-d H:i:s") ."\" WHERE UserID=\"" .
            $_POST["UserNameEntryField"] . "\" AND Password=\"" . $_POST["Password"] ."\"";
            $Auth_Result = DB_query($sql, $db);
        }

        // Incorrect password
        else {
		$demo_text = "<font size=\"3\" color=\"red\"><b>" .  _("incorrect password") . "</b></font><br /><b>" . _("The user/password combination<br> is not a valid user of the system");
		include("includes/Login.php");
        }
    }// End of userid/password check

    // Now check that the user as logged in has access to the page being called. The $PageSecurity
    // value must be set in the script before header.inc is included. $SecurityGroups is an array of
    // arrays defining access for each group of users. These definitions can be modified in config.php.

    if (!is_array($SecurityGroups[$_SESSION["AccessLevel"]])) {
        echo "<br /><br /><br /><center><b>" .
	_("Security settings have not been defined for your user account. Please advise your system administrator") . "</b>";
        exit;
    }
    if (!in_array($PageSecurity, $SecurityGroups[$_SESSION["AccessLevel"]]) OR !isset($PageSecurity)) {
        include("header.inc");
	echo "<tr><td class='menu_group_items'>
		<table width='100%' class='table_index'>
			<tr><td class='menu_group_item'>";
	echo "<b><font size='+1'><center>" . _("The security settings on your account do not permit you
to access this function.") . "</font></b></center>";
	echo "</td></tr></table></td>";
	include("footer.inc");
	exit;
    }
    // Run with debugging messages for the system administrator(s) but not anyone else
    if (in_array(15, $SecurityGroups[$_SESSION["AccessLevel"]])) {
        $debug = 1;
    } else {
        $debug = 0;
    }
?>
