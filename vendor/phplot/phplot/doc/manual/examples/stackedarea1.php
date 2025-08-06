<?php
# PHPlot Example: Stacked Area chart
require_once __DIR__ . '/../../../src/phplot.php';

$data = array(
  array('1960', 30, 10,  6, 38, 14,  2),
  array('1970', 20, 17,  9, 32,  2, 20),
  array('1980', 20, 14, 12, 27,  2, 25),
  array('1990',  5, 26, 15, 26, 18, 10),
  array('2000', 28,  0, 18, 16, 33,  5),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('stackedarea');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle('Candy Sales by Flavor');

# Set Y data limits, tick increment, and titles:
$plot->SetPlotAreaWorld(NULL, 0, NULL, 100);
$plot->SetYTickIncrement(10);
$plot->SetYTitle('% of Total');
$plot->SetXTitle('Year');

# Colors are significant to this data:
$plot->SetDataColors(array('red', 'green', 'blue', 'yellow', 'cyan', 'magenta'));
$plot->SetLegend(array('Cherry', 'Lime', 'Lemon', 'Banana', 'Apple', 'Berry'));
# Make legend lines go bottom to top, like the area segments (PHPlot > 5.4.0)
$plot->SetLegendReverse(True);

# Turn off X tick labels and ticks because they don't apply here:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

$plot->DrawGraph();
