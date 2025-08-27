<?php

# PHPlot Test - Image map and embedded image - pie
# This writes an HTML file to the directory named by the environment
# variable RESULTDIR (if defined), and does a simple pattern match to check.
require_once 'phplot.php';

$out_name = basename(__FILE__, '.php');

$plot_type = 'pie';
$data_type = 'text-data-single';

$image_map = "";


/*
Callback handler for generating an image map for a pie chart.

  NOTE: The code in this function is excluded from the license terms for
  PHPlot, the PHPlot Reference Manual, and the PHPlot Test Suite. You may
  freely copy, use, modify, and redistribute the code in this function.
  Attribution is not necessary.  Or, to put it another way, I am placing
  this function in the public domain.

Arguments:
  $im, $passthru : standard arguments for all callbacks.
  $shape : always 'pie'
  $segment : 0 for the first pie segment, 1 for the next, etc.
  $xc, $yc : Center of the pie, in device coordinates
  $wd, $ht : Pie diameters - width (horizontal), height (vertical)
  $start_angle, $end_angle : Segment arc angles, in degrees, offset from
    360. That is, the values are (360-A) for angle A. This is the way
    PHPlot processes the angles for the GD function imagefilledarc().
    Note that sin(360-A) = -sin(A); and cos(360-A) = cos(A).
    Since the Y axis  (sin) is reversed in device, or image, coordinates
    with Y=0 at the top, this works out correctly.

Method used:
    Approximate a pie segment using a polygon. Note the pie is not necessarily
circular, but is an ellipse.
    +  The 1st point is the pie center.
    +  The 2nd point is on the circumference*, at the start angle.
    +  The last point is on the circumference*, at the end angle.
    +  In between the 2nd and last point are N>=0 additional points on the
circumference*, spaced no more than 20 degrees apart. (20 is chosen by
trial and error for a reasonable fit.) So small segments will be approximated
by a single triangle. Larger segments will have more vertices.

    *Note: These points are actually slightly outside the circumference.
This is done by increasing the two radius values by a small amount (2 pixels).
This produces a better fit, for the case where we want to make sure all the
interior is covered, even if some of the exterior is also included.  (Using
the actual radii would result in the area omitting a small part of the pie
interior. For an image map, this would result in dead spaces.)

    The segment subdivisions are made to have about equal angles. This results
in a closer fit. For example, with a maximum sub-segment arc of 20 degrees,
and a segment of 24 degrees, we make two 12 degree sub-segments rather than a
20 degree and a 4 degree.

    Note: Web image map coordinates have 0,0 in upper left, so Y is reversed.

*/
function store_map($im, $passthru, $shape, $segment, $unused,
   $xc, $yc, $wd, $ht, $start_angle, $end_angle)
{
    global $image_map;

    // Choose the largest step_angle <= 20 degrees that divides the segment
    // into equal parts. (20 degrees is chosen as a threshold.)
    // Note start_angle > end_angle due to reversal (360-A) of arguments.
    $arc_angle = $start_angle - $end_angle;
    $n_steps = (int)ceil($arc_angle / 20);
    $step_angle = $arc_angle / $n_steps;

    // Radius along horizontal and vertical, plus a tiny adjustment factor.
    $rx = $wd / 2 + 2;
    $ry = $ht / 2 + 2;
    // Push the initial point into the array: the center of the pie.
    $points = array($xc, $yc);

    // Loop by step_angle from end_angle to start_angle.
    // Don't use "$theta += $step_angle" because of cumulative error.
    // Note $theta and $done_angle are in radians; $step_angle and $end_angle
    // are in degrees.
    $done_angle = deg2rad($start_angle);

    for ($i = 0; ; $i++) {
      // Advance to next step, but not past the end:
      $theta = min($done_angle, deg2rad($end_angle + $i * $step_angle));

      // Generate a point at the current angle:
      $points[] = (int)($xc + $rx * cos($theta));
      $points[] = (int)($yc + $ry * sin($theta));

      // All done after generating a point at done_angle.
      if ($theta >= $done_angle) break;
    }

    // Demonstration data: Title (and tool-tip text), alt text, URL:
    $title = "Segment $segment";
    $alt = "Region for segment $segment";
    $href = "example_$segment.html";
    // Make a comma-separated list of coordinates, for the image map:
    $coords = implode(',', $points);

    // Generate the image map area:
    $image_map .= "  <area shape=\"poly\" coords=\"$coords\""
               .  " title=\"$title\" alt=\"$alt\" href=\"$href\">\n";
}

# Data for pie chart
$data = array(
  array('',  1),
  array('',  2),
  array('',  3),
  array('',  2),
  array('',  5),
);

$plot = new Phplot\Phplot\phplot(800, 600);
// Disable error images, since this script produces HTML:
$plot->SetFailureImage(False);
// Disable automatic output of the image:
$plot->SetPrintImage(False);
// Set up the rest of the plot:
$plot->SetTitle("'$plot_type' plot with image map");
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType($data_type);
$plot->SetPlotType($plot_type);
// Set the data_points callback which will generate the image map:
$plot->SetCallback('data_points', 'store_map');
// Produce the graph; this also creates the image map via callback:
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
if (!preg_match('!<area shape="poly".*<img src="data:image/png!ms', $html)) {
    fwrite(STDERR, "Error: output did not match expected pattern\n");
    exit(1);
}
