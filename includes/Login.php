<?php
/* $Revision: 1.18 $ */
// Display demo user name and password within login form if $allow_demo_mode is true
include ('includes/LanguageSetup.php');

if ($allow_demo_mode == True AND !isset($demo_text)) {
	$demo_text = _('login as user') .': <i>' . _('demo') . '</i><BR>' ._('with password') . ': <i>' . _('weberp') . '</i>';
} elseif (!isset($demo_text)) {
	$demo_text = _('Please login here');
}

?>

<HTML>
<HEAD>
    <TITLE><?php echo $_SESSION['CompanyRecord']['coyname'];?></TITLE>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo _('ISO-8859-1'); ?>" />
    <link rel="stylesheet" href="css/<?php echo $theme;?>/login.css" type="text/css" />
</HEAD>

<BODY leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">


    <TABLE width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" class="mainTable">
        <TR>
            <TD align="left" valign="top"><img src="css/webERP.gif"></TD>
		</TR>

        <TR>
            <TD align="center" valign="top">

			<?php
				if (get_magic_quotes_gpc()){ 
					echo '<p style="background:white">';
					echo _('Your webserver is configured to enable Magic Quotes. This may cause problems if you use punctuation (such as quotes) when doing data entry. You should contact your webmaster to disable Magic Quotes');
					echo '</p>';
				}
			?>

		    <TABLE class="login" border="1" cellpadding="0" cellspacing="0">
		        <TR>
		            <TD colspan="2" rowspan="2">
                    <TABLE border="0" cellpadding="0" cellspacing="0">
					<FORM action="<?php echo $_SERVER['PHP_SELF'];?>" name="loginform" method="post">
                        <TR>
                            <TD colspan="5" bgcolor="#FFFFFF"><img src="css/spacer.gif"></TD>
			</TR>
			
                        <TR>

			<TD VALIGN="CENTER" ALIGN="CENTER" class="logoBackground"><img src="logo_server.jpg" ></TD>

                            <TD class="middleBar"><img src="css/spacer.gif" width="12"></TD>

                          <TD background="css/bg.gif" colspan="3" valign="top">	
                                <TABLE border="0" cellpadding="3" cellspacing="0" width="100%">

                                    <TR>
                                        <TD><SPAN class="loginText"><?php echo _('Company'); ?>:</SPAN><BR />
					<?php
						if ($AllowCompanySelectionBox == true){
							echo '<SELECT name="CompanyNameField">';
							$DirHandle = dir('companies/');
							while (false != ($CompanyEntry = $DirHandle->read())){
								if (is_dir('companies/' . $CompanyEntry) AND $CompanyEntry != '..' AND $CompanyEntry != 'CVS' AND $CompanyEntry!='.'){
									echo "<OPTION  VALUE='$CompanyEntry'>$CompanyEntry";
								}
							}
							echo '</SELECT>';
						} else {
                                         		echo '<INPUT type="TEXT" name="CompanyNameField"  VALUE="' . $DefaultCompany . '">';
						}
					?>
					 <br />
					 <SPAN class="loginText"><?php echo _('User name'); ?>:</SPAN><BR />
                                         <INPUT type="TEXT" name="UserNameEntryField"/><br />
                                         <SPAN class="loginText"><?php echo _('Password'); ?>:</SPAN><BR />
                                         <INPUT type="PASSWORD" name="Password">
                                         <BR />
                                         <SPAN class="loginText"><?php echo $demo_text;?></SPAN>
                                        </TD>
                                    </TR>

                                    <TR>
                                        <TD align="right"><input type="submit" value=">><?php echo _('Login'); ?>" name="SubmitUser" /></TD>
                                    </TR>
                                </table>
                            </TD>
                        </TR>

                        <TR>
                            <TD colspan="5" bgcolor="#FFFFFF"><img src="css/spacer.gif" width="346" height="1" alt="" /></TD>
                        </TR>
						</form>
                    </table>

		            </TD>
		            <TD WIDTH="3" class="borderBox"> </TD>
		        </TR>
		        <TR>
		            <TD class="borderBox">&nbsp;</TD>
		        </TR>
		   	<TR>
		            <TD COLSPAN="3" class="borderBox"></TD>
		        </TR>
		    </table>

            </TD>
        </TR>
    </table>
    <script language="JavaScript" type="text/javascript">
    //<![CDATA[
            <!--
            document.forms[0].CompanyField.select();
            document.forms[0].CompanyField.focus();
            //-->
    //]]>
    </script>
</body>
</html>
