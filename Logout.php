<?php
/* $Revision: 1.17 $ */
$PageSecurity =1;

include('includes/session.inc');

?>
<html>
<head>
	<title><?php echo $_SESSION['CompanyRecord']['coyname'];?> - <?php echo _('Log Off'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php echo _('ISO-8859-1'); ?>" />
	<link rel="stylesheet" href="css/<?php echo $theme;?>/login.css" type="text/css" />
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">

<div id="container">
	<div id="login_logo"></div>
	<div id="login_box">
	<form action=" <?php echo $rootpath;?>/index.php" name="loginform" method="post">
		<label><?php echo _('Thank you for using webERP'); ?></label>
	<br />
	<div id="demo_text"><?php echo $demo_text;?></div>
	<input class="button" type="submit" value="<?php echo _('Login'); ?>" name="SubmitUser" />
	</form>
	</div>
</div>

</body>
</html>

<?php
	// Cleanup
	session_start();
	session_unset();
	session_destroy();
?>
</body>
</html>


