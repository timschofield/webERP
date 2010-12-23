<?php

/* Add column to www_users for the pdf language to be used */

AddColumn('pdflanguage', 'www_users', 'tinyint(1)', 'NOT NULL', '0', 'language', $db);

UpdateDBNo(11, $db);

?>