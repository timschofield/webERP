<?php
/* $Revision: 1.12 $ */
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
    <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
    <link rel="stylesheet" href="css/<?php echo $theme;?>/login.css" type="text/css" />
</HEAD>

<BODY leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
    <TABLE width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" class="mainTable">
        <TR>
            <TD align="left" valign="top"><img src="css/webERP.gif"></TD>
		</TR>

        <TR>
            <TD align="center" valign="top">

		    <TABLE class="login" border="0" cellpadding="0" cellspacing="0">
		        <TR>
		            <TD colspan="2" rowspan="2">
                    <TABLE border="0" cellpadding="0" cellspacing="0">
					<FORM action="<?php echo $_SERVER['PHP_SELF'];?>" name="loginform" method="post">
                        <TR>
                            <TD colspan="5" bgcolor="#FFFFFF"><img src="css/spacer.gif"></TD>
			</TR>
			

                        <TR>

			<TD VALIGN="CENTER" ALIGN="CENTER" class="logoBackground"><img src="logo.jpg" ></TD>

                            <TD class="middleBar"><img src="css/spacer.gif" width="12"></TD>

                          <TD background="css/bg.gif" colspan="3" valign="top">	
                                <TABLE border="0" cellpadding="3" cellspacing="0" width="100%" class="loginBox">

                                    <TR>
                                        <TD><span class="loginText"><?php echo _('User name'); ?>:</span><br />
                                         <input type="TEXT" name="UserNameEntryField"/><br />
                                         <span class="loginText"><?php echo _('Password'); ?>:</span><br />
                                         <input type="PASSWORD" name="Password">
                                         <br />
                                         <span class="loginText"><?php echo $demo_text;?></span>
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
            document.forms[0].UserNameEntryField.select();
            document.forms[0].UserNameEntryField.focus();
            //-->
    //]]>
    </script>
</body>
</html>
