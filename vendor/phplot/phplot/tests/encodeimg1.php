<?php
# PHPlot test: Data URL (RFC2397)
# Note: To test Data URL right, you need to make an HTML file and then view
# it in a browser. But that doesn't fit well with the way the PHPlot Test Suite
# generates and validates results. Instead, this script will save the
# HTML output to a fixed name file, and also do some simple regexp matching
# on it.
require_once 'phplot.php';

# If this is not empty, it names a file to save the HTML:
$save_html_file = 'encodeimg1.html';
# The test driver can set the environment variable RESULTDIR. If set, use
# that for the output.
$r = getenv("RESULTDIR");
if (empty($r)) $save_html = $save_html_file;
else $save_html = $r . DIRECTORY_SEPARATOR . $save_html_file;

# =============

$data = array();
for ($i = 0; $i <= 360; $i += 15) {
  $theta = deg2rad($i);
  $data[] = array('', $i, cos($theta), sin($theta));
}
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetPrintImage(False);
$p->SetFailureImage(False);
$p->SetTitle('PHPlot Test - Data URL Encoding');
$p->SetImageBorderType('plain');
$p->SetImageBorderWidth(4);
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

$data_url = $p->EncodeImage();

$html = <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
     "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Testing Data URL Inline PHPlot</title>
</head>
<body>
<h1>Inline Image</h1>
<p>This is a plot of sin() and cos().
<p><img src="$data_url" alt="Plot Image">

</body>
</html>
END;

# Save resulting HTML for later viewing:
if (!empty($save_html) && ($f = fopen($save_html, "w"))) {
    fwrite($f, $html);
    fwrite(STDOUT, "Note: Wrote HTML to $save_html_file in results directory\n");
    fclose($f);
}

# One simple pattern match is about all that is possible here:
if (!preg_match('!src="data:image/png;base64,!m', $html)) {
    fwrite(STDERR, "Error: did not match data url pattern\n");
    exit(1);
}
