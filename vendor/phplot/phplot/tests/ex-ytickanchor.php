<?php

# PHPlot Example: Using a Y tick anchor to force a tick at 0 (part 1)
# This requires PHPlot >= 5.4.0
require_once 'phplot.php';

# The variable $set_anchor can be set to a value in another script
# which calls this script, to set the Y anchor to that value.
if (isset($set_anchor))
    $case = "with Y tick anchor at $set_anchor";
else
    $case = "without Y tick anchor";

# Function to plot:
function f($x)
{
  if ($x == 0) return 0;
  return 5 + 8 * sin(200 * M_PI / $x);
}

$data = array();
for ($x = 0; $x < 500; $x++)
  $data[] = array('', $x, f($x));

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain'); // For presentation in the manual
$plot->SetTitle("Example $case");
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetPlotType('lines');
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
# Set the plot range.  This was not needed before PHPlot-6.0.0, when the
# default behavior demonstrated the need for setting a Y tick anchor at 0.
# But starting with PHPlot-6.0.0, the default behavior improved, so this
# example no longer shows the need for the tick anchor without forcing it.
$plot->SetPlotAreaWorld(NULL, -3.5, NULL, 13.5);

if (isset($set_anchor))
    $plot->SetYTickAnchor($set_anchor);

$plot->DrawGraph();
