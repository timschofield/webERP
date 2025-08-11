<?php

# Testing PHPlot: Y Data Label Lines, for horizontal plots
# This test is based on datalabellines.php, but for horizontal plots with
# X and Y swapped in the data array. However, there are no horizontal bubble
# or error plots, so those options are eliminated here.
#
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => "Horizontal Plot Y Data Label Lines\n",
  'suffix' => "No labels, no lines",           # Title part 2
  'groups' => 1,            # Number of data sets (1 or more)
  'labelpos' => 'none',     # Y Data Label Position: plotleft, plotright, both, none
  'labellines' => False,    # Draw data lines? False or True
  'plottype' => 'points',   # Plot type: lines points linepoints
        ), $tp);
require_once 'phplot.php';

# Check for PHPlot method and skip the test if it is missing:
if (!method_exists('Phplot\Phplot\phplot', 'SetDrawYDataLabelLines')) {
    echo "Skipping test because it requires SetDrawYDataLabelLines()\n";
    exit(2);
}

$n_y = 12; // Fixed number of Y values (rows)
$max_x = 20; // Max random X

extract($tp); // Import test parameters as variables

# To get a repeatable test with 'random' data:
mt_srand(99);

# Make a data array with $groups data sets.
$data = array();
$data_type = 'text-data-yx';

for ($pt = 0; $pt < $n_y; $pt++) {
    $row = array(@strftime('%b', mktime(12, 12, 12, $pt+1, 1, 2000)));
    for ($r = 0; $r < $groups; $r++) {
        $row[] = mt_rand(0, $max_x);
    }
    $data[] = $row;
}

$plot = new Phplot\Phplot\phplot(640, 480);
$plot->SetTitle($title . $suffix);
$plot->SetDataType($data_type);
$plot->SetDataValues($data);
$plot->SetPlotType($plottype);
$plot->SetPlotAreaWorld(0, 0, $max_x, $n_y);
# Position data labels:
$plot->SetYDataLabelPos($labelpos);
# Turn data label lines on or off:
$plot->SetDrawYDataLabelLines($labellines);

# So you can tell which axis
$plot->SetXTitle('Dependent value');
$plot->SetYTitle('Independent value');

# Turn off Tick labels and Y Tick marks - they aren't used.
$plot->SetYTickLabelPos('none');
$plot->SetYTickPos('none');
# Don't make any plot lines dashed, to avoid confusion with data label lines
$plot->SetLineStyles('solid');
# Don't draw grids, so we can see the data lines.
$plot->SetDrawXGrid(False);
$plot->SetDrawYGrid(False);
$plot->DrawGraph();
