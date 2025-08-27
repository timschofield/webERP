<?php

include_once __DIR__ . '/EmulationTest.php';

/**
 * Tests checking the conformity of the emulated calls with the php native extension - gettext usage
 */
class EmulationTranslateTest extends EmulationTest
{
  // use translations from the examples - we are sure those exist
  static $test_locale = 'de_CH';
  static $test_domain = 'messages';
  static $test_directory;
  static $test_charset = 'UTF-8';

  public static function set_up_before_class()
  {
    parent::set_up_before_class();

    // sadly we can not initialize this directly, at least in php 5.3
    static::$test_directory = __DIR__ . '/../examples/locale';
  }

  public function set_up()
  {
    parent::set_up();

    testable_T::textdomain(static::$test_domain);
    testable_T::bindtextdomain(static::$test_domain, static::$test_directory);
    testable_T::bind_textdomain_codeset(static::$test_domain, static::$test_charset);
  }

  /**
   * @dataProvider dcgettext_provider
   */
  public function test_dcgettext($domain, $msgid, $category) {
    if (!extension_loaded('gettext')) {
      $this->markTestSkipped('this test requires the gettext extension');
    }
    $ret = testable_T::_dcgettext($domain, $msgid, $category);
    $eret = dcgettext($domain, $msgid, $category);
    $this->assertEquals($eret, $ret);
  }

  public function dcgettext_provider() {
    return array(

    );
  }

  /**
   * @dataProvider dcngettext_provider
   */
  public function test_dcngettext($domain, $singular, $plural, $number, $category) {
    if (!extension_loaded('gettext')) {
      $this->markTestSkipped('this test requires the gettext extension');
    }
    $ret = testable_T::_dcngettext($domain, $singular, $plural, $number, $category);
    $eret = dcngettext($domain, $singular, $plural, $number, $category);
    $this->assertEquals($eret, $ret);
  }

  public function dcngettext_provider() {
    return array(

    );
  }

  /**
   * @dataProvider dgettext_provider
   */
  public function test_dgettext($domain, $message) {
    if (!extension_loaded('gettext')) {
      $this->markTestSkipped('this test requires the gettext extension');
    }
    $ret = testable_T::_dgettext($domain, $message);
    $eret = dgettext($domain, $message);
    $this->assertEquals($eret, $ret);
  }

  public function dgettext_provider() {
    return array(

    );
  }

  /**
   * @dataProvider dngettext_provider
   */
  public function test_dngettext($domain, $singular, $plural, $number) {
    if (!extension_loaded('gettext')) {
      $this->markTestSkipped('this test requires the gettext extension');
    }
    $ret = testable_T::_dngettext($domain, $singular, $plural, $number);
    $eret = dngettext($domain, $singular, $plural, $number);
    $this->assertEquals($eret, $ret);
  }

  public function dngettext_provider() {
    return array(

    );
  }

  /**
   * @dataProvider gettext_provider
   */
  public function test_gettext($message) {
    if (!extension_loaded('gettext')) {
      $this->markTestSkipped('this test requires the gettext extension');
    }
    $ret = testable_T::_gettext($message);
    $eret = gettext($message);
    $this->assertEquals($eret, $ret);
  }

  public function gettext_provider() {
    return array(

    );
  }

  /**
   * @dataProvider ngettext_provider
   */
  public function test_ngettext($singular, $plural, $count) {
    if (!extension_loaded('gettext')) {
      $this->markTestSkipped('this test requires the gettext extension');
    }
    $ret = testable_T::_ngettext($singular, $plural, $count);
    $eret = ngettext($singular, $plural, $count);
    $this->assertEquals($eret, $ret);
  }

  public function ngettext_provider() {
    return array(

    );
  }
}
