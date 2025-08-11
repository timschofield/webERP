<?php

# PHPlot Example - Box Plot (without outliers)
require_once 'phplot.php';

# The experimental results:
$results = array(
    'Material A1' => array(11.1, 8.4, 11.9, 13, 10.2, 9.2),
    'Material A2' => array(10.6, 9.8, 9.1, 12, 8.5, 7.1, 8.2, 7.8, 11.8),
    'Material B1' => array(9.9, 11.2, 10.9, 12.9, 8.5),
    'Material C1' => array(10.1, 11.8, 11.6, 8.5, 9.6, 12, 12, 8.9, 12.7, 10.3, 11.1),
    'Material C2' => array(10.9, 11.9, 7.3, 11.7, 12.6, 9.2, 10, 7, 7.1),
    'Material C3' => array(11.9, 9.1, 10.5, 10.7, 10, 7.3, 10.1, 11.7, 10.4, 9),
    'Material D1' => array(12, 8, 9.8, 11.4, 10.5, 12.5, 7.1, 8.6, 7.6, 7.4, 8.2, 7.1),
    'Material E1' => array(10.6, 8.2, 8.2, 7.9, 12, 7, 9.3),
);

# Return the k-th percentile value of a sorted array, where 0 <= $k <= 1
# If there is no single value, return the average of the two adjacent ones.
# Caution: Don't copy this code. It may not be valid, but suits the example.
function kpercentile($row, $k)
{
    $n = count($row);
    $p = $k * ($n - 1);
    if ($p == (int)($p)) return $row[$p];
    return ($row[(int)$p] + $row[(int)($p+1)]) / 2;
}

# Convert the experimental results to a PHPlot data array.
function reduce_data($results)
{
    $data = array();
    foreach ($results as $label => $row) {
        $n_samples = count($row);
        sort($row);
        $data[] = array("$label\n($n_samples Samples)",
                         $row[0],                  // Minimum
                         kpercentile($row, 0.25),  // Q1 = 25%
                         kpercentile($row, 0.50),  // Median
                         kpercentile($row, 0.75),  // Q3 = 75%
                         $row[$n_samples-1]);      // Maximum
    }
    return $data;
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle('Box Plot (without outliers)');
$plot->SetDataType('text-data');
$plot->SetDataValues(reduce_data($results));
$plot->SetPlotType('boxes');
# These 2 lines make tick marks line up with text-data data points:
$plot->SetXTickIncrement(1);
$plot->SetXTickAnchor(0.5);
$plot->SetImageBorderType('plain'); // Improves presentation in the manual
$plot->DrawGraph();
