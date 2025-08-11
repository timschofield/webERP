<?php

# Null X range error case
# From forum post on scatter plot with all same X value, 3/27/2008
require_once 'phplot.php';

# The bug condition causes PHPlot to hang, so set a time limit:
set_time_limit(3);

# "33,1.3 33,2.5 33,4.5 ... hundreds more ... "
$x = 33;
$y = 1.3;
for ($i = 0; $i < 200; $i++) {
  $data[] = array('', $x, $y);
  # $y += 0.03;
}

# Debug scale callback:
function debug_scale_f($img, $passthru, $fname, $args)
{
  fwrite(STDERR, "+debug_scale $fname: " . print_r($args, True) . "\n");
}

$p = new Phplot\Phplot\phplot(800,600);
#$p->SetCallback('debug_scale', 'debug_scale_f');
$p->SetTitle('Scatter Plot without X range');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');

$p->SetPlotType('points');
$p->DrawGraph();
