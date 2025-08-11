<?php

# PHPlot Test - Image map and embedded image - bars or stackedbars
# This writes an HTML file to the directory named by the environment
# variable RESULTDIR (if defined), and does a simple pattern match to check.
require_once 'phplot.php';

# This must be set in the calling script too:
if (!isset($out_name)) $out_name = basename(__FILE__, '.php');
# These can be set in a calling script:
if (!isset($plot_type)) $plot_type = 'bars'; // bars, stackedbars
if (!isset($horizontal)) $horizontal = FALSE;  // True for horizontal bars

$image_map = "";

if ($horizontal) {
    $data_type = 'text-data-yx';
    $title = "Horizontal '$plot_type' plot with image map";
} else {
    $data_type = 'text-data';
    $title = "Vertical '$plot_type' plot with image map";
}

function store_map($im, $passthru, $shape, $row, $col, $x1, $y1, $x2, $y2)
{
  global $image_map;
  if ($shape != 'rect') die("Error expecting rect shapes from plot\n");
  $image_map .= sprintf("  <area shape=\"rect\" coords=\"%d,%d,%d,%d\""
       .  " title=\"Group %d bar %d\""
       .  " alt=\"Region for Group %d bar %d\""
       .  " href=\"example_%d_%d.html\">\n",
       $x1, $y1, $x2, $y2, $row, $col, $row, $col, $row, $col);
}

# Data for {horiz, vertical} {bar, stackedbar}
$data = array(
  array('A',  3,  6,  1),
  array('B',  2,  4,  2),
  array('C',  1,  2,  4),
  array('D',  0,  0,  5),
  array('E',  1,  2,  7),
  array('F',  2,  4,  5),
  array('G',  3,  6,  8),
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
