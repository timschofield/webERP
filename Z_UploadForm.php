<?php
/* $Revision: 1.6 $ */

$PageSecurity=15;

include('includes/session.inc');

$title=_('File Upload');

include('includes/header.inc');

echo "<FORM ENCTYPE='multipart/form-data' ACTION='Z_UploadResult.php' METHOD=POST>
		<INPUT TYPE='hidden' name='MAX_FILE_SIZE' value='1000000'>" .
		_('Send this file') . ": <INPUT NAME='userfile' TYPE='file'>
		<INPUT TYPE='submit' VALUE='" . _('Send File') . "'>
		</FORM>";

include('includes/footer.inc');
?>
