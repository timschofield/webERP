<?php
/*
   Copyright (c) 2003, 2009 Danilo Segan <danilo@kvota.net>.
   Copyright (c) 2005 Nico Kaiser <nico@siriux.net>

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

namespace PGettext;

use PGettext\Plurals\Header as PluralHeader;
use PGettext\Streams\StreamReaderInterface;

/**
 * Provides a simple gettext replacement that works independently from
 * the system's gettext abilities.
 * It can read MO files and use them for translating strings.
 * The files are passed to gettext_reader as a Stream (see streams.php)
 *
 * This version has the ability to cache all strings and translations to
 * speed up the string lookup.
 * While the cache is enabled by default, it can be switched off with the
 * second parameter in the constructor (e.g. when using very large MO files
 * that you don't want to keep in memory)
 *
 * @todo implement an interface
 */
class gettext_reader {
  public $error = 0; // public variable that holds error code (0 if no error)

  protected $BYTEORDER = 0; // 0: low endian, 1: big endian
  protected $STREAM = null;
  protected $short_circuit = false;
  protected $enable_cache = false;
  protected $originals = null; // offset of original table
  protected $translations = null; // offset of translation table
  protected $pluralheader = null; // cache header field for plural forms
  protected $total = 0; // total string count
  protected $table_originals = null;  // table for original strings (offsets)
  protected $table_translations = null;  // table for translated strings (offsets)
  protected $cache_translations = null;  // original -> translation mapping

  /* Methods */

  /**
   * Constructor
   *
   * @param StreamReaderInterface|null $Reader the StreamReader object
   * @param bool $enable_cache Enable or disable caching of strings (default on)
   */
  function __construct($Reader, $enable_cache = true) {
    // If there isn't a StreamReader, turn on short circuit mode.
    if (! $Reader || (isset($Reader->error) && $Reader->error != 0)) {
      /// @todo raise a warning in case $Reader->error != 0 (here or in the Reader class?)
      $this->short_circuit = true;
      return;
    }

    // Caching can be turned off
    $this->enable_cache = $enable_cache;

    $MAGIC1 = "\x95\x04\x12\xde";
    $MAGIC2 = "\xde\x12\x04\x95";

    $this->STREAM = $Reader;
    $magic = $this->read(4);
    if ($magic == $MAGIC1) {
      $this->BYTEORDER = 1;
    } elseif ($magic == $MAGIC2) {
      $this->BYTEORDER = 0;
    } else {
      $this->error = 1; // not MO file
    }

    /// @todo FIXME: Do we care about revision? We should.
    $revision = $this->readint();

    $this->total = $this->readint();
    $this->originals = $this->readint();
    $this->translations = $this->readint();
  }

  /**
   * Reads a 32bit Integer from the Stream
   *
   * @return integer from the Stream
   */
  protected function readint() {
    if ($this->BYTEORDER == 0) {
      // low endian
      $input = unpack('V', $this->STREAM->read(4));
      return array_shift($input);
    } else {
      // big endian
      $input = unpack('N', $this->STREAM->read(4));
      return array_shift($input);
    }
  }

  /**
   * @param int $bytes
   * @return false|string
   */
  function read($bytes) {
    return $this->STREAM->read($bytes);
  }

  /**
   * Reads an array of Integers from the Stream
   *
   * @param int $count How many elements should be read
   * @return int[]
   */
  function readintarray($count) {
    if ($this->BYTEORDER == 0) {
      // low endian
      return unpack('V'.$count, $this->STREAM->read(4 * $count));
    } else {
      // big endian
      return unpack('N'.$count, $this->STREAM->read(4 * $count));
    }
  }

  /**
   * Loads the translation tables from the MO file into the cache
   * If caching is enabled, also loads all strings into a cache
   * to speed up translation lookups
   */
  protected function load_tables() {
    if (is_array($this->cache_translations) &&
      is_array($this->table_originals) &&
      is_array($this->table_translations))
      return;

    /* get original and translations tables */
    if (!is_array($this->table_originals)) {
      $this->STREAM->seekto($this->originals);
      $this->table_originals = $this->readintarray($this->total * 2);
    }
    if (!is_array($this->table_translations)) {
      $this->STREAM->seekto($this->translations);
      $this->table_translations = $this->readintarray($this->total * 2);
    }

    if ($this->enable_cache) {
      $this->cache_translations = array ();
      /* read all strings in the cache */
      for ($i = 0; $i < $this->total; $i++) {
        $this->STREAM->seekto($this->table_originals[$i * 2 + 2]);
        $original = $this->STREAM->read($this->table_originals[$i * 2 + 1]);
        $this->STREAM->seekto($this->table_translations[$i * 2 + 2]);
        $translation = $this->STREAM->read($this->table_translations[$i * 2 + 1]);
        $this->cache_translations[$original] = $translation;
      }
    }
  }

  /**
   * Returns a string from the "originals" table
   *
   * @param int $num Offset number of original string
   * @return string Requested string if found, otherwise ''
   */
  protected function get_original_string($num) {
    $length = $this->table_originals[$num * 2 + 1];
    $offset = $this->table_originals[$num * 2 + 2];
    if (! $length)
      return '';
    $this->STREAM->seekto($offset);
    $data = $this->STREAM->read($length);
    return (string)$data;
  }

