<?php

# PHPlot test: Overlay OHLC chart with lines
# From PHPlot forum 2010-12-20
require_once 'phplot.php';

// First data array for OHLC data: Date, Open, High, Low, Close
$data1 = array(
    array('Dec  6', 20, 24, 19, 22),
    array('Dec  7', 22, 26, 20, 26),
    array('Dec  8', 26, 28, 22, 24),
    array('Dec  9', 24, 30, 22, 28),
    array('Dec 10', 28, 30, 15, 18),
);
$n_points = count($data1);

// Second data array contains two lines, calculated from above.
// 1st is opening prices, 2nd is middle of the daily range = (low+high)/2.
$data2 = array();
for ($i = 0; $i < $n_points; $i++) {
   $data2[] = array('', $data1[$i][1], ($data1[$i][2] + $data1[$i][3]) / 2);
}

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetPrintImage(0);  // Do not output image until told

// First plot:

$p->SetDataValues($data1);
$p->SetDataType('text-data');
$p->SetPlotType('ohlc');
$p->SetPlotAreaWorld(NULL, 0); // For Y to start at 0
$p->SetXTickPos('none');
$p->SetTitle('OHLC and Line Plot Overlay');
$p->SetXTitle('Date', 'plotdown');
$p->SetYTitle('Security Price', 'plotleft');

$p->SetDrawPlotAreaBackground(True);
$p->SetPlotBgColor('PeachPuff');

$p->SetMarginsPixels(50, 50, 50, 50);
$p->DrawGraph();

// Second plot:

$p->SetDrawPlotAreaBackground(False);
$p->SetDataValues($data2);
$p->SetDataType('text-data');
$p->SetPlotType('lines');
$p->SetDataColors(array('red', 'orange'));
// Must clear X and Y titles or they are drawn again, possibly with offset.
$p->SetXTitle('');
$p->SetYTitle('');
$p->DrawGraph();

// Now output the completed image
$p->PrintImage();
