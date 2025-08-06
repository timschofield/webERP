<?php

# PHPlot Example - Horizontal Stacked Bars
require_once 'phplot.php';

$column_names = array(
                 'Beef', 'Fish', 'Pork', 'Chicken', 'Butter',
                                                         'Cheese',
                                                               'Ice Cream');
//                   |       |       |       |       |       |       |
$data = array(
    array('1910',   48.5,   11.2,   38.2,   11.0,   18.4,    3.9,    1.9),
    array('1930',   33.7,   10.2,   41.1,   11.1,   17.6,    4.7,    9.7),
    array('1950',   44.6,   11.9,   43.0,   14.3,   10.9,    7.7,   17.4),
    array('1970',   79.6,   11.7,   48.1,   27.4,    5.4,   11.4,   17.8),
    array('1990',   63.9,   14.9,   46.4,   42.4,    4.0,   24.6,   15.8),
);
$plot = new Phplot\Phplot\phplot(800, 500);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetTitle("U.S. Annual Per-Capita Consumption\n"
              . "of Selected Meat and Dairy Products");
$plot->SetLegend($column_names);
#  Move the legend to the lower right of the plot area:
$plot->SetLegendPixels(700, 300);
$plot->SetDataValues($data);
$plot->SetDataType('text-data-yx');
$plot->SetPlotType('stackedbars');
$plot->SetXTitle('Pounds Consumed Per Capita');
#  Show data value labels:
$plot->SetXDataLabelPos('plotstack');
#  Rotate data value labels to 90 degrees:
$plot->SetXDataLabelAngle(90);
#  Format the data value labels with 1 decimal place:
$plot->SetXDataLabelType('data', 1);
#  Specify a whole number for the X tick interval:
$plot->SetXTickIncrement(20);
#  Disable the Y tick marks:
$plot->SetYTickPos('none');
$plot->DrawGraph();
