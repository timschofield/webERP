<?php

# PHPlot test - thinbarline, horiz & vert - vert baseline
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'horiz' => False,         # Vertical or horizontal
  'implicit' => True,       # If true, use text-data-*
  'low' => 0,               # Bottom of data range
  'high' => 10,             # Top of data range
  'axis0' => False,         # If true, move indep var axis to 0
        ), $tp);
require_once 'phplot.php';

extract($tp);  # Bring all config variables into local context

$data = array();
if ($implicit) {
    // Data array with implicit independent variables
    for ($i = $low; $i <= $high; $i++)
        $data[] = array("P$i", $i);
} else {
    // Data array with explicit independent variables
    $iv = 0;
    for ($i = $low; $i <= $high; $i++)
        $data[] = array("P$i", $iv++, $i);
}

if ($horiz) {
    $dir = 'Horizontal';
    $dep_var = 'X'; // For method names
    $ind_var = 'Y';
    if ($implicit) {
        $datatype = 'text-data-yx';
    } else {
        $datatype = 'data-data-yx';
    }
    $ticklabelpos = 'plotleft';
} else {
    $dir = 'Vertical';
    $dep_var = 'Y'; // For method names
    $ind_var = 'X';
    if ($implicit) {
        $datatype = 'text-data';
    } else {
        $datatype = 'data-data';
    }
    $ticklabelpos = 'plotdown';
}
$title = "Thinbarline, $dir ($datatype)\n($low : $high)";
if ($axis0) $title .= " (Axis moved to 0)";

$plot = new Phplot\Phplot\phplot(400, 400);
$plot->SetTitle($title);
$plot->SetPlotType('thinbarline');
$plot->SetDataType($datatype);
$plot->SetDataValues($data);
// If there are 20 or more points, show ticks and tick labels on
// independent axis. If < 20, show data labels and no ticks.
if (count($data) >= 20) {
    call_user_func(array($plot, "Set{$ind_var}TickLabelPos"), $ticklabelpos);
} else {
    call_user_func(array($plot, "Set{$ind_var}TickPos"), 'none');
}

if ($axis0) call_user_func(array($plot, "Set{$ind_var}AxisPosition"), 0);
$plot->DrawGraph();
