<?php

/* New config values for whether to show frequently ordered items
 * on order entry, and if so then how many months to show
 */

NewConfigValue('FrequentlyOrderedItems', '0', $db);
NewConfigValue('NumberOfMonthMustBeShown', '6', $db);

UpdateDBNo(8, $db);

?>