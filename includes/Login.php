<?php
	// Display demo user name and password within login form if "$allow_demo_mode" is true
	$demo_text = "";
	if ($allow_demo_mode == True) {
	    $demo_text = "login as user: <i>demo</i><BR>with password: <i>weberp</i>";
	} else {
		$demo_text = "Please login here";
	}
?>

<html>
<head>
    <title><?echo $CompanyName;?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=us-ascii" />
    <link rel="stylesheet" href="css/<?echo $theme;?>/login.css" type="text/css" />
</head>

<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
    <table bgcolor="#285B86" width="100%" height="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="left" valign="top"><img src="css/default/images/webERP+.gif" /></td>
		</tr>

        <tr>
            <td align="center" valign="top">

		    <table class="login" border="0" cellpadding="0" cellspacing="0">
		        <tr>
		            <td colspan="2" rowspan="2">
                    <table border="0" cellpadding="0" cellspacing="0">
					<form action="<?echo $_SERVER['PHP_SELF'];?>" name="loginform" method="post">
                        <tr>
                            <td colspan="5" bgcolor="#FFFFFF"><img src="css/default/images/spacer.gif"></td>

			</tr>

                        <tr>

			<td><img src="logo.jpg" ></td>

                            <td bgcolor="#367CB5"><img src="css/default/images/spacer.gif" width="12" height="150" alt="" /></td>

                            <td background="css/default/images/outline/bg.gif" width="150" height="100" colspan="3" valign="top">
                                <table border="0" cellpadding="3" cellspacing="0" width="100%">
                                    <tr>
                                        <td align="right"><!--<span class="loginText">Client login<input name="external_login" type="checkbox" value="1" class="loginText"></span>--><br /></td>
                                    </tr>

                                    <tr>
                                        <td class="loginText"><span>User name:</span><br />
                                         <input type="TEXT" name="UserNameEntryField"/><br />
                                         <span>Password:</span><br />
                                         <input type="PASSWORD" name="Password">
                                         <br /><br />
                                         <?echo $demo_text;?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td align="right"><input type="submit" value=">> Login" name="SubmitUser" /></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5" bgcolor="#FFFFFF"><img src="css/default/images/spacer.gif" width="346" height="1" alt="" /></td>
                        </tr>
						</form>
                    </table>

		            </td>
		            <td background="css/default/images/outline/r.gif" colspan="3" align="right" valign="top"><img src="css/default/images/outline/tr.gif" width="10" height="10" alt="" /></td>
		        </tr>
		        <tr>
		            <td background="css/default/images/outline/r.gif"><img src="css/default/images/outline/r.gif" width="10" height="10" alt="" /></td>
		        </tr>
		        <tr>
		            <td background="css/default/images/outline/bm.gif"><img src="css/default/images/outline/bl.gif" width="10" height="10" alt="" /></td>
		            <td background="css/default/images/outline/bm.gif"><img src="css/default/images/outline/bm.gif" width="10" height="10" alt="" /></td>
		            <td><img src="css/default/images/outline/br.gif" width="10" height="10" alt="" /></td>
		        </tr>
		    </table>

            </td>
        </tr>
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
