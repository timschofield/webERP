<?php

//$PageSecurity = 15;

require(__DIR__ . '/includes/session.php');

$Title=__('File Upload');
include('includes/header.php');

echo '<form ENCtype="multipart/form-data" action="' . $RootPath . '/Z_UploadResult.php" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
		__('Send this file') . ': <input name="userfile" type="file" />
		<input type="submit" value="' . __('Send File') . '" />
		</form>';

include('includes/footer.php');
