<?php

include_once __DIR__ . '/PolyfillTestCase.php';
include_once __DIR__ . '/testable_T.php';

abstract class EmulationTest extends PGettext_PolyfillTestCase
{
  static $original_domain;
  static $original_directory;
  static $original_codeset;

  /**
   * Method executed once before all the class' tests - record original gettext status
   * @return void
   */
  public static function set_up_before_class()
  {
    parent::set_up_before_class();

    if (extension_loaded('gettext')) {
      static::$original_domain = textdomain(null);
      static::$original_directory = bindtextdomain(static::$original_domain, null);
      static::$original_codeset = bind_textdomain_codeset(static::$original_domain, null);
    }
  }

  /**
   * Method once after all the class' tests - reset gettext status to its pristine version
   * @return void
   */
  public static function tear_down_after_class()
  {
    if (extension_loaded('gettext')) {
      textdomain(static::$original_domain);
      bindtextdomain(static::$original_domain, static::$original_directory);
      bind_textdomain_codeset(static::$original_domain, static::$original_codeset);
    }

    parent::tear_down_after_class();
  }

  /**
   * Method executed before each of the class' tests - reset gettext status to its pristine version
   * @return void
   */
  public function set_up()
  {
    parent::set_up();

    if (extension_loaded('gettext')) {
      textdomain(static::$original_domain);
      bindtextdomain(static::$original_domain, static::$original_directory);
      bind_textdomain_codeset(static::$original_domain, static::$original_codeset);
    }

    testable_T::resetDomains();
  }
}
