<?php

# PHPlot error test - Attempt to allocate an image too big for memory.
require_once 'phplot.php';
require 'esupport.php';
set_error_handler('test_catch_exit');

$data = array(
  array('A',  1, 4),
  array('B',  2, 6),
  array('C',  3, 0),
);

# Limit memory to about 8MB
ini_set('memory_limit', 8000000);

# Try to create an image that is too big:
$p = new Phplot\Phplot\phplot(3000, 3000);
$p->SetTitle('Memory?');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType('lines');
$p->DrawGraph();
