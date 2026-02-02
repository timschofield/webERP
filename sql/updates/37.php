<?php

ChangeColumnSize('location', 'mrpparameters', 'VARCHAR(50)', ' NULL ', '', '50');

UpdateDBNo(basename(__FILE__, '.php'), __('Update back the size of the location field in mrpparameters to 50 chars'));
