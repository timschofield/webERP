<?php

# PHPlot test: Overlay 2 plots to get 2 different Y axis scales
# Based on a posting 2010-07-16 to PHPlot forum
require_once 'phplot.php';

// First data array for Left axis graph:
$data1 = array(
    array('', 0, 20),
    array('', 1, 15),
    array('', 2,  8),
    array('', 3,  6),
    array('', 4,  3),
    array('', 5,  7),
    array('', 6, 14),
    array('', 7, 28),
    array('', 8, 32),
    array('', 9, 35),
);

// Second data array for Right axis graph:
$data2 = array(
    array('', 0, 17),
    array('', 1, 22),
    array('', 2, 30),
    array('', 3, 36),
    array('', 4, 49),
    array('', 5, 57),
    array('', 6, 67),
    array('', 7, 73),
    array('', 8, 78),
    array('', 9, 81),
);

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetPrintImage(0);  // Do not output image until told

// First plot:

$p->SetDataValues($data1);
$p->SetDataType('data-data');
$p->SetPlotType('lines');
$p->SetPlotAreaWorld(NULL, 0, NULL, NULL); // Force Y to start at 0
$p->SetPlotAreaPixels(60, 40, 740, 560); // Force same window for both plots
$p->SetYTickPos('plotleft'); // The default
$p->SetYTickLabelPos('plotleft'); // The default
$p->SetXTickIncrement(1);
$p->SetDataColors('blue');  // Force data color
$p->SetTitle('Overlay Line Plots to get Different Y Scales');
$p->SetTitleColor('black');
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Temperature', 'plotleft');
$p->SetYTitleColor('blue');
$p->DrawGraph();

// Second plot:

$p->SetDataValues($data2);
$p->SetDataType('data-data');
$p->SetPlotType('lines');
$p->SetPlotAreaWorld(NULL, 0, NULL, NULL); // Force Y to start at 0
$p->SetYTickPos('plotright');
$p->SetYTickLabelPos('plotright');
// Right Y title only:
$p->SetXTitle(''); // Clear this so it doesn't plot again
$p->SetYTitle('Volume', 'plotright');
$p->SetYTitleColor('green');
$p->SetXTickPos('none');  // Suppress X ticks and labels - done above
$p->SetXTickLabelPos('none');
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(1);
$p->SetDataColors('green'); // Use a different color for this data line
$p->SetDrawYGrid(False);  // Grid lines already done above
$p->DrawGraph();  // Produce the second graph

$p->PrintImage(); // Now output the completed image
