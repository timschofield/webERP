<?php

# PHPlot test: Return image encoded as raw
require_once 'phplot.php';
$data = array();
for ($i = 0; $i <= 360; $i += 15) {
  $theta = deg2rad($i);
  $data[] = array('', $i, cos($theta), sin($theta));
}
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetPrintImage(False);
$p->SetFailureImage(False);
$p->SetTitle('PHPlot Test - Raw Image Encoding');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(90);
$p->SetYTickIncrement(0.2);
$p->SetPlotAreaWorld(0, -1, 360, 1);
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);
$p->SetPlotType('lines');
$p->DrawGraph();

$data = $p->EncodeImage('raw');
// fwrite(STDERR, "EncodeImage('raw') size = " . strlen($data) . "\n");
echo $data;
