<?php

# Testing PHPlot - Multi-plot label format issue
# This shows an issue with X and Y label formats when doing multiple plots.
# PHPlot sets the X and Y data label formats to the X and Y label formats
# if the former are not set. But if the X and Y label formats change for the
# second plot, it doesn't reset the X and Y data label formats. (Because
# it can't distinguish between the internally-applied defaultd and user-set
# values.) This is part of bug #164.
# Set $case to 1, 2, or 3 and include this script (default is case=1).
#   Case 1: Baseline, set all 4 of X,Y,XD,YD for second plot
#   Case 2: Shows the bug by setting only X,Y for both plots.
#   Case 3: Shows XD,YD stick to second plot if set.

require_once 'phplot.php';

$data = array(
    array('A', 1, 10),
    array('B', 2, 20),
    array('C', 3, 30),
    array('D', 4, 40),
    array('E', 5, 30),
    array('F', 6, 20),
);

if (empty($case)) $case = 1;
switch ($case) {
case 1:
    $fmt = array( 1 => array('x' => '/%s/', 'y' => '<%s>'),
                  2 => array('x' => '[%s]', 'xd' => '[%s]',
                             'y' => '{%s}', 'yd' => '{%s}',
                            ),
                );
    $subtitle = "Upper plot: /X/     <Y>\n"
              . "Lower plot: [X,XD]  {Y,YD}";

    break;

case 2:
    $fmt = array( 1 => array('x' => '/%s/', 'y' => '<%s>'),
                  2 => array('x' => '[%s]', 'y' => '{%s}'),
                );
    $subtitle = "Upper plot: /X/  <Y>\n"
              . "Lower plot: [X]  {Y}";
    break;

case 3:
    $fmt = array( 1 => array('x' => '/%s/', 'xd' => '|%s|',
                             'y' => '<%s>', 'yd' => '"%s"',
                            ),
                  2 => array( 'x' => '[%s]', 'y' => '{%s}',
                            ),
                );
    $subtitle = "Upper plot: /X/ |XD|  <Y> \"YD\"\n"
              . "Lower plot: [X]  {Y}";
    break;
}



// Common setup:
$plot = new Phplot\Phplot\phplot(460, 600);
$plot->SetPrintImage(False);
$plot->SetTitle("Multiple Plots, X,Y label format\n$subtitle");
$plot->SetPlotType('linepoints');
$plot->SetDataValues($data);
$plot->SetDataType('data-data');
$plot->SetYDataLabelPos('plotin');
// Enable both X tick and X data labels, but with data labels above.
$plot->SetXDataLabelPos('plotup');
$plot->SetXTickLabelPos('plotdown');
$plot->SetPlotBorderType('full');

// Plot #1:
$plot->SetPlotAreaPixels(NULL, 80, NULL, 305);
if (isset($fmt[1]['x']))  $plot->SetXLabelType('printf', $fmt[1]['x']);
if (isset($fmt[1]['y']))  $plot->SetYLabelType('printf', $fmt[1]['y']);
if (isset($fmt[1]['xd'])) $plot->SetXDataLabelType('printf', $fmt[1]['xd']);
if (isset($fmt[1]['yd'])) $plot->SetYDataLabelType('printf', $fmt[1]['yd']);
$plot->DrawGraph();

// Plot #2:
$plot->SetPlotAreaPixels(NULL, 345, NULL, 570);
if (isset($fmt[2]['x']))  $plot->SetXLabelType('printf', $fmt[2]['x']);
if (isset($fmt[2]['y']))  $plot->SetYLabelType('printf', $fmt[2]['y']);
if (isset($fmt[2]['xd'])) $plot->SetXDataLabelType('printf', $fmt[2]['xd']);
if (isset($fmt[2]['yd'])) $plot->SetYDataLabelType('printf', $fmt[2]['yd']);
$plot->DrawGraph();

// Finish:
$plot->PrintImage();
