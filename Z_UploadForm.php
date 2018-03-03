<?php

//$PageSecurity=15;

include('includes/session.php');

$Title=_('File Upload');

include('includes/header.php');

echo '<form ENCtype="multipart/form-data" action="Z_UploadResult.php" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
		_('Send this file') . ': <input name="userfile" type="file" />
		<input type="submit" value="' . _('Send File') . '" />
		</form>';

include('includes/footer.php');
?>