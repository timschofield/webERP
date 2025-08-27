<?php
# PHPlot test: Coloring various items
require_once 'phplot.php';

$data = array();
$x = 0.0;
$dx = M_PI / 4.0;
for ($i = 0; $i < 50; $i++) {
  $y = $x * sin($x);
  $data[] = array('', $i, $y);
  $x += $dx;
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetPlotType('lines');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetLineWidths(2);
$plot->SetPlotAreaWorld(0, -40, 50, 40);

# Turn everything on to test colors:
$plot->SetTitle("Color Tests\n"
       . " SetGridColor blue (used for axes, legend border)\n"
       . " SetLightGridColor magenta\n"
       . " SetTextColor red\n"
       . " SetTickColor green\n"
       . " SetTitleColor gold"
  );
$plot->SetDrawXGrid(True);
$plot->SetDrawYGrid(True);
$plot->SetLegend('Color Tests');
$plot->SetXTitle('X Title Here');
$plot->SetYTitle('Y Title Here');

# Set colors:
$plot->SetGridColor('blue');
$plot->SetLightGridColor('magenta');
$plot->SetTextColor('red');
$plot->SetTickColor('green');
$plot->SetTitleColor('gold');

$plot->DrawGraph();
