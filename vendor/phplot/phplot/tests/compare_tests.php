<?php

# compare_tests : Check test output from PHPlot testing

# Default image viewer command. This needs to accept 1 or 2 image file
# names; with 2 names it should allow back/forth toggling.
# The VIEWER environment variable overrides this.
define('DEFAULT_VIEWER', 'qiv -p');

# Default text file compare command.
# The DIFF environment variable overrides this.
define('DEFAULT_DIFF', 'diff -u');

# Image file converters and flag indicating that compare_tests should
# recheck images that differ after stripping meta-data:
define('DO_IMAGE_RECHECK', TRUE);
# If DO_IMAGE_RECHECK is true, these must be defined to command lines
# that convert a file with the specified extension on standard input
# to a 'plain' form like PNM on standard output:
$converters = array(
  'gif' => 'giftopnm',
  'png' => 'pngtopnm',
  'jpg' => 'jpegtopnm',
);

# Display usage and exit:
function usage()
{
    fprintf(STDERR, '
Usage: compare_tests [options] result_dir reference_dir
  result_dir is the directory where the test results can be found.
  reference_dir is the directory with saved reference output.
  Options:
     -a   View all file pairs
     -d   View all differing pairs
     -n   View all new pairs
     -q   Quiet mode: do not list matching tests
     -m P Check only tests matching wildcard match pattern P
   Also -d -n, or -dn or -nd views new or differing pairs.
   The VIEWER environment variable defines the command line for an image
file viewer. The default is: %s
   The DIFF environment variable defines the command line for text
file comparison. The default is: %s
',  DEFAULT_VIEWER, DEFAULT_DIFF);
    exit(1);

}

# After they have been found to differ in a byte-for-byte check, re-check
# them to see if they are image files that match when converted to PNM
# (which strips off any metadata).
# This is used by compare_files if DO_IMAGE_RECHECK is defined above.
# Returns TRUE if the files are image files of the same type and match after
# conversion, else FALSE.
function recompare_files($file1, $file2)
{
  global $converters;

  $ext1 = strtolower(pathinfo($file1, PATHINFO_EXTENSION));
  $ext2 = strtolower(pathinfo($file2, PATHINFO_EXTENSION));

  // Same file extensions? If not, then no match.
  if ($ext1 != $ext2) return FALSE;

  // Convertable image file? If not, then no match.
  if (!isset($converters[$ext1])) return FALSE;

  // Build command lines to convert them:
  $cmd1 = $converters[$ext1] . ' 2> /dev/null < ' . $file1;
  $cmd2 = $converters[$ext2] . ' 2> /dev/null < ' . $file2;

  // Convert, compare, and return the result:
  return (`$cmd1` === `$cmd2`);
}

# Compare two files, byte for byte, return True if match else False.
# The files (usually images) aren't too big, so just read them into
# memory and compare.
# In some cases, image files can differ only by meta-data (e.g. PNG files
# from different versions of libgd or libpng). To avoid many false difference
# reports, there is an option for a second-stage compare (see recompare_files).
function compare_files($file1, $file2)
{
  if (($s1 = file_get_contents($file1)) === False
        || ($s2 = file_get_contents($file2)) === False) return FALSE;
  if ($s1 === $s2) return TRUE;

  // Files differ, but maybe check the images without meta-data:
  if (DO_IMAGE_RECHECK) return recompare_files($file1, $file2);

  // The files differ:
  return FALSE;
}

# Return true if the given filename seems to be an image file:
function is_image_file($filename)
{
    return preg_match('/\\.(png|gif|jpg)$/i', $filename);
}

# View a single image or text file. This is used when there is a new test
# result (no matching reference file), or when the results match but the
# 'view all' option was used.
function view_file($filename)
{
    global $viewer;

    if (is_image_file($filename)) {
        echo "View: $filename\n";
        system("$viewer $filename");
    } else {
        # Text file
        echo "===== Text file: $filename\n";
        readfile($filename);
        echo "=====\n";
    }
}

# View a pair of image or text files. This is used when the test results
# do not match: the reference file differs from the result file.
function view_files($filename, $refname)
{
    global $viewer, $diff;

    if (is_image_file($filename)) {
        echo "View: $refname $filename\n";
        system("$viewer $refname $filename");
    } else {
        # Text file
        echo "===== Compare: $refname $filename\n";
        system("$diff $refname $filename");
        echo "=====\n";
    }
}

# Return a sorted list of image files and text output files in the $dir:
# This also uses the global $match_pattern as a filter, if not empty.
function get_file_list($dir)
{
    global $match_pattern;

    $list = array();
    $d = opendir($dir);
    if (!$d) die("Failed to open directory: $dir\n");
    while (($filename = readdir($d)) !== False) {
        # Look only at image and .out files:
        if (preg_match('/\\.(png|gif|jpg|out)$/i', $filename)) {
            if (empty($match_pattern) || fnmatch($match_pattern, $filename)) {
                $list[] = $filename;
            }
        }
    }
    sort($list);
    return $list;
}

# Reporting helper, used to show list of files
function show_result_details($what, $list)
{
    if (count($list) == 0) return;
    echo "\nFiles that $what:\n    "
          . wordwrap(implode(' ', $list), 72, "\n    ")
          . "\n";
}



# MAIN:

# Get the command lines to use for viewer and text file compare:
if (($viewer = getenv('VIEWER')) === False)
    $viewer = DEFAULT_VIEWER;
if (($diff = getenv('DIFF')) === False)
    $diff = DEFAULT_DIFF;

# Default options:
$view_all = False; # -a option
$view_new = False; # -n option
$view_dif = False; # -d option
$be_quiet = FALSE; # -q option
$match_pattern = '';  # -m option
# Parse command arguments:
$argc = $_SERVER['argc'];
$argv = $_SERVER['argv'];
if ($argc <= 1) usage();
for ($narg = 1; $narg < $argc; $narg++) {
    $arg = $argv[$narg];
    if ($arg[0] != '-') break;
    if ($arg == '--') {
        $narg++;
        break;
    }
    $nc = strlen($arg);
    for ($ac = 1; $ac < $nc; $ac++) {
        switch ($arg[$ac]) {
            case 'n': $view_new = True; break;
            case 'd': $view_dif = True; break;
            case 'a': $view_all = True; break;
            case 'q': $be_quiet = True; break;
            case 'm':
                if (++$narg >= $argc) usage(); # Missing arg value
                $match_pattern = $argv[$narg];
                break;
            default: usage();
        }
    }
}
# Non-flag arguments are now $argv[$narg : $argc-1]
if ($argc - $narg != 2) usage();
$outdir = $argv[$narg++];
$refdir = $argv[$narg];

$n_same = 0;
$n_new  = 0;
$n_diff = 0;
$s_diff = array();
$s_new = array();
$n_total = 0;   # Total result files, before -m filtering.

# Get a sorted list of image files and text output files from the outdir:
$o_list = get_file_list($outdir);
$n_check = count($o_list);
if ($n_check == 0) {
    fwrite(STDERR, "Error: No matching files to check\n");
    exit(1);
}

# Process each result file:
foreach ($o_list as $filename) {
    $srcname = $outdir . DIRECTORY_SEPARATOR . $filename;
    $refname = $refdir . DIRECTORY_SEPARATOR . $filename;
    if (!file_exists($refname)) {
        $n_new++;
        $s_new[] = $filename;
        echo "+ $filename: No reference to compare with (new test)\n";
        if ($view_all || $view_new)
            view_file($srcname);

    } elseif (compare_files($srcname, $refname)) {
        $n_same++;
        if (!$be_quiet)
            echo "  $filename: Matches\n";
        if ($view_all)
            view_file($srcname);

    } else {
        $n_diff++;
        $s_diff[] = $filename;
        echo "! $filename: Output differs\n";
        if ($view_all || $view_dif)
            view_files($srcname, $refname);
    }
}

# Check for any deleted or missing results files:
$s_gone = array_diff(get_file_list($refdir), $o_list);
$n_gone = count($s_gone);

# Report the results:
echo "\nResults: Same: $n_same,  Differ: $n_diff,  New: $n_new,  Removed: $n_gone\n";
show_result_details('differ',  $s_diff);
show_result_details('are new',     $s_new);
show_result_details('were removed', $s_gone);
