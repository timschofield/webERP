<?php
/* $Revision: 1.3 $ */
$title="File Upload";
$PageSecurity=15;
include("includes/session.inc");
include("includes/header.inc");

?>

<FORM ENCTYPE="multipart/form-data" ACTION="Z_UploadResult.php" METHOD=POST>
<INPUT TYPE="hidden" name="MAX_FILE_SIZE" value="1000000">
Send this file: <INPUT NAME="userfile" TYPE="file">
<INPUT TYPE="submit" VALUE="Send File">
</FORM>

<?php include("includes/footer.inc"); ?>
