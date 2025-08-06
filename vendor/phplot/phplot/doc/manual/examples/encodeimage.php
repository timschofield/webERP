<?php
# PHPlot Example: Using 'data:' URL scheme to embed an image
# Unlike other examples, this outputs a complete HTML page with embedded image.
require_once __DIR__ . '/../../../src/phplot.php';

# Generate data for: Y1 = sin(x), Y2 = cos(x)
$end = M_PI * 2.0;
$delta = $end / 20.0;
$data = array();
for ($x = 0; $x <= $end; $x += $delta)
  $data[] = array('', $x, sin($x), cos($x));

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetFailureImage(False); // No error images
$plot->SetPrintImage(False); // No automatic output
$plot->SetImageBorderType('plain');
$plot->SetPlotType('lines');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetTitle('Line Plot, Sin and Cos - Embedded Image');
$plot->SetLegend(array('sin(t)', 'cos(t)'));
$plot->SetPlotAreaWorld(0, -1, 6.8, 1);
$plot->SetXDataLabelPos('none');
$plot->SetXTickIncrement(M_PI / 8.0);
$plot->SetXLabelType('data');
$plot->SetPrecisionX(3);
$plot->SetYTickIncrement(0.2);
$plot->SetYLabelType('data');
$plot->SetPrecisionY(1);
$plot->SetDrawXGrid(True);
$plot->SetDrawYGrid(True);
$plot->DrawGraph();

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
     "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>PHPlot Example - Inline Image</title>
</head>
<body>
<h1>PHPlot Example - Inline Image</h1>
<p>This is a plot of sin() and cos().</p>
<img src="<?php echo $plot->EncodeImage();?>" alt="Plot Image">
</body>
</html>
