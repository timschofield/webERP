<?php

# PHPlot Test - Image map and embedded image - points
# With tooltip text showing point value
# This writes an HTML file to the directory named by the environment
# variable RESULTDIR (if defined), and does a simple pattern match to check.
require_once 'phplot.php';

# This must be set in the calling script too:
if (!isset($out_name)) $out_name = basename(__FILE__, '.php');
# These can be set in a calling script:
if (!isset($plot_type)) $plot_type = 'points'; // points, linepoints
if (!isset($error_plot)) $error_plot = FALSE;  // True for error plot

# Radius in pixels of the image map circle around each point:
define('MAP_RADIUS', 20);

$image_map = "";

# $data is the data array, assumed to be using data type above
function store_map($im, $data, $shape, $row, $col, $x, $y)
{
  global $image_map, $data_type;
  if ($shape != 'dot') die("Error expecting dot shapes from plot\n");
  # Get values from data array for tooltip text:
  $xw = $data[$row][1];
  if ($data_type == 'data-data') {
    $yw = $data[$row][$col + 2]; // Skips 1 for label, 1 for X value
    $tooltip = sprintf("(x=%.1f, y=%.1f)", $xw, $yw);
  } elseif ($data_type == 'data-data-error') {
    $yw = $data[$row][3 * $col + 2]; // 3 values per point
    $yerr1 = $data[$row][3 * $col + 3];
    $yerr2 = $data[$row][3 * $col + 4];
    $tooltip = sprintf("(x=%.1f, y=%.1f +%.1f / -%.1f)", $xw, $yw, $yerr1, $yerr2);
  } else {
    $tooltip = ""; // Don't know what to do
  }

  $image_map .= sprintf("  <area shape=\"circle\" coords=\"%d,%d,%d\""
       .  " title=\"$tooltip\""
       .  " alt=\"Region for $tooltip\""
       .  " href=\"example_%d_%d.html\">\n",
       $x, $y, MAP_RADIUS, $row, $col, $row, $col);
}

if ($error_plot) {
    $data_type = 'data-data-error';
    $what = 'error plot';
    # Data for points, linepoints - error plots
    $data = array(
      array('',  0,  0, 1, 1),
      array('',  1,  2, 1, 2),
      array('',  2,  4, 2, 1),
      array('',  3,  5, 1, 3),
      array('',  4,  5, 0, 1),
      array('',  5,  7, 1, 2),
      array('',  6,  6, 3, 1),
      array('',  7,  8, 1, 0),
    );
} else {
    $data_type = 'data-data';
    $what = 'plot';
    # Data for points, linepoints:
    $data = array(
      array('',  0,  0,  1),
      array('',  1,  2,  1),
      array('',  2,  4,  2),
      array('',  3,  6,  3),
      array('',  4,  8,  5),
      array('',  5, 10,  8),
      array('',  6, 12, 12),
      array('',  7, 14, 20),
    );
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetFailureImage(False); // No error images
$plot->SetPrintImage(False); // No automatic output
$plot->SetTitle("'$plot_type' $what with image map");
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType($data_type);
$plot->SetPlotType($plot_type);
$plot->SetXTickIncrement(1);
# Check return value in case callback isn't defined:
if (!$plot->SetCallback('data_points', 'store_map', $data))
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