  /**
   * Returns a string from the "translations" table
   *
   * @param int $num Offset number of original string
   * @return string Requested string if found, otherwise ''
   */
  protected function get_translation_string($num) {
    $length = $this->table_translations[$num * 2 + 1];
    $offset = $this->table_translations[$num * 2 + 2];
    if (! $length)
      return '';
    $this->STREAM->seekto($offset);
    $data = $this->STREAM->read($length);
    return (string)$data;
  }

  /**
   * Binary search for string
   *
   * @param string $string
   * @param int $start (internally used in recursive function)
   * @param int $end (internally used in recursive function)
   * @return int string number (offset in originals table)
   */
  protected function find_string($string, $start = -1, $end = -1) {
    if (($start == -1) or ($end == -1)) {
      // find_string is called with only one parameter, set start end end
      $start = 0;
      $end = $this->total;
    }
    if (abs($start - $end) <= 1) {
      // We're done, now we either found the string, or it doesn't exist
      $txt = $this->get_original_string($start);
      if ($string == $txt)
        return $start;
      else
        return -1;
    } else if ($start > $end) {
      // start > end -> turn around and start over
      return $this->find_string($string, $end, $start);
    } else {
      // Divide table in two parts
      $half = (int)(($start + $end) / 2);
      $cmp = strcmp($string, $this->get_original_string($half));
      if ($cmp == 0)
        // string is exactly in the middle => return it
        return $half;
      else if ($cmp < 0)
        // The string is in the upper half
        return $this->find_string($string, $start, $half);
      else
        // The string is in the lower half
        return $this->find_string($string, $half, $end);
    }
  }

  /**
   * Translates a string
   *
   * @param string $string string to be translated
   * @return string translated string (or original, if not found)
   */
  public function translate($string) {
    if ($this->short_circuit)
      return $string;

    $this->load_tables();

    if ($this->enable_cache) {
      // Caching enabled, get translated string from cache
      if (array_key_exists($string, $this->cache_translations))
        return $this->cache_translations[$string];
      else
        return $string;
    } else {
      // Caching not enabled, try to find string
      $num = $this->find_string($string);
      if ($num == -1)
        return $string;
      else
        return $this->get_translation_string($num);
    }
  }

  /**
   * Parse full PO header and extract only plural forms line.
   *
   * @return string verbatim plural form header field
   */
  public function extract_plural_forms_header_from_po_header($header) {
    if (preg_match("/(^|\n)plural-forms: ([^\n]*)\n/i", $header, $regs))
      $expr = $regs[2];
    else
      $expr = "nplurals=2; plural=n == 1 ? 0 : 1;";
    return $expr;
  }

  /**
   * Get possible plural forms from MO header
   *
   * @return PluralHeader plural form header object
   */
  protected function get_plural_forms() {
    // let's assume message number 0 is header
    // this is true, right?
    $this->load_tables();

    // cache header field for plural forms
    if ($this->pluralheader == null) {
      if ($this->enable_cache) {
        $header = $this->cache_translations[""];
      } else {
        $header = $this->get_translation_string(0);
      }
      $expr = $this->extract_plural_forms_header_from_po_header($header);
      $this->pluralheader = new PluralHeader($expr);
    }
    return $this->pluralheader;
  }

  /**
   * Detects which plural form to take
   *
   * @param int $n count
   * @return int array index of the right plural form
   */
  public function select_string($n) {
    $plural_header = $this->get_plural_forms();
    $plural = $plural_header->expression->evaluate($n);

    /// @todo raise a warning when $plural >= $total or $plural < 0
    if ($plural >= $plural_header->total) {
      $plural = $plural_header->total - 1;
    } elseif ($plural < 0) {
      $plural = 0;
    }

    return $plural;
  }

  /**
   * Plural version of gettext
   *
   * @param string $single
   * @param string $plural
   * @param string $number
   * @return string translated plural form
   */
  public function ngettext($single, $plural, $number) {
    if ($this->short_circuit) {
      if ($number != 1)
        return $plural;
      else
        return $single;
    }

    // find out the appropriate form
    $select = $this->select_string($number);

    // this should contain all strings separated by NULLs
    $key = $single . chr(0) . $plural;

    if ($this->enable_cache) {
      if (! array_key_exists($key, $this->cache_translations)) {
        return ($number != 1) ? $plural : $single;
      } else {
        $result = $this->cache_translations[$key];
        $list = explode(chr(0), $result);
        return $list[$select];
      }
    } else {
      $num = $this->find_string($key);
      if ($num == -1) {
        return ($number != 1) ? $plural : $single;
      } else {
        $result = $this->get_translation_string($num);
        $list = explode(chr(0), $result);
        return $list[$select];
      }
    }
  }

  public function pgettext($context, $msgid) {
    $key = $context . chr(4) . $msgid;
    $ret = $this->translate($key);
    if (strpos($ret, "\004") !== FALSE) {
      return $msgid;
    } else {
      return $ret;
    }
  }

  public function npgettext($context, $singular, $plural, $number) {
    $key = $context . chr(4) . $singular;
    $ret = $this->ngettext($key, $plural, $number);
    if (strpos($ret, "\004") !== FALSE) {
      return $singular;
    } else {
      return $ret;
    }
  }
}
