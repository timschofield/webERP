<?php
	$PageSecurity = 1;
	
	// Uncomment do deactivate attempts counter
	//$_SESSION['AttemptsCounter'] = 0;
	
	include("includes/session.inc");
?>
<html>
<head>
    <title><?echo $CompanyName;?> - Log Off</title>
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
            
		    <table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0">
		        <tr>
		            <td colspan="2" rowspan="2">
                    <table width="200" border="0" cellpadding="0" cellspacing="0">
						<form action="<?echo $_SERVER['PHP_SELF'];?>" name="loginform" method="post">
                        <tr>
                            <td colspan="5" bgcolor="#FFFFFF"><img src="css/default/images/spacer.gif" width="346" height="1"></td>
                        </tr>

                        <tr>
                            <td><img src="logo.jpg"></td>

                            <td bgcolor="#367CB5"><img src="css/default/images/spacer.gif" width="12" /></td>

                            <td background="css/default/images/outline/bg.gif" colspan="3" valign="top">
                                <table border="0" cellpadding="3" cellspacing="0" width="100%">
									<tr>
										<td align="center" class="loginText">
											<br /><br />Thank you for using the webERP+ system<br /><br />
											<?echo "$CompanyName";?>
											<br />
											<a href="<?echo $rootpath;?>/index.php?<?echo SID;?>"><b>Click here to Login Again</b></a>
										</td>
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


