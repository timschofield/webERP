<?php

ChangeColumnSize('stockact', 'stockcategory', 'VARCHAR(20)', ' NOT NULL ', '', '20');
ChangeColumnSize('stockact', 'lastcostrollup', 'VARCHAR(20)', ' NOT NULL ', '', '20');

UpdateDBNo(basename(__FILE__, '.php'), __('Correct stock category GL account code column size to correct over-expanding in update 43'));
