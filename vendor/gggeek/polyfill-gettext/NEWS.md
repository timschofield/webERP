### Release 2.0.0-beta1, 2025-7-20

* moved the code from bazaar on https://launchpad.net/php-gettext to git at GitHub
* added Composer package definition
* introduced namespace `PGetText`
* ported the code to Psr autoloading
* moved class members and methods to private/protected, in accord with existing phpdoc comments
* removed global variables and functions in favour of new class `PGetText\T`, decluttering the global namespace
  (see the table below for changes)
* introduced Continuous Integration: tests are run on every Commit and Pull Request, on all supported PHP versions
* fixed the charset conversion for when mbstring is enabled but no `mb_internal_encoding` is set
* made it possible to use `setlocale()` instead of `_setlocale` or `T_setlocale` to init the locale

#### Table of changed/removed classes, global functions and global variables

| Type       | Name                       | Replacement                            | Notes                         |
|------------|----------------------------|----------------------------------------|-------------------------------|
| class      | domain                     | PGetText\domain                        |                               |
| class      | gettext_reader             | PGetText\gettext_reader                |                               |
| class      | CachedFileReader           | PGetText\Streams\CachedFileReader      |                               |
| class      | FileReader                 | PGetText\Streams\FileReader            |                               |
| class      | StreamReader               | PGetText\Streams\StreamReader          |                               |
| class      | StringReader               | PGetText\Streams\StringReader          |                               |
|            |                            |                                        |                               |
| function   | __                         | PGetText\T::__                         |                               |
| function   | _bindtextdomain            | PGetText\T::_bindtextdomain            |                               |
| function   | _bind_textdomain_codeset   | PGetText\T::_bind_textdomain_codeset   |                               |
| function   | _check_locale_and_function | PGetText\T::_check_locale_and_function | protected                     |
| function   | _dcgettext                 | PGetText\T::_dcgettext                 |                               |
| function   | _dcngettext                | PGetText\T::_dcngettext                |                               |
| function   | _dcnpgettext               | PGetText\T::_dcnpgettext               |                               |
| function   | _dcpgettext                | PGetText\T::_dcpgettext                |                               |
| function   | _dgettext                  | PGetText\T::_dgettext                  |                               |
| function   | _dngettext                 | PGetText\T::_dngettext                 |                               |
| function   | _dnpgettext                | PGetText\T::_dnpgettext                |                               |
| function   | _dpgettext                 | PGetText\T::_dpgettext                 |                               |
| function   | _encode                    | PGetText\T::_encode                    | protected                     |
| function   | _get_codeset               | PGetText\T::_get_codeset               | protected                     |
| function   | _get_default_locale        | PGetText\T::_get_default_locale        | protected                     |
| function   | _get_reader                | PGetText\T::_get_reader                | protected                     |
| function   | _gettext                   | PGetText\T::_gettext                   |                               |
| function   | _ngettext                  | PGetText\T::_ngettext                  |                               |
| function   | _npgettext                 | PGetText\T::_npgettext                 |                               |
| function   | _pgettext                  | PGetText\T::_pgettext                  |                               |
| function   | _setlocale                 | (none)                                 |                               |
| function   | _textdomain                | PGetText\T::_textdomain                |                               |
| function   | get_list_of_locales        | PGetText\T::get_list_of_locales        |                               |
| function   | locale_emulation           | PGetText\T::locale_emulation           |                               |
| function   | T_                         | PGetText\T::_                          |                               |
| function   | T_bindtextdomain           | PGetText\T::bindtextdomain             |                               |
| function   | T_bind_textdomain_codeset  | PGetText\T::bind_textdomain_codeset    |                               |
| function   | T_dcgettext                | PGetText\T::dcgettext                  |                               |
| function   | T_dcngettext               | PGetText\T::dcngettext                 |                               |
| function   | T_dcnpgettext              | PGetText\T::dcnpgettext                |                               |
| function   | T_dcpgettext               | PGetText\T::dcpgettext                 |                               |
| function   | T_dgettext                 | PGetText\T::dgettext                   |                               |
| function   | T_dngettext                | PGetText\T::dngettext                  |                               |
| function   | T_dnpgettext               | PGetText\T::dnpgettext                 |                               |
| function   | T_dpgettext                | PGetText\T::dpgettext                  |                               |
| function   | T_gettext                  | PGetText\T::gettext                    |                               |
| function   | T_ngettext                 | PGetText\T::ngettext                   |                               |
| function   | T_npgettext                | PGetText\T::npgettext                  |                               |
| function   | T_pgettext                 | PGetText\T::pgettext                   |                               |
| function   | T_setlocale                | PGetText\T::setlocale                  |                               |
| function   | T_textdomain               | PGetText\T::textdomain                 |                               |
|            |                            |                                        |                               |
| global var | $text_domains              | PGetText\T::$text_domains              | protected static class member |
| global var | $default_domain            | PGetText\T::$default_domain            | protected static class member |
| global var | $LC_CATEGORIES             | PGetText\T::$LC_CATEGORIES             | protected static class member |
| global var | $EMULATEGETTEXT            | PGetText\T::$emulate_locales           | protected static class member |
| global var | $CURRENTLOCALE             | PGetText\T::$current_locale            | protected static class member |
