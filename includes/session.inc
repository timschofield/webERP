<?php
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

        $sql = "SELECT FullAccess, CustomerID, LastVisitDate, PageSize, DefaultLocation, BranchCode" .
        ", ModulesAllowed, Blocked FROM WWW_Users WHERE UserID=\"" . $_POST["UserNameEntryField"] .
        "\" AND Password=\"" . $_POST["Password"] . "\"";
        $Auth_Result = DB_query($sql, $db);

        // Determine data base error
        if (DB_error_no($db) != 0 AND $debug == 1) {
            echo "<br />The SQL to retrieve the user details failed because :" . DB_error_msg($db) .
            "<br />The SQL used was :<br />$sql";
        }

        // Populate session variables with data base results
        if (DB_num_rows($Auth_Result) > 0) {
            $myrow = DB_fetch_row($Auth_Result);
	    if ($myrow[7]==1){
	    	//the account is blocked
		die(include("includes/FailedLogin.php"));
	    }
            $_SESSION["AccessLevel"] = $myrow[0];
            $_SESSION["CustomerID"] = $myrow[1];
            $_SESSION["UserBranch"] = $myrow[5];
            $_SESSION["PageSize"] = $myrow[3];
            $_SESSION["UserStockLocation"] = $myrow[4];
            $_SESSION["ModulesEnabled"] = explode(",", $myrow[6]);
            $sql = "UPDATE WWW_Users SET LastVisitDate=\"". date("Y-m-d H:i:s") ."\" WHERE UserID=\"" .
            $_POST["UserNameEntryField"] . "\" AND Password=\"" . $_POST["Password"] ."\"";
            $Auth_Result = DB_query($sql, $db);
        }

        // Incorrect password
        else {
            $die_string = "<center><br /><br /><font size=\"5\" color=\"red\"><b>incorrect password" .
            "</b></font><br /><br /><b>The user/password combination is not a valid user of the " .
            "system.</b><br /><br />If you are not an authorized user, please contact the proper " .
            "personnel to obtain an account to enable you to use the system.</center>";
            die($die_string);
        }
    }// End of userid/password check

    // Now check that the user as logged in has access to the page being called. The $PageSecurity
    // value must be set in the script before header.inc is included. $SecurityGroups is an array of
    // arrays defining access for each group of users. These definitions can be modified in config.php.

    if (!is_array($SecurityGroups[$_SESSION["AccessLevel"]])) {
        echo "<br /><br /><br /><center><b>Security settings have not been " .
        "defined for your user account. Please advise your system administrator</b>";
        exit;
    }
    if (!in_array($PageSecurity, $SecurityGroups[$_SESSION["AccessLevel"]]) OR !isset($PageSecurity)) {
        echo "<br /><br /><br /><br /><br /><br /><br /><center><b>The security settings " .
        "on your account do not permit you to access this function.</b>";
        echo "<br /><br /><a href=\"$rootpath/index.php?" . SID . "\">Back To Main Menu</a>";
        exit;
    }
    // Run with debugging messages for the system administrator(s) but not anyone else
    if (in_array(15, $SecurityGroups[$_SESSION["AccessLevel"]])) {
        $debug = 1;
    } else {
        $debug = 0;
    }
?>
