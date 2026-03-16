<?php

RemoveMenuItem('GL', 'Reports', 'Financial Statements', '/GLStatements.php');

UpdateDBNo(basename(__FILE__, '.php'), __('Remove the menu items for Financial Statemnets'));
