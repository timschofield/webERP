# PHPlot Test Suite

## OVERVIEW:

This README describes the test suite for PHPlot.

Because PHPlot produces (only) visual graphical output, testing cannot be
fully automated. At some point, a person needs to look at the output to
determine if it is correct. Nevertheless, the test suite tries to provide
some level of fully-automatic testing which can be used for pre-release
testing and regression testing. ('Regression testing' is sometimes defined
as testing for re-appearance of a bug, but it can also mean testing to see
if something that used to work is now broken.)

The PHPlot Test Suite consists of test scripts, driver and support files.

There are generally three types of test scripts, although it is not
required that a test exactly match one of these types:

* Graphic tests are expected to run successfully and output a plot image
file, usually PNG (GIF and JPEG work too). Most tests are graphic tests.

* Unit tests are expected to test some internal function, self-verify, and
not produce an image file.

* Error tests are expected to check for some error condition, and probably
fail with an error message.

The test driver can run through all the tests with a single command.  Each
test script is run using the PHP interpreter, and the status of the test is
checked. The test driver will save any generated output images, and report
the test results as counts of the tests that passed, failed, or were skipped.

To run these tests, you need PHPlot and a PHP CLI (command line
interpreter).  To view the test output images, you need an image viewer
program.  The 'make' program and a shell are needed to create a
packaged release of the test suite.

For copyright and license information on the PHPlot Test Suite, see the
included file LICENSES.


### CONTENTS:

* README.md          This file
* LICENSES           Copyright and license information

