<?php

ChangeColumnType('version', 'purchorders', 'DECIMAL(5,2)', 'NOT NULL', '1.00');

UpdateDBNo(basename(__FILE__, '.php'), __('Change column type'));
