<?php
$title="File Upload Result";
$PageSecurity=15;

include("includes/session.inc");
include("includes/header.inc");


echo "<P>The file " . $HTTP_POST_FILES['userfile']['name'] . " was uploaded to the server in the /tmp directory and has been renamed temp";

move_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'], "/tmp/temp");


include("includes/footer.inc");
?>