* compare_tests.php  Script to compare tests results from two runs
* config.php         Configuration script. Sets up font names.
* drawtext.php       Used by drawtext* tests.
* esupport.php       Test support library for error tests.
* images/*           Static image files and a font, used in tests.
* makedata.php       Test support function - build a data array
* run_test.php       Test driver script.
* testclass.php      Test support script.
* tests.ini          Test validation data, list of test names.
* transparent.html   Wrapper page to display the transparent tests output.
* usupport.php       Test support library for unit tests.

* All other *.php    Test scripts


### SETUP:

Several environment variables can be used by the test suite:

* PHP: The path to the PHP interpreter (PHP CLI) for testing.
  This is required. The test driver will not run if this is not defined.
* RESULTDIR: Directory path for saving results. Optional; defaults to
  "results" in the current directory.
  This directory must not already exist.
* VIEWER: The path to an image viewer program. Used by compare_tests.php.
  The program needs to accept one or two image filenames on the command
  line.  (Two are used for comparing old and new images.)
  This is used by the compare_tests.php script.
* DIFF: The path to a text diffing program. Used by compare_tests.php

The PHP environment variable is used because it may be desirable to run the
test driver with one version of PHP (perhaps the latest version), but run
the tests using a different version of PHP (perhaps an older version).

There is a configuration file called "config.php" which is used to
configure some parts of the tests. Currently, this is only used so the
scripts can find some TrueType font files. Edit config.php and follow the
comments if you need to change the font directory or font file names. The
supplied script has values that should work on many systems. It uses the
"DejaVu" font family, adjusting the names for some known system differences.
On Windows, your system may have this font if you have OpenOffice.org or
LibreOffice installed.

The file tests.ini contains a list of all tests, and validation data. This
is used by the test driver to determine which tests to run and what to
expect in their output. More information can be found below.


## RUNNING TESTS:

To run all the tests use the 'run_test.php' script with the "-all" option.
Remember to set the PHP environment variable first (see above).
On Linux, you might use a command like this:

     export PHP=/usr/bin/php
     php run_test.php -all

On Windows, you might use something like this:

     SET PHP=c:\php\php.exe
     %PHP% run_test.php -all

This will run through all the tests. Output files will be placed in a
directory named by the RESULTDIR environment variable, if defined, else
a directory named 'results' in the current directory. The results directory
must not already exist, or testing will not take place. At the end of the
testing, a summary report will be displayed. All output written to the
screen when testing will also be written to a file "test.log" in the
results directory.

To run selected tests, you can use: .../php run_test.php script_name...


## ANALYZING RESULTS:

After testing, you can view the image output files if you want in the
'results' directory. That directory also contains tests.log, a copy of what
was displayed on the screen during testing, and any error output or
standard output text captured during testing.

If you save the image files from one run (*.png *.gif *.jpg) into a
different subdirectory, you can rerun tests and compare the files with:

    /path/to/php compare_tests.php [options] result_directory reference_directory

The following options are available:
   -a   View all image files pairwise (result and reference)
   -d   View differing image files pairwise
   -n   View new image files (those that don't exist in the reference)
With no options, compare_tests.php only does a binary file compare of the
image files and reports files which differ. (Note that the tests have been
designed to minimize useless differences, such as date/time or random
values. Still, there are often slight image differences between releases of
PHP or the font system, and even with the same font, Windows and *nix
systems will produce slightly different text.) With one or more options, it
will also view the selected images.

For example:

    php compare_tests.php -dn results results.saved

The compare_tests.php script expects the VIEWER environment variable to
name a viewing program or commands. By default, if VIEWER is not defined,
the command used is: "qiv -p"

A good viewing program will let you compare images by pressing a key to
flip between the 'before' and 'after' images. This works in qiv, for
example - pressing the spacebar toggles between the two images. Note that
the compare_tests.php script passes one pair of images - before and after -
to the viewer for each differing result, so you can easily compare them.
You exit the viewer to move to the next different set.  (The script
compares before and after images as binary files.  Unfortunately, image
files can differ but not have any easily apparent visual differences.
Toggling back and forth between the two images makes it easier to find the
changes.)


## DESIGNING TESTS:

As stated above, the tests generally fall into 3 types: graphics tests
(produce a plot), unit tests (check an internal function and self-verify),
and error tests (fail with an expected message).

In fact, the test driver validates each test using information in the test
configuration file tests.ini.  The validation is limited to checking the
scripts exit status and output streams.  The information in tests.ini
describes what each test is expected to do, so the distinction between
graphics, unit, and error tests is only a convention.

To write a new test, write a PHP script that conforms to the rules below,
and add a section to tests.ini describing the expected behavior of the
test. You can then run the test with the test driver (run_test.php).

Requirements for writing test scripts:

1) Each script needs to be self-contained, although it can include other
   scripts from the same directory. It can't use external databases or
   other external data sources, or access Internet sites.

2) The script cannot use or process command line arguments.

3) The script must have "require_once 'phplot.php';" without any path.

4) If the script completes successfully, it must not use exit(). It needs to
   just continue to the end of the script file, or use return from a global
   context (outside any function). Any test using exit(), even exit(0), will
   assume to not have passed, because the test driver will detect that it did
   not run through to the end.

5) The script should produce the same output each time it runs. If the script
   produces an image, the image should be identical for each run. (This is
   necessary for quicker checking.)

   a) If you use a random function for data, you must seed the generator with
      a constant. Use mt_srand() to seed, and mt_rand() to make random
      numbers. Don't use srand() and rand() because they produce different
      results on different platforms, even with the same seed.

   b) Don't make the result dependent on the current date or time.

   c) Don't include full file paths in any output messages.

6) If the script produces a plot, the plot should have a plot title. A title
   explaining what the plot should look like is best.  (For example, "Line
   graph with 3 lines" is better than "Peak Stock Price").  Somebody will
   have to look at a lot of these and decide if they look "right", and a
   title helps.

7) If the script needs to write files, temporary or otherwise, it must place
   them in the directory named by the RESULTDIR environment variable.
   For example, to make a file called 'testfile.dat', use something like:
    $f = fopen(getenv("RESULTDIR") . DIRECTORY_SEPARATOR . "testfile.dat", "w");
   Note: This filename is a bad example. To avoid filename collisions, each
   test script should begin filenames with its own name.

8) If a script gets an error, it should use exit(1) after writing an error
   message to stderr. Note that this doesn't necessarily mean the test failed,
   as the validation data determines the pass/fail status.

9) A script can use exit(2) to indicate that it should be skipped. This
   is appropriate if the script depends on a class method which is not
   present. (This is used to reduce the number of errors when testing older
   versions of the software.)


Some tests, generally "Graphics Tests", are expected to produce an image.
The image file must be produced on standard output, with no other output
written to the standard output stream (STDOUT).
The corresponding tests.ini directive is: create_image.

Most tests are expected to write nothing at all to the standard error
stream (STDERR) unless they fail.
The corresponding tests.ini directive is: stderr_empty


Adding the test to the tests.ini configuration file:

Add a section to the file for the test. If the script is named
"my-testcase1.php", the entry starts like this:

       [my-testcase1]

Where the "section name" is the basename of the test script, without
extension.

The test driver will apply its defaults to the configuration for the test.
You do not need to specify any directives if the following are all true:
   + Your test makes an image file on standard output
   + Your test writes nothing at all to error output (unless it fails)
   + Your test falls of the end, without an exit().
This is generally true for all "Graphic Tests".

If you are writing a unit test, or an error test, you need to provide
additional information in tests.ini so the driver can validate your test.
For a unit test that includes its own validation code (that is, it checks
results and exits with a non-zero status if it fails), the only directive
you need is:

     [my-unit-testcase]
     create_image=False

If you are writing an error test, you need to tell the driver that the test
is supposed to exit with an error status, and you should provide a match
pattern for the error text written to standard error. For example:

     [my-error-testcase]
     create_image=False       (see note)
     exit_error=True
     stderr_match="can't find .* font"

Note: For many error conditions, PHPlot creates an error message image. In
that case, create_image should be true. If you use create_image=False and
the tests creates an image, even an error message image, the test will be
considered to have failed.

For more information, see the section below on test configuration.


Parameterized test:

Some of the scripts are parameterized. The 'core' script uses an optional
array $tp to control the graph. 'Secondary' scripts just set the array,
then include the core script. For example, see stock.php (core) and
stock?.php (secondaries). This is used to test small changes, for example
setting a line width parameter. In some cases, individual variables are
used instead of an array. This should all be documented at the top of the
'core' script.


Access to protected methods:

Some tests, especially unit tests, call protected methods of PHPlot. This is
discouraged, and these tests are likely to break as PHPlot internals change,
but sometimes this is the best way to test PHPlot.  At one point, certain
methods of PHPlot were left as "public" specifically for the test scripts.
However, this now avoided by using an extended class that can provide access
to protected methods. The class is in "testclass.php" and defines a few
"test_xxx()" methods that call the PHPlot protected "xxx()" method.


Skipping tests:

A script uses exit(2) to indicate that it should be skipped. The tests.ini
data for the test is completely ignored for skipped tests. Skipping is
appropriate when a required method is missing, for example:
  if (!method_exists('Classname', 'methodname')) { ... }

It is also possible to use a version constant to check for a particular
version of the software, or newer, before continuing. Note that the integer
constant PHPlot::version_id was only added in PHPlot-6.0.0, and the string
constant PHPlot::version is not useful for comparison. A script can
therefore only check for versions 6.0.0 or later.

Before using exit(2), the script should write a short (1-line) message to
stdout explaining why it is being skipped.


## THE tests.ini CONFIGURATION FILE:

The tests.ini file contains configuration data for each test script in the
PHPlot test suite. The file is parsed by the PHP parse_ini_file() function
so it must conform to the PHP "ini" file syntax requirements.

For each test file (/path/file.php), the test name is the basename without
extension (file). The test name is also the section name in tests.ini.
Within each section, there are a number of configuration directives that
can be specified. If a directive is not given, the default is applied. The
defaults are defined for the most common case, a "graphic test" which
produces an image file on standard output, nothing on standard error, and
ends by falling off the end of the script. For this type of test, no
configuration directives are needed.

The list of all section names in the tests.ini file defines the set of
tests that run when using the "-all" option to the test driver.
(The test scripts must be found in the current directory.)

Here are the available configuration directives:

create_image:
  ; Does the test create an image file on stdout?
  ; True: Test must create an image file.
  ; False: Test must NOT create an image file.
  ; Default: True, but forced to False if stdout_match is provided.
  create_image=True

exit_ok:
  ; Does the test run successfully to the end?
  ; True: Make sure test exits by falling off the end
  ; False: Do not check if the test falls off the end
  ; Default: True. If exit_error is True, then exit_ok is forced to False.
  exit_ok=True

exit_error:
  ; Does the test exit with failure (non-zero) status?
  ; True: Make sure test exits with non-zero status.
  ; False: Do not check for error status.
  ; Default: False
  exit_error=False

stderr_empty:
  ; Should standard error be empty after the test?
  ; True: Yes, stderr must be empty when the test completes.
  ; False: No, the test might write to stderr.
  ; Default: True, but forced to False if stderr_match is provided.
  stderr_empty=True

stdout_match:
  ; Match pattern for standard output (see note below)
  ; If empty, no matching is done on stdout.
  ; Default: Empty
  stdout_match=""

stderr_match:
  ; Match pattern for standard error (see note below)
  ; If empty, no matching is done on stderr.
  ; Default: Empty
  stderr_match=""


Notes:

Regular expression matches (stdout_match, stderr_match): These are patterns
for preg_match() (without delimiters). Case insensitive matching is used.
The 's' modifier is also used. This lets matches cross newlines, so the
'.' meta-character can match a newline.

Although exit_ok=True checks for return status of 0 (success), this does
not mean that exit(0) can be used to end a test with exit_ok=True.  If
exit(0) is used, it will validate as having exited successfully, but the
check for running to the end will fail, and therefore the test will fail.
There is no way to skip the check for a test running to the end, if the
test is meant to exit successfully.  (The check is performed by including
the test script, followed by a touch() on a flag file. If the flag file
does not exist when the test completes, it did not run through to the end.
This checking is necessary in order to catch any test scripts that abort
without writing an error message.)

You can set both exit_ok=False and exit_error=False, and skip both the
check for exit status and the check for running through to the end, but
this is not recommended because the test may fail without being detected.
Note that you can use "return" from anywhere in your script's global
context (not inside a function) to get the same effect as running through
to the end.


## ISSUES:

Transparent Background:

The tests named "transparent*" produce transparent background images, but you
cannot tell this unless you have a viewer that properly shows this. ImageMagick
does not seem to do so (for GIF; for PNG it shows a checkerboard). Qiv does,
but only if the -p option is used.

If necessary, the output files need to be placed on a web page with a
background color or image. There is a file 'transparent.html' which loads 4
images from the transparent*.php tests to show transparency.
