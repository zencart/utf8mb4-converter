<?php
/**
 * convert_db2utf8
 *
 * @package eCommerce-Service
 * @copyright Copyright 2004-2007, Andrew Berezin eCommerce-Service.com
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: convert_db2utf8.php, v 2.0.1 16.10.2007 14:47 Andrew Berezin $
 *
 * Updated 2014-06-28 to change mysql_ functions to mysqli_ functions, for compatibility with PHP >= 5.4
 * Updated 2015-01-11 (lat9).  Check for, and convert, overall database collation, too!
 * Updated 2018-01-01 (mc12345678).  Support both quoted and unquoted databases when converting the overall database collation.
 *
 */
error_reporting(E_ALL & ~E_NOTICE);
$desiredCollation = 'utf8_general_ci'; // could optionally use utf8_unicode_ci

/**
 * Get the database credentials
 */
if (file_exists('includes/local/configure.php')) {
  /**
   * load any local(user created) configure file.
   */
  include('includes/local/configure.php');
}
if (file_exists('includes/configure.php')) {
  /**
   * load the main configure file.
   */
  include('includes/configure.php');
}
if (file_exists('configure.php')) {
  include('configure.php');
}

if (!defined('DB_SERVER_USERNAME')) {
  die("ERROR: configure.php file not found, or doesn't contain database user credentials.");
}
if (!defined('DB_PREFIX')) define('DB_PREFIX', '');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-EN">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="robots" content="noindex,nofollow" />
  <title>UTF-8 Database Converter</title>
</head>
<body>
  <div class="wrap">
  <h1>UTF-8 Database Converter</h1>
<?php
if (isset($_POST['db_prefix'])) {
  $db_prefix = $_POST['db_prefix'];
} else {
  $db_prefix = DB_PREFIX;
}
if (!isset($_POST['submit']) ) {
?>
  <p>Before proceeding with the final step please make a complete backup of your database.<br/>
  <fieldset>
    <legend><?php echo ''; ?></legend>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="utf8-db-converter">
      <label for="db-prefix"><?php echo 'DB Prefix'; ?></label>
      <input type="text" name="db_prefix" value="<?php echo $db_prefix; ?>" id="db-prefix" />
      <br />
<?php $tables = UTF8_DB_Converter(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, $db_prefix, true); ?>
      <input type="submit" name="review" value="Review Tables" />
<?php if ($tables > 0) { ?>
      <input type="submit" name="submit" value="Start converting &raquo;" />
<?php } ?>
    </form>
  </fieldset>
<?php
} else {
  $tables = UTF8_DB_Converter(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD, DB_DATABASE, $db_prefix, false);
?>
    <p><strong>The database has been converted to UTF-8</strong>. <a href="<?php echo HTTP_SERVER; ?>" target="_blank">View site &raquo;</a></p>
<?php
}
?>
  </div>
