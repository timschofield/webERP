<?php

# PHPlot test: Two plots on image with auto-scaling
require_once 'phplot.php';

$data1 = array(
    array('', 0, 10, 50),
    array('', 1, 20, 40),
    array('', 2, 30, 20),
    array('', 3, 40,  0),
);

$data2 = array(
    array('', 0, 10, 80),
    array('', 1, 20, 40),
    array('', 2, 90, 20),
    array('', 3, 40, 10),
    array('', 7, 50,  5),
    array('', 9,  0,  0),
);

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetPrintImage(0);  // Do not output image until told

// First plot:

$p->SetDataValues($data1);
$p->SetDataType('data-data');
$p->SetPlotType('lines');
$p->SetTitle('Two plots with auto scaling');
$p->SetXTitle('X Axis Title 1');
$p->SetYTitle('Y Axis Title 1');
$p->SetXTickIncrement(0.5);
$p->SetPlotAreaPixels(50, 50, 350, 550);
$p->DrawGraph();

// Second plot:

$p->SetDataValues($data2);
$p->SetDataType('data-data');
$p->SetPlotType('lines');
$p->SetXTitle('X Axis Title 2');
$p->SetYTitle('Y Axis Title 2');
$p->SetXTickIncrement(NULL); // Reset to auto tick calculation
$p->SetPlotAreaWorld(); // Resets scale to auto
$p->SetPlotAreaPixels(450, 50, 750, 550);
$p->DrawGraph();

// Now output the completed image
$p->PrintImage();
