<?php

/**
 * Class for creating a plot using a truecolor (24-bit R,G,B) image
 *
 * The PHPlot_truecolor class extends the PHPlot class to use GD truecolor
 * images. Unlike PHPlot which usually creates indexed RGB files limited to
 * 256 total colors, PHPlot_truecolor objects use images which do not have a
 * limit on the number of colors.
 *
 * Note: Without a background image, the PHPlot class creates a palette
 * (indexed) color image, and the PHPlot_truecolor class creates a truecolor
 * image. If a background image is used with the constructor of either class,
 * the type of image produced matches the type of the background image.
 *
 */

namespace Phplot\Phplot;

class phplot_truecolor extends phplot
{
    /**
     * Constructor: Sets up GD truecolor image resource, and initializes plot style controls
     *
     * @param int $width  Image width in pixels
     * @param int $height  Image height in pixels
     * @param string $output_file  Path for output file. Omit, or NULL, or '' to mean no output file
     * @param string $input_file   Path to a file to be used as background. Omit, NULL, or '' for none
     */
    public function __construct($width = 600, $height = 400, $output_file = null, $input_file = null)
    {
        $this->initialize('imagecreatetruecolor', $width, $height, $output_file, $input_file);
    }
}
