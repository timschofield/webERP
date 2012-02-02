<?php

/* $Id$*/

$AllowAnyone=True; /* Allow all users to log off  */

include('includes/session.inc');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title><?php echo $_SESSION['CompanyRecord']['coyname'];?> - <?php echo _('Log Off'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="css/<?php echo $theme;?>/login.css" type="text/css" />
</head>

<body>

<div id="container">
	<div id="login_logo"></div>
	<div id="login_box">
	<form action="<?php echo $rootpath;?>/index.php" id="loginform" method="post">
    <p>
<?php
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
?>
	<span><?php echo _('Thank you for using webERP'); ?></span>
	</p>
	<p><input class="button" type="submit" value="<?php echo _('Login'); ?>" name="SubmitUser" /></p>
	</form>
	</div>
</div>

<?php
	// Cleanup
	session_unset();
	session_destroy();
?>
</body>
</html>
