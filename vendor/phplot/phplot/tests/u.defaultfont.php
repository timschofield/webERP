<?php

# PHPlot unit test - default TT font.
# This test requires a specific font (see TEST_FONT) be present in the images/
# directory. The listed font is redistributable under the Open Fonts License.
require_once 'phplot.php';
require_once 'config.php'; // TTF setup
require_once 'usupport.php'; // Unit test support
require_once 'testclass.php'; // Superclass with access to protected members

define('TEST_FONT', 'FreeUniversal-Regular.ttf');

# Accumulates error text:
$error = '';
// $test_debug = True; // Uncomment for more verbosity.

# 1. Get default, no setup.
$p = new phplot_test2();
$font = $p->test_GetDefaultTTFont();
expect_match('[a-z]\\.ttf$', $font,
             'Case 1. GetDefaultTTFont', $error);
if (!empty($test_debug)) echo "1. font=$font\n";

# 2. Set default font without path.
$p = new phplot_test2();
$p->SetDefaultTTFont($phplot_test_ttfonts['serif']);
$font = $p->test_GetDefaultTTFont();
expect_match('[a-z]\\.ttf$', $font,
             'Case 2. SetDefaultTTFont to serif', $error);
if (!empty($test_debug)) echo "2. font=$font\n";

# 3. Set path only without font.
$p = new phplot_test2();
$p->SetTTFPath($phplot_test_ttfdir);
$font = $p->test_GetDefaultTTFont();
expect_match('[a-z]\\.ttf$', $font,
             'Case 3. SetTTFPath to dir, then get default', $error);
if (!empty($test_debug)) echo "3. font=$font\n";

# 4. Set font path to local variant, then specify font name with extension.
$p = new phplot_test2();
$p->SetTTFPath(getcwd() . DIRECTORY_SEPARATOR . 'images');
$p->SetDefaultTTFont(TEST_FONT);
$font = $p->test_GetDefaultTTFont();
expect_match('[a-z]\\.ttf$', $font,
             'Case 4. Set local font dir, then font name', $error);
if (!empty($test_debug)) echo "4. font=$font\n";

# 5. SetFontTTF with path and extension
$font_with_path_and_ext = $phplot_test_ttfdir . $phplot_test_ttfonts['serif'];
$p = new phplot_test2();
$p->SetFontTTF('title', $font_with_path_and_ext, 12);
$font = $p->fonts['title']['font'];
expect_match('[a-z]\\.ttf$', $font,
             'Case 5. Set title font with path and extension', $error);
if (!empty($test_debug)) echo "5. font=$font\n";

# 6. SetFontTTF with path and no extension - expected to fail.
$pp = pathinfo($phplot_test_ttfonts['serif']);
$font_with_path_no_ext = $phplot_test_ttfdir
                         . basename($phplot_test_ttfonts['serif'], '.ttf');
// Note this uses the PHPlot_test2() class which suppresses the error image
// and allows the messages to be completely off.
$p = new phplot_test2();
$p->hide_error = True; // Disables error message from PHPlot_test2
if ($p->SetFontTTF('title', $font_with_path_no_ext, 12)) {
  $font = $p->fonts['title']['font'];
} else {
  $font = 'EXPECTED_NO_FONT';
}
expect_equal('EXPECTED_NO_FONT', $font,
             'Case 6. Set title font with path and no extension', $error);
if (!empty($test_debug)) echo "6. result=$font\n";

if (!empty($error)) {
    fwrite(STDERR, $error);
    exit(1);
}
echo "Default font unit tests PASS\n";
