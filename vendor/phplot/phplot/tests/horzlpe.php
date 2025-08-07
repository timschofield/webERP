<?php

# Testing PHPlot - Horizontal lines/points/linepoints+error plots - master
# This tests new horizontal lines, points, and linepoints plots, added at
# PHPlot-6.0.0. Because the lines/points/linepoints and error plot variations
# code was rewritten then (merging error plots with non-error-plot functions),
# the test also includes vertical and error plots.
# Other scripts can set the variables below then include this script.
# All 15 cases (3 plot types, 5 data types) are valid. There are no
# horizontal error plots at this time, but there could be with the addition
# of 1 or 2 new data types.
require 'phplot.php';

# Parameters, which can be set by calling scripts:
#   $plot_type = 'lines' | 'points' | 'linepoints'
if (!isset($plot_type)) $plot_type = 'lines';
#   $data_type = 'text-data' | 'data-data' | 'data-data-error'
#              | 'text-data-yx' | 'data-data-yx'
if (!isset($data_type)) $data_type = 'text-data';

# Skip the test with older PHPlot versions that do not support the
# horizontal plot types under test.
if (($data_type == 'text-data-yx' || $data_type == 'data-data-yx')
   && (!defined('Phplot\Phplot\phplot::version_id') || Phplot\Phplot\phplot::version_id < 60000)) {
    echo "Skipping test: missing horizontal plot type support\n";
    exit(2);
}

# ======

# Decode the data type:
$implied_x = $data_type == 'text-data' || $data_type == 'text-data-yx';
$horizontal = $data_type == 'data-data-yx' || $data_type == 'text-data-yx';
$error_bars = $data_type == 'data-data-error';

# Build data arrays. All work for horizontal or vertical, except there
# are (currently) no horizontal error bar plots, nor implied-X error bar plots.
if ($implied_x && !$error_bars) {
    # Implied X or Y, no error bars
    $data = array(
                array('A', 0,  1),
                array('B', 1,  3),
                array('C', 2,  5),
                array('D', 3,  7),
                array('E', 4,  9),
                array('F', 5, 11),
                array('G', 6,  9),
                array('H', 7,  7),
        );
} elseif (!$implied_x && !$error_bars) {
    # Explicit X or Y, no error bars
    $data = array(        // Note record order does not matter here
                array('A', 0,   0,  0),
                array('B', 1,   1,  2),
                array('H', 7,   7, 14),
                array('D', 3,   3,  6),
                array('F', 5,   5, 10),
                array('C', 2,   2,  4),
                array('G', 6,   6, 12),
                array('E', 4,   4,  8),
        );
} elseif (!$implied_x && $error_bars) {
    # Explicit X, error bars
    $data = array( //      X    Y +e -e   Y +e -e
                array('A', 0,   0, 1, 0,  0, 0, 0),
                array('B', 1,   1, 1, 1,  2, 1, 1),
                array('H', 7,   7, 1, 1, 14, 1, 1),
                array('D', 3,   3, 1, 0,  6, 0, 0),
                array('F', 5,   5, 1, 1, 10, 1, 0),
                array('C', 2,   2, 0, 1,  4, 1, 1),
                array('G', 6,   6, 1, 1, 12, 0, 1),
                array('E', 4,   4, 0, 0,  8, 1, 1),
        );
} else {
    fwrite(STDERR, "Error: Unknown data type or not decoded: $data-type\n");
    exit(1);
}

$p = new Phplot\Phplot\phplot(800, 800);
$p->SetTitle("Testing PHPlot ($plot_type, $data_type)");
$p->SetDataType($data_type);
$p->SetDataValues($data);
$p->SetPlotType($plot_type);

if ($horizontal) {
    $p->SetXTitle('X Axis - Dependent variable');
    $p->SetYTitle('Y Axis - Independent variable');
    $p->SetXDataLabelPos('plotin');
    // Tick labels left, Axis Data labels right, so both can be seen.
    $p->SetYTickLabelPos('plotleft');
    $p->SetYDataLabelPos('plotright');
} else {
    $p->SetXTitle('X Axis - Independent variable');
    $p->SetYTitle('Y Axis - Dependent variable');
    $p->SetYDataLabelPos('plotin');
    // Tick labels below, Axis Data labels above, so both can be seen.
    $p->SetXTickLabelPos('plotdown');
    $p->SetXDataLabelPos('plotup');
}

# Customizations for error plots:
if ($error_bars) {
    # With error plots, the default 90 degree position for data value labels
    # will overlay the error bar, so move them to 45 degrees:
    $p->data_value_label_angle = 45;

    # Turn off the grid lines, so the error bars are move visible.
    $p->SetDrawXGrid(False);
    $p->SetDrawYGrid(False);

    # Playing
    // $p->SetErrorBarLineWidth(3);
    // $p->SetErrorBarShape('line');
    // $p->SetErrorBarSize(2);
    // $p->SetErrorBarColors('red');
}

$p->DrawGraph();
