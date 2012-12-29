<?php

/* $Id$*/

$AllowAnyone=True; /* Allow all users to log off  */

include('includes/session.inc');

// Cleanup
session_unset();
session_destroy();
?>