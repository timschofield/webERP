<?php

# PHPlot test: Plot auto-range test with partial range specification
# This tests use of SetPlotAreaWorld() to set one or both ends of the Y
# range, and how it interacts with the automatic range calculation.
# It also tests the 'regressive' cases, listed in the manual, such as
# setting one end of the plot range on the wrong side of the data.
#
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'subtitle' => '(baseline)',        # Plot title additional line
  'min' => 50,             # Lowest data value
  'max' => 100,            # Highest data value
  'plot_min_y' => NULL,    # SetPlotAreaWorld Ymin
  'plot_max_y' => NULL,    # SetPlotAreaWorld Ymax
        ), $tp);

require_once 'phplot.php';

# Extract all test parameters as local variables:
extract($tp);

# Make a simple data array covering the range min to max:
for ($x = 0; $x <= 10; $x++)
    $data[$x] = array('', $x, $min + ($max - $min) * $x / 10);

# Build a title including the options:
$title = "Auto-Range test, Range = [$min : $max]\n";
if (isset($plot_min_y)) {
    if (isset($plot_max_y)) {
        $title .= "Y plot area MIN = $plot_min_y and MAX = $plot_max_y";
    } else {
        $title .= "Y plot area MIN = $plot_min_y";
    }
} elseif (isset($plot_max_y)) {
    $title .= "Y plot area MAX = $plot_max_y";
} else {
    $title .= 'Y plot area range is not set';
}
if (!empty($subtitle)) $title .= "\n$subtitle";

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($title);
$p->SetDataValues($data);
$p->SetDataType('data-data');
$p->SetPlotType('linepoints');
if (isset($plot_max_y) || isset($plot_min_y))
    $p->SetPlotAreaWorld(NULL, $plot_min_y, NULL, $plot_max_y);

$p->DrawGraph();
