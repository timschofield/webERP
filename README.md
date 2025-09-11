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

A live demo of the latest (currently RC) release is available on the webERP support site, where you can log in and experiment
with all the webERP features: https://www.weberp.org/demo/

## Download Now

*NB* the "not yet released" v5 version is recommended over the latest v4 version for new installations. You can
download the latest daily-updated version from [GitHub](https://github.com/timschofield/webERP/archive/refs/heads/master.zip)

The latest stable version is currently [v4.15.2](https://github.com/timschofield/webERP/releases/tag/v4.15.2), and can
be downloaded from [SourceForge](http://sourceforge.net/projects/web-erp/files/latest/download)

## Requirements
- A web server - webERP has been tested on Apache, Nginx, Lighthttpd, and Hiawatha
- PHP version 8.1 and above
- MySQL version 5.7.5 and above, or MariaDB version 5.5 and above (recent, supported versions recommended)
- A web browser with HTML5 compatibility

Further information about hardware and software requirements is available in the [documentation](https://www.weberp.org/Documentation.html)
on the website.

_NB:_ please note that the website documentation is for webERP version 4. For version 5, look at the [included install guide](doc/INSTALL.md)

## Installation

### New installation

1. [Download the latest webERP source code](https://github.com/timschofield/webERP/archive/refs/heads/master.zip).
2. Unzip the downloaded file.
3. Everything inside the folder you unzipped needs to be uploaded/copied to your webserver, for example, into your
   `public_html` or `www` or `html` folder (the folder will already exist on your webserver).
4. Create an empty database, taking note of your username, password, hostname, and database name.
   NB: the database user must have sufficient permissions to create triggers and functions.
5. In your browser, enter the address to your site, such as: www.example.com (or if you uploaded it into another subdirectory
   such as foldername use www.example.com/foldername)
6. Follow the instructions that appear in your browser for installation.

### Upgrading

1. [Download the latest webERP source code](https://github.com/timschofield/webERP/archive/refs/heads/master.zip).
2. Unzip the downloaded file.
3. Backup the `config.php` script and `companies/` directory from your previous installation.
4. Everything inside the folder you unzipped needs to be uploaded/copied to your webserver, overwriting your previous installation.
5. Verify that the `config.php` script and `companies/` directory are intact, and if not, restore them from your backup.
6. In your browser, enter the address to your site, such as: www.example.com (or if you uploaded it into another subdirectory such as foldername use www.example.com/foldername).
7. After you log-in, if any database upgrades are required, you will be prompted to install them.

Further information about installation and upgrading is available in the [documentation](https://www.weberp.org/demo/ManualContents.php?ViewTopic=GettingStarted).

## Documentation

The webERP user documentation is included in every installation, and can be accessed by clicking on the `Manual` button on the
top menu bar. The documentation is also available within the [live demo.](https://www.weberp.org/demo/ManualContents.php)

The developer's documentation is also included in every installation, in markdown format. It is found in `./doc/developers`.
It can be browsed online at https://github.com/timschofield/webERP/tree/master/doc/developers

## Support

Free support is available 24/7, provided by our enthusiastic community of actual webERP users, integrators, and the developers themselves.
The primary means of support is through the forum at: https://github.com/timschofield/webERP/discussions
You may also join the mailing list at: https://sourceforge.net/projects/web-erp/lists/web-erp-users
The answers to most questions can be found by searching the forums, or the mailing list archives at: https://sourceforge.net/p/web-erp/mailman/

## Contribute to the webERP project

Contributions of code and documentation including How-Tos with screenshots etc... are very much appreciated. If your business
has done such training materials for your own team this will no doubt be useful to many others and a productive way that
you could contribute. Contributions in the form of bug reports or other feedback through the forums or mailing lists above
also help to improve the project.

General guidelines for contributing code can be found at: https://www.weberp.org/Development.html

The docs at https://github.com/timschofield/webERP/tree/master/doc/developers include a Code of Conduct as well as
Coding Standards and a detailed Development Workflow.
Developers interested in contributing should read those document carefully and follow the guidelines therein. Standards and
conventions used in the code are rigorously applied in the interests of consistency and readability.

## Legal

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; version 2 of the License.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

A copy of the GNU General Public License is included in the doc directory along with this program; if not, write to the
Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

Copyright © weberp.org 2003-2025 - Contact: info@weberp.org
