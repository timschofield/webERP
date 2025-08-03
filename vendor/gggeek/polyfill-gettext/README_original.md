# php-gettext

A pure-php implementation of the API provided by the
[PHP gettext extension](https://www.php.net/manual/en/book.gettext.php).

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
  file data, a class implementing `StreamReaderInterface` has to be provided
  to do all the input. For your convenience, I've already
  implemented two classes for reading files: `FileReader` and
  `StringReader` (`CachedFileReader` is a combination of the two: it
  loads entire file contents into a string, and then works on that).
  See the example below for usage. You can for instance use `StringReader`
  when you read in data from a database, or you can create your own
  implementation of StreamReaderInterface for anything you like.

## Examples

See in the `examples/` subdirectory. There are a couple of files.
`pigs_dropin.php` and `pigs_fallback.php` are example usages,
`locale/xx_XX/LC_MESSAGES/messages.po` is a translation for each language,
and `messages.mo` is the corresponding binary version, generated with

    msgfmt -o messages.mo messages.po

There is also a simple `update.sh` script that can be used to generate the
PO and MO files via calls to xgettext and msgfmt.

## TODO

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
