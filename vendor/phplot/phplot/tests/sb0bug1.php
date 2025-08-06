<?php

# PHPlot test: Bug #3296884 Undefined variable with stackedbars, first stack 0s
/* From bug report:
   #3296884 Undefined variable with stackedbars       2011-05-03

"If I want to draw a diagram with stackedbars and all values of the first
data set to zero and I have the option SetYDataLabelPos ('plotstack') set I
get the message Undefined variable: upward in phplot.php on line 5934 / 5935.
PHPlot Version is 5.3.1."

*/
$data = array(
    array('Jan 2011', 0, 0),
    array('Feb 2011', 0.9, 0.3),
    array('Mar 2011', 0.5, 1)
);
require_once 'phplot.php';
$plot = new Phplot\Phplot\phplot(400, 300);
$plot->SetTitle('Stacked bar 0 bug: initial stack all 0s');
$plot->SetPlotType('stackedbars');
$plot->SetDataValues($data);
$plot->SetYDataLabelPos('plotstack');
$plot->DrawGraph();
