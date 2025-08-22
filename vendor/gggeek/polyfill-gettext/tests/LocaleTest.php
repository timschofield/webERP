<?php

include_once __DIR__ . '/PolyfillTestCase.php';

use PGettext\T;

/**
 * Tests checking code dealing with setting the locale
 */
class LocaleTest extends PGettext_PolyfillTestCase
{
  /**
   * @return string[]|false
   */
  protected function list_system_locales()
  {
    if (PHP_OS === 'WINNT') {
      /// @todo ... retrieve the list of available locales
    } else {
      // for unix-like systems, get the list of available locales using `locale -a`
      exec('which locale', $output, $retcode);
      if ($retcode == 0) {
        $output = array();
        exec('locale -a', $output, $retcode);
        if ($retcode == 0) {
          return $output;
        }
      }
    }
    return false;
  }

  /**
   * Returns the first system-installed locale which is not C or POSIX
   * @return false|string
   */
  protected function get_system_available_locale()
  {
    if ($locales = $this->list_system_locales()) {
      foreach ($locales as $locale) {
        if (!in_array($locale, array('C', 'C.utf8', 'POSIX'))) {
          return $locale;
        }
      }
    }
    return false;
  }

  public function test_setlocale_by_env_var()
  {
    if (!function_exists('setlocale') || T::emulate_function('setlocale', null)) {
      $this->markTestSkipped('no native setlocale function found');
    }

    $locale = 'C.utf8';
    putenv("LC_ALL=");
    putenv("LANG=$locale");
    if (setlocale(LC_MESSAGES, '') === false) {
      $this->markTestSkipped('native setlocale function failed setting locale from env var');
    }

    $this->assertEquals($locale, T::setlocale(LC_MESSAGES, 0));
    $this->assertEquals('LC_MESSAGES=' . $locale, T::setlocale(LC_ALL, 0));
  }

  public function test_setlocale_system()
  {
    /// @todo figure out which locale is available by default on windows
    if (PHP_OS === 'WINNT') {
      $this->markTestSkipped('ToDo: figure out a locale which is always installed on Windows');
    }
    // For an existing locale, it never needs emulation.
    $locale = 'C';
    T::setlocale(LC_MESSAGES, $locale);
    $this->assertEquals($locale, T::setlocale(LC_MESSAGES, 0));
    $this->assertEquals('LC_MESSAGES=' . $locale, T::setlocale(LC_ALL, 0));
    $this->assertEquals($locale, setlocale(LC_MESSAGES, 0));
    $this->assertEquals(!extension_loaded('gettext'), T::locale_emulation());
  }

  public function test_setlocale_emulation()
  {
    // If we set it to a non-existent locale, it still works, but uses emulation.
    $locale = 'xxx_XXX';
    T::setlocale(LC_MESSAGES, $locale);
    $this->assertEquals($locale, T::setlocale(LC_MESSAGES, 0));
    $this->assertEquals('LC_MESSAGES=' . $locale, T::setlocale(LC_ALL, 0));
    // nb: setlocale is available even when the gettext extension is disabled
    $this->assertNotEquals($locale, setlocale(5, 0));
    $this->assertEquals(true, T::locale_emulation());
  }

  public function test_setlocale_array()
  {
    $locale = $this->get_system_available_locale();
    if (!$locale) {
      $this->markTestSkipped('found no system-installed locales except for C and POSIX');
    }
    T::setlocale(LC_MESSAGES, 'xxx_XXX', $locale);
    $this->assertEquals($locale, T::setlocale(LC_MESSAGES, 0));
    $this->assertEquals('LC_MESSAGES=' . $locale, T::setlocale(LC_ALL, 0));
    // nb: setlocale is available even when the gettext extension is disabled
    $this->assertEquals($locale, setlocale(5, 0));
    $this->assertEquals(!extension_loaded('gettext'), T::locale_emulation());
  }

  public function test_setlocale_wrong_category()
  {
    if (! is_callable(array($this, 'expectWarning'))) {
      $this->markTestSkipped('current phpunit version has no support for testCase::expectWarning');
    }
    $this->expectWarning();
    $ret = T::setlocale(1, 'C');
    $this->assertEquals(false, $ret);
  }

  public function test_get_list_of_locales()
  {
    // For a locale containing country code, we prefer full locale name, but if that's not found, fall back
    // to the language only locale name.
    $this->assertEquals(array("sr_RS", "sr"),
      T::get_list_of_locales("sr_RS"));

    // If language code is used, it's the only thing returned.
    $this->assertEquals(array("sr"),
      T::get_list_of_locales("sr"));

    // There is support for language and charset only.
    $this->assertEquals(array("sr.UTF-8", "sr"),
      T::get_list_of_locales("sr.UTF-8"));

    // It can also split out character set from the full locale name.
    $this->assertEquals(array("sr_RS.UTF-8", "sr_RS", "sr"),
      T::get_list_of_locales("sr_RS.UTF-8"));

    // There is support for @modifier in locale names as well.
    $this->assertEquals(array("sr_RS.UTF-8@latin", "sr_RS@latin", "sr@latin", "sr_RS.UTF-8", "sr_RS", "sr"),
      T::get_list_of_locales("sr_RS.UTF-8@latin"));

    // We can pass in only language and modifier.
    $this->assertEquals(array("sr@latin", "sr"),
      T::get_list_of_locales("sr@latin"));

    // If locale name is not following the regular POSIX pattern,
    // it's used verbatim.
    $this->assertEquals(array("something"),
      T::get_list_of_locales("something"));

    // Passing in an empty string returns an empty array.
    $this->assertEquals(array(),
      T::get_list_of_locales(""));
  }
}
