<?php

include_once __DIR__ . '/PolyfillTestCase.php';

use PGettext\T;

class LocaleTest extends PGettext_PolyfillTestCase
{
  public function test_setlocale_by_env_var()
  {
    if (!function_exists('setlocale') || T::emulate_function('setlocale', null)) {
      $this->markTestSkipped('no native setlocale function found');
    }
    // T::setlocale defaults to a locale name from environment variable LANG - as long as the native setlocale method
    // returns false.
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
    $this->assertNotEquals($locale, setlocale(5, 0));
    $this->assertEquals(true, T::locale_emulation());
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
