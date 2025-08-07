<?php
# PHPlot error test - argument error with returning handler, redo the graph.
require 'esupport.php';
set_error_handler('test_catch_return');
require_once 'phplot.php';
$data = array(array('a', 1, 1), array('b', 2, 3), array('c', 3, 5));
$plot = new Phplot\Phplot\phplot();
# Don't do any output of the error image:
$plot->SetIsInline(True);
$plot->SetOutputFile('/dev/null');
if (!$plot->SetDataType('data-text')) {
    restore_error_handler();
    fwrite(STDERR, "Return after error. Destroy object and try again\n");
    unset($plot);
    $plot = new Phplot\Phplot\phplot();
    $plot->SetDataType('data-data');
}
$plot->SetDataValues($data);
$plot->SetPlotType('lines');
$plot->SetOutputFile('');
$plot->DrawGraph();
