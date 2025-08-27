<?php

# PHPlot Unit Test - streaming plots
# Notes:
# As the tests under the PHP CLI, there is no way to capture the output
# from header(), which is an important part of the streaming plots feature.
# This test just checks the overall syntax of the output frames.
require_once 'phplot.php';

// Extend PHPlot class to allow access to protected variable(s):
class PHPlot_pv extends Phplot\Phplot\phplot {
    public function GET_stream_boundary() { return $this->stream_boundary; }
    public function GET_stream_frame_header() { return $this->stream_frame_header; }
}

$errors = 0;
$mime_type = 'image/jpeg';

# Data arrays for 2 frames, text-data.
$data1 = array(
      array('', 1, 2, 3),
      array('', 2, 4, 6),
      array('', 3, 6, 9),
    );
$data2 = array(
      array('', 2, 4, 6),
      array('', 3, 6, 9),
      array('', 4, 8, 6),
    );

# Check the overall structure of a frame. Return True if OK, else False.
# Frame needs: boundary, content type and content length, then the image.
# This does nothing with the image (there is another test for that).
function check_frame($frame, $boundary)
{
    global $mime_type;

    return preg_match("\x01--$boundary\\s\x01", $frame)
        && preg_match("\x01Content-Type: $mime_type\\s\x01i", $frame)
        && preg_match("\x01Content-Length: (\\d*)\\s\x01i", $frame);
}

# Initial plot object setup:
$plot = new phplot_pv(640, 480);
$plot->SetDataType('text-data');
$plot->SetPlotType('lines');
$plot->SetFileFormat('jpg');
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetXDataLabelPos('none');
$plot->SetPlotAreaWorld(NULL, 0, NULL, 10);
$plot->SetPrintImage(False);

# StartStream does no output, only headers (which we cannot catch using the
# CLI), so just check the variables it sets:
$plot->StartStream();
$boundary = $plot->GET_stream_boundary();
# Check: Stream frame header includes content-type with expected MIME type
$sfh = $plot->GET_stream_frame_header();
if (!preg_match("\x01Content-Type: $mime_type\x01", $sfh)) {
    fwrite(STDERR, "Error: Did not match Content type in header:\n$sfh\n");
    $errors++;
}

$plot->SetDataValues($data1);
$plot->DrawGraph();
ob_start();
$plot->PrintImageFrame();
$frame1 = ob_get_clean();

$plot->SetDataValues($data2);
$plot->DrawGraph();
ob_start();
$plot->PrintImageFrame();
$frame2 = ob_get_clean();

if (!check_frame($frame1, $boundary) || !check_frame($frame2, $boundary)) {
    # This is kind of useless for debugging, but the frame isn't text so
    # we can't just print it...
    fwrite(STDERR, "Error: Frame(s) did not match expected pattern\n");
    $errors++;
}

ob_start();
$plot->EndStream();
$eof = ob_get_clean();
if (!preg_match("\x01--$boundary--\x01", $eof)) {
    fwrite(STDERR, "Error: Did not match end of stream: '$eof'\n");
    $errors++;
}

echo basename(__FILE__) . ': ';
if ($errors > 0) {
   echo "Failed\n";
   exit(1);
}
echo "Passed\n";
# Testing requires falling off the end if the test passes.
