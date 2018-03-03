<?php


$AllowAnyone=True; /* Allow all users to log off  */

include('includes/session.php');

// Cleanup
session_unset();
session_destroy();
setcookie('PHPSESSID',"",time()-3600,'/');

?>