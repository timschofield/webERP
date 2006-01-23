<?php
/* $Revision: 1.14 $ */
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
    <table width="100%" height="100%" border="0" cellpadding="0" cellspacing="0" class="mainTable">
        <tr>
            <td align="left" valign="top"><img src="css/webERP.gif" /></td>
		</tr>

        <tr>
            <td align="center" valign="top">

		    <table bgcolor="#FFFFFF" border="0" cellpadding="0" cellspacing="0">
		        <tr>
		            <td colspan="2" rowspan="2">
                    <table width="200" border="0" cellpadding="0" cellspacing="0">
						<form action="<?php echo $_SERVER['PHP_SELF'];?>" name="loginform" method="post">
                        <tr>
                            <td colspan="5"><img src="<?php echo "css/spacer.gif" ?>" width="346" height="1"></td>
                        </tr>

                        <tr>
                            <td valign="center" align="center" class="logoBackground"><img src="<?php echo 'companies/' . $_SESSION['DatabaseName'] . '/logo.jpg'; ?>"></td>

                            <td class="middleBar"><img src="<?php echo "css/spacer.gif" ?>" width="12" /></td>

                            <td colspan="3" valign="top" >
                                <table border="0" cellpadding="3" cellspacing="0" width="100%">
									<tr>
										<td align="center" class="loginText">
											<br /><br /><?php echo _('Thank you for using webERP'); ?><br /><br />
				<?php echo $_SESSION['CompanyRecord']['coyname'];?>
											<br />
											<a href=" <?php echo $rootpath;?>/index.php"><b><?php echo _('Click here to Login Again'); ?></b></a>
										</td>
									</tr>
                                </table>
                            </td>
                        </tr>

                        <tr>
                            <td colspan="5" bgcolor="#FFFFFF"><img src="<?php echo "css/spacer.gif" ?>" width="346" height="1" alt="" /></td>
                        </tr>
						</form>
                    </table>

		            </td>
		            <td colspan="3" width="3" class="borderBox"></td>
		        </tr>
		        <tr>
		            <td width="3" class="borderBox"></td>
		        </tr>
		        <tr>
		            <td COLSPAN="3" class="borderBox"></td>
		            </td>
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


