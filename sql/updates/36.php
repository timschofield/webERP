<?php

ChangeColumnSize('location', 'mrpparameters', 'VARCHAR(5)', ' NULL ', '', '5');

UpdateDBNo(basename(__FILE__, '.php'), __('Update the size of the location field in mrpparameters to 5 chars'));
