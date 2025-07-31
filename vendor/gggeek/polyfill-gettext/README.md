# Polyfill-Gettext

A pure-php implementation of the API provided by the [PHP gettext extension](https://www.php.net/manual/en/book.gettext.php).

Evolved from the php-gettext codebase available at https://launchpad.net/php-gettext.

[![License](https://poser.pugx.org/gggeek/polyfill-gettext/license)](https://packagist.org/packages/gggeek/polyfill-gettext)
[![Latest Stable Version](https://poser.pugx.org/gggeek/polyfill-gettext/v/stable)](https://packagist.org/packages/gggeek/polyfill-gettext)
[![Total Downloads](https://poser.pugx.org/gggeek/polyfill-gettext/downloads)](https://packagist.org/packages/gggeek/polyfill-gettext)

[![Build Status](https://github.com/gggeek/polyfill-gettext/actions/workflows/ci.yaml/badge.svg)](https://github.com/gggeek/polyfill-gettext/actions/workflows/ci.yaml)
[![Code Coverage](https://codecov.io/gh/gggeek/polyfill-gettext/branch/master/graph/badge.svg)](https://app.codecov.io/gh/gggeek/phpxmlrpc)

Licensed under the GPLv2 (or any later version), see [LICENSE](LICENSE)

Copyright 2003, 2006, 2009 -- Danilo "angry with PHP[1]" Segan.


## Introduction

Polyfill-Gettext implements an API for internationalization of your php application, i.e. it provides methods which can
be used to translate to different languages the strings displayed by the user interface.

Translations are read from GNU gettext MO files. Those are binary containers for translations, produced by e.g. GNU msgfmt.

Note that this library does not provide anything to create, edit or manage the translation files, however it should not
be too hard to find tutorials on how to do that using a variety of tools and accommodating different workflows.


## Features

* Support for simple translations

* Support for `ngettext` calls (plural forms).

  You may also use plural forms. Translations in MO files need to provide this, and they must also provide "plural-forms"
  header. Please see `info gettext` for more details.

* Support for reading translations from straight files, or strings.

  Since different backends can conceivably be used for storing and reading in the MO file data, a class implementing
  `StreamReaderInterface` can be provided to handle all the input. For your convenience, two classes for reading files
  are already implemented: `FileReader` and `StringReader` (`CachedFileReader` being a combination of the two: it
  loads entire file contents into a string, and then works on that). You can for instance use `StringReader`
  when you read in data from a database, or you can create your own  implementation of `StreamReaderInterface` for
  anything you like.

  See the example below for more details.


## Installation

Install the library using Composer, then be sure to require the Composer autoloader in your code before calling any
gettext function.


## Usage

### Standard gettext API emulation

Basically, you will be able to use in your code all the standard gettext functions documented on:

       https://www.php.net/gettext

regardless of the fact that the php gettext extension is available and enabled (via php ini settings) or not.

This makes the polyfill a useful inclusion for every php application which might see widespread usage on shared hosting,
where there is often little control over php configuration and the installed extensions.

The only improvement you might want to make to your code is to check the return value of the `setlocale()` call to see
if the chosen locale is system supported or not. If it is not, making a call to `PGettext\T::setlocale($locale)` will make
translation calls work in any case - but only if the native php gettext extension is disabled.

See the example file `examples/pigs_dropin.php` for more details.

### Usage as fallback for unsupported locales

By using the functions provided by this library instead of the gettext API, your translations will be used via gettext
emulation whenever:
* either the php gettext extension is not available,
* or the php gettext extension is enabled, but _the desired locale is not installed in the system_.

This is an improvement over the API emulation usage described above, as it allows to use gettext functions regardless
of the fact that the desired locale has been installed on the system (as a reminder: for gettext to work, the locales
to be used have to be manually installed on the system. This is achieved separately from php setup/configuration).

Note that, if the php gettext extension is available, and the desired locale is installed in the system, the native
gettext functions will be used transparently, for maximum execution speed.

See the example file `examples/pigs_fallback.php` for more details.

### Managing the translation files (.mo, .po)

[To be documented...]

### Customizing library usage

#### Providing translation files from custom storage

You can create custom 'stream reader' classes (a class that implements `StreamReaderInterface`) which will provide data
for the `gettext_reader`, and/or custom 'reader' classes (a class that implements `ReaderInterface`).

After having created your custom classes, you can make use of them, in various ways.

The shortest version:

Set the names of your classes to `T::$reader_class` and/or `T::$stream_reader_class`. Done! Note that this requires
compatibility of your new classes constructor arguments with the existing ones.

The medium version:

Create a subclass of `Pgettext\T`, and override its methods `T::build_reader` and `T::build_stream_reader`. Use your
subclass in translation calls. Note that this does not support transparent emulation of the php native extension functions.

The long version:

Create one `StreamReaderInterface` instance which will provide data for the `ReaderInterface`, with eg.

    $streamer = new MyFileStream('data.mo');

Then, use that as a parameter to reader constructor:

    $wohoo = new MyGettextReader($streamer, $whatever, $args...);

If you want to disable pre-loading of entire message catalog in memory (if, for example, you have a multi-thousand
message catalog which you'll use only occasionally), use `false` for the second parameter to the gettext_reader constructor:

    $wohoo = new PGettext\gettext_reader($streamer, false);

From now on, you have all the benefits of gettext data at your disposal, so may run:

    print $wohoo->translate("This is a test");
    print $wohoo->ngettext("%d bird", "%d birds", $birds);

You might need to pass parameter `-k` to `xgettext` to make it extract all the strings from the php source code.
For the above example, try with

    xgettext -ktranslate -kngettext:1,2 file.php

That should create `messages.po` which contains two messages for translation.

I suggest creating simple aliases for those functions.


## Examples

See in the `examples/` subdirectory, there are a couple of files. `pigs_dropin.php` and `pigs_fallback.php` are example
usages, `locale/xx_XX/LC_MESSAGES/messages.po` is a translation for each language, and `messages.mo` is the
corresponding binary version, generated with

    msgfmt -o messages.mo messages.po

There is also a simple `update.sh` script that can be used to generate the PO and MO files via calls to `xgettext` and `msgfmt`.


## Known limitations

The following are known limitations in the emulation of the native gettext extension API:

* not all warnings / exceptions are emulated, which would be thrown by the native gettext extension, when invalid
  parameters are passed to functions such as f.e. passing an empty string to `textdomain` calls


## Bugs

Report bugs and feature requests at https://github.com/gggeek/polyfill-gettext/issues


## Todo

* support other means than using mbstring of converting between character sets

* expand the test suite and improve the emulation of the native gettext API to cover errors, returned values, support
  for all environment variables, etc...

* Improve speed

* Try to use hash tables in MO files: with pre-loading, would it be useful at all?


## Frequently asked questions

* Why yet another reimplementation of the gettext extension API?

  Because at the time I was looking for one, I did not find any that fit the bill.

  The original php-gettext package has not seen any commit or release since 2015.

  On github, there is https://github.com/smmoosavi/php-gettext, but it does not attempt to be a transparent drop-in.

  Same goes for all the packages which can be found on Packagist while searching for 'gettext', such as
  https://github.com/php-gettext/Gettext.

  After starting this, in July 2025, I found out the https://github.com/phpmyadmin/motranslator project, which does in
  fact stay close to the original php-gettext functionality, and might be a good candidate to use instead of this library.
  The main differences, as far as I can tell from a cursory analysis, are: it does require php 8.2, and
  symfony/expression-language, while we are self-contained and require php 5.3 or later, and it does not automatically
  reimplement the php gettext api when the extension is not loaded, registering instead the same functions prefixed with
  `_`.


## Original readme

See [README_original.md](README_original.md)
