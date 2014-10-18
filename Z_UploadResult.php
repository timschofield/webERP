<?php
/* $Id: Z_UploadResult.php 5784 2012-12-29 04:00:43Z daintree $*/

//$PageSecurity=15;

include('includes/session.inc');

$Title=_('File Upload Result');

include('includes/header.inc');


prnMsg( _('The file') . ' ' . $HTTP_POST_FILES['userfile']['name'] . ' ' . _('was uploaded to the server in the /tmp directory and has been renamed temp'),'info');

move_uploaded_file($HTTP_POST_FILES['userfile']['tmp_name'], '/tmp/temp');

include('includes/footer.inc');

?>