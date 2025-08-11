<?php

# PHPlot test: Horizontal error plots with style controls
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'subtitle' => '',
  'plot_type' => 'lines',       # A plot type supporting error plots
  'horizontal' => False,        # True for horizontal plot, else vertical
  'eb_shape' => NULL,           # Error bar shape if set: line | tee
  'eb_size' => NULL,            # Error bar size in pixels if set
  'eb_lwidth' => NULL,          # Error bar line width in pixels if set
  'eb_colors' => NULL,          # Error bar colors if set (array or single)
        ), $tp);
require_once 'phplot.php';
extract($tp);

# Skip the test if horizontal error plots are not yet implemented.
if ($horizontal && !method_exists('Phplot\Phplot\phplot', 'DrawXErrorBars')) {
    echo "Skipping test because horizontal error plots are not implemented\n";
    exit(2);
}


$data_type = $horizontal ? 'data-data-yx-error' : 'data-data-error';
$orient = $horizontal ? 'Horizontal' : 'Vertical';
$title = "Horizontal and Vertical Error Plots: $orient $plot_type plot";
if (!empty($subtitle)) $title .= "\n" . $subtitle;

# Error plot data array with 2 data sets, and all combinations of
# zero or positive +err/-err, and some missing Y values too.
$data = array(
          array('', 1,   3, 1, 1,        12, 2, 2),
          array('', 2,   3, 0.5, 0.5,    '', 1, 1),
          array('', 3,   3, 0.2, 0.2,    14, 1, 1),
          array('', 4,   3, 0.1, 0.1,    11, 2, 2),
          array('', 5,   3, 0.05, 0.05,  11, 2, 0),
          array('', 6,   3, 0, 0,        11, 0, 2),
          array('', 7,   3, 1, 1,        11, 0, 0),
          array('', 8,  '', 1, 0,        12, 3, 3),
          array('', 9,   3, 2, 2,        13, 1, 1),
);

$p = new Phplot\Phplot\phplot(800, 800);
$p->SetTitle($title);
$p->SetDataType($data_type);
$p->SetDataValues($data);
$p->SetPlotType($plot_type);

if ($horizontal) {
    # Horizontal plot
    $p->SetPlotAreaWorld(NULL, 0, NULL, 10);
    $p->SetXTitle('X Axis - Dependent variable');
    $p->SetYTitle('Y Axis - Independent variable');
    $p->SetXDataLabelPos('plotin');
    # Tick labels left, Axis Data labels right, so both can be seen.
    $p->SetYTickLabelPos('plotleft');
    $p->SetYDataLabelPos('plotright');
} else {
    # Vertical plot
    $p->SetPlotAreaWorld(0, NULL, 10, NULL);
    $p->SetXTitle('X Axis - Independent variable');
    $p->SetYTitle('Y Axis - Dependent variable');
    $p->SetYDataLabelPos('plotin');
    # Tick labels below, Axis Data labels above, so both can be seen.
    $p->SetXTickLabelPos('plotdown');
    $p->SetXDataLabelPos('plotup');
}

$p->SetPlotBorderType('full');

# With error plots, the default 90 degree position for data value labels
# will overlay the error bar, so move them to another angle:
$p->data_value_label_angle = 135;

# Turn off the grid lines, so the error bars are move visible.
$p->SetDrawXGrid(False);
$p->SetDrawYGrid(False);

# Style variations:
if (!empty($eb_lwidth)) $p->SetErrorBarLineWidth($eb_lwidth);
if (isset($eb_shape)) $p->SetErrorBarShape($eb_shape);
if (isset($eb_size)) $p->SetErrorBarSize($eb_size);
if (!empty($eb_colors)) $p->SetErrorBarColors($eb_colors);

$p->DrawGraph();
