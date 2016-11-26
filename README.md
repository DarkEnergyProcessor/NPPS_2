NPPS 2
======

A refactored version of [NPPS](https://github.com/MikuAuahDark/NPPS) suitable for SIF v4.0.3.

###Overview

NPPS (short of **Null-Pointer Private Server**) is a SIF private server engine designed to be
simple, flexible, and fast. Modules and action can be easily modified, so it can be used for other
SIF versions (v3.1.x, v2.0.5, ...)

For security reasons, NPPS is written in PHP 7, which mostly uses null coalescing operator, scalar
type hinting, and return type hinting. With it, calls to `is_*` is nicely decreased while keeping
good security.

###System Requirements

* PHP 7.0 (PHP 7.1-Relase Candidate is untested).

* MBString PHP extension.

* cURL PHP extension (if DLC from prod configuration is enabled).

* SQLite3 PHP module (not PDO SQLite3). SQLite v3.7.0 or later is required if you're using SQLite3 as DB backend.

* MySQL v5.5 (or later) and MySQLi PHP module (necessary if you're using MySQL as DB backend)

* For Windows: Windows 7 SP1 or Windows Server 2008 R2 SP1 with latest updates (because you can't run PHP 7 in the earlier Windows version). Windows 8.1 or Windows Server 2012 is recommended.

* For Ubuntu: Ubuntu 16.04 (with simple `apt-get`), or 14.04 with [Ondřej Surý PPA](https://launchpad.net/~ondrej/+archive/ubuntu/php) to install PHP 7 and it's modules.

* For Mac OS X 10.6 and above: [use this method](http://php-osx.liip.ch/). **64-bit only**.

###Some Notes

* This one is still incomplete.
