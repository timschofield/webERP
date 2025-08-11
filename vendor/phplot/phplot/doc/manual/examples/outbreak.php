<?php
# PHPlot Example: Plot Overlay (lines and stackedbars)
require_once __DIR__ . '/../../../src/phplot.php';

$title = '2009 Outbreak, Positive Tests';

# Note: Graph is based on the real thing, but the data is invented.
# Data for plot #1: stackedbars:
$y_title1 = 'Number of positive tests';
$data1 = array(
    array('1/09',  200,  200,  300),
    array('2/09',  300,  100,  700),
    array('3/09',  400,  200,  800),
    array('4/09',  500,  300, 1200),
    array('5/09',  400,  400, 2500),
    array('6/09',  500,  600, 3600),
    array('7/09',  400, 1200, 3300),
    array('8/09',  300, 1500, 2500),
    array('9/09',  200, 1900,  800),
    array('10/09', 100, 2000,  200),
    array('11/09', 100, 2500,  100),
    array('12/09', 100, 2700,  200),
);
$legend1 = array('Strain A', 'Strain B', 'Strain C');

# Data for plot #2: linepoints:
$y_title2 = 'Percent Positive';
$data2 = array(
    array('1/09',   5),
    array('2/09',  10),
    array('3/09',  15),
    array('4/09',  30),
    array('5/09',  40),
    array('6/09',  45),
    array('7/09',  47),
    array('8/09',  35),
    array('9/09',  25),
    array('10/09', 20),
    array('11/09', 25),
    array('12/09', 30),
);
$legend2 = array('% positive');

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // For presentation in the manual
$plot->SetPrintImage(False); // Defer output until the end
$plot->SetTitle($title);
$plot->SetPlotBgColor('gray');
$plot->SetLightGridColor('black'); // So grid stands out from background

# Plot 1
$plot->SetDrawPlotAreaBackground(True);
$plot->SetPlotType('stackedbars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data1);
$plot->SetYTitle($y_title1);
# Set and position legend #1:
$plot->SetLegend($legend1);
$plot->SetLegendPixels(5, 30);
# Set margins to leave room for plot 2 Y title on the right.
$plot->SetMarginsPixels(120, 120);
# Specify Y range of these data sets:
$plot->SetPlotAreaWorld(NULL, 0, NULL, 5000);
$plot->SetYTickIncrement(500);
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
# Format Y tick labels as integers, with thousands separator:
$plot->SetYLabelType('data', 0);
$plot->DrawGraph();

# Plot 2
$plot->SetDrawPlotAreaBackground(False); // Cancel background
$plot->SetDrawYGrid(False); // Cancel grid, already drawn
$plot->SetPlotType('linepoints');
$plot->SetDataValues($data2);
# Set Y title for plot #2 and position it on the right side:
$plot->SetYTitle($y_title2, 'plotright');
# Set and position legend #2:
$plot->SetLegend($legend2);
$plot->SetLegendPixels(690, 30);
# Specify Y range of this data set:
$plot->SetPlotAreaWorld(NULL, 0, NULL, 50);
$plot->SetYTickIncrement(10);
$plot->SetYTickPos('plotright');
$plot->SetYTickLabelPos('plotright');
$plot->SetDataColors('black');
# Format Y tick labels as integers with trailing percent sign:
$plot->SetYLabelType('data', 0, '', '%');
$plot->DrawGraph();

# Now output the graph with both plots:
$plot->PrintImage();
