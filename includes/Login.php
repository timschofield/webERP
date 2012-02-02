<?php
/* $Id$*/

// Display demo user name and password within login form if $allow_demo_mode is true
//include ('LanguageSetup.php');

if ($allow_demo_mode == True and !isset($demo_text)) {
	$demo_text = _('login as user') .': <i>' . _('admin') . '</i><br />' ._('with password') . ': <i>' . _('weberp') . '</i>';
} elseif (!isset($demo_text)) {
	$demo_text = _('Please login here');
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
            "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>webERP Login screen</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="css/<?php echo $theme;?>/login.css" type="text/css" />
</head>
<body>

<?php
if (get_magic_quotes_gpc()){
	echo '<p style="background:white">';
	echo _('Your webserver is configured to enable Magic Quotes. This may cause problems if you use punctuation (such as quotes) when doing data entry. You should contact your webmaster to disable Magic Quotes');
	echo '</p>';
}
?>

<div id="container">
	<div id="login_logo"></div>
	<div id="login_box">
	<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" id="loginform" method="post">
	<p><input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
	<span><?php echo _('Company'); ?>:</span>
    
	<?php
		if ($AllowCompanySelectionBox == true){
			echo '<select name="CompanyNameField">';
			
			$Companies = scandir('companies/', 0);
			foreach ($Companies as $CompanyEntry){
				if (is_dir('companies/' . $CompanyEntry) AND $CompanyEntry != '..' AND $CompanyEntry != '' AND $CompanyEntry!='.svn' AND $CompanyEntry!='.'){
					if ($CompanyEntry==$DefaultCompany) {
						echo '<option selected="selected" value="' . $CompanyEntry . '">' . $CompanyEntry . '</option>';
					} else {
						echo '<option  value="' . $CompanyEntry . '">' . $CompanyEntry . '</option>';
					}
				}
			}
			echo '</select>';
		} else {
			echo '<input type="text" name="CompanyNameField"  value="' . $DefaultCompany . '" />';
		}
	?>
    
	<span><?php echo _('User name'); ?>:</span>
	<input type="text" name="UserNameEntryField" />
	<span><?php echo _('Password'); ?>:</span>
	<input type="password" name="Password" /></p>
	<div id="demo_text"><?php echo $demo_text;?></div>
	<p><input class="button" type="submit" value="<?php echo _('Login'); ?>" name="SubmitUser" /></p>
	</form>
	</div>
</div>
    <script type="text/javascript">
            <!--
                  document.loginform.UserNameEntryField.focus();
            //-->
    </script>
</body>
</html>