<?php

# Testing phplot - Version constants (PHPlot>=5.4.0)
# This is a unit test. No image is produced.
require_once 'phplot.php';
$failure = '';

# Check: Constant is defined.
if (!defined('Phplot\Phplot\phplot::version')) {
    $failure .= "phplot does not define a version constant\n";

# Check: Format #.#.#...
} elseif (!preg_match('/^(\\d+\\.\\d+\\.\\d+)/', Phplot\Phplot\phplot::version, $v)) {
    $failure .= "phplot version constant is not in the expected format\n";
} else {
    $version_from_constant = $v[1];

    # These checks are only if the version constant looks right.

    # Read the top ~25 lines of the source, looking for the version comment.
    # This could be a comment in the source:  "PHPLOT Version x.y.z"
    # Or a phpdoc docblock-type tag:          "@version x.y.z"
    #   Whichever is found first is used. Source code should contain only
    #   one of these.
    # The version comment or tag, if found,  is checked against the value of
    # the version constant.
    #
    # Open the source file, using the INCLUDE path.
    $f = fopen('phplot.php', 'r', TRUE);
    if (!$f) exit(1); // Error already reported by fopen. Fatal.
    $found = False;
    for ($i = 0; $i < 25; $i++) {
        $s = fgets($f);
        if ($s === False) break;
        if (preg_match('/(PHPLOT Version|@version) +(\\d+\\.\\d+\\.\\d+)/i', $s, $m)) {
            $version_from_comment = $m[2];
            $found = True;
            break;
        }
    }
    fclose($f);
    if (!$found) {
        $failure .= "Unable to read version comment from source file\n";
    } elseif ($version_from_constant != $version_from_comment) {
        $failure .= "File comment version $version_from_comment"
                  . " != version constant $version_from_constant\n";
    }
}

# This was added in 6.0.0, so don't fail the test if it is missing.
if (defined('Phplot\Phplot\phplot::version_id')) {
    $version_from_id = ((int)(Phplot\Phplot\phplot::version_id / 10000)) . '.' .
           ((int)(Phplot\Phplot\phplot::version_id / 100) % 100) . '.' .
           (int)(Phplot\Phplot\phplot::version_id % 100);

    if ($version_from_id != $version_from_constant) {
        $failure .= "Mismatch between version: " . Phplot\Phplot\phplot::version . "\n"
                  . "          and version_id: " . Phplot\Phplot\phplot::version_id . "\n";
    }
} else {
    echo "Note: PHPlot version_id constant is not defined\n";
}

if (!empty($failure)) {
    fwrite(STDERR, "Version test failed:\n$failure\n");
    exit(1); // Test framework says: exit on error, fall off if pass.
}

echo "Pass: PHPlot version = " . Phplot\Phplot\phplot::version . "\n";
