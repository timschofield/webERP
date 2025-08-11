<?php
# PHPlot example: Bar chart, embedded image with image map
require_once __DIR__ . '/../../../src/phplot.php';

# This global string accumulates the image map AREA tags.
$image_map = "";

# Data for bar chart:
$data = array(
    array('1950', 40, 95, 20),
    array('1960', 45, 85, 30),
    array('1970', 50, 80, 40),
    array('1980', 48, 77, 50),
    array('1990', 38, 72, 60),
    array('2000', 35, 68, 70),
    array('2010', 30, 67, 80),
);

# Callback for 'data_points': Generate 1 <area> line in the image map:
function store_map($im, $passthru, $shape, $row, $col, $x1, $y1, $x2, $y2)
{
    global $image_map;

    # Title, also tool-tip text:
    $title = "Group $row, Bar $col";
    # Required alt-text:
    $alt = "Region for group $row, bar $col";
    # Link URL, for demonstration only:
    $href = "javascript:alert('($row, $col)')";
    # Convert coordinates to integers:
    $coords = sprintf("%d,%d,%d,%d", $x1, $y1, $x2, $y2);
    # Append the record for this data point shape to the image map string:
    $image_map .= "  <area shape=\"rect\" coords=\"$coords\""
               .  " title=\"$title\" alt=\"$alt\" href=\"$href\">\n";
}

# Create and configure the PHPlot object.
$plot = new Phplot\Phplot\phplot(640, 480);
# Disable error images, since this script produces HTML:
$plot->SetFailureImage(False);
# Disable automatic output of the image by DrawGraph():
$plot->SetPrintImage(False);
# Set up the rest of the plot:
$plot->SetTitle("PHPlot Example: Bar Chart with Image Map");
$plot->SetImageBorderType('plain');
$plot->SetDataValues($data);
$plot->SetDataType('text-data');
$plot->SetPlotType('bars');
$plot->SetXTickPos('none');
# Set the data_points callback which will generate the image map.
$plot->SetCallback('data_points', 'store_map');
$plot->SetPlotAreaWorld(NULL, 0, NULL, 100);
# Produce the graph; this also creates the image map via callback:
$plot->DrawGraph();

# Now output the HTML page, with image map and embedded image:
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
     "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>PHPlot Example: Bar Chart with Image Map</title>
</head>
<body>
<h1>PHPlot Example: Bar Chart with Image Map</h1>
<map name="map1">
<?php echo $image_map; ?>
</map>
<p>This is a plot with image map and tooltip text.</p>
<img src="<?php echo $plot->EncodeImage();?>" alt="Plot Image" usemap="#map1">
</body>
</html>
