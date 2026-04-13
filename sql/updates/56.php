<?php

// Add currency field to hremployees table to support multi-currency salary tracking
AddColumn('currency', 'hremployees', 'CHAR(3)', 'NULL', '', 'currentsalary');

UpdateDBNo(basename(__FILE__, '.php'), __('Add currency field to hremployees table'));
