<?php
/*
   Copyright (c) 2020 Sunil Mohan Adapa <sunil at medhas dot org>

   Drop in replacement for native gettext.

   This file is part of PHP-gettext.

   PHP-gettext is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   PHP-gettext is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with PHP-gettext; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

/*
Unicode plural data for various languages is under the Unicode License
Agreement taken from:
https://unicode-org.github.io/cldr-staging/charts/37/supplemental/language_plural_rules.html

UNICODE, INC. LICENSE AGREEMENT - DATA FILES AND SOFTWARE

See Terms of Use for definitions of Unicode Inc.'s Data Files and Software.

NOTICE TO USER: Carefully read the following legal agreement. BY DOWNLOADING,
INSTALLING, COPYING OR OTHERWISE USING UNICODE INC.'S DATA FILES ("DATA
FILES"), AND/OR SOFTWARE ("SOFTWARE"), YOU UNEQUIVOCALLY ACCEPT, AND AGREE TO
BE BOUND BY, ALL OF THE TERMS AND CONDITIONS OF THIS AGREEMENT. IF YOU DO NOT
AGREE, DO NOT DOWNLOAD, INSTALL, COPY, DISTRIBUTE OR USE THE DATA FILES OR
SOFTWARE.

COPYRIGHT AND PERMISSION NOTICE

Copyright Â© 1991-2020 Unicode, Inc. All rights reserved. Distributed under the
Terms of Use in https://www.unicode.org/copyright.html.

Permission is hereby granted, free of charge, to any person obtaining a copy
of the Unicode data files and any associated documentation (the "Data Files")
or Unicode software and any associated documentation (the "Software") to deal
in the Data Files or Software without restriction, including without
limitation the rights to use, copy, modify, merge, publish, distribute, and/or
sell copies of the Data Files or Software, and to permit persons to whom the
Data Files or Software are furnished to do so, provided that either (a) this
copyright and permission notice appear with all copies of the Data Files or
Software, or (b) this copyright and permission notice appear in associated
Documentation.

THE DATA FILES AND SOFTWARE ARE PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT OF THIRD
PARTY RIGHTS. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR HOLDERS INCLUDED IN
THIS NOTICE BE LIABLE FOR ANY CLAIM, OR ANY SPECIAL INDIRECT OR CONSEQUENTIAL
DAMAGES, OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR
PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS
ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THE
DATA FILES OR SOFTWARE.

Except as contained in this notice, the name of a copyright holder shall not
be used in advertising or otherwise to promote the sale, use or other dealings
in these Data Files or Software without prior written authorization of the
copyright holder.
*/

include_once __DIR__ . '/PolyfillTestCase.php';

class PluralsTest extends PGettext_PolyfillTestCase
{
  public function evaluations_provider() {
    return array(
      // Simple expressions
      array("1", array(0 => 1, 1 => 1, 2 => 1)),
      array("n", array(0 => 0, 1 => 1, 2 => 2)),
      array("!n", array(0 => 1, 1 => 0, 2 => 0)),
      array("n == 1", array(0 => 0, 1 => 1, 2 => 0)),
      array("n != 1", array(0 => 1, 1 => 0, 2 => 1)),
      array("n > 1", array(0 => 0, 1 => 0, 2 => 1)),
      array("n < 1", array(0 => 1, 1 => 0, 2 => 0)),
      array("n >= 1", array(0 => 0, 1 => 1, 2 => 1)),
      array("n <= 1", array(0 => 1, 1 => 1, 2 => 0)),
      array("n && 1", array(0 => 0, 1 => 1)),
      array("n && 0", array(0 => 0, 1 => 0)),
      array("n || 1", array(0 => 1, 1 => 1)),
      array("n || 0", array(0 => 0, 1 => 1)),
      array("n + 1", array(0 => 1, 1 => 2, 2 => 3)),
      array("n - 1", array(0 => -1, 1 => 0, 2 => 1)),
      array("n * 2", array(0 => 0, 1 => 2, 2 => 4)),
      array("n / 2", array(0 => 0, 1 => 0, 2 => 1)),
      array("n % 3", array(0 => 0, 1 => 1, 2 => 2, 3 => 0, 4 => 1)),
      array("n ? 1 : 2", array(0 => 2, 1 => 1)),
      array("n == 1 ? 0 : n == 2 ? 1 : 2", array(0 => 2, 1 => 0, 2 => 1, 3 => 2)),
      // Bambara, Burmese, Cantonese, Chinese, Dzongkha, Igbo, Indonesian,
      // Japanese, Javanese, Kabuverdianu, Khmer, Korean, Koyraboro Senni,
      // Lakota, Lao, Lojban, Makonde, Malay, Nâ€™Ko, Osage, Root, Sakha, Sango,
      // Sichuan Yi, Sundanese, Thai, Tibetan, Tongan, Vietnamese, Wolof,
      // Yoruba
      array("0", array(0 => 0, 1 => 0, 2 => 0)),
      // Cebuano, Filipino, Tagalog
      array("(n % 10 == 4) || (n % 10 == 6) || (n % 10 == 9) ? 1 : 0",
       array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 5 => 0, 7 => 0, 8 => 0, 10 => 0,
        11 => 0, 12 => 0, 13 => 0, 15 => 0, 17 => 0, 18 => 0, 20 => 0, 21 => 0,
        100 => 0, 1000 => 0, 10000 => 0, 100000 => 0, 1000000 => 0, 4 => 1,
        6 => 1, 9 => 1, 14 => 1, 16 => 1, 19 => 1, 24 => 1, 26 => 1, 104 => 1,
        1004 => 1)),
      // Central Atlas Tamazight
      array("((n == 0) || (n == 1) || (n >= 11 && n <= 99)) ? 0 : 1",
       array(0 => 0, 1 => 0, 11 => 0, 12 => 0, 98 => 0, 99 => 0, 2 => 1, 3 => 1,
        8 => 1, 9 => 1, 100 => 1, 101 => 1, 102 => 1, 111 => 1, 199 => 1)),
      // Icelandic, Macedonian
      array("(n % 10 == 1) && (n % 100 != 11) ? 0 : 1",
       array(1 => 0, 21 => 0, 31 => 0, 101 => 0, 121 => 0, 1001 => 0, 0 => 1,
        2 => 1, 10 => 1, 11 => 1, 111 => 1, 1000 => 1, 1011 => 1)),
      // Akan, Amharic, Armenian, Assamese, Bangla, Bhojpuri, French, Fulah,
      // Gujarati, Gun, Hindi, Kabyle, Kannada, Lingala, Malagasy, Nigerian
      // Pidgin, Northern Sotho, Persian, Portuguese, Punjabi, Sinhala,
      // Tigrinya, Walloon, Zulu
      array("(n == 0) || (n == 1) ? 0 : 1",
       array(0 => 0, 1 => 0, 2 => 1, 3 => 1, 10 => 1, 100 => 1, 1000 => 1)),
      // Afrikaans, Albanian, Aragonese, Asturian, Asu, Azerbaijani, Basque,
      // Bemba, Bena, Bodo, Bulgarian, Catalan, Central Kurdish, Chechen,
      // Cherokee, Chiga, Danish, Divehi, Dutch, English, Esperanto, Estonian,
      // European Portuguese, Ewe, Faroese, Finnish, Friulian, Galician,
      // Ganda, Georgian, German, Greek, Hausa, Hawaiian, Hungarian, Ido,
      // Interlingua, Italian, Jju, Kako, Kalaallisut, Kashmiri, Kazakh,
      // Kurdish, Kyrgyz, Luxembourgish, Machame, Malayalam, Marathi, Masai,
      // MetaÊ¼, Mongolian, Nahuatl, Nepali, Ngiemboon, Ngomba, North Ndebele,
      // Norwegian, Norwegian BokmÃ¥l, Norwegian Nynorsk, Nyanja, Nyankole,
      // Odia, Oromo, Ossetic, Papiamento, Pashto, Romansh, Rombo, Rwa, Saho,
      // Samburu, Sardinian, Sena, Shambala, Shona, Sicilian, Sindhi, Soga,
      // Somali, South Ndebele, Southern Kurdish, Southern Sotho, Spanish,
      // Swahili, Swati, Swedish, Swiss German, Syriac, Tamil, Telugu, Teso,
      // Tigre, Tsonga, Tswana, Turkish, Turkmen, Tyap, Urdu, Uyghur, Uzbek,
      // Venda, VolapÃ¼k, Vunjo, Walser, Western Frisian, Xhosa, Yiddish
      array("(n != 1)", array(0 => 1, 2 => 1, 3 => 1, 10 => 1, 100 => 1, 1 => 0)),
      // Latvian, Prussian
      array("n%10==1 && n%100!=11 ? 1 : (n % 10 == 0 || (n % 100 >= 11 && n % 100 <= 19)) ? 0 : 2",
       array(0 => 0, 10 => 0, 11 => 0, 12 => 0, 19 => 0, 20 => 0, 30 => 0,
        100 => 0, 110 => 0, 111 => 0, 119 => 0, 120 => 0, 1 => 1, 21 => 1,
        31 => 1, 101 => 1, 121 => 1, 2 => 2, 3 => 2, 22 => 2, 29 => 2,
        102 => 2, 109 => 2, 122 => 2)),
      // Colognian, Langi
      array("n == 0 ? 0 : n == 1 ? 1 : 2",
       array(0 => 0, 1 => 1, 2 => 2, 3 => 2, 10 => 2, 100 => 2, 1000 => 2)),
      // Inari Sami, Inuktitut, Lule Sami, Nama, Northern Sami, Sami languages
      // array(Other)), Santali, Skolt Sami, Southern Sami
      array("(n == 1) ? 0 : (n == 2) ? 1 : 2",
       array(0 => 2, 1 => 0, 2 => 1, 3 => 2, 100 => 2, 1000 => 2)),
      // Belarusian, Russian, Ukrainian
      array("n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2",
       array(1 => 0, 21 => 0, 31 => 0, 41 => 0, 101 => 0, 1001 => 0, 2 => 1,
        3 => 1, 4 => 1, 22 => 1, 24 => 1, 32 => 1, 102 => 1, 1002 => 1, 0 => 2,
        5 => 2, 11 => 2, 12 => 2, 13 => 2, 14 => 2, 15 => 2, 20 => 2, 25 => 2,
        100 => 2, 111 => 2, 1000 => 2)),
      // Polish
      array("n == 1 ? 0 : (n % 10 >= 2) && (n % 10 <= 4) && ((n % 100 < 12) || (n % 100 > 14)) ? 1 : 2",
       array(1 => 0, 2 => 1, 3 => 1, 4 => 1, 22 => 1, 23 => 1, 24 => 1, 32 => 1,
        33 => 1, 34 => 1, 102 => 1, 103 => 1, 104 => 1, 122 => 1, 123 => 1,
        124 => 1, 5 => 2, 6 => 2, 11 => 2, 12 => 2, 13 => 2, 20 => 2, 21 => 2,
        30 => 2, 31 => 2, 100 => 2, 101 => 2, 105 => 2, 111 => 2, 112 => 2,
        113 => 2, 121 => 2)),
      // Lithuanian
      array("(n % 10 == 1) && (n % 100 != 11) ? 0 : ((n % 10 >= 2) && (n % 10 <= 9) && ((n % 100 < 11) || (n % 100 > 19))) ? 1 : 2",
       array(1 => 0, 21 => 0, 31 => 0, 91 => 0, 101 => 0, 121 => 0, 2 => 1, 3 => 1,
        9 => 1, 22 => 1, 23 => 1, 29 => 1, 102 => 1, 103 => 1, 109 => 1,
        122 => 1, 129 => 1, 0 => 2, 10 => 2, 11 => 2, 19 => 2, 20 => 2,
        30 => 2, 40 => 2, 110 => 2, 111 => 2, 119 => 2, 120 => 2)),
      // Bosnian, Croatian, Serbian, Serbo-Croatian
      array("(n % 10 == 1) && (n % 100 != 11) ? 0 : (n % 10 >= 2) && (n % 10 <= 4) && ((n % 100 < 12) || (n % 100 > 14)) ? 1 : 2",
       array(1 => 0, 21 => 0, 31 => 0, 91 => 0, 101 => 0, 121 => 0, 2 => 1, 3 => 1,
        4 => 1, 22 => 1, 23 => 1, 24 => 1, 102 => 1, 103 => 1, 104 => 1,
        122 => 1, 124 => 1, 5 => 2, 6 => 2, 10 => 2, 11 => 2, 12 => 2, 13 => 2,
        14 => 2, 20 => 2, 25 => 2, 30 => 2, 100 => 2, 105 => 2, 110 => 2,
        111 => 2, 112 => 2, 113 => 2, 114 => 2, 120 => 2, 125 => 2, 130 => 2)),
      // Tachelhit
      array("n <= 1 ? 0 : (n >= 2 && n <= 10) ? 1 : 2",
       array(0 => 0, 1 => 0, 2 => 1, 3 => 1, 4 => 1, 9 => 1, 10 => 1, 11 => 2,
        12 => 2, 99 => 2, 100 => 2, 101 => 2, 102 => 2, 110 => 2)),
      // Moldavian, Romanian
      array("(n == 1 ? 0 : ((n == 0) || ((n % 100 >=2) && (n % 100 <= 19))) ? 1 : 2)",
       array(1 => 0, 0 => 1, 2 => 1, 3 => 1, 10 => 1, 11 => 1, 19 => 1, 102 => 1,
        119 => 1, 20 => 2, 21 => 2, 100 => 2, 101 => 2, 120 => 2, 121 => 2)),
      // Czech, Slovak
      array("n == 1 ? 0 : (n >= 2) && (n <= 4) ? 1 : 2",
       array(1 => 0, 2 => 1, 3 => 1, 4 => 1, 0 => 2, 5 => 2, 10 => 2, 11 => 2,
        100 => 2)),
      // Manx
      array("n % 10 == 1 ? 0 : n % 10 == 2 ? 1 : n % 20 == 0 ? 2 : 3",
       array(1 => 0, 11 => 0, 21 => 0, 31 => 0, 2 => 1, 12 => 1, 22 => 1, 32 => 1,
        0 => 2, 20 => 2, 40 => 2, 60 => 2, 3 => 3, 10 => 3, 13 => 3, 19 => 3,
        23 => 3, 30 => 3)),
      // Scottish Gaelic
      array("(n == 1) || (n == 11) ? 0 : (n == 2) || (n == 12) ? 1 : (n >= 3) && (n <= 19) ? 2 : 3",
       array(1 => 0, 11 => 0, 2 => 1, 12 => 1, 3 => 2, 4 => 2, 9 => 2, 10 => 2,
        13 => 2, 14 => 2, 19 => 2, 0 => 3, 20 => 3, 21 => 3, 100 => 3,
        101 => 3, 102 => 3, 111 => 3)),
      // Breton
      array("(n % 10 == 1) && (n % 100 != 11) && (n % 100 != 71) && (n % 100 != 91) ? 0 : (n % 10 == 2) && (n % 100 != 12) && (n % 100 != 72) && (n % 100 != 92) ? 1 : ((n % 10 == 3) || (n % 10 == 4) || (n % 10 == 9)) && ((n % 100 < 10) || (n % 100 > 19)) && ((n % 100 < 70) || (n % 100 > 79)) && ((n % 100 < 90) || (n % 100 > 99)) ? 2 : (n != 0) && (n % 1000000 == 0) ? 3 : 4",
       array(1 => 0, 21 => 0, 31 => 0, 61 => 0, 81 => 0, 101 => 0, 121 => 0,
        2 => 1, 22 => 1, 32 => 1, 62 => 1, 82 => 1, 102 => 1, 122 => 1, 3 => 2,
        4 => 2, 9 => 2, 23 => 2, 24 => 2, 29 => 2, 63 => 2, 64 => 2, 69 => 2,
        83 => 2, 84 => 2, 89 => 2, 103 => 2, 104 => 2, 109 => 2, 123 => 2,
        124 => 2, 129 => 2, 1000000 => 3, 2000000 => 3, 0 => 4, 5 => 4, 8 => 4,
        10 => 4, 11 => 4, 12 => 4, 13 => 4, 14 => 4, 19 => 4, 20 => 4, 25 => 4,
        28 => 4, 30 => 4, 71 => 4, 72 => 4, 73 => 4, 74 => 4, 79 => 4, 80 => 4,
        105 => 4, 108 => 4, 110 => 4, 111 => 4, 112 => 4, 113 => 4, 114 => 4,
        119 => 4)),
      // Lower Sorbian, Slovenian, Upper Sorbian
      array("n % 100 == 1 ? 0 : n % 100 == 2 ? 1 : (n % 100 == 3) || (n % 100 == 4) ? 2 : 3",
       array(1 => 0, 101 => 0, 201 => 0, 2 => 1, 102 => 1, 202 => 1, 3 => 2,
        4 => 2, 103 => 2, 104 => 2, 203 => 2, 204 => 2, 0 => 3, 5 => 3,
        100 => 3, 105 => 3, 200 => 3, 205 => 3)),
      // Hebrew
      array("n == 1 ? 0 : n == 2 ? 1 : (n % 10 == 0) && (n > 10) ? 2 : 3",
       array(1 => 0, 2 => 1, 20 => 2, 30 => 2, 40 => 2, 50 => 2, 100 => 2,
        0 => 3, 3 => 3, 4 => 3, 10 => 3, 11 => 3, 19 => 3, 21 => 3, 29 => 3,
        101 => 3, 102 => 3, 109 => 3, 111 => 3, 119 => 3)),
      // Maltese
      array("n == 1 ? 0 : (n == 0) || ((n % 100 >= 2) && (n % 100 <= 10)) ? 1 : (n % 100 >= 11) && (n % 100 <= 19) ? 2 : 3 ",
       array(1 => 0, 0 => 1, 2 => 1, 3 => 1, 9 => 1, 10 => 1, 102 => 1, 103 => 1,
        110 => 1, 11 => 2, 12 => 2, 18 => 2, 19 => 2, 111 => 2, 119 => 2,
        20 => 3, 21 => 3, 100 => 3, 101 => 3, 120 => 3, 121 => 3)),
      // Irish
      array("n == 1 ? 0 : n == 2 ? 1 : (n >= 3) && (n <= 6) ? 2 : (n >= 7) && (n <= 10) ? 3 : 4",
       array(1 => 0, 2 => 1, 3 => 2, 4 => 2, 5 => 2, 6 => 2, 7 => 3, 8 => 3,
        9 => 3, 10 => 3, 0 => 4, 11 => 4, 12 => 4, 100 => 4, 101 => 4,
        102 => 4, 110 => 4)),
      // Arabic, Najdi Arabic
      array("n == 0 ? 0 : n == 1 ? 1 : n == 2 ? 2 : (n % 100 >=3) && (n % 100 <= 10) ? 3 : (n % 100 >= 3) ? 4 : 5",
       array(0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 3, 9 => 3, 10 => 3, 103 => 3,
        104 => 3, 109 => 3, 110 => 3, 11 => 4, 12 => 4, 13 => 4, 98 => 4,
        99 => 4, 111 => 4, 112 => 4, 113 => 4, 100 => 5, 101 => 5, 102 => 5,
        200 => 5)),
      // Welsh
      array("n == 0 ? 0 : n == 1 ? 1 : n == 2 ? 2 : n == 3 ? 3 : n == 6 ? 4 : 5",
       array(0 => 0, 1 => 1, 2 => 2, 3 => 3, 6 => 4, 4 => 5, 5 => 5, 7 => 5,
        10 => 5, 100 => 5)),
      // Cornish
      array("n == 0 ? 0 : n == 1 ? 1 : (n % 20 == 2) ? 2 : (n % 20 == 3) ? 3 : (n % 20 == 1) ? 4 : 5",
       array(0 => 0, 1 => 1, 2 => 2, 22 => 2, 42 => 2, 102 => 2, 3 => 3, 23 => 3,
        43 => 3, 103 => 3, 21 => 4, 41 => 4, 101 => 4, 4 => 5, 10 => 5,
        11 => 5, 12 => 5, 13 => 5, 20 => 5, 24 => 5, 40 => 5, 104 => 5,
        120 => 5))
    );
  }

  /**
   * @dataProvider evaluations_provider
   */
  public function test_evaluations($expression_string, $value_tests) {
    $parser = new PGettext\Plurals\Parser($expression_string);
    $expression = $parser->parse();
    foreach ($value_tests as $input => $expected_output) {
      $output = $expression->evaluate($input);
      $this->assertEquals($output, $expected_output);
    }
  }

  public function expressions_provider() {
    return array(
      array('1 + !n', '(1 + (! n))'),
      array('1 + 2 + 3 + 4 + 500', '((((1 + 2) + 3) + 4) + 500)'),
      array('1 + (2 + (3 + 4))', '(1 + (2 + (3 + 4)))'),
      array('1 || 2 && 3', '(1 || (2 && 3))'),
      array('1 == 2 != 3', '((1 == 2) != 3)'),
      array('1 <= 2 + 3', '(1 <= (2 + 3))'),
      array('1 - 2 % 3', '(1 - (2 % 3))'),
      array('1 - !2 % 3', '(1 - ((! 2) % 3))'),
      array('1 + 2 * 3 / 1', '(1 + ((2 * 3) / 1))'),
      array('1 + 2 * 3 + 1', '((1 + (2 * 3)) + 1)'),
      array('n%10==1 && n%100!=11', '(((n % 10) == 1) && ((n % 100) != 11))'),
      array('n ? 1 + 2 : 3 * 4', '(n ? (1 + 2) : (3 * 4))'),
      array('n == 1 ? n < 10 ? 1 * 1 : 1 * 2 : 1 * 3',
        '((n == 1) ? ((n < 10) ? (1 * 1) : (1 * 2)) : (1 * 3))'),
    );
  }

  /**
   * @dataProvider expressions_provider
   */
  public function test_expressions($expression_string, $expected_output) {
    $parser = new PgetText\Plurals\Parser($expression_string);
    $expression = $parser->parse();
    $output = $expression->to_string();
    $this->assertEquals($output, $expected_output);
  }

  public function syntax_provider() {
    return array(
      array("(0", 'Mismatched parenthesis'),
      array("(((0) + 1)", 'Mismatched parenthesis'),
      array("(((0) + 1) + 2", 'Mismatched parenthesis'),
      array("0) + 1", 'Could not parse completely'),
      array("a", 'Lexical analysis failed'),
      array("a ? 1 : 0", 'Lexical analysis failed'),
      array("1 + ", 'Primary expected'),
      array("1 + +", 'Primary expected'),
      array("1 + ! +", 'Primary expected'),
      array("1 ? 2 :", 'Primary expected'),
      array("1 ( 2", 'Operator expected'),
      array("1 n", 'Operator expected'),
      array("1 ? 2", 'Invalid ? expression'),
    );
  }

  /**
   * @dataProvider syntax_provider
   */
  function test_syntax($expression_string, $expected_output) {
    $this->expectExceptionMessage($expected_output);
    $parser = new PGettext\Plurals\Parser($expression_string);
    $parser->parse();
  }

  function header_provider() {
    return array(
      // Valid
      array("nplurals=1; plural=0;", 1, "0"),
      array("  nplurals  =  1  ;  plural  =  0  ;  ", 1, "0"),
      array("nplurals=4; plural=(n == 1) || (n == 11) ? 0 : (n == 2) || (n == 12) ? 1 : (n >= 3) && (n <= 19) ? 2 : 3;",
        4,
        "(((n == 1) || (n == 11)) ? 0 : (((n == 2) || (n == 12)) ? 1 : (((n >= 3) && (n <= 19)) ? 2 : 3)))"),
      // Invalid
      array("badvalue", 2, "((n == 1) ? 0 : 1)"),
      array("badvalue=1", 2, "((n == 1) ? 0 : 1)"),
      array("nplurals=n", 2, "((n == 1) ? 0 : 1)"),
      array("nplurals=1;", 2, "((n == 1) ? 0 : 1)"),
      array("nplurals=1 plural=0", 2, "((n == 1) ? 0 : 1)"),
      array("nplurals=1; badvalue;", 2, "((n == 1) ? 0 : 1)"),
      array("nplurals=1; badvalue=0;", 2, "((n == 1) ? 0 : 1)"),
      array("nplurals=1; plural=0", 2, "((n == 1) ? 0 : 1)"),
      array("badvalue=1; plural=badvalue;", 2, "((n == 1) ? 0 : 1)"),
      array("nplurals=1; plural=exit();", 2, "((n == 1) ? 0 : 1)"),
    );
  }

  /**
   * @dataProvider header_provider
   */
  function test_header($header_value, $expected_total, $expected_expression) {
    $header = new PGettext\Plurals\Header($header_value);
    $this->assertEquals($header->total, $expected_total);
    $this->assertEquals($header->expression->to_string(), $expected_expression);
  }
}
