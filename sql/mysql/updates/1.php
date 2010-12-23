<?php

/* Ensure that all tablse use the utf8_general_cli
 * character set
 */

$sql='SHOW TABLES';
$result=DB_Query($sql, $db);
while ($table = DB_fetch_row($result)) {
	if (CharacterSet($table[0], $db) != 'utf8_general_ci') {
		$response=executeSQL('ALTER TABLE '.$table[0].' CONVERT TO CHARACTER SET utf8', $db);
		if ($response==0) {
			OutputResult( _('The character set of').' '.$table[0].' '._('has been changed to utf8_general_ci') , 'success');
		} else {
			OutputResult( _('The character set of').' '.$table[0].' '._('could not be changed to utf8_general_ci') , 'error');
		}
	} else {
		OutputResult( _('The character set of').' '.$table[0].' '._('is already utf8_general_ci') , 'info');
	}
}

UpdateDBNo(1, $db);

?>