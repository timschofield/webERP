<?php
/*
   Copyright (c) 2005 Steven Armstrong <sa at c-area dot ch>
   Copyright (c) 2009 Danilo Segan <danilo@kvota.net>

   Drop in replacement for native gettext.

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

use PGettext\T;

// LC_MESSAGES is not available if php has not been compiled with libintl, while the other constants are always available.
// On Linux and Solaris, the values are: LC_CTYPE=0, LC_NUMERIC=1, LC_TIME=2, LC_COLLATE=3, LC_MONETARY=4, LC_MESSAGES=5, LC_ALL=6
// On Windows, the values are: LC_CTYPE=2, LC_NUMERIC=4, LC_TIME=5, LC_COLLATE=1, LC_MONETARY=3, LC_MESSAGES=undefined, LC_ALL=0
// On FreeBSD (14), the values are: LC_CTYPE=2, LC_NUMERIC=4, LC_TIME=5, LC_COLLATE=1, LC_MONETARY=3, LC_MESSAGES=6, LC_ALL=0
if (!defined('LC_MESSAGES')) {
  $lc_constants_values_in_use = array();
  foreach(array('LC_CTYPE', 'LC_NUMERIC', 'LC_TIME', 'LC_COLLATE', 'LC_MONETARY', 'LC_ALL') as $constant) {
    $lc_constants_values_in_use[] = constant($constant);
  }
  if (in_array(5, $lc_constants_values_in_use)) {
    if (in_array(6, $lc_constants_values_in_use)) {
      define('LC_MESSAGES',	max($lc_constants_values_in_use) + 1);
    } else {
      define('LC_MESSAGES',	6);
    }
  } else {
    define('LC_MESSAGES',	5);
  }
  unset($lc_constants_values_in_use);
}

// *** Wrappers used as a drop in replacement for the standard gettext functions ***

if (!function_exists('gettext')) {

  /**
   * @param string $message
   * @return string
   */
  function _($message) {
    return gettext($message);
  }
  T::emulate_function('_');

  /**
   * @param string $domain
   * @param string|null $codeset
   * @return string|false
   */
  function  bind_textdomain_codeset($domain, $codeset = null) {
    return T::_bind_textdomain_codeset($domain, $codeset);
  }
  T::emulate_function('bind_textdomain_codeset');

  /**
   * @param string $domain
   * @param string|null $directory
   * @return string|false
   */
  function bindtextdomain($domain, $directory = null) {
    return T::_bindtextdomain($domain, $directory);
  }
  T::emulate_function('bindtextdomain');

  /**
   * @param string $domain
   * @param string $message
   * @param int $category
   * @return string
   */
  function dcgettext($domain, $message, $category) {
    return T::_dcgettext($domain, $message, $category);
  }
  T::emulate_function('dcgettext');

  /**
   * @param string $domain
   * @param string $singular
   * @param string $plural
   * @param int $count
   * @param int $category
   * @return string
   */
  function dcngettext($domain, $singular, $plural, $count, $category) {
    return T::_dcngettext($domain, $singular, $plural, $count, $category);
  }
  T::emulate_function('dcngettext');

  /**
   * @param string $domain
   * @param string $message
   * @return string
   */
  function dgettext($domain, $message) {
    return T::_dgettext($domain, $message);
  }
  T::emulate_function('dgettext');

  /**
   * @param string $domain
   * @param string $singular
   * @param string $plural
   * @param int $count
   * @return string
   */
  function dngettext($domain, $singular, $plural, $count) {
    return T::_dngettext($domain, $singular, $plural, $count);
  }
  T::emulate_function('dngettext');

  /**
   * @param string $message
   * @return string
   */
  function gettext($message) {
    return T::_gettext($message);
  }
  T::emulate_function('gettext');

  /**
   * @param string $singular
   * @param string $plural
   * @param int $count
   * @return string
   */
  function ngettext($singular, $plural, $count) {
    return T::_ngettext($singular, $plural, $count);
  }
  T::emulate_function('ngettext');

  /**
   * @param string|null $domain
   * @return string
   */
  function textdomain($domain = null) {
    return T::_textdomain($domain);
  }
  T::emulate_function('textdomain');
}

/// @todo is it possible that `setlocale` is ever not available? It is defined in ext/standard/string.c...
if (!function_exists('setlocale')) {
  /**
   * @param int $category
   * @param string $locale
   * @param ...
   * @return string
   */
  function  setlocale($category, $locale)
  {
    return call_user_func_array(array('T', 'setlocale'), func_get_args());
  }
  T::emulate_function('setlocale');
}
