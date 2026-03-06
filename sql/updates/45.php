<?php

ChangeColumnSize('stockact', 'stockcategory', 'VARCHAR(20)', ' NOT NULL ', '', '20');
ChangeColumnSize('stockact', 'lastcostrollup', 'VARCHAR(20)', ' NOT NULL ', '', '20');

UpdateDBNo(basename(__FILE__, '.php'), __('New table for stock item notes'));
