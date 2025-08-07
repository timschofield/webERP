<?php

# Testing PHPlot - Multiple plot, sticky settings for tick increment
# This tests for a problem with under-development R6 PHPlot which calculated
# tick steps and stored them back into the object, unlike R5. The result
# was that the 2nd plot used the correct range (because it was reset) but
# the tick steps from the 1st plot, which was wrong for the new data set.

require_once 'phplot.php';

# Make a data array for the given X and Y ranges:
function build_data($x1, $dx, $nx, $y1, $y2)
{
    $dy = ($y2 - $y1) / ($nx - 1);
    $data = array();
    while ($nx-- > 0) {
        $data[] = array('', $x1, $y1);
        $x1 += $dx;
        $y1 += $dy;
    }
    return $data;
}


// Common setup:
$plot = new Phplot\Phplot\phplot(460, 600);
$plot->SetTitle("Multiple Plots, Sticky Settings\n"
              . "Reset data range and not tick step between plots");
$plot->SetDataType('data-data');
$plot->SetPlotType('linepoints');
$plot->SetPrintImage(False);

// Plot #1:
$plot->SetPlotAreaPixels(40, 60, NULL, 300);
$plot->SetDataValues(build_data(0, 1, 4, 5, 2));
$plot->SetPlotAreaWorld();
$plot->DrawGraph();

// Plot #2:
$plot->SetPlotAreaPixels(40, 330, NULL, 570);
$plot->SetDataValues(build_data(0, 10, 4, 0, 400));
$plot->SetPlotAreaWorld();
$plot->DrawGraph();

// Finish:
$plot->PrintImage();
