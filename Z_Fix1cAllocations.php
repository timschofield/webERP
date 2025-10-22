<?php

require(__DIR__ . '/includes/session.php');

$Title=__('Fully allocate Customer transactions where < 1 c unallocated');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
		<div class="centre">' . __('This will update debtor transactions to show them as fully allocated where there is less than 1 cent remaining unallocated.') . '<br /><input type="submit" name="FixAllocations" value="' . __('Fix Allocations') .'" />
		</div></form>';

if (isset($_POST['FixAllocations'])){
	DB_query('UPDATE debtortrans
				SET alloc=ovamount+ovgst+ovfreight+ovdiscount, settled=1
				WHERE ABS(alloc-ovamount-ovgst-ovfreight-ovdiscount) <0.01;');
	prnMsg(__('Updated debtor transactions'),'success');
}

include('includes/footer.php');
