<?php
/*
   Copyright (c) 2003,2004,2005,2009 Danilo Segan <danilo@kvota.net>.
   Copyright (c) 2005,2006 Steven Armstrong <sa@c-area.ch>

   This file is part of Polyfill-Gettext.

   Polyfill-Gettext is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   Polyfill-Gettext is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with Polyfill-Gettext; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

error_reporting(E_ALL | E_STRICT);

require_once(__DIR__ . '/../vendor/autoload.php');

// Define constants
define('PROJECT_DIR', __DIR__);
define('LOCALE_DIR', PROJECT_DIR .'/locale');
define('DEFAULT_LOCALE', setlocale(LC_MESSAGES, 0));

use PGettext\T;

// for unix-like systems, get the list of available locales using `locale -a`
$installed_locales = array();
exec('which locale', $output, $retcode);
if ($retcode == 0) {
  $output = array();
  exec('locale -a', $output, $retcode);
  if ($retcode == 0) {
    $installed_locales = $output;
  }
}

// 'esperanto' instead of its iso code 'eo' is not an error - we use it to showcase a locale which is never part of the
// ones installed on the system
$supported_locales = array(DEFAULT_LOCALE, 'en_US', 'sr_RS', 'de_CH', 'esperanto');
$encoding = 'UTF-8';
$domain = 'messages';

$locale = (isset($_GET['lang']) && in_array($_GET['lang'], $supported_locales)) ? $_GET['lang'] : DEFAULT_LOCALE;

// Polyfill-Gettext setup. This also sets up the locale for all other non-gettext localization-related methods
T::setlocale(LC_ALL, $locale);

// Set the text domain
T::bindtextdomain($domain, LOCALE_DIR);
T::bind_textdomain_codeset($domain, $encoding);
T::textdomain($domain);

header("Content-type: text/html; charset=$encoding");
?>
<html lang="en">
<head>
<title>Polyfill-Gettext fallback example</title>
</head>
<body>
<h1>Polyfill-Gettext as a fallback solution</h1>
<p>Example showing how to use Polyfill-Gettext as a fallback solution if either the native gettext library is not available or the system does not support the requested locale.</p>
<?php

if (extension_loaded('gettext')) {
  print "<p>NB: The native gettext extension is active on this PHP installation</p>\n";
} else {
  print "<p>NB: The native gettext extension is not active on this PHP installation</p>\n";
}

if ($installed_locales) {
  print "<p>Locales available on the system: ";
  foreach($installed_locales as $i => $l) {
    print htmlspecialchars($l) . (($i < count($installed_locales) -1) ? ', ' : '');
  }
  print "</p>\n";
}

print "<p>Test locales: ";
foreach($supported_locales as $l) {
	print "[<a href=\"?lang=$l\">$l</a>] ";
}
print "</p>\n";

if (T::locale_emulation()) {
  print "<p>Locale '" . htmlspecialchars($locale) . "' is <strong>not</strong> supported on your system, using poliyfill gettext implementation.</p>\n";
}
else {
  print "<p>Locale '" . htmlspecialchars($locale) . "' is supported by your system, using native gettext implementation.</p>\n";
}
?>

<hr />

<?php
// Using Polyfill-Gettext (which might be using native gettext under the hood if it is enabled and supports the current locale)
print "<pre>";
print T::_("This is how the story goes.\n\n");
for ($number=6; $number>=0; $number--) {
  printf(T::ngettext("%d pig went to the market\n", "%d pigs went to the market\n", $number), $number);
}
print "</pre>\n";
?>

<hr />
<p>&laquo; <a href="./index.php">back</a></p>
</body>
</html>
