<?php

# PHPlot Test - streaming plots, extract image from first frame
# This produces a single-frame "stream", extracts the image part, and
# writes it to stdout - as if producing a single plot.
require_once 'phplot.php';

$data = array(
      array('', 1, 2, 3),
      array('', 2, 4, 6),
      array('', 3, 6, 9),
    );

$plot = new Phplot\Phplot\phplot(640, 480);
$plot->SetTitle("Testing Streaming Plots");
$plot->SetDataType('text-data');
$plot->SetPlotType('lines');
$plot->SetFileFormat('jpg');
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetXDataLabelPos('none');
$plot->SetPlotAreaWorld(NULL, 0, NULL, 10);
$plot->SetPrintImage(False);

# This produces no output, just HTTP headers which CLI ignores.
$plot->StartStream();

# Capture the frame:
$plot->SetDataValues($data);
$plot->DrawGraph();
ob_start();
$plot->PrintImageFrame();
$frame = ob_get_clean();

# Extract the image from the frame:
if (!preg_match("\x01Content-Length: (\\d*)\\s*(.*)\x01ms", $frame, $match)
     || empty($match[2])) {
    fwrite(STDERR, "(stream1) Error: Did not match frame\n");
    exit(1);
}
echo rtrim($match[2]);
