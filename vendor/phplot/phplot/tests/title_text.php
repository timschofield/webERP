<?php

# Testing phplot - Title issues: order dependency (bug #1816844), alignment,
# multi- vs single-line, fonts.
# This uses config.php to identify TrueType font locations and names.
require_once 'config.php';
#
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Title Text (baseline)',  # First or only line
  'title_lines' => 1,       # Number of lines in the main, X, and Y titles
  'use_ttf' => False,       # True to use TTF text, False for GD text
  'line_spacing' => NULL,   # Line spacing, or NULL to omit
  'line_spacing_before' => True,   # If true, set line_spacing before titles, else after.
  'x_title_pos' => 'plotdown',  # X Title Position: plotdown plotup both none
  'y_title_pos' => 'plotleft',  # Y Title Position: plotleft plotright both none
  'ttfont' => $phplot_test_ttfonts['sans'],  # TrueType font filename
  'ttfdir' => $phplot_test_ttfdir,   # TrueType font directory
  'ttfsize' => 14,    # TrueType font size in points
        ), $tp);
require_once 'phplot.php';

$data = array(
  array('', 0, 0),
  array('', 1, 10),
);

$p = new Phplot\Phplot\phplot(800,600);

if ($tp['use_ttf']) {
  $p->SetTTFPath($tp['ttfdir']);
  $p->SetDefaultTTFont($tp['ttfont']);
}
# Set line spacing before titles:
if (isset($tp['line_spacing']) && $tp['line_spacing_before']) {
  $p->SetLineSpacing($tp['line_spacing']);
}

$title = $tp['title'];
$x_title = 'X Axis Title';
$y_title = 'Y Axis Title';
for ($i = 2; $i <= $tp['title_lines']; $i++) {
  $title .= "\nTitle Line $i";
  $x_title .= "\nX Title Line $i";
  $y_title .= "\nY Title Line $i";
}

$p->SetTitle($title);
$p->SetXTitle($x_title, $tp['x_title_pos']);
$p->SetYTitle($y_title, $tp['y_title_pos']);

# Set line spacing after titles:
if (isset($tp['line_spacing']) && !$tp['line_spacing_before']) {
  $p->SetLineSpacing($tp['line_spacing']);
}
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetPlotType('lines');
$p->DrawGraph();
