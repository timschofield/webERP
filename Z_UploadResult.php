<?php
/* $Revision: 1.3 $ */
$PageSecurity=15;

include('includes/session.inc');
$title=_('File Upload Result');
include('includes/header.inc');


echo '<P>' . _('The file') . ' ' . $HTTP_POST_FILES['userfile']['name'] . ' ' . _('was uploaded to the server in the /tmp directory and has been renamed temp');

move_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'], "/tmp/temp");

include("includes/footer.inc");
?>
