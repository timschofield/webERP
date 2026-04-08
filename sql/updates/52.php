<?php

DropIndex('stockserialitems', 'stockid_serialno');

if ($_SESSION['Updates']['Errors'] == 0) {
  UpdateDBNo(basename(__FILE__, '.php'), __('Remove incorrect constraint'));
}
