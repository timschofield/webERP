<?php

# PHPlot Test - Image map and embedded image - Box plot
# This writes an HTML file to the directory named by the environment
# variable RESULTDIR (if defined), and does a simple pattern match to check.
require_once 'phplot.php';

$out_name = basename(__FILE__, '.php');

$image_map = "";

function store_map($im, $passthru, $shape, $row, $unused_col, $x1, $y1, $x2, $y2)
{
  global $image_map;
  if ($shape != 'rect') die("Error expecting rect shapes from plot\n");
  $image_map .= sprintf("  <area shape=\"rect\" coords=\"%d,%d,%d,%d\""
       .  " title=\"Row %d\""
       .  " alt=\"Region for Row %d\""
       .  " href=\"example_%d.html\">\n",
       $x1, $y1, $x2, $y2, $row, $row, $row);
}

$data = array(
    #          X   Ymin  YQ1  Ymid  YQ3  Ymax   Outliers
    array('A', 1,   10,  12,   15,  17,   20),
    array('B', 2,    5,  10,   16,  20,   24,   2, 3, 4),
    array('C', 3,   12,  13,   14,  15,   16,  20),
    array('D', 4,   10,  11,   12,  13,   14),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetFailureImage(False); // No error images
$plot->SetPrintImage(False); // No automatic output
$plot->SetTitle("Boxes plot with image map");
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType('data-data');
$plot->SetPlotType('boxes');
# Check return value in case callback isn't defined:
if (!$plot->SetCallback('data_points', 'store_map'))
    die("Missing callback\n");
$plot->DrawGraph();

$data_url = $plot->EncodeImage();

$html = <<<END
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
     "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>PHPlot Example - Inline Image</title>
</head>
<body>
<h1>PHPlot Example - Inline Image</h1>
<map name="map1">
$image_map</map>
<p>This is a plot with image map and tooltip text.</p>
<img src="$data_url" alt="Plot Image" usemap="#map1">
</body>
</html>

END;


# Save resulting HTML for later viewing (in test directory, if applicable):
$save_html_file = $out_name . '.html';
$r = getenv("RESULTDIR");
if (empty($r)) $save_html = $save_html_file;
else $save_html = $r . DIRECTORY_SEPARATOR . $save_html_file;

if (($f = fopen($save_html, "w"))) {
    fwrite($f, $html);
    # Note: message cannot include path to results directory, so that
    # test results run in different directories can be compared.
    echo "Note: Wrote HTML to $save_html_file in results directory\n";
    fclose($f);
}

# A simple pattern match, quick data check:
if (!preg_match('!<area shape="rect".*<img src="data:image/png!ms', $html)) {
    fwrite(STDERR, "Error: output did not match expected pattern\n");
    exit(1);
}