</body>
</html>
<?php
function UTF8_DB_Converter($dbServer, $dbUser, $dbPassword, $dbDatabase, $dbPrefix='', $showOnly=true) {
  global $dbStat, $link_id, $desiredCollation;
  $time_start = microtime_float();
  @set_time_limit(0);
  $dbStat = array();

  echo 'Connecting to DB server ' . $dbServer . '<br />' . "\n";
  if (!$link_id = mysqli_connect($dbServer, $dbUser, $dbPassword)) {
    echo 'Error connecting to mySQL: ' . mysqli_connect_errno() . ': ' . mysql_connect_error() . '<br />' . "\n";
    die;
  }
  $mySQLversion = mysqli_get_server_info($link_id);
  echo 'mySQL Server version ' . $mySQLversion . '<br />' . "\n";

  preg_match("/^(\d+)\.(\d+)\.(\d+)/", $mySQLversion, $m);
  $mySQLversionClear = sprintf("%d%02d%02d", $m[1], $m[2], $m[3]);
  if ($mySQLversionClear <= 40101) {
    die('This version not supported!!!');
  }

  echo 'Selected DB: ' . $dbDatabase . '<br />' . "\n";
  if (!mysqli_select_db($link_id, $dbDatabase)) {
    echo 'Error selecting database: ' . mysqli_errno($link_id) . ': ' . mysqli_error($link_id) . '<br />' . "\n";
    die;
  }
  
//-bof-20160111-lat9-Show/update overall database collation, too.
  $query = db_query("SHOW VARIABLES LIKE \"character\_set\_database\"");
  $database_charset = mysqli_fetch_assoc($query);
  $charset_value = $database_charset['Value'];
  echo 'Current DB character-set: ' . $charset_value;
  if ($charset_value != 'utf8') {
    if ($showOnly) {
      echo ' <span style="font-weight: bold; color: red;">&lt;== Will be converted, too, when you convert the tables!</span>';
    } else {
      db_query("ALTER DATABASE `" . $dbDatabase . "` CHARACTER SET utf8 COLLATE $desiredCollation");
      echo ' <span style="font-weight: bold; color: green;">&lt;== Converted!</span>';
    }    
  }
  echo '<br />' . "\n";
//-eof-2016011-lat9-Show overall database collation
  
  $query = db_query("SHOW CHARACTER SET LIKE 'utf8'");
  if (!$charset = mysqli_fetch_assoc($query)) {
    die("Charset 'utf8' not found!!!");
  }

  echo 'DB Prefix: "' . $dbPrefix . '"' . '<br />' . "\n";

  $totalProcessingTables = $totalConvertedTables = 0;
  $query_tables = db_query("SHOW TABLE STATUS FROM `" . $dbDatabase . "`");
  echo '<table border="1px" width="100%"><tr>' .
       '<th>Name</th>' .
       '<th>Collation</th>' .
//       '<th>Engine</th>' .
       '<th>Rows</th>' .
       '<th>Data<br />length</th>' .
       '<th>Create time</th>' .
       '<th>Update time</th>' .
       '<th>Action</th>' .
       '</tr>';
  while ($table = mysqli_fetch_assoc($query_tables)) {
    if (!preg_match('@^' . $dbPrefix . '@', $table['Name'])) continue;
    $totalProcessingTables++;
    echo '<tr>' .
         '<td><strong>' . $table['Name'] . '</strong>' . ($table['Comment'] != '' ? '<br /><i>' . $table['Comment'] . '</i>' : '') . '</td>' .
         '<td>' . $table['Collation'] . '</td>' .
//         '<td>' . $table['Engine'] . '</td>' .
         '<td align="right">' . $table['Rows'] . '</td>' .
         '<td align="right">' . $table['Data_length'] . '</td>' .
         '<td align="center">' . $table['Create_time'] . '</td>' .
         '<td align="center">' . $table['Update_time'] . '</td>' .
         '<td align="center">';
/*
    $query_fields = db_query("SHOW FULL columns FROM `" . $table['Name'] . "`");
    $convert = false;
    while ($fields = mysqli_fetch_assoc($query_fields)) {
      if (isset($fields['Collation'])) {
//        echo $table['Name'] . ' [' . $fields['Field'] . '] ' . $fields['Type'] . ' - ' . $fields['Collation'] . "<br />\n";
        $convert = true;
        break;
      }
    }
*/
    if ($table['Collation'] == $desiredCollation) {
      $action = 'Skip';
    } else {
      $totalConvertedTables++;
      if (!$showOnly) {
        db_query("ALTER TABLE `" . $table['Name'] . "` CONVERT TO CHARACTER SET utf8 COLLATE " . $desiredCollation);
        db_query("ALTER TABLE `" . $table['Name'] . "` DEFAULT CHARACTER SET utf8 COLLATE " . $desiredCollation);
        db_query("OPTIMIZE TABLE `" . $table['Name'] . "`");
        $action = '<b>Converted!</b>';
      } else {
        $action = 'Convert';
      }
    }
    echo $action . '</td>' .
         '</tr>';
  }

  echo '</table>';

  if (!$showOnly) {
    db_query("ALTER DATABASE `" . $dbDatabase . "` CHARACTER SET utf8 COLLATE " . $desiredCollation);
  }

  mysqli_close($link_id);

  $total_time = microtime_float() - $time_start;
  echo 'Total processing tables ' . $totalProcessingTables . ', converted tables ' . $totalConvertedTables . '. Execution time ' . timefmt($total_time) . '<br />' . "\n";
  echo 'DB statistic: ';
  foreach($dbStat as $sql_command => $time) {
    echo '&nbsp;' . $sql_command . ': ' . sizeof($time) . ' ' . timefmt(array_sum($time)) . '; ';
  }
  echo '<br />' . "\n";

  return $totalConvertedTables;
}

function db_query($sql) {
  global $link_id;
  global $dbStat;
  $st = microtime_float();
//  echo $sql . "<br />\n";
  if (!$ret = mysqli_query($link_id, $sql)) {
    echo 'Error: ' . mysqli_errno($link_id) . ': ' . mysqli_error($link_id) . '<br />' . "\n";
    echo 'SQL: ' . $sql . '<br />' . "\n";
  }
  $sql_command = explode(' ', substr($sql, 0, 16));
  $sql_command = strtolower($sql_command[0]);
  $dbStat[$sql_command][] = microtime_float()-$st;
  return($ret);
}

function microtime_float() {
   list($usec, $sec) = explode(' ', microtime());
   return ((float)$usec + (float)$sec);
}

function timefmt($s) {
  $m = floor($s/60);
  $s = $s - $m*60;
  $h = floor($m/60);
  $m = $m - $h*60;
  if ($h > 0) {
    $tfmt = $h . ':' . $m . ':' . number_format($s, 4);
  } elseif ($m > 0) {
    $tfmt = $m . ':' . number_format($s, 4);
  } else {
    $tfmt = number_format($s, 4);
  }
  return $tfmt;
}
