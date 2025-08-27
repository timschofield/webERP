<?php

include_once __DIR__ . '/EmulationTest.php';

/**
 * Tests checking the conformity of the emulated calls with the php native extension - gettext setup
 */
class EmulationSetupTest extends EmulationTest
{
  /**
   * @dataProvider textdomain_provider
   */
  public function test_textdomain($domain)
  {
    if (!extension_loaded('gettext')) {
      $this->markTestSkipped('this test requires the gettext extension');
    }
    $ret = testable_T::_textdomain($domain);
    $eret = textdomain($domain);
    $this->assertEquals($eret, $ret);
  }

  public function textdomain_provider()
  {
    $cases = array(
      array(null),
      array(-1),
      array(-10),
      array(1),
      array(10),
      /// @todo fix these cases - raise + expect an error
      //array(array()),
      //array(new stdClass()),
      array('messages'),
      array('!"£$%^&*()_+-=[]{};\'#,./<>?\\|`¬'),
    );
    if (version_compare(PHP_VERSION, '8.4.0', '<')) {
      /// @todo php 8.4 raises a valueError for these - reintroduce them plus set an error expectation
      $cases[] = array('');
      $cases[] = array(0);
      $cases[] = array('0');
    }
    return $cases;
  }

  /**
   * @dataProvider bindtextdomain_provider
   */
  public function test_bindtextdomain($domain, $directory)
  {
    if (!extension_loaded('gettext')) {
      $this->markTestSkipped('this test requires the gettext extension');
    }
    $ret = testable_T::_bindtextdomain($domain, $directory);
    $eret = bindtextdomain($domain, $directory);
    $this->assertEquals($eret, $ret);
  }

  public function bindtextdomain_provider()
  {
    $directory = __DIR__ . '/../examples/locale';

    $textDomains = array(
      /// @todo fix these cases - raise + expect an error
      //null,
      //'',
      -1,
      0,
      '0',
      1,
      /// @todo fix these cases - raise + expect an error `textdomain(): Argument #1 ($domain) must be of type ?string, array given`
      //array(),
      //new stdClass(),
      'xxx-XXX',
      'C'
    );
    $directories = array(
      null,
      '',
      '   ',
      -1,
      0,
      '0',
      1,
      /// @todo fix these cases - expect an error?
      //array(),
      //new stdClass(),
      '/tmp/not-existent-directory',
      $directory,
      $directory . DIRECTORY_SEPARATOR,
      $directory . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR,
    );

    $sets = array();
    foreach($textDomains as $textDomain) {
      foreach ($directories as $directory) {
        $sets[] = array($textDomain, $directory);
      }
    }
    return $sets;
  }

  /**
   * @dataProvider bind_textdomain_codeset_provider
   */
  public function test_bind_textdomain_codeset($domain, $codeset)
  {
    if (!extension_loaded('gettext')) {
      $this->markTestSkipped('this test requires the gettext extension');
    }
    $ret = testable_T::_bind_textdomain_codeset($domain, $codeset);
    $eret = bind_textdomain_codeset($domain, $codeset);
    $this->assertEquals($eret, $ret);
  }

  public function bind_textdomain_codeset_provider()
  {
    $textDomains = array(
      -1,
      0,
      '0',
      1,
      /// @todo fix these cases - raise + expect an error `textdomain(): Argument #1 ($domain) must be of type ?string, array given`
      //array(),
      //new stdClass(),
      'xxx-XXX',
      'C'
    );
    if (version_compare(PHP_VERSION, '8.4.0', '<')) {
      /// @todo php 8.4 raises a valueError for these - reintroduce them plus set an error expectation
      $textDomains[] = null;
      $textDomains[] = '';
    }
    $codesets = array(
      null,
      '',
      -1,
      0,
      '0',
      1,
      /// @todo fix these cases - expect an error
      //array(),
      //new stdClass(),
      'xxx-XXX',
      'UTF-8',
      'iso-8859-1'
    );

    $sets = array();
    foreach($textDomains as $textDomain) {
      foreach ($codesets as $codeset) {
        $sets[] = array($textDomain, $codeset);
      }
    }
    return $sets;
  }
}
