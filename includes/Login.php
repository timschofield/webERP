<?php
/* $Revision: 1.19 $ */
// Display demo user name and password within login form if $allow_demo_mode is true
include ('includes/LanguageSetup.php');

if ($allow_demo_mode == True AND !isset($demo_text)) {
	$demo_text = _('login as user') .': <i>' . _('demo') . '</i><BR>' ._('with password') . ': <i>' . _('weberp') . '</i>';
} elseif (!isset($demo_text)) {
	$demo_text = _('Please login here');
}

?>

<html>
<head>
    <title><?php echo $_SESSION['CompanyRecord']['coyname'];?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo _('ISO-8859-1'); ?>" />
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
	<form action="<?php echo $_SERVER['PHP_SELF'];?>" name="loginform" method="post">
	<label><?php echo _('Company'); ?>:</label>
	<?php
		if ($AllowCompanySelectionBox == true){
			echo '<select name="CompanyNameField">';
			$DirHandle = dir('companies/');
			while (false != ($CompanyEntry = $DirHandle->read())){
				if (is_dir('companies/' . $CompanyEntry) AND $CompanyEntry != '..' AND $CompanyEntry != 'CVS' AND $CompanyEntry!='.'){
					echo "<option  value='$CompanyEntry'>$CompanyEntry";
				}
			}
			echo '</select>';
		} else {
			echo '<input type="text" name="CompanyNameField"  value="' . $DefaultCompany . '">';
		}
	?>
	<br />
	<label><?php echo _('User name'); ?>:</label><br />
	<input type="TEXT" name="UserNameEntryField"/><br />
	<label><?php echo _('Password'); ?>:</label><br />
	<input type="PASSWORD" name="Password"><br />
	<div id="demo_text"><?php echo $demo_text;?></div>
	<input class="button" type="submit" value="<?php echo _('Login'); ?>" name="SubmitUser" />
	</form>
	</div>
</div>
    <script language="JavaScript" type="text/javascript">
    //<![CDATA[
            <!--
            document.forms[0].CompanyNameField.select();
            document.forms[0].CompanyNameField.focus();
            //-->
    //]]>
    </script>
</body>
</html>
