<?php
/* $Id$*/

//$PageSecurity=15;

include('includes/session.inc');

$title=_('File Upload');

include('includes/header.inc');

echo "<form ENCtype='multipart/form-data' action='Z_UploadResult.php' method=post>";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo "<input type='hidden' name='MAX_FILE_SIZE' value='1000000'>" .
		_('Send this file') . ": <input name='userfile' type='file'>
		<input type='submit' VALUE='" . _('Send File') . "'>
		</form>";

include('includes/footer.inc');
?>