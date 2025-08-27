<?php

# Testing phplot - Color Chart
require_once 'phplot.php';

// Extend PHPlot class to allow access to protected variable(s):
class PHPlot_pv extends Phplot\Phplot\phplot {
    public function GET_rgb_array() { return $this->rgb_array; }
}

$p = new phplot_pv(800,700);

# Use internal color map rgb_array to get a list of colors:
$colors = array_keys($p->GET_rgb_array());
sort($colors);
$nc = count($colors);
# Make a data array with 1 point, $nc data sets:
$dp = array('');
for ($i = 0; $i < $nc; $i++) {
  $dp[] = $nc - $i;
}
$data = array($dp);

$p->SetPlotType('bars');
$p->SetDataType('text-data');
$p->SetDataValues($data);

# Use all colors:
$p->SetDataColors($colors);
$p->SetShading(0);

# Make a legend with all the color names:
$p->SetLegend($colors);

$p->SetPlotAreaWorld(0.1, 0, NULL, NULL);

$p->SetTitle('PHPlot Default Color Map');
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->SetYTickIncrement(1);

$p->DrawGraph();
