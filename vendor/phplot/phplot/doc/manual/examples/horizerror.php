<?php
# PHPlot Example - Horizontal Error Plot
require_once __DIR__ . '/../../../src/phplot.php';

# The experimental results as a series of temperature measurements:
$results = array(98, 102, 100, 103, 101, 105, 110, 108, 109);
# The accuracy of our measuring equipment is +/- 5%
$error_factor = 0.05;

# Convert the experimental results to a PHPlot data array for error plots.
function reduce_data($results, $error_factor)
{
    # Use the average of measurements to approximate the error amount:
    $err = $error_factor * array_sum($results) / count($results);

    # Build the 'data-data-yx-error' data array:
    $data = array();
    $i = 1;
    foreach ($results as $value) {
        $data[] = array("Sample $i", $i++, $value, $err, $err);
    }
    return $data;
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle('Experiment Results');
$plot->SetXTitle('Melting Temperature (degrees C)');
$plot->SetDataValues(reduce_data($results, $error_factor));
$plot->SetDataType('data-data-yx-error');
$plot->SetPlotType('points');
$plot->SetYTickPos('none');
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->SetPlotAreaWorld(80);
$plot->DrawGraph();
