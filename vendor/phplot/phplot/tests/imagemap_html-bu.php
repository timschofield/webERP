<?php

# PHPlot Test - Image map and embedded image - bubbles plot
# This writes an HTML file to the directory named by the environment
# variable RESULTDIR (if defined), and does a simple pattern match to check.
require_once 'phplot.php';

$out_name = basename(__FILE__, '.php');

$image_map = "";

function store_map($im, $passthru, $shape, $row, $col, $xc, $yc, $d)
{
  global $image_map;
  if ($shape != 'circle') die("Error expecting circle shapes from plot\n");
  $image_map .= sprintf("  <area shape=\"circle\" coords=\"%d,%d,%d\""
       .  " title=\"Row %d col %d\""
       .  " alt=\"Region for Row %d col %d\""
       .  " href=\"example_%d_%d.html\">\n",
       $xc, $yc, $d/2, $row, $col, $row, $col, $row, $col);
}

$data = array(
    array('A', 1,  10, 2,  20, 3 ),
    array('B', 2,  5, 18,  10, 9 ),
    array('C', 3,  2, 1,   12, 15 ),
    array('D', 4,  8, 6,   16, 20 ),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetFailureImage(False); // No error images
$plot->SetPrintImage(False); // No automatic output
$plot->SetTitle("Bubbles plot with image map");
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType('data-data-xyz');
$plot->SetPlotType('bubbles');
$plot->SetPlotAreaWorld(0, 0, 5, 25);
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
if (!preg_match('!<area shape="circle".*<img src="data:image/png!ms', $html)) {
    fwrite(STDERR, "Error: output did not match expected pattern\n");
    exit(1);
}
