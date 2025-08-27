<?php

# PHPlot Test: difference between data set count and legend lines
# Tests for a possible issue (which was fixed) when the number
# of lines in the legend is greater than the number of data columns,
# and if the data colors array was truncated to the number of data
# columns, then the legend colors would repeat.
require_once 'phplot.php';
$n_data = 4;  // Number of data sets
$n_legend = 10; // Number of legend lines

$subtitle = "$n_data data sets, $n_legend legend lines";
$data = array();
for ($i = 0; $i < 3; $i++) {
    $row = array("=$i=");
    for ($j = 0; $j < $n_data; $j++) $row[] = $i + $j;
    $data[] = $row;
}
for ($j = 0; $j < $n_legend; $j++)
     $legend[$j] = "Legend Line $j";
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Legend Line Count Tests\n$subtitle");
$p->SetLegend($legend);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->DrawGraph();
