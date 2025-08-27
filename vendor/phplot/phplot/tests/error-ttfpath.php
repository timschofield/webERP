<?php

# Error test - bad TTF font or path.
# This is a parameterized test with 1 parameter: 'case'.
# Test cases:
#    $case=1    Calling SetUseTTF(True) [with no path setup]
#    $case=2    Calling SetDefaultTTFont(bad full path)
#    $case=3    Calling SetDefaultTTFont(bad filename)
#    $case=4    Calling SetTTFPath(bad dir)
#    $case=5    Good font path in SetTTFPath, bad filename in SetFont
# The default is case=0 for no error - valid font setup.
if (empty($case)) $case = 0;

require_once 'phplot.php';
# This has some valid font info:
require_once 'config.php';

$data = array(
  array('A',  1, 4),
  array('B',  2, 6),
  array('C',  3, 0),
);

$p = new Phplot\Phplot\phplot(400,300);
$p->SetTitle('Error test');
$p->SetDataType('data-data');
$p->SetDataValues($data);

$p->SetXDataLabelPos('none');

# Errors:
switch ($case) {
    case 1:
        # Test: Turn on TTF without path setup
        $p->SetUseTTF(True);
        break;
    case 2:
        # Test: Bad default font with path
        $p->SetDefaultTTFont("/no/such/path/to/font.ttf");
        break;
    case 3:
        # Test: Bad default font without path
        $p->SetDefaultTTFont("nosuchfont.ttf");
        break;
    case 4:
        # Test: Bad font path
        $p->SetTTFPath("/no/such/font/dir");
        break;
    case 5:
        # Test: Good font path and default, bad font selected
        $p->SetTTFPath($phplot_test_ttfdir);
        $p->SetDefaultTTFont($phplot_test_ttfonts['sans']);
        $p->SetFont('title', 'nosuchfont.ttf', 14);
        break;
    default:
        # Test: Valid font path and font
        $p->SetTTFPath($phplot_test_ttfdir);
        $p->SetDefaultTTFont($phplot_test_ttfonts['sans']);
        $p->SetFont('title', $phplot_test_ttfonts['serif'], 14);
}
$p->SetPlotType('lines');
$p->DrawGraph();
