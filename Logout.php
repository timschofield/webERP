<?php
/* $Revision: 1.17 $ */
/* $Id$*/
//$PageSecurity =1;
$AllowAnyone=True; /* Allow all users to log off - needed for autoamted runs */

include('includes/session.inc');

?>
<html>
<head>
	<title><?php echo $_SESSION['CompanyRecord']['coyname'];?> - <?php echo _('Log Off'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="stylesheet" href="css/<?php echo $theme;?>/login.css" type="text/css" />
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<div id="container">
	<div id="login_logo"></div>
	<div id="login_box">
	<form action=" <?php echo $rootpath;?>/index.php" name="loginform" method="post">
<?php
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
?>
		<label><?php echo _('Thank you for using webERP'); ?></label>
	<br />
	<input class="button" type="submit" value="<?php echo _('Login'); ?>" name="SubmitUser" />
	</form>
	</div>
</div>

</body>
</html>

<?php
	// Cleanup
	session_unset();
	session_destroy();
?>
</body>
</html>
