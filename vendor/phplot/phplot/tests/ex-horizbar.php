<?php

# PHPlot Example - Horizontal Bars
require_once 'phplot.php';

$data = array(
  array('San Francisco CA', 20.11),
  array('Reno NV', 7.5),
  array('Phoenix AZ', 8.3),
  array('New York NY', 49.7),
  array('New Orleans LA', 64.2),
  array('Miami FL', 52.3),
  array('Los Angeles CA', 13.2),
  array('Honolulu HI', 18.3),
  array('Helena MT', 11.3),
  array('Duluth MN', 31.0),
  array('Dodge City KS', 22.4),
  array('Denver CO', 15.8),
  array('Burlington VT', 36.1),
  array('Boston MA', 42.5),
  array('Barrow AL', 4.2),
);

$plot = new Phplot\Phplot\phplot(800, 800);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetTitle("Average Annual Precipitation (inches)\n"
              . "Selected U.S. Cities");
$plot->SetBackgroundColor('gray');
#  Set a tiled background image:
$plot->SetPlotAreaBgImage('images/drop.png', 'centeredtile');
#  Force the X axis range to start at 0:
$plot->SetPlotAreaWorld(0);
#  No ticks along Y axis, just bar labels:
$plot->SetYTickPos('none');
#  No ticks along X axis:
$plot->SetXTickPos('none');
#  No X axis labels. The data values labels are sufficient.
$plot->SetXTickLabelPos('none');
#  Turn on the data value labels:
$plot->SetXDataLabelPos('plotin');
#  No grid lines are needed:
$plot->SetDrawXGrid(FALSE);
#  Set the bar fill color:
$plot->SetDataColors('salmon');
#  Use less 3D shading on the bars:
$plot->SetShading(2);
$plot->SetDataValues($data);
$plot->SetDataType('text-data-yx');
$plot->SetPlotType('bars');
$plot->DrawGraph();
