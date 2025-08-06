<?php

# PHPlot test: Stacked Bars with zero segments - master
require_once 'phplot.php';
# This is a parameterized test. Other scripts can set the variables
# shown below, then include this script.
#  $horizontal = True; // To get a horizontal plot, else vertical.
#  $shading = N;  // 0 for no shading, N>0 for shading depth N, else default.
#  $axis = N; // Move axis to N (integer), else do not move axis.

# Make a data array with 4-bar segments of negative and positive numbers.
$v = array(0, 2, -2); // Values to use in the bar segments
for ($i1 = 0; $i1 <= 2; $i1++) {
    for ($i2 = 0; $i2 <= 2; $i2++) {
        for ($i3 = 0; $i3 <= 2; $i3++) {
            for ($i4 = 0; $i4 <= 2; $i4++) {
                $label = "[{$v[$i1]}, {$v[$i2]}, {$v[$i3]}, {$v[$i4]}]";
                $data[] = array($label, $v[$i1], $v[$i2], $v[$i3], $v[$i4]);
            }
        }
    }
}

if (!empty($horizontal)) {
    $ind = 'Y';
    $dep = 'X';
    $datatype = 'text-data-yx';
    $width = 600;
    $height = 1000;
    $labelposition = 'plotleft';
    $labelangle = 0;
    $x0 = 4 * min($v) - 2;
    $x1 = 4 * max($v) + 2;
    $y0 = NULL;
    $y1 = NULL;
} else {
    $ind = 'X';
    $dep = 'Y';
    $datatype = 'text-data';
    $width = 1000;
    $height = 600;
    $labelposition = 'plotdown';
    $labelangle = 90;
    $x0 = NULL;
    $x1 = NULL;
    $y0 = 4 * min($v) - 2;
    $y1 = 4 * max($v) + 2;
}

# Auto title:
$title = (empty($horizontal)? "Vertical" : "Horizontal") . " Stacked Bars"
   . " with Zero-Length Segments\n";
if (!isset($shading)) $title .= "Default shading";
elseif ($shading == 0) $title .= "Unshaded";
else $title .= "Shading: $shading";
$title .= isset($axis)? ", Axis moved to $axis" : ", default axis position";

$plot = new Phplot\Phplot\phplot($width, $height);
$plot->SetTitle($title);
$plot->SetPlotType('stackedbars');
$plot->SetDataValues($data);
$plot->SetDataType($datatype);
call_user_func(array($plot, "Set{$dep}DataLabelPos"), 'plotstack');
call_user_func(array($plot, "Set{$ind}TickLabelPos"), 'none');
call_user_func(array($plot, "Set{$ind}TickPos"), 'none');
call_user_func(array($plot, "Set{$ind}DataLabelPos"), $labelposition);
call_user_func(array($plot, "Set{$ind}LabelAngle"), $labelangle);
$plot->SetPlotAreaWorld($x0, $y0, $x1, $y1);
if (isset($shading)) $plot->SetShading($shading);
if (isset($axis)) call_user_func(array($plot, "Set{$ind}AxisPosition"), $axis);
$plot->DrawGraph();
