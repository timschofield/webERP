<?php

# PHPlot test: Return image encoded as base64
require_once 'phplot.php';
$data = array();
for ($i = 0; $i <= 360; $i += 15) {
  $theta = deg2rad($i);
  $data[] = array('', $i, cos($theta), sin($theta));
}
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetPrintImage(False);
$p->SetFailureImage(False);
$p->SetTitle('PHPlot Test - Base64 Image Encoding');
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

$data = base64_decode($p->EncodeImage('base64'), True);
if ($data === False) {
    fwrite(STDERR, "Failed to decode data as base64\n");
    exit(1);
}
echo $data;
