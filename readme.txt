This PHP script can be used to convert your MySQL database tables to utf8mb4 while retaining the integrity of all internal multibyte characters. 

REQUIREMENTS:  For best results, use MySQL 5.7 or newer and Zen Cart v1.5.6 or newer BEFORE PROCEEDING.


CAUTION: ALWAYS MAKE A DATABASE BACKUP FIRST!!!!


Instructions:
0) BACKUP YOUR DATABASE!!!
a) upload the accompanying PHP file to your store's primary folder (same place as ipn_main_handler.php)
b) edit the PHP file to set your database username, password, dbname, and prefix (if any)
c) use your browser to access the file 
The conversion will show current progress as it proceeds, and statistics upon completion. 
d) DELETE THE FILE FROM YOUR SERVER when finished.

e) Update your Zen Cart configuration to use the new character set. BOTH your admin AND storefront files need these.
- configure.php (both) need a line which says: define('DB_CHARSET', 'utf8mb4');
- language files (english.php, etc) need define('CHARSET', 'utf-8'); and define('LC_TIME, 'en_US.utf8'); (for english)
- ALL your language files need to be encoded as UTF8-without-BOM. This is a function of your editor's "save" settings.



REVISION HISTORY
----------------
* Adapted from http://stackoverflow.com/questions/105572/
* r0.1 2011-06-01 DrByte www.zen-cart.com - Added support for multi-key and partial-length indices and table prefixes
* r0.2 2011-07-01 DrByte www.zen-cart.com - Added support for proper handling of column defaults and proper null handling
* r0.3 2011-07-02 DrByte www.zen-cart.com - Added support for variable-length char fields
* r0.4 2020-09-19 DrByte www.zen-cart.com - Updated to work with utf8mb4 primarily (does NOT account for index fields longer than allowed 191 chars; upgrade to Zen Cart v1.5.6 first so the schema can handle these limitations)
