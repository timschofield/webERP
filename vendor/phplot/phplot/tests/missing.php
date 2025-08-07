<?php

# Testing phplot - Plots with missing points in data
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Missing Value Test',
  'suffix' => " (lines, default behavior)",       # Title part 2
  'xmiss1' => 5,         # First X|Y (0-14 or NULL) for which Y|X is missing
  'xmiss2' => NULL,      # Second X|Y (0-14 or NULL) for which Y|X is missing
  'DBLines' => NULL,     # DrawBrokenLines: True or False or NULL to omit
  'PType' => 'lines',    # Plot Type
  'DataLines' => False,  # Labels at top or right, and data lines on
  'horizontal' => False, # Horizontal plot
        ), $tp);
require_once 'phplot.php';
extract($tp); # Import parameters into namespace

# Note: This doesn't automatically insert the plot/data type in the title
# because it was originally written only for lines/squared and then extended
# to other types, but I don't want to make unwanted mismatches when comparing
# to previous test results.

# Build a valid data array, before taking out missing points.
# Supported plot types:
# + 'bars' and 'stackedbars', using data type text-data
#   or text-data-yx for horizontal.
# + 'lines', 'points', 'linepoints', 'squared', 'thinbarline' - using data-data
#   or data-data-yx for horizontal (if supported).
# + 'pie' using data type text-data-single only.
# Unsupported plot types:
# - 'area' and 'stackedarea' - don't support missing values.
# - Error plots (data-data-error) - tested in a separate set of tests.
# - OHLC plots - tested in a separate set of tests.

if ($PType == 'bars' || $PType == 'stackedbars') {
    $datatype = 'text-data';
    $offset = 1;
    for ($i = 0; $i < 15; $i++) $data[$i] = array('', $i * $i);
} elseif ($PType == 'pie') {
    $datatype = 'text-data-single';
    $offset = 1;
    for ($i = 0; $i < 15; $i++) $data[$i] = array('', 5);
} else {
    $datatype = 'data-data';
    $offset = 2;
    for ($i = 0; $i < 15; $i++) $data[$i] = array('', $i, $i * $i);
}

if ($horizontal) {
    $datatype .= '-yx';
    $title .= ' [Horiz]';
    $iv = 'Y';
    $dv = 'X';
} else {
    $iv = 'X';
    $dv = 'Y';
}

# Need to show the missing independent variables in the title:
if (isset($xmiss1)) {
    $data[$xmiss1][$offset] = '';
    $title .= " $iv={$xmiss1}";
}
if (isset($xmiss2)) {
    $data[$xmiss2][$offset] = '';
    $title .= " $iv={$xmiss2}";
}

$p = new Phplot\Phplot\phplot(1024, 768);
$p->SetTitle($title . $suffix);
$p->SetPlotType($PType);
$p->SetDataType($datatype);
$p->SetDataValues($data);
if ($DataLines) {
    if ($horizontal) {
        $p->SetYDataLabelPos('plotright');
        $p->SetDrawYDataLabelLines(True);
    } else {
        $p->SetXDataLabelPos('plotup');
        $p->SetDrawXDataLabelLines(True);
    }
    $p->SetDrawXGrid(False);
    $p->SetDrawYGrid(False);
}

if (isset($DBLines)) $p->SetDrawBrokenLines($DBLines);

# Set tick increment on independent variable axis:
call_user_func(array($p, "Set{$iv}TickIncrement"), 1);
# Set tick increment on dependent variable axis:
call_user_func(array($p, "Set{$dv}TickIncrement"), 20);

$p->DrawGraph();
