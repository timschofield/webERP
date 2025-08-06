<?php

# PHPlot Example: Bar chart with bar color depending on value
require_once 'phplot.php';

# Callback for picking a data color.
# PHPlot will call this every time it needs a data color.
# This returns a color index which depends on the data value.
# Color 0 is for values >= 80%, 1 is for >= 60%, 2 is for < 60%.
# The data_array must have 'text-data' type.
function pickcolor($img, $data_array, $row, $col)
{
  $d = $data_array[$row][$col+1]; // col+1 skips over the row's label
  if ($d >= 80) return 0;
  if ($d >= 60) return 1;
  return 2;
}

# The data array has our monthly performance as a percentage.
$data = array(
    array('Jan',  95), array('Feb',  75), array('Mar',  83),
    array('Apr',  66), array('May',  90), array('Jun',  80),
    array('Jul',  70), array('Aug',  50), array('Sep',  60),
    array('Oct',  70), array('Nov',  80), array('Dec',  45),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetPlotType('bars');
$plot->SetDataValues($data);
$plot->SetDataType('text-data');
$plot->SetTitle('Monthly Performance Rating');

# Turn off X Tick labels which have no meaning here.
$plot->SetXTickPos('none');

# Force the Y axis to be exactly 0:100
$plot->SetPlotAreaWorld(NULL, 0, NULL, 100);

# Establish the function 'pickcolor' as a data color selection callback.
# Set the $data array as the pass-through argument, so the function has
# access to the data values without relying on global variables.
$plot->SetCallback('data_color', 'pickcolor', $data);

# The three colors are meaningful to the data color callback.
$plot->SetDataColors(array('green', 'yellow', 'red'));

# The legend will explain the use of the 3 colors.
$plot->SetLegend(array('Exceeded expectations', 'Met expectations',
  'Failed to meet expectations'));

$plot->DrawGraph();
