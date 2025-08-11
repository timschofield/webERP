<?php

# PHPlot test - Multiple Pie Charts, with label and other variations
require_once 'phplot.php';


$data = array(
    array('Gold',        20),
    array('Silver',      13),
    array('Copper',       7),
    array('Tin',         18),
    array('Bronze',      10),
    array('Iron',         4),
    array('Platinum',     2),
    array('Nickel',       5),
);
$data_type = 'text-data-single';

# Build legend from data array:
$legend = array();
foreach ($data as $row) $legend[] = $row[0] . ' = ' . $row[1];

# Common setup for all plots on the image:
$plot = new Phplot\Phplot\phplot(800, 625);
$plot->SetTitle('Multiple pie charts');
$plot->SetPrintImage(False);
$plot->SetBackgroundColor('wheat');
$plot->SetDrawPlotAreaBackground(True);
$plot->SetPlotType('pie');
$plot->SetDataType($data_type);
$plot->SetDataValues($data);
$plot->SetPlotBorderType('full');
#$plot->SetLabelScalePosition(0.50);

# Plot 1 - upper left
$plot->SetPlotAreaPixels(4, 25, 398, 323);
$plot->SetPieLabelType('label');
$plot->SetPlotBgColor('plum');
$plot->DrawGraph();

# Plot 2 - upper right
$plot->SetPlotAreaPixels(402, 25, 797, 323);
$plot->SetPieLabelType('index');
$plot->SetPlotBgColor('salmon');
$plot->DrawGraph();

# Plot 3 - lower left
$plot->SetPlotAreaPixels(4, 327, 398, 623);
$plot->SetPieLabelType(''); // Reset to default
$plot->SetDrawPlotAreaBackground(False);
$plot->DrawGraph();

# Plot 4 - lower right
$plot->SetPlotAreaPixels(402, 327, 797, 623);
$plot->SetPieLabelType('value');
$plot->SetPlotBgColor('gray');
$plot->SetDrawPlotAreaBackground(True);

// One legend for the whole image. Set it up before the last plot.
$plot->SetLegendPosition(0.5, 0.5, 'plot', 0.0, 0.0);
$plot->SetLegend($legend);
$plot->DrawGraph();

# Done
$plot->PrintImage();
