<?php

# Testing PHPlot: Output file and output file types
# This tests the output file parameter in the PHPlot constructor,
# SetFileFormat, and SetOutputFile.
# To work in the test environment, it writes the image to a temporary
# file, then outputs the file to stdout.
#
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Output File Test',
  'suffix' => " (default)", # Title part 2
  'ftype' => NULL,          # file type: png gif jpg, NULL to omit.
  'useset' => True,         # True to use SetOutputFile, False to use constructor
        ), $tp);
require_once 'phplot.php';

$data = array( array('', 0, 0), array('', 10, 10));
# make a temporary file for the image in the current directory.
$outfile = tempnam('.', 'outfile_img');

if ($tp['useset'])
  $plot = new Phplot\Phplot\phplot(360, 240);
else
  $plot = new Phplot\Phplot\phplot(360, 240, $outfile);
$plot->SetPlotType('lines');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetTitle($tp['title'] . $tp['suffix']);
# This is needed for output files, even though there are no headers:
$plot->SetIsInline(True);

if ($tp['useset'])
  $plot->SetOutputFile($outfile);
if (isset($tp['ftype']))
  $plot->SetFileFormat($tp['ftype']);

$plot->DrawGraph();
unset($plot);
# Output and delete the generated file:
readfile($outfile);
unlink($outfile);
