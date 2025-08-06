<?php

# PHPlot test: multiple identical plots, baseline/master, 2x2 bar chart
# Idea is that only SetPlotAreaPixels should be needed between plots, and
# all plots should be identical.
require_once 'phplot.php';

# Other scripts can set these variables and then include this script.
#    Image size
if (!isset($image_width)) $image_width = 1000;
if (!isset($image_height)) $image_height = 800;
#    Margin between plots, and plot to edge of image
if (!isset($plot_area_margin)) $plot_area_margin = 60;
#    Number of plots to draw, across and down, on the image
if (!isset($n_across)) $n_across = 2;
if (!isset($n_down)) $n_down = 2;
#    Plot type (bars, stackedbars, lines, ...)
if (!isset($plot_type)) $plot_type = 'bars';
#    Legend positioning: 'default', 'world', 'pixels', 'plotrelative'.
if (!isset($legend_positioning)) $legend_positioning = 'world';

$title = "Multiple ($n_across x $n_down) Identical '$plot_type' plots"
       . ", Legend position: $legend_positioning";

$data = array(
    array('Jan', 0, 1, 2, 3),
    array('Feb', 1, 2, 4, 8),
    array('Mar', 2, 6, 12, 18),
    array('Apr', 3, 7, 11, 15),
    array('May', 4, 8, 16, 32),
    array('Jun', 5, 8, 11, 14),
);


# Calculate plot area sizes:

$plot_area_width = ($image_width - ($n_across + 1) * $plot_area_margin)
                   / $n_across;
$plot_area_height = ($image_height - ($n_down + 1) * $plot_area_margin)
                   / $n_down;

# Create object
$p = new Phplot\Phplot\phplot($image_width, $image_height);
$p->SetPrintImage(0);  // Do not output image until told

# Set up for first plot:

$p->SetDataValues($data);
$p->SetDataType('text-data');
$p->SetPlotType($plot_type);
$p->SetXTickPos('none');
$p->SetYDataLabelPos('plotin');
$p->SetTitle($title);
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Y Axis Title');
$p->SetDrawPlotAreaBackground(True);
$p->SetPlotBgColor('PeachPuff');
$p->SetBackgroundColor('lavender');
$p->SetImageBorderType('solid');
$p->SetImageBorderColor('navy');
$p->SetImageBorderWidth(3);
$p->SetPlotBorderType('full');
$p->SetLegend(array('Path A', 'Path B', 'Path C', 'Path D'));

switch ($legend_positioning) {
case 'world':
    $p->SetLegendWorld(0.1, 35);
    break;
case 'pixels':
    $p->SetLegendPixels(50, 50);
    break;
case 'plotrelative':
    # This places the upper left corner of the legend box at an offset of
    # (5,5) from the upper left corner of the plot area.
    $p->SetLegendPosition(0, 0, 'plot', 0, 0, 5, 5);
    break;
# No default: Default is to not position the legend and let it default.
}

# Draw n_across * n_down plots:
$plot_area_y1 = $plot_area_margin;
for ($iy = 0; $iy < $n_down; $iy++) {

    $plot_area_x1 = $plot_area_margin;
    $plot_area_y2 = $plot_area_y1 + $plot_area_height;
    for ($ix = 0; $ix < $n_across; $ix++) {

        $plot_area_x2 = $plot_area_x1 + $plot_area_width;

        $p->SetPlotAreaPixels($plot_area_x1, $plot_area_y1,
                              $plot_area_x2, $plot_area_y2);
        $p->DrawGraph();
        $plot_area_x1 = $plot_area_x2 + $plot_area_margin;
    }
    $plot_area_y1 = $plot_area_y2 + $plot_area_margin;
}

# Now output the completed image
$p->PrintImage();
