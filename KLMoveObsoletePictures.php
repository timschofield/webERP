<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Move Pictures of Obsolete Items');

include('includes/header.php');
include('includes/KLControlBoardFunctions.php');

include('includes/OCOpenCartGeneralFunctions.php');
include('includes/OCOpenCartConnectDB.php');
include('includes/OCWeberpToOpenCartSync.php');

PicturesToMoveToObsolete(true, $RootPath);
FlagWebERPObsoleteItemsInOpenCart($RootPath);

include('includes/footer.php');
