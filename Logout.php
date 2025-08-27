<?php

require(__DIR__ . '/includes/session.php');

$SQL = "DELETE FROM session_data WHERE userid='" . $_SESSION['UserID'] . "' AND field='module'";
$Result = DB_query($SQL);
$SQL ="INSERT INTO session_data VALUES('" . $_SESSION['UserID'] . "', 'module', '" . $_SESSION['Module'] . "')";
$Result = DB_query($SQL);
$SQL = "DELETE FROM sessions WHERE sessionid = '" . session_id() . "'";
$Result = DB_query($SQL);

// Cleanup
session_unset();
session_destroy();

// Do not assume php.ini defaults are in use on other systems using this app.
$Name = session_name();

// We do not use session_set_cookie_params(), so fetch the php.ini values.
// This information is needed for proper handling to avoid the "common pitfalls"
// referenced within the PHP setcookie() documentation:
// "Cookies must be deleted with the same parameters as they were set with."
$CookieInfo = session_get_cookie_params();

/////////////////// OWASP

// Destroy the cookie handling based on OWASP recommendations:
// https://www.owasp.org/index.php/PHP_Security_Cheat_Sheet#Proper_Deletion

setcookie($Name, '', 1, $CookieInfo['path']);
setcookie($Name, false);
unset($_COOKIE[$Name]);

/////////////////// END OWASP

/*
    Testing the cookie destroy handling with the three calls above appears to be
    OK. However, one could easily be misled to assume that the cookie is NOT
    destroyed while viewing the cookies in the browser's debugger while using
    our app after logout.

    The cookie's name (which comes from session.name, or session_name() could be
    used to override) will appear to remain with a newly regenerated value,
    which it does, but this is due to OUR handling as we have tight-coupling to
    certain $_SESSION data in files index.php and the included header.php.

    Post-logout handling is redirected to index.php, and the index.php file
    includes session.php. Function session_start() is called from session.php
    and that call recreates the cookie with same name but with a new cookie
    value. (basically, the cookie is removed and then recreated)

    So, to test that the cookie is actually removed, I added a header redirect
    in index.php (followed by exit, before including session.php) to another
    local file that does NOT have a session_start() call. When watching the
    cookies in the browser's debugger, one can witness that the cookie with the
    session name is removed.

    I verified this behavior with Firefox and Chrome.
*/
