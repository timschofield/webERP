<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Move Pictures of Obsolete Items');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');
include('includes/KLControlBoardFunctions.php');

PicturesToMoveToObsolete(true, $RootPath);

include('includes/footer.php');
