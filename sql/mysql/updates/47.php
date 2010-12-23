<?php

/* Include the ManualContents.php script in the page security table
 */

InsertRecord('pagesecurity', array('script', 'security'), array('ManualContents.php',10), array('script', 'security'), array('ManualContents.php',10), $db);

UpdateDBNo(47, $db);

?>