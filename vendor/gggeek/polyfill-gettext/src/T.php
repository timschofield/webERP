<?php

namespace PGettext;

use PGettext\Streams\StreamReaderInterface;

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

class T
{
  protected static $text_domains = array();
  protected static $current_domain = 'messages';
  /**
   * The keys are locale names
   * @var bool[]
   */
  protected static $emulate_locales = array();
  protected static $current_locale = '';
  protected static $emulated_functions = array();
  /**
   * Can be changed to implement custom stream readers which do accept the same constructor arguments as gettext_reader
   * @var string
   */
  public static $reader_class = '\PGettext\gettext_reader';
  /**
   * Can be changed to implement custom stream readers which do accept the same constructor arguments as FileReader
   * @var string
   */
  public static $stream_reader_class = '\PGettext\Streams\FileReader';
  /**
   * Controls whether translation strings will be cached or not (by default, in memory)
   * @var bool
   */
  public static $enable_cache = true;
  /**
   * Note: the index value is the numeric value of the php constant of the same name as the value
   * @see https://www.php.net/manual/en/function.setlocale.php
   * @var string[]
   */
  protected static $LC_CATEGORIES = array('LC_CTYPE', 'LC_NUMERIC', 'LC_TIME', 'LC_COLLATE', 'LC_MONETARY', 'LC_MESSAGES', 'LC_ALL');


  // *** Custom implementation of the standard gettext related functions, plus a few similar ones ***

  /**
   * Alias for gettext.
   */
  public static function __($msgid) {
    return static::_gettext($msgid);
  }

  /**
   * Specify the character encoding in which the messages from the DOMAIN message catalog will be returned.
   * @param string $domain
   * @param string|null $codeset
   * @return string|false
   */
  public static function _bind_textdomain_codeset($domain, $codeset = null) {
    // using 0 works for php version up to 7.4
    if ($domain == '' && ($domain !== 0 || version_compare(PHP_VERSION, '8.0.0', '>='))) {
      return false;
    }
    /// @todo throw a ValueError if $domain == ''
    if (static::initialize_domain_if_needed($domain) < 0) {
      return false;
    }
    if ($codeset !== null) {
      /// @todo if mbstring and its alternatives are not available, return false?
      static::$text_domains[$domain]->codeset = $codeset;
    }
    return static::$text_domains[$domain]->codeset;
  }

  /**
   * Sets or gets the path for a domain.
   * @param string $domain
   * @param string|null $directory
   * @return string|false
   */
  public static function _bindtextdomain($domain, $directory = null) {
    if (static::initialize_domain_if_needed($domain) < 0) {
      return false;
    }

    // "An empty string means the current directory"
    if ($directory === '') {
      $directory = getcwd();
    }

    // if $directory is null (or 0), do not set the dir
    if ($directory != null && $directory !== '0') {
      $directory = (string)$directory;

      // here was: "ensure $directory ends with a slash ('/' should work for both, but let's still play nice)"

      if (! is_dir($directory)) {
        return false;
      }
      $directory = realpath($directory);
      static::$text_domains[$domain]->path = $directory;
    }
    return static::$text_domains[$domain]->path;
  }

  /**
   * Overrides the domain for a single lookup.
   * @param string $domain
   * @param string $message
   * @param int $category
   * @return string
   */
  public static function _dcgettext($domain, $message, $category) {
    $l10n = static::get_reader($domain, $category);
    return static::encode($l10n->translate($message));
  }

  /**
   * Plural version of dcgettext.
   * @param string $domain
   * @param string $singular
   * @param string $plural
   * @param int $count
   * @param int $category
   * @return string
   */
  public static function _dcngettext($domain, $singular, $plural, $count, $category) {
    $l10n = static::get_reader($domain, $category);
    return static::encode($l10n->ngettext($singular, $plural, $count));
  }

  /**
   * Override the current domain.
   * @param string $domain
   * @param string $message
   * @return string
   */
  public static function _dgettext($domain, $message) {
    /// @todo throw ValueError if $domain is the empty string
    $l10n = static::get_reader($domain);
    return static::encode($l10n->translate($message));
  }

  /**
   * Plural version of dgettext.
   * @param string $domain
   * @param string $singular
   * @param string $plural
   * @param int $count
   * @return string
   */
  public static function _dngettext($domain, $singular, $plural, $count) {
    /// @todo throw ValueError if $domain is the empty string
    $l10n = static::get_reader($domain);
    return static::encode($l10n->ngettext($singular, $plural, $count));
  }

  /**
   * Lookup a message in the current domain.
   * @param string $message
   * @return string
   */
  public static function _gettext($message) {
    $l10n = static::get_reader();
    return static::encode($l10n->translate($message));
  }

  /**
   * Plural version of gettext.
   * @param string $singular
   * @param string $plural
   * @param int $count
   * @return string
   */
  public static function _ngettext($singular, $plural, $count) {
    $l10n = static::get_reader();
    return static::encode($l10n->ngettext($singular, $plural, $count));
  }

  /**
   * Sets the default domain.
   * @param string|null $domain
   * @return string
   */
  public static function _textdomain($domain = null) {
    /// @todo throw a ValueError if $domain === ''
    if ($domain != null && $domain !== '0') {
      static::$current_domain = $domain;
    }
    return static::$current_domain;
  }

  // *** 'context' gettext calls ***

  /**
   * Overrides the domain and category for a plural context-based lookup.
   */
  public static function _dcnpgettext($domain, $context, $singular, $plural, $number, $category) {
    $l10n = static::get_reader($domain, $category);
    return static::encode($l10n->npgettext($context, $singular, $plural, $number));
  }

  /**
   * Overrides the domain and category for a single context-based lookup.
   */
  public static function _dcpgettext($domain, $context, $message, $category) {
    $l10n = static::get_reader($domain, $category);
    return static::encode($l10n->pgettext($context, $message));
  }

  /**
   * Override the current domain in a context ngettext call.
   */
  public static function _dnpgettext($domain, $context, $singular, $plural, $number) {
    $l10n = static::get_reader($domain);
    return static::encode($l10n->npgettext($context, $singular, $plural, $number));
  }

  /**
   * Override the current domain in a context gettext call.
   */
  public static function _dpgettext($domain, $context, $message) {
    $l10n = static::get_reader($domain);
    return static::encode($l10n->pgettext($context, $message));
  }

  /**
   * Context version of ngettext.
   */
  public static function _npgettext($context, $singular, $plural, $number) {
    $l10n = static::get_reader();
    return static::encode($l10n->npgettext($context, $singular, $plural, $number));
  }

  /**
   * Context version of gettext.
   */
  public static function _pgettext($context, $message) {
    $l10n = static::get_reader();
    return static::encode($l10n->pgettext($context, $message));
  }

  // *** Wrappers to use if the standard gettext functions are available, but the current locale is not supported by the system. ***

  /**
   * Alias for gettext.
   */
  public static function _($message) {
    if (static::check_locale_and_function('_'))
      return _($message);
    else
      return static::_gettext($message);
  }

  /**
   * Specify the character encoding in which the messages from the DOMAIN message catalog will be returned.
   * @param string $domain
   * @param string|null $codeset
   * @return string|false
   */
  public static function bind_textdomain_codeset($domain, $codeset) {
    // bind_textdomain_codeset is available only in PHP 4.2.0+
    if (static::check_locale_and_function('bind_textdomain_codeset'))
      return bind_textdomain_codeset($domain, $codeset);
    else
      return static::_bind_textdomain_codeset($domain, $codeset);
  }

  /**
   * Sets or gets the path for a domain.
   * @param string $domain
   * @param string|null $directory
   * @return string|false
   */
  public static function bindtextdomain($domain, $directory) {
    if (static::check_locale_and_function('bindtextdomain'))
      return bindtextdomain($domain, $directory);
    else
      return static::_bindtextdomain($domain, $directory);
  }

  /**
   * Overrides the domain for a single lookup.
   * @param string $domain
   * @param string $message
   * @param int $category
   * @return string
   */
  public static function dcgettext($domain, $message, $category) {
    if (static::check_locale_and_function('dcgettext'))
      return dcgettext($domain, $message, $category);
    else
      return static::_dcgettext($domain, $message, $category);
  }

  /**
   * Plural version of dcgettext.
   * @param string $domain
   * @param string $singular
   * @param string $plural
   * @param int $count
   * @param int $category
   * @return string
   */
  public static function dcngettext($domain, $singular, $plural, $count, $category) {
    if (static::check_locale_and_function('dcngettext'))
      return dcngettext($domain, $singular, $plural, $count, $category);
    else
      return static::_dcngettext($domain, $singular, $plural, $count, $category);
  }

  /**
   * Override the current domain.
   * @param string $domain
   * @param string $message
   * @return string
   */
  public static function dgettext($domain, $message) {
    if (static::check_locale_and_function('dgettext'))
      return dgettext($domain, $message);
    else
      return static::_dgettext($domain, $message);
  }

  /**
   * Plural version of dgettext.
   * @param string $domain
   * @param string $singular
   * @param string $plural
   * @param int $count
   * @return string
   */
  public static function dngettext($domain, $singular, $plural, $count) {
    if (static::check_locale_and_function('dngettext'))
      return dngettext($domain, $singular, $plural, $count);
    else
      return static::_dngettext($domain, $singular, $plural, $count);
  }

  /**
   * Lookup a message in the current domain.
   * @param string $message
   * @return string
   */
  public static function gettext($message) {
    if (static::check_locale_and_function('gettext'))
      return gettext($message);
    else
      return static::_gettext($message);
  }

  /**
   * Plural version of gettext.
   * @param string $singular
   * @param string $plural
   * @param int $count
   * @return string
   */
  public static function ngettext($singular, $plural, $count) {
    if (static::check_locale_and_function('ngettext'))
      return ngettext($singular, $plural, $count);
    else
      return static::_ngettext($singular, $plural, $count);
  }

  /**
   * Sets the default domain.
   * @param string|null $domain
   * @return string
   */
  public static function textdomain($domain) {
    if (static::check_locale_and_function('textdomain'))
      return textdomain($domain);
    else
      return static::_textdomain($domain);
  }

  // *** not in the PHP library ***

  public static function dcnpgettext($domain, $context, $singular, $plural, $number, $category) {
    if (static::check_locale_and_function('dcnpgettext'))
      return dcnpgettext($domain, $context, $singular, $plural, $number, $category);
    else
      return static::_dcnpgettext($domain, $context, $singular, $plural, $number, $category);
  }

  public static function dcpgettext($domain, $context, $message, $category) {
    if (static::check_locale_and_function('dcpgettext'))
      return dcpgettext($domain, $context, $message, $category);
    else
      return static::_dcpgettext($domain, $context, $message, $category);
  }

  public static function dnpgettext($domain, $context, $singular, $plural, $number) {
    if (static::check_locale_and_function('dnpgettext'))
      return dnpgettext($domain, $context, $singular, $plural, $number);
    else
      return static::_dnpgettext($domain, $context, $singular, $plural, $number);
  }

  public static function dpgettext($domain, $context, $message) {
    if (static::check_locale_and_function('dpgettext'))
      return dpgettext($domain, $context, $message);
    else
      return static::_dpgettext($domain, $context, $message);
  }

  public static function npgettext($context, $singular, $plural, $number) {
    if (static::check_locale_and_function('npgettext'))
      return npgettext($context, $singular, $plural, $number);
    else
      return static::_npgettext($context, $singular, $plural, $number);
  }

  public static function pgettext($context, $message) {
    if (static::check_locale_and_function('pgettext'))
      return pgettext($context, $message);
    else
      return static::_pgettext($context, $message);
  }

  // *** Utility methods ***

  /**
   * Sets a requested locale (or queries the currently set locale), if needed emulating it.
   * @param int $category only LC_MESSAGES and LC_ALL are supported.
   *        On Linux, the values are: LC_CTYPE=0, LC_NUMERIC=1, LC_TIME=2, LC_COLLATE=3, LC_MONETARY=4, LC_MESSAGES=5, LC_ALL=6
   *        BUT the values are different on other OS! Eg. on FreeBSD 14, LC_MESSAGES=6
   * @param string $locale
   * @return string|false
   */
  public static function setlocale($category, $locale) {
    if ($category != LC_ALL && $category != LC_MESSAGES) {
      trigger_error("Function T::setlocale only accepts LC_MESSAGES and LC_ALL", E_USER_WARNING);
      return false;
    }

    /// @todo emit a warning if we get passed a string for $category, as recent php versions do

    if ($locale === 0 || $locale === '0') {
      $locale = static::get_current_locale(true);
      return $category == LC_ALL ? "LC_MESSAGES=" . $locale : $locale;
    } else {
      // we make sure the `setlocale` function is not the polyfill one, to avoid loops!
      if (function_exists('setlocale') && !isset(static::$emulated_functions['setlocale'])) {
        $args = func_get_args();
        $ret = call_user_func_array('setlocale', $args);
        if ($ret !== false) {
          // Locale successfully set. Disable emulation for the current locale (this does not mean we will try to call
          // non-existing gettext methods)
          static::$current_locale = $ret;
          static::$emulate_locales[static::$current_locale] = false;
        } else {
          // Failed setting it. Enable emulation for the current locale
          if ($category != LC_MESSAGES) {
            trigger_error("Function T::setlocale called with LC_ALL category, but in emulated mode only messages will be translated. Use LC_MESSAGES instead", E_USER_WARNING);
          }
          array_shift($args);
          static::$current_locale = static::get_default_locale($args);
          static::$emulate_locales[static::$current_locale] = true;
        }
      } else {
        // No function setlocale(), emulate it all. NB: this should never happen irl, as setlocale is always defined by
        // php and never emulated...
        static::$current_locale = static::get_default_locale($locale);
        static::$emulate_locales[static::$current_locale] = true;
      }

      // Allow locale to be changed on the go for one translation domain.
      /// @todo review - is this necessary / correct?
      if (array_key_exists(static::$current_domain, static::$text_domains)) {
        unset(static::$text_domains[static::$current_domain]->l10n);
      }

      return static::$current_locale;
    }
  }

  /**
   * Returns whether we are using our emulated gettext API (true) or the PHP built-in one (false).
   * @param null|bool $emulateLocale pass in a bool value to change the current value instead of just querying it.
   *                                 Note that the library does its best to determine the correct value on its own,
   *                                 you should normally not have to force this.
   * @param null|string $locale
   * @return bool
   */
  public static function locale_emulation($emulateLocale = null, $locale = null) {
    if (!extension_loaded('gettext')) {
      // we handle the rare case where the extension is not loaded, but someone else is providing the gettext family of
      // functions
      $functionsToEmulate = array('_' => true, 'bind_textdomain_codeset' => true, 'bindtextdomain' => true,
        'dcgettext' => true, 'dcngettext' => true, 'dgettext' => true, 'dngettext' => true, 'gettext' => true,
        'ngettext' => true, 'textdomain' => true);
      return (count(array_intersect_key($functionsToEmulate, static::$emulated_functions)) == count($functionsToEmulate));
    }
    if ($locale == '') {
      $locale = static::get_current_locale();
    }
    if ($emulateLocale !== null) {
      static::$emulate_locales[$locale] = (bool)$emulateLocale;
    }
    if (!isset(static::$emulate_locales[$locale])) {
      static::$emulate_locales[$locale] = static::should_emulate_locale($locale);
    }
    return static::$emulate_locales[$locale];
  }

  /**
   * Notify the T class that it is used to emulate a given native php function
   * @param string $function
   * @param null|bool $doEmulate if null is passed in, the current value is returned unchanged
   * @return bool|null
   */
  public static function emulate_function($function, $doEmulate = true)
  {
    if ($doEmulate !== null) {
      static::$emulated_functions[$function] = (bool)$doEmulate;
    }
    return isset(static::$emulated_functions[$function]) ? static::$emulated_functions[$function] : null;
  }

  /**
   * Return a list of locales to try for any POSIX-style locale specification.
   * @param string $locale
   * @return string[]
   */
  public static function get_list_of_locales($locale) {
    // Figure out all possible locale names and start with the most specific ones.
    // I.e. for sr_CS.UTF-8@latin, look through all of sr_CS.UTF-8@latin, sr_CS@latin, sr@latin, sr_CS.UTF-8, sr_CS, sr.
    $locale_names = array();
    $lang = NULL;
    $country = NULL;
    $charset = NULL;
    $modifier = NULL;
    if ($locale) {
      if (preg_match("/^(?P<lang>[a-z]{2,3})"    // language code
        ."(?:_(?P<country>[A-Z]{2}))?"           // country code
        ."(?:\.(?P<charset>[-A-Za-z0-9_]+))?"    // charset
        ."(?:@(?P<modifier>[-A-Za-z0-9_]+))?$/", // @ modifier
        $locale, $matches)) {

        if (isset($matches["lang"])) $lang = $matches["lang"];
        if (isset($matches["country"])) $country = $matches["country"];
        if (isset($matches["charset"])) $charset = $matches["charset"];
        if (isset($matches["modifier"])) $modifier = $matches["modifier"];

        if ($modifier) {
          if ($country) {
            if ($charset)
              array_push($locale_names, "{$lang}_$country.$charset@$modifier");
            array_push($locale_names, "{$lang}_$country@$modifier");
          } elseif ($charset)
            array_push($locale_names, "$lang.$charset@$modifier");
          array_push($locale_names, "$lang@$modifier");
        }
        if ($country) {
          if ($charset)
            array_push($locale_names, "{$lang}_$country.$charset");
          array_push($locale_names, "{$lang}_$country");
        } elseif ($charset)
          array_push($locale_names, "$lang.$charset");
        array_push($locale_names, $lang);
      }

      // If the locale name doesn't match POSIX style, just include it as-is.
      if (!in_array($locale, $locale_names))
        array_push($locale_names, $locale);
    }
    return $locale_names;
  }

  /**
   * Utility function to get a StreamReader for the given text domain.
   * @param string|null $domain
   * @param int $category see the LC_ constants.
   * @param bool|null $enable_cache when null, static::$enable_cache decides whether to cache translation strings or not
   * @return ReaderInterface
   */
  protected static function get_reader($domain=null, $category=LC_MESSAGES, $enable_cache=null) {
    if (!isset($domain)) $domain = static::$current_domain;

    $initialized = static::initialize_domain_if_needed($domain);
    if ($initialized < 0) {
/// @todo throw
    }

    if (!isset(static::$text_domains[$domain]->l10n)) {
      // get the current locale
      $locale = static::setlocale(LC_MESSAGES, 0);
      /// @todo is it correct to use the current directory as default?
      $bound_path = isset(static::$text_domains[$domain]->path) ? static::$text_domains[$domain]->path : '.';
      $subpath = static::$LC_CATEGORIES[$category] ."/$domain.mo";

      $locale_names = static::get_list_of_locales($locale);
      $stream_reader = null;
      foreach ($locale_names as $locale) {
        $full_path = $bound_path . '/' . $locale . '/' . $subpath;
        if (file_exists($full_path)) {
          $stream_reader = static::build_stream_reader($full_path);
          break;
        }
      }

      if ($enable_cache === null) {
        $enable_cache = static::$enable_cache;
      }
      static::$text_domains[$domain]->l10n = static::build_reader($stream_reader, $enable_cache);
    }

    return static::$text_domains[$domain]->l10n;
  }

  /**
   * Can be overridden in subclasses.
   * @param string $full_path
   * @return StreamReaderInterface
   */
  protected static function build_stream_reader($full_path) {
    return new static::$stream_reader_class($full_path);
  }

  /**
   * Can be overridden in subclasses.
   * @param StreamReaderInterface $stream_reader
   * @param bool $enable_cache
   * @return ReaderInterface
   */
  protected static function build_reader($stream_reader, $enable_cache=true) {
    return new static::$reader_class($stream_reader, $enable_cache);
  }

  /**
   * Check if the current locale and specified function is supported on this system.
   * @param string|false $function
   * @param string|null $locale to check a locale other than the current one
   * @return bool true means the locale/function is supported and needs no emulation
   * @todo rename?
   */
  protected static function check_locale_and_function($function=false, $locale=null) {
    if ($function and (isset(static::$emulated_functions[$function]) || !function_exists($function)))
      return false;
    if ($locale == '') {
      $locale = static::get_current_locale(true);
    }
    if (!isset(static::$emulate_locales[$locale])) {
      static::$emulate_locales[$locale] = static::should_emulate_locale($locale);
    }
    return !static::$emulate_locales[$locale];
  }

  /**
   * Get the codeset for the given domain.
   * @param string|null $domain
   * @return string
   */
  protected static function get_codeset($domain=null) {
    if (!isset($domain)) $domain = static::$current_domain;
    static::initialize_domain_if_needed($domain);
    return isset(static::$text_domains[$domain]->codeset) ? static::$text_domains[$domain]->codeset : (
      (extension_loaded('mbstring') && mb_internal_encoding() != '') ? mb_internal_encoding() : (
        /// @todo should we default to this? Esp. for php 5.x? Or leave an empty string and handle it while transcoding
        ini_get('internal_encoding') != '' ? ini_get('mbstring.internal_encoding') : ('UTF-8')
      )
    );
  }

  /**
   * Convert the given string to the encoding set by bind_textdomain_codeset.
   * @param string $text
   * @return string
   * @todo move to a separate class?
   * @todo add charset conversion based on other php extensions/classes/methods: iconv, Uconverter, utf8_encode and co.
   */
  protected static function encode($text) {
    $target_encoding = static::get_codeset();
    if (function_exists("mb_detect_encoding")) {
      $source_encoding = mb_detect_encoding($text);
      if ($source_encoding != $target_encoding)
        $text = mb_convert_encoding($text, $target_encoding, $source_encoding);
    }
    return $text;
  }

  /**
   * Returns passed in $locale, or environment variable $LANG if $locale == ''.
   * @param string|string[]|null $locale if null or empty string, use LANG env var
   * @return string|false
   * @todo we should most likely support other env vars, as per
   *       https://www.gnu.org/software/gettext/manual/html_node/Locale-Environment-Variables.html
   *       https://www.gnu.org/software/gettext/manual/html_node/The-LANGUAGE-variable.html
   * @todo rename?
   */
  protected static function get_default_locale($locale) {
/// @todo check what happens in native PHP when an array of locales is passed in, and the 1st element is the empty string
    if (is_array($locale)) {
      $locale = reset($locale);
    }
    if ($locale == '')
      return getenv('LANG');
    else
      return $locale;
  }


  /**
   * NB: end-user code should not call this but T::setlocale(LC_MESSAGES/LC_ALL, 0) instead.
   * @param bool $set_current_if_unset
   * @return string|false
   */
  protected static function get_current_locale($set_current_if_unset = false) {
    if (static::$current_locale != '') {
      return static::$current_locale;
    }

    // we use a setlocale(LC_MESSAGES, 0) call, followed by analysis of env vars, to determine the current locale
    if (function_exists('setlocale') && !isset(static::$emulated_functions['setlocale'])) {
      $locale = setlocale(LC_MESSAGES, 0);
      if ($locale !== false) {
        if ($set_current_if_unset) {
          static::$current_locale = $locale;
        }
        return $locale;
      }
    }
    return static::get_default_locale('');
  }

  /**
   * @param string $locale
   * @return bool
   */
  protected static function should_emulate_locale($locale) {
    if (!function_exists('setlocale') || isset(static::$emulated_functions['setlocale'])) {
      return true;
    }

    $currentLocale = setlocale(LC_MESSAGES, 0);
    if ($locale == $currentLocale) {
      return false;
    }
    $ok = (setlocale(LC_MESSAGES, $locale) !== false);
    setlocale(LC_MESSAGES, $currentLocale);
    return $ok;
  }

  /**
   * @param string $domain
   * @return int 1 when the domain is created on the fly, 0 if it existed, < 0 on failure
   */
  protected static function initialize_domain_if_needed($domain)
  {
    if (is_object($domain) || is_array($domain) || is_resource($domain)) {
      return -1;
    }
    if (!array_key_exists($domain, static::$text_domains)) {
      // Initialize an empty domain object.
      $domainObj = new domain();
      if (!isset(static::$emulated_functions['bindtextdomain'])) {
        $currentDirectory = bindtextdomain($domain, null);
        if ($currentDirectory !== false) {
          $domainObj->path = $currentDirectory;
        }
      }
      if (!isset(static::$emulated_functions['bind_textdomain_codeset'])) {
        $currentCodeset = bind_textdomain_codeset($domain, null);
        if (is_string($currentCodeset)) {
          $domainObj->codeset = $currentCodeset;
        }
      }
      static::$text_domains[$domain] = $domainObj;
      return 1;
    }
    return 0;
  }
}
