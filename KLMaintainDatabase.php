<?php

include('includes/session.php');
$Title = __('KL DataBase Maintenance');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLPrices.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLEmails.php');
include('KLDailyChecks.php');

KL_DailyCleanDB(TRUE, '');


include('includes/footer.php');
