<?php


# PHPlot Example: Area chart, 6 areas.
require_once 'phplot.php';

$data = array(
  array('1960', 100, 70, 60, 54, 16,  2),
  array('1970', 100, 80, 63, 54, 22, 20),
  array('1980', 100, 80, 66, 54, 27, 25),
  array('1990', 100, 95, 69, 54, 28, 10),
  array('2000', 100, 72, 72, 54, 38,  5),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('area');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle('Candy Sales by Flavor');

# Set Y data limits, tick increment, and titles:
$plot->SetPlotAreaWorld(NULL, 0, NULL, 110);
$plot->SetYTickIncrement(10);
$plot->SetYTitle('% of Total');
$plot->SetXTitle('Year');

# Colors are significant to this data:
$plot->SetDataColors(array('red', 'green', 'blue', 'yellow', 'cyan', 'magenta'));
$plot->SetLegend(array('Cherry', 'Lime', 'Lemon', 'Banana', 'Apple', 'Berry'));

# Turn off X tick labels and ticks because they don't apply here:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

$plot->DrawGraph();
