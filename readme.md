This PHP script can be used to convert your MySQL database tables to utf8mb4 
while retaining the integrity of all internal multibyte characters. 

## REQUIREMENTS
For best results, use MySQL 5.7 or newer and Zen Cart v1.5.6 or newer BEFORE PROCEEDING.

## IMPORTANT
It's actually **very important** to be using Zen Cart v1.5.6 or even 1.5.7 before proceeding with 
this conversion, because the upgrade steps in v156/v157 increase database table sizes to allow for the 
larger storage requirements of multibyte characters. If you run this conversion on shorter fields then
you may experience loss of data or the script may even fail if the database has strict mode enabled 
and the fields aren't long enough for the larger characters.

#### Generic Script
While this script was primarily set up to assist Zen Cart users, nothing in it is specific to Zen Cart. In theory one could use it to migrate all the tables in any database to utf8mb4.


## CAUTION: ALWAYS MAKE A DATABASE BACKUP FIRST!!!!


## Instructions:

0) BACKUP YOUR DATABASE!!!

a) upload the accompanying PHP file to your store's root folder (same place as `ipn_main_handler.php`)

b) edit the PHP file to set your database username, password, dbname, and prefix (if any)

c) use your browser to access the file 
The conversion will show current progress as it proceeds, and statistics upon completion. 

d) DELETE THE FILE FROM YOUR SERVER when finished.

e) Update your Zen Cart configuration to use the new character set. BOTH your admin AND storefront files need these.
- `configure.php` (both) need a line which says: `define('DB_CHARSET', 'utf8mb4');`
- language files (english.php, etc) need `define('CHARSET', 'utf-8');` 
- language files (english.php, etc) need `'en_US.utf8'` (for english) in the list of `$locales`. See [example](https://github.com/zencart/zencart/blob/c6422419a9264b4102d36f6389d826b6cbee2b9a/includes/languages/english.php#L23-L24)
- ALL your language files need to be encoded as UTF8-without-BOM. This is a function of your editor's "save" settings.



## Troubleshooting

If you encounter errors converting certain tables due to bad data in them, simply fix the bad data and then re-run the script. It will recognize what has already been converted and continue with the last failed operation.  For more details on corrective action, please see https://docs.zen-cart.com/user/upgrading/convert_to_utf8/

In most cases the script is able to correctly re-create all indexes that were originally on the tables; however in some cases of failure due to bad data it may have already deleted an index that it wasn't yet ready to re-create; in these cases you will have to recreate the index yourself. Clues regarding the index are shown on the screen when the indexes are temporarily deleted: it would be wise to note all those details before re-running the script so that you can check for those indexes afterward. Troubleshooting this factor is technically advanced. You might want to consult the original mysql_zencart.sql install script for details of all the indexes that should exist on each table. (An export of table-structure-only could make the inspection process quite simple.)

----

## In case of missing defaults (if you ran an older version of this script)

Older versions of the `utf8mb4-conversion` script (prior to 03/05/2021) had a defect wherein blank defaults would not be recreated.  So a field definition like 

```
customers_firstname varchar(32) NOT NULL default ''
```

would become

```
customers_firstname varchar(32) NOT NULL
```

This has been fixed but if you ran the script prior to the fix, your database structure will be incorrect since the defaults are missing. 

To fix this, use the script `optional/fix_blank_defaults.php` as follows:

Instructions:

0) BACKUP YOUR DATABASE!!!

a) upload the file `optional/fix_blank_defaults.php` to your store's root folder (same place as `ipn_main_handler.php`)

b) edit the PHP file to set your database username, password, dbname, and prefix (if any)

c) use your browser to access the file The conversion will show current progress as it proceeds. 

d) DELETE THE FILE FROM YOUR SERVER when finished.



----

## REVISION HISTORY
* Adapted from http://stackoverflow.com/questions/105572/
* r0.1 2011-06-01 DrByte www.zen-cart.com - Added support for multi-key and partial-length indices and table prefixes
* r0.2 2011-07-01 DrByte www.zen-cart.com - Added support for proper handling of column defaults and proper null handling
* r0.3 2011-07-02 DrByte www.zen-cart.com - Added support for variable-length char fields
* r0.4 2020-09-19 DrByte www.zen-cart.com - Updated to work with utf8mb4 primarily (does NOT account for index fields longer than allowed 191 chars; upgrade to Zen Cart v1.5.6 first so the schema can handle these limitations)
* r0.5 2020-10-29 DrByte www.zen-cart.com - Added `repair table` statements, and extended timeouts for the benefit of larger tables
* r0.6 2021-03-05 DrByte www.zen-cart.com - Fixed broken "DEFAULT" regeneration
* r0.7 2021-03-05 DrByte www.zen-cart.com - Enhanced output/logging
* r1.0 2021-03-05 DrByte www.zen-cart.com - Skip already-converted fields (can be disabled by setting option to false)
* r1.1 2021-03-05 swguy  thatsoftwareguy.com - Add optional fix_blank_defaults.php script to fix databases updated prior to r0.6.
* r1.2 2021-11-05 lat9/DrByte
  * Add additional quotes on text-fields' default values (DrByte)
  * Updating to use `configure.php` for settings, correcting default values (lat9)

