<?php

# Stacked Bars (horiz & vert) with positive and negative values - baseline
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'suffix' => "\nVertical, Positive values",  # Title part 2
  'horiz' => False,     # True for horizontal bars, False for vertical
  'shading' => NULL,    # Bar shading in pixels, 0 for flat, NULL for default
  'signedness' => 1,    # 1:All >0, -1: All <0; 0: Both >0 and <0 data values
  'axis' => NULL,       # Move axis (X or Y) to value, NULL to skip
        ), $tp);
require_once 'phplot.php';

extract($tp);  # Bring all config variables into local context

if ($signedness > 0) {
    $data = array(
        array('[ 1  2  3  4]', 1, 2, 3, 4),
        array('[ 4  3  2  1]', 4, 3, 2, 1),
        array('[ 1  1  1  1]', 1, 1, 1, 1),
        array('[ 4  0  4  0]', 4, 0, 4, 0),
        array('[ 0  4  0  4]', 0, 4, 0, 4),
        array('[ 1  0  0  1]', 1, 0, 0, 1),
        array('[ 0  0  3  3]', 0, 0, 3, 3),
    );
} elseif ($signedness < 0) {
    $data = array(
        array('[-1 -2 -3 -4]',-1,-2,-3,-4),
        array('[-4 -3 -2 -1]',-4,-3,-2,-1),
        array('[-1 -1 -1 -1]',-1,-1,-1,-1),
        array('[-4  0 -4  0]',-4, 0,-4, 0),
        array('[ 0 -4  0 -4]', 0,-4, 0,-4),
        array('[-1  0  0 -1]',-1, 0, 0,-1),
        array('[ 0  0 -3 -3]', 0, 0,-3,-3),
    );
} else {
    $data = array(
        array('[ 1  2  3  4]', 1, 2, 3, 4),
        array('[-4 -3 -2 -1]',-4,-3,-2,-1),
        array('[ 2 -3  4 -2]', 2,-3, 4,-2),
        array('[-2  3 -4  2]',-2, 3,-4, 2),
        array('[ 3 -6  2  5]', 3,-6, 2, 5),
        array('[ 0  1 -1  1]', 0, 1,-1, 1),
        array('[ 0 -1  1 -1]', 0,-1, 1,-1),
    );
}

# For method names:
$dep_var = $horiz ? 'X' : 'Y';
$ind_var = $horiz ? 'Y' : 'X';

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType('stackedbars');
$plot->SetDataType($horiz ? 'text-data-yx' : 'text-data');
$plot->SetDataValues($data);
$plot->SetTitle('Stacked bars, positive and negative' . $suffix);
if (isset($shading))
    $plot->SetShading($shading);
call_user_func(array($plot, "Set{$ind_var}TickPos"), 'none');
call_user_func(array($plot, "Set{$dep_var}DataLabelPos"), 'plotstack');
call_user_func(array($plot, "Set{$dep_var}TickIncrement"), 1);
if (isset($axis))   # Move the independent axis position:
    call_user_func(array($plot, "Set{$ind_var}AxisPosition"), $axis);
$plot->DrawGraph();
