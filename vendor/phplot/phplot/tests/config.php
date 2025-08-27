<?php

# config.php : Site configuration for PHPlot tests
# This file contains system-dependent settings for testing PHPlot.
# The settings are:
#    phplot_test_ttfdir  = A directory with TrueType font files.
#    phplot_test_ttfonts[] = An array of font names. The available
#      array keys are:  (sans|serif|mono)(|bold|italic|bolditalic)

# This file has settings for Windows (PHP_OS=WINNT) and 'everything else'.
# It has been tried on Slackware Linux, Xubuntu Linux, and Windows XP.
# It may or may not work on different operating systems, or even those
# same operating systems - depending on the available fonts.

# In order to facilitate automatic results comparisons, the same fonts are
# used on all systems by default. This is the "DejaVu" font family.
# These are not standard on Windows systems, but will be present if you
# have Libreoffice installed. (OpenOffice.org might have the same fonts,
# but with slightly different names - without dashes - so it won't work.)

# If you are running on Windows and don't have DejaVu fonts, you can set
# this variable to use 'standard' Windows fonts instead:
#   $phplot_test_use_windows_standard_fonts = TRUE;
# The tests will work, but it will be harder to compare with those from
# other systems.


# DejaVu font family:

$phplot_test_ttfonts = array(
  'sans'            => 'DejaVuSans.ttf',                 # DejaVu Sans
  'sansbold'        => 'DejaVuSans-Bold.ttf',            # DejaVu Sans Bold
  'sansitalic'      => 'DejaVuSans-Oblique.ttf',         # DejaVu Sans Oblique
  'sansbolditalic'  => 'DejaVuSans-BoldOblique.ttf',     # DejaVu Sans Bold Oblique
  'serif'           => 'DejaVuSerif.ttf',                # DejaVu Serif
  'serifbold'       => 'DejaVuSerif-Bold.ttf',           # DejaVu Serif Bold
  'serifitalic'     => 'DejaVuSerif-Italic.ttf',         # DejaVu Serif Italic
  'serifbolditalic' => 'DejaVuSerif-BoldItalic.ttf',     # DejaVu Serif Bold Italic
  'mono'            => 'DejaVuSansMono.ttf',             # DejaVu Sans Mono
  'monobold'        => 'DejaVuSansMono-Bold.ttf',        # DejaVu Sans Mono Bold
  'monoitalic'      => 'DejaVuSansMono-Oblique.ttf',     # DejaVu Sans Mono Oblique
  'monobolditalic'  => 'DejaVuSansMono-BoldOblique.ttf', # DejaVu Sans Mono Bold Oblique
);

# Operating system specific fixes:
if (PHP_OS == "WINNT") {

    # Explicitly tell it where to find the fonts:
    $phplot_test_ttfdir = $_SERVER['windir'] . '\\fonts\\';

    # You can select to use these standard Windows fonts instead, but
    # then it will be harder to verify results vs Linux output.
    if (!empty($phplot_test_use_windows_standard_fonts)) {

        $phplot_test_ttfonts = array(
          'sans'            => 'arial.ttf',     # Arial
          'sansbold'        => 'arialbd.ttf',   # Arial Bold
          'sansitalic'      => 'ariali.ttf',    # Arial Italic
          'sansbolditalic'  => 'arialbi.ttf',   # Arial Bold Italic
          'serif'           => 'times.ttf',     # Times New Roman
          'serifbold'       => 'timesbd.ttf',   # Times New Roman Bold
          'serifitalic'     => 'timesi.ttf',    # Times New Roman Italic
          'serifbolditalic' => 'timesbi.ttf',   # Times New Roman Bold Italic
          'mono'            => 'cour.ttf',      # Courier New
          'monobold'        => 'courbd.ttf',    # Courier New Bold
          'monoitalic'      => 'couri.ttf',     # Courier New Italic
          'monobolditalic'  => 'courbi.ttf',    # Courier New Bold Italic
        );
    }

} elseif (file_exists('/usr/share/fonts/truetype/dejavu')) {
    # Ubuntu (and others) use this base directory:
    $phplot_test_ttfdir = '/usr/share/fonts/truetype/';

    # In order for GD to find the fonts using only the basename, the
    # subdirectory prefix has to be inserted before all of the names:
    foreach ($phplot_test_ttfonts as $key => $value) {
        $phplot_test_ttfonts[$key] = 'dejavu/' .  $value;
    }
    unset($key);
    unset($value);

} else {
    # Use this as a fallback / default for other X.org systems:
    $phplot_test_ttfdir = '/usr/share/fonts/TTF/';

}
