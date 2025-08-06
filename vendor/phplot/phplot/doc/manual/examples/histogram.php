<?php
# PHPlot Example - Histogram of a Photograph
# Display a photo image with its value histogram overlaid
# Note: This requires PHPlot-5.1.1 or higher for Truecolor support.
# Unlike the other examples, and contrary to the usual PHPlot recommendation,
# this script creates JPEG not PNG, because most of the image is the original
# photograph and PNG results in an overlarge file.
require_once __DIR__ . '/../../../src/phplot.php';
require_once __DIR__ . '/../../../src/phplot_truecolor.php';

# Tunable parameters:
$param = array(
    'plot_image_width' => 640,      # Width of final image
    'plot_image_height' => 480,     # Height of final image
    'histogram_color' => 'magenta', # Color to use for histogram lines
    'histogram_alpha' => 50,        # Histogram transparency (0=opaque, 127=clear)
    'draw_border' => True,          # If true, put a border around the histogram
    'border_color' => 'red',        # Border color, if draw_border is true
    'hx' => 0.6,                    # Upper left X relative position of histogram
    'hy' => 0.0,                    # Upper left Y relative position of histogram
    'h_width' => 0.4,               # Relative width of histogram
    'h_height' => 0.35,             # Relative height of histogram
);

/*
  Make a histogram from an image file, which can be palette or truecolor.
  Returns an array $histogram[i] where i is from 0 to 255. Each histogram[i]
  is the number of pixels in the image with grayscale value i.
  (Grayscale is computed using the NTSC formula, but with integers.)
*/
function get_histogram($image_file)
{
    list($width, $height, $imtype) = getimagesize($image_file);
    if (!empty($width)) {
        switch ($imtype) {
            case IMAGETYPE_JPEG:
                $im = imagecreatefromjpeg($image_file);
                break;
            case IMAGETYPE_PNG:
                $im = imagecreatefrompng($image_file);
                break;
            case IMAGETYPE_GIF:
                $im = imagecreatefromgif($image_file);
                break;
        }
    }
    if (empty($width) || empty($im)) {
        fwrite(STDERR, "Error invalid image file name: $image_file\n");
        return NULL;
    }

    # Initialize the histogram counters:
    $histogram = array_fill(0, 256, 0);

    # Process every pixel. Get the color components and compute the gray value.
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $pix = imagecolorsforindex($im, imagecolorat($im, $x, $y));
            $value = (int)((30 * $pix['red'] + 59 * $pix['green']
                          + 11 * $pix['blue']) / 100);
            $histogram[$value]++;
        }
    }
    return $histogram;
}

/*
  Make a 'plot', containing a scaled-down version of an image with
  a histogram overlay.
*/
function plot_histogram($image_filename, $param)
{
    extract($param);
    $histo = get_histogram($image_filename);
    if (empty($histo)) return;
    for ($i = 0; $i < 256; $i++) $data[$i] = array('', $histo[$i]);
    $p = new Phplot\Phplot\phplot_truecolor($plot_image_width, $plot_image_height);
    $p->SetFileFormat('jpg');
    $p->SetBgImage($image_filename, 'scale');
    $p->SetDataType('text-data');
    $p->SetDrawXAxis(False);
    $p->SetDrawYAxis(False);
    $p->SetDataValues($data);
    $p->SetXDataLabelPos('none');
    $p->SetXTickLabelPos('none');
    $p->SetYTickLabelPos('none');
    $p->SetXTickPos('none');
    $p->SetYTickPos('none');
    $p->SetDrawYGrid(False);
    $p->SetDataColors($histogram_color, NULL, $histogram_alpha);
    $p->SetPlotType('thinbarline');
    if ($draw_border) {
        $p->SetGridColor($border_color);
        $p->SetPlotBorderType('full');
    } else {
        $p->SetPlotBorderType('none');
    }
    # Compute the position of the histogram plot within the image.
    $hx0 = (int)($hx * $plot_image_width);
    $hy0 = (int)($hy * $plot_image_height);
    $hx1 = (int)($h_width * $plot_image_width) + $hx0;
    $hy1 = (int)($h_height * $plot_image_height) + $hy0;
    $p->SetPlotAreaPixels($hx0, $hy0, $hx1, $hy1);
    $p->DrawGraph();
}

/* Demo main. */
plot_histogram(__DIR__ . '/geese.jpg', $param);
