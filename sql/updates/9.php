<?php

ChangeConfigValue('VersionNumber', '5.0.0.rc');

UpdateDBNo(basename(__FILE__, '.php'), _('Update version number'));

?>