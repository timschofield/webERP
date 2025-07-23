# Polyfill-Gettext

A pure-php implementation of the API provided by the [PHP gettext extension](https://www.php.net/manual/en/book.gettext.php).

Evolved from the php-gettext codebase available at https://launchpad.net/php-gettext.

[![License](https://poser.pugx.org/gggeek/polyfill-gettext/license)](https://packagist.org/packages/gggeek/polyfill-gettext)
[![Latest Stable Version](https://poser.pugx.org/gggeek/polyfill-gettext/v/stable)](https://packagist.org/packages/gggeek/polyfill-gettext)
[![Total Downloads](https://poser.pugx.org/gggeek/polyfill-gettext/downloads)](https://packagist.org/packages/gggeek/polyfill-gettext)

[![Build Status](https://github.com/gggeek/polyfill-gettext/actions/workflows/ci.yaml/badge.svg)](https://github.com/gggeek/polyfill-gettext/actions/workflows/ci.yaml)
[![Code Coverage](https://codecov.io/gh/gggeek/polyfill-gettext/branch/master/graph/badge.svg)](https://app.codecov.io/gh/gggeek/phpxmlrpc)

# Original Readme follows (now updated to take into account recent API changes)

Copyright 2003, 2006, 2009 -- Danilo "angry with PHP[1]" Segan
Licensed under GPLv2 (or any later version, see COPYING)

[1] PHP is actually cyrillic, and translates roughly to
"works-doesn't-work" (UTF-8: Ради-Не-Ради)

## Introduction

How many times did you look for a good translation tool, and
found out that gettext is best for the job? Many times.

How many times did you try to use gettext in PHP, but failed
miserably, because either your hosting provider didn't support
it, or the server didn't have adequate locale? Many times.

Well, this is a solution to your needs. It allows using gettext
tools for managing translations, yet it doesn't require the gettext
php extension at all. It parses generated MO files directly, and thus
might be a bit slower than the (maybe provided) gettext library.

Polyfill-Gettext is a simple reader for GNU gettext MO files. Those
are binary containers for translations, produced by GNU msgfmt.

## Why?

I got used to having gettext work even without gettext
library. It's there in my favourite language Python, so I was
surprised that I couldn't find it in PHP. I even Googled for it,
but to no avail.

So, I said, what the heck, I'm going to write it for this
disgusting language of PHP, because I'm often constrained to it.

## Features

* Support for simple translations

* Support for `ngettext` calls (plural forms, see a note under bugs)
  You may also use plural forms. Translations in MO files need to
  provide this, and they must also provide "plural-forms" header.
  Please see `info gettext` for more details.

* Support for reading translations from straight files, or strings (!!!)
  Since I can imagine many different backends for reading in the MO
  file data, a class implementing `StreamReaderInterface` has to be provided to do all
  the input. For your convenience, I've already
  implemented two classes for reading files: `FileReader` and
  `StringReader` (`CachedFileReader` is a combination of the two: it
  loads entire file contents into a string, and then works on that).
  See the example below for usage. You can for instance use `StringReader`
  when you read in data from a database, or you can create your own
  implementation of StreamReaderInterface for anything you like.

## Bugs

Report them at https://github.com/gggeek/polyfill-gettext/issues

## Usage

Install the library using Composer, then be sure to require the Composer autoloader in your code.

### Standard gettext interface emulation

Check the example in `examples/pigs_dropin.php`.

Basically, you can use all the standard gettext interfaces as documented on:

       https://www.php.net/gettext

The only catch is that you can check the return value of `setlocale()` to see if your locale is system supported or not.
If it is not, and the native gettext extension is disabled, adding a call to `PgetText\T::setlocale()` will make
translations work in any case. See the `pigs_dropin.php` example file for more details.

### Usage as fallback for unsupported locales

Check the example file `examples/pigs_fallback.php`.

By using the functions provided by this library instead of the gettext API, your translations will be used via gettext
emulation whenever the php gettext extension is not available or the desired locale is not installed in the system.
If the php gettext extension is available, and the desired locale is installed in the system, the native gettext function
will be used transparently for maximum execution speed.

### Custom library usage

Create one 'stream reader' (a class that implements `StreamReaderInterface`) which will
provide data for the `gettext_reader`, with eg.

    $streamer = new FileStream('data.mo');

Then, use that as a parameter to gettext_reader constructor:

    $wohoo = new PGetText\gettext_reader($streamer);

If you want to disable pre-loading of entire message catalog in
memory (if, for example, you have a multi-thousand message catalog
which you'll use only occasionally), use `false` for second
parameter to gettext_reader constructor:

    $wohoo = new PGetText\gettext_reader($streamer, false);

From now on, you have all the benefits of gettext data at your
disposal, so may run:

    print $wohoo->translate("This is a test");
    print $wohoo->ngettext("%d bird", "%d birds", $birds);

You might need to pass parameter `-k` to `xgettext` to make it
extract all the strings. In above example, try with

    xgettext -ktranslate -kngettext:1,2 file.php

That should create `messages.po` which contains two messages for
translation.

I suggest creating simple aliases for these functions.

## Examples

See in the `examples/` subdirectory. There are a couple of files.
`pigs_dropin.php` and `pigs_fallback.php` are example usages, `locale/xx_XX/LC_MESSAGES/messages.po` is a translation for
each language, and `messages.mo` is the corresponding binary version, generated with

    msgfmt -o messages.mo messages.po

There is also a simple `update.sh` script that can be used to generate the
PO and MO files via calls to xgettext and msgfmt.

## TODO

* support other means than using mbstring of converting between character sets

* expand the test suite and improve the emulation of the native gettext API to cover errors, returned values, support
  for all environment variables, etc...

* Improve speed to be even more comparable to the native gettext
  implementation.

* Try to use hash tables in MO files: with pre-loading, would it
  be useful at all?

## Never-asked-questions

* Why did you mark this as version 1.0 when this is the first code
  release?

  Well, it's quite simple. I consider that the first released thing
  should be labeled "version 1" (first, right?). Zero is there to
  indicate that there's zero improvement and/or change compared to
  "version 1".

  I plan to use version numbers 1.0.* for small bugfixes, and to
  release 1.1 as "first stable release of version 1".

  This may trick someone that this is actually useful software, but
  as with any other free software, I take NO RESPONSIBILITY for
  creating such a masterpiece that will smoke crack, trash your
  hard disk, and make lasers in your CD device dance to the tune of
  Mozart's 40th Symphony (there is one like that, right?).

* Can I...?

  Yes, you can. This is free software (as in freedom, free speech),
  and you might do whatever you wish with it, provided you do not
  limit freedom of others (GPL).

  I'm considering licensing this under LGPL, but I *do* want
  *every* PHP-gettext user to contribute and respect ideas of free
  software, so don't count on it happening anytime soon.

  I'm sorry that I'm taking away your freedom of taking others'
  freedom away, but I believe that's negligible as compared to what
  freedoms you could take away. ;-)

  Uhm, whatever.

* Why yet another reimplementation of the gettext API?

  Because at the time I was looking for one, I did not find any that fit the bill.

  The original php-gettext package has not seen any commit or release since 2015.

  On github, there is https://github.com/smmoosavi/php-gettext, but it does not attempt to be a transparent drop-in.

  Same goes for all the packages which can be found on Packagist while searching for 'gettext', such as
  https://github.com/php-gettext/Gettext.

  After starting this, in July 2025, I found out the https://github.com/phpmyadmin/motranslator project, which does in
  fact stay close to the original php-gettext functionality, and might be a good candidate to use instead of this library.
  The main differences, as far as I can tell from a cursory analysis, are: it does require php 8.2, and symfony/expression-language,
  while we are self-contained and require php 5.3 or later, and it does not automatically reimplement the php gettext
  api when the extension is not loaded, registering instead the same functions prefixed with `_`.
