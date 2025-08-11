<?php

# Test: Stacked area
require_once 'phplot.php';
# This is based on area1 with adjusted numbers.
$data = array(
  array('1960', 30, 10,  6, 38, 14,  2),
  array('1970', 20, 17,  9, 32,  2, 20),
  array('1980', 20, 14, 12, 27,  2, 25),
  array('1990',  5, 26, 15, 26, 18, 10),
  array('2000', 28,  0, 18, 16, 33,  5),
);
$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType('stackedarea');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetTitle('Candy Sales by Flavor');
$plot->SetPlotAreaWorld(NULL, 0, NULL, 110);
$plot->SetYTickIncrement(10);
$plot->SetYTitle('% of Total');
$plot->SetXTitle('Year');
$plot->SetDataColors(array('red', 'green', 'blue', 'yellow', 'cyan', 'magenta'));
$plot->SetLegend(array('Cherry', 'Lime', 'Lemon', 'Banana', 'Apple', 'Berry'));
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->DrawGraph();
