<?php

# PHPlot test: Overlay squared + points plot
require_once 'phplot.php';

mt_srand(0); // For 'random' but consistent values
// Build a data array:
for ($i = 0; $i < 50; $i++)
    $data[] = array('', $i, mt_rand(0, 10));

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetPrintImage(0);  // Do not output image until told

// First plot:

$p->SetDataValues($data);
$p->SetDataType('data-data');
$p->SetPlotType('squared');
$p->SetTitle('Squared+Points Overlay Plot');
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Y Axis Title');
$p->DrawGraph();

// Second plot:

$p->SetPlotType('points');
$p->SetXTitle(''); // Disable; already done
$p->SetYTitle(''); // Disable; already done
$p->SetDrawYGrid(False); // Don't draw over the grid
$p->DrawGraph();

$p->PrintImage(); // Now output the completed image
