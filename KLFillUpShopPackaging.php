<?php

include('includes/session.php');
$Title = __('List of packaging needed to fill up the shops');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');
include('includes/KLUIGeneralFunctions.php');

CheckPackagingToBeRefilled(true, true, $RootPath);

include('includes/footer.php');
