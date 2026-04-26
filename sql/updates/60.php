<?php

ChangeColumnSize('authorizerexpenses', 'pctabs', 'VARCHAR(100)', ' NOT NULL ', '', '100');

UpdateDBNo(basename(__FILE__, '.php'), __('Increase the pctabs column for expense authorizerto 100 chars'));
