Name
====
convert_db2utf8

Features
========
Convert database tables to utf8.

Version Date
==============
v 2.0.1 16.10.2007 14:47
v3.0 2014-06-28 DrByte updated to convert mysql_ functions to mysqli_ for compatibility with PHP >= 5.4
v4.0 2016-01-11 lat9   Updated to convert the overall database collation, too, if it's not utf8.
v4.1 2018-01-01 mc12345678 Updated to support both quoted and unquoted databases when converting the overall database collation.

Author
======
Andrew Berezin http://eCommerce-Service.com

Description
===========
This Script convert database tables to utf8

Support thread
==============
http://www.zen-cart.com/downloads.php?do=file&id=1318

Affected files
==============
None

Affects DB
==========
Yes (convert database tables to utf8).

DISCLAIMER
==========
Installation of this contribution is done at your own risk.
Backup your ZenCart database and any and all applicable files before proceeding.

Install
=======
0. Backup your database.
1. Unzip and upload all files to your store directory.

Un-Install
==========
1. Delete all files that were copied from the installation package.

Use
===
Run http://<your store>/convert_db2utf8.php