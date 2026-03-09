<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Move Pictures of Obsolete Items');

include(__DIR__ . '/includes/header.php');
include(__DIR__ . '/includes/KLControlBoardFunctions.php');

include(__DIR__ . '/includes/OCOpenCartGeneralFunctions.php');
include(__DIR__ . '/includes/OCOpenCartConnectDB.php');
include(__DIR__ . '/includes/OCWeberpToOpenCartSync.php');

PicturesToMoveToObsolete(true, $RootPath);
FlagWebERPObsoleteItemsInOpenCart($RootPath);

include(__DIR__ . '/includes/footer.php');
