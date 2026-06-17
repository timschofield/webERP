<?php

NewMenuItem('hr', 'Transactions', __('My Appraisals as Reviewer'), '/HRMyAppraisalsAsReviewer.php', 10);
NewScript('HRMyAppraisalsAsReviewer.php', 15);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('HR My Appraisals as Reviewer new script'));
}
