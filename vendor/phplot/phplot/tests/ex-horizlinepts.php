<?php

# PHPlot Example - Horizontal linepoints plot with Y Data Label Lines
require_once 'phplot.php';

# Check for PHPlot method and skip the test if it is missing:
if (!method_exists('Phplot\Phplot\phplot', 'SetDrawYDataLabelLines')) {
    echo "Skipping test because it requires SetDrawYDataLabelLines()\n";
    exit(2);
}

$data = array(
            array("SEA\nLEVEL", 0, ''),
            array('100m', 1, 10),
            array('200m', 2, 22),
            array('300m', 3, 30),
            array('400m', 4, 46),
            array('500m', 5, 53),
            array('600m', 6, 65),
            array('700m', 7, 70),
            array('800m', 8, 50),
            array('900m', 9, 35),
        );

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetTitle('Wind Speed at Altitude');
$plot->SetDataType('data-data-yx');
$plot->SetDataValues($data);
$plot->SetPlotType('linepoints');
$plot->SetPlotAreaWorld(0, 0, 100, 10);
$plot->SetDrawYDataLabelLines(True);
$plot->SetXTitle('Wind Speed');
$plot->SetYTitle('Altitude');
$plot->SetYTickLabelPos('none');
$plot->SetYTickPos('none');
$plot->SetXDataLabelPos('plotin');
$plot->SetDrawXGrid(False);
$plot->SetDrawYGrid(False);
$plot->DrawGraph();
