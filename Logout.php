<?php

/* $Id: Logout.php 5785 2012-12-29 04:47:42Z daintree $*/

$AllowAnyone=True; /* Allow all users to log off  */

include('includes/session.php');

// Cleanup
session_unset();
session_destroy();
?>