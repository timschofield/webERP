<?php

AddColumn('stockid', 'hremployees', 'VARCHAR(64)', 'NULL', '', 'locationid');
AddColumn('normalhours', 'hremployees', 'DOUBLE', 'NOT NULL', '40', 'stockid');

AddIndex(array('stockid'), 'hremployees', 'idx_stockid');

UpdateDBNo(basename(__FILE__, '.php'), __('Unify employees and hremployees tables'));
