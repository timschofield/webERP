<?php

# PHPlot Test - Image map and embedded image - OHLC, candlesticks
# This writes an HTML file to the directory named by the environment
# variable RESULTDIR (if defined), and does a simple pattern match to check.
require_once 'phplot.php';

# This must be set in the calling script too:
if (!isset($out_name)) $out_name = basename(__FILE__, '.php');
# This can be set in a calling script:
if (!isset($plot_type)) $plot_type = 'candlesticks2'; // candlesticks, ohlc

$data_type = 'text-data';
$title = "'$plot_type' plot with image map";

$image_map = "";

// Note $col is unused for this plot type
function store_map($im, $passthru, $shape, $row, $unused, $x1, $y1, $x2, $y2)
{
  global $image_map;
  if ($shape != 'rect') die("Error expecting rect shapes from plot\n");
  $image_map .= sprintf("  <area shape=\"rect\" coords=\"%d,%d,%d,%d\""
       .  " title=\"Value %d\""
       .  " alt=\"Region for Value %d\""
       .  " href=\"example_%d.html\">\n",
       $x1, $y1, $x2, $y2, $row, $row, $row);
}

# Data for candlesticks plot without X values.
$data = array(
//              Open, High, Low, Close
  array('Mon',  10,   12,    9,  11),
  array('Tue',  11,   18,   11,  15),
  array('Wed',  15,   15,   10,  15),
  array('Thu',  15,   16,    8,  10),
  array('Fri',  10,   17,   10,  16),

  array('Mon',  16,   19,   15,  18),
  array('Tue',  18,   20,   10,  10),
  array('Wed',  10,   10,    5,   6),
  array('Thu',   6,    6,    6,   6),
  array('Fri',   6,   10,    4,   6),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetFailureImage(False); // No error images
$plot->SetPrintImage(False); // No automatic output
$plot->SetTitle($title);
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType($data_type);
$plot->SetPlotType($plot_type);
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
    echo "Note: Wrote HTML to $save_html_file in results directory\n";
    fclose($f);
}

# A simple pattern match, quick data check:
if (!preg_match('!<area shape="rect".*<img src="data:image/png!ms', $html)) {
    fwrite(STDERR, "Error: output did not match expected pattern\n");
    exit(1);
}
