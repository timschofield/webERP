<?php

include('includes/session.php');
$Title = _('List of packaging needed to fill up the shops');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');
include('includes/KLUIGeneralFunctions.php');

CheckPackagingToBeRefilled(TRUE, TRUE, $RootPath);

include('includes/footer.php');
