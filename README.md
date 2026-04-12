# [webERP - Accounting and Business Administration ERP System](https://www.weberp.org/)

[![Download](https://img.shields.io/sourceforge/dm/web-erp.svg)](https://sourceforge.net/projects/web-erp/files/latest/download)
[![Download](https://img.shields.io/sourceforge/dt/web-erp.svg)](https://sourceforge.net/projects/web-erp/files/latest/download)
[![Build Status](https://github.com/timschofield/webERP/actions/workflows/ci.yaml/badge.svg)](https://github.com/timschofield/webERP/actions/workflows/ci.yaml)
[![GitHub last commit (master)](https://img.shields.io/github/last-commit/timschofield/webERP/master.svg)](https://github.com/timschofield/webERP/commits/master/)
[![GitHub pull requests](https://img.shields.io/github/issues-pr-raw/timschofield/webERP.svg)](https://github.com/timschofield/webERP/pulls)

## Introduction

webERP is a free open-source ERP system, providing best practise, multi-user business administration and accounting tools
over the web. For further information and for a full list of features, please visit the support site at: https://www.weberp.org/

## Demo

A live webERP demo using the latest code is available at https://www.weberp.org/demo/ where you can log in and experiment
with all the features of webERP.

## Download Now

Downloading the latest release [from GitHub](https://github.com/timschofield/webERP/releases) is recommended for new installations. If you prefer,
clone the project and follow the stable branch. 

You can download a snapshot of the active development branch [from GitHub](https://github.com/timschofield/webERP/archive/refs/heads/master.zip) (or fork or clone the repo).

## Requirements
- Web server (webERP has been tested on Apache, Nginx, Lighthttpd, and Hiawatha).
- PHP version 8.1 and above (the latest supported release is recommended).
- Either MySQL version 5.7.5+ or MariaDB version 5.5+ (if MariaDb, v10 is recommended but v10-specific features are not used).
- Web browser with HTML5 compatibility.

Further information about hardware and software requirements can be found in [webERP documentation](https://www.weberp.org/Documentation.html)
and elsewhere on the website.

## Installation

### New installation

1. Download the latest webERP source code [from GitHub](https://github.com/timschofield/webERP/releases).
2. Extract the top level folder from the downloaded file.
3. Everything inside the extracted folder needs to be uploaded/copied to your webserver, for example, into your
   `public_html` or `www` or `html` folder (the folder will already exist on your webserver).
4. Create an empty database, taking note of your username, password, hostname, and database name.
   NB: the database user must have sufficient permissions to create triggers and functions.
5. In your browser, enter the address to your site, such as: www.example.com (or if you uploaded it into another subdirectory
   such as _foldername_ use www.example.com/foldername)
6. Follow the instructions that appear in your browser for installation.

### Upgrading

1. Download the latest webERP source code [from GitHub](https://github.com/timschofield/webERP/releases).
2. Extract the top level folder from the downloaded file.
3. Backup the `config.php` script and `companies/` directory from your previous installation.
4. Everything inside the folder you unzipped needs to be uploaded/copied to your webserver, overwriting your previous installation.
5. Verify that the `config.php` script and `companies/` directory are intact, and if not, restore them from your backup.
6. In your browser, enter the address to your site, such as: www.example.com (or if you uploaded it into another subdirectory such as foldername use www.example.com/foldername).
7. After you log-in, if any database upgrades are required, you will be prompted to install them.

Further information about installation and upgrading is available in the [documentation](https://www.weberp.org/demo/ManualContents.php?ViewTopic=GettingStarted).

## Documentation

The webERP "Manual" is a guide for both users and developers. This is intentional to save time finding information, avoid duplication and encourage
consistency. If the information is too detailed for your needs, you're diving deeper than you need to.

The Manual is part of webERP. Users access the Manual by clicking on the `Manual` button in the top menu bar (also available in
the [live demo.](https://www.weberp.org/demo/ManualContents.php)) (the manual first displays content specific to the current script).

The Manual is supplemented by detailed developer documentation when warranted. Refer to the webERP `./doc/developers/` folder or browse
online at https://github.com/timschofield/webERP/tree/master/doc/developers

## Support

Free support is available 24/7 from an enthusiastic community of webERP users, integrators, developers and supporters.

The primary means of communication and support is the GitHub repository, which includes a [discussions forum](https://github.com/timschofield/webERP/discussions), an [issue tracker](https://github.com/timschofield/webERP/issues) and a [Wiki](https://github.com/timschofield/webERP/wiki). Generally start with a discussion and elevate when and if appropriate to either an issue (specific defective or missing functionality) or a Wiki page (summarized conclusion for understanding and guidance).

A low-volume mailing list used mostly for notices can be joined at: https://sourceforge.net/projects/web-erp/lists/web-erp-users

Communication in the community is encouraged but before posting please do a quick search in the project on GitHub or a general web search in case your question has already been answered. The mailing list archive can be searched at: https://sourceforge.net/p/web-erp/mailman/

## Contribute to the webERP project

Contributions of code and documentation including How-Tos with screenshots etc... are very much appreciated. Perhaps you could share training
slides or process information created by you or your business for the benefit of others. Contributing bug reports, testing code or providing
feedback or suggestions in an Issue, Discussion or Wiki page in the repository are other ways to help the project.

General guidelines for contributing code can be found at: https://www.weberp.org/Development.html

The webERP Code of Conduct, Coding Standards and a detailed Development Workflow can be found at https://github.com/timschofield/webERP/tree/master/doc/developers

Contributors need to read those document carefully and follow the guidelines therein. Code standards and conventions are rigorously applied
in the interests of consistency and readability.

## Legal

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

A copy of the GNU General Public License is included in the doc directory along with this program; if not, write to the
Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

Copyright © 2003-2026 The webERP Contributors - Contact: info@weberp.org
