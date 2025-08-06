<?php

# PHPlot test - Pie Chart Sizing and Label Variations - baseline
require_once 'config.php'; // Configure fonts
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'suffix' => 'baseline',      # Title part 2
  'data_choice' => 1,          # Select data array: 1, 2 or 3
  'n_slices' => 16,            # For data_choice==2, number of slices
  'pie_diam_factor' => NULL,   # If set, oblateness for shaded pie (dflt=0.5)
  'shading' => NULL,           # If set, pie shading SetShading()
  'label_pos' => NULL,         # If set, label position SetLabelScalePosition()
  'pie_label_args' => array(), # If set array of args to SetPieLabelType()
  'ttfonts' => FALSE,          # Use TrueType fonts?
  'font_size' => NULL,         # If set, TrueType or GD font size
  'plot_border' => NULL,       # If set, plot border type SetPlotBorderType()
  'plot_margins' => NULL,      # If set, array SetMarginsPixels(l,r,t,b)
  'precision_y' => NULL,       # Default precision, SetPrecisionY()
  'image_aspect' => 'S',       # Image aspect: S=square, P=portrait, L=landscape
  'data_colors' => NULL,       # If set, data colors
        ), $tp);
require_once 'phplot.php';

$font = $phplot_test_ttfdir . $phplot_test_ttfonts['sans'];

// Recursively expand an array of values and arrays into a readable string.
function show_arrays($arr)
{
  if (!is_array($arr)) return $arr;
  $values = array();
  foreach ($arr as $a) $values[] = is_array($a) ? show_arrays($a) : $a;
  return '(' . implode(', ', $values) . ')';
}

extract($tp);  # Import all parameters as local variables

/*
  Notes:
    data_choice=1 is a fixed text-data-single array with meaningful labels.
    data_choice=2 is variable (n_slices) text-data-single generated array.
    data_choice=3 uses text-data to check for unsupported label usage.

     Examples of $tp['pie_label_args']:
       array('label');                   Use label from text-data-single array
       array('percent', 'data', 1, '', '%');   Same as default: 88.9%
       array('percent');                       Percent, using Y data precision
       array('value', 'printf', '0x%02x');     Slice value in hex?
       array('percent', 'printf', '%2.1f%%');  Percent as ##.#%
       array('index', 'custom', 'mylabs');     Index as arg to custom function
       array(array('value', 'percent'), 'custom', 'mylabs'); Multi-part label
*/

if ($data_choice == 1) {
    $data = array(
        array('Gold',        20),
        array('Silver',      13),
        array('Copper',       7),
        array('Tin',         18),
        array('Bronze',      10),
        array('Iron',         4),
        array('Platinum',     2),
        array('Nickel',       5),
    );
    $n_slices = count($data);
    $data_type = 'text-data-single';
} elseif ($data_choice == 2) {
    $data = array();
    for ($i = 0; $i < $n_slices; $i++) $data[] = array("==$i==", 1);
    $data_type = 'text-data-single';
} elseif ($data_choice == 3) {
    // Note: Each column is a slice, and labels are not valid here.
    $data = array(
        array('Error',     0, 1, 1, 2, 3, 5,  8, 13, 21),
        array('Error',     1, 1, 2, 3, 5, 8, 13, 21, 34),
    );
    $n_slices = count($data);
    $data_type = 'text-data';
} else {
    fwrite(STDERR, "Error: invalid data_choice=$data_choice\n");
    exit(1);
}

# Build a title. 1st line is fixed + parameter suffix; 2nd etc lines are auto.
$t = "Pie size/label test: $suffix\n";
$v = array();
if (isset($pie_diam_factor)) $v[] = "DiamFactor=$pie_diam_factor";
if (isset($shading)) $v[] = "Shading=$shading";
if (isset($label_pos)) $v[] = "LabelPos=$label_pos";
if (!empty($v)) $t .= implode(', ', $v) . "\n";
if (!empty($pie_label_args))
    $t .= "LabelType=" . show_arrays($pie_label_args) .  "\n";
if (!empty($plot_margins))
  $t .= "Margins=" . show_arrays($plot_margins) .  "\n";

$title = trim($t);

# Determine image aspect: 800x800 square, 800x400 landscape, or 400x800 portrait
$width  = ($image_aspect == 'S' || $image_aspect == 'L') ? 800 : 400;
$height = ($image_aspect == 'S' || $image_aspect == 'P') ? 800 : 400;

$plot = new Phplot\Phplot\phplot($width, $height);
$plot->SetPlotType('pie');
$plot->SetDataType($data_type);
$plot->SetDataValues($data);

if (!empty($plot_margins))
    call_user_func_array(array($plot, 'SetMarginsPixels'), $plot_margins);

# Font setup
if (!empty($ttfonts)) {
  if (isset($font_size))
      $plot->SetFontTTF('generic', $font, $font_size);
  else
      $plot->SetFontTTF('generic', $font);
} elseif (isset($font_size)) {
      $plot->SetFontGD('generic', $font_size);
}

$plot->SetTitle($title);

if (!empty($plot_border)) $plot->SetPlotBorderType($plot_border);
if (isset($pie_diam_factor)) $plot->pie_diam_factor = $pie_diam_factor;
if (isset($shading)) $plot->SetShading($shading);
if (isset($label_pos)) $plot->SetLabelScalePosition($label_pos);

# For backward compatibility testing:
if (isset($precision_y)) $plot->SetPrecisionY($precision_y);

if (!empty($pie_label_args))
    call_user_func_array(array($plot, 'SetPieLabelType'), $pie_label_args);
// For test using colors as labels:
if (!empty($data_colors)) $plot->SetDataColors($data_colors);

$plot->DrawGraph();
