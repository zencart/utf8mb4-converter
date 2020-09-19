<?php
/**
 * A script to convert database collation/charset to utf8mb4
 *
 * @copyright Copyright 2003-2020 Zen Cart Development Team
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: utf8mb4-conversion.php $
 *
 * @copyright Adapted from http://stackoverflow.com/questions/105572/ and https://mathiasbynens.be/notes/mysql-utf8mb4
 *
 * NOTE!!!! NOTE!!!! NOTE!!!!
 * You should upgrade your Zen Cart store (and database) to at least v1.5.6 before running this script.
 *
 */

$username = 'your_database_username_here';
$password = 'your_database_password_here';
$db = 'your_database_name_here';
$host = 'localhost';
$prefix = '';  // if your tablenames start with "zen_" or some other common prefix, enter that here.


// recommended setting is 'utf8mb4':
$target_charset = 'utf8mb4';



/////// DO NOT CHANGE BELOW THIS LINE ////////

$collation_fallbacks = array();
$collation_fallbacks[] = 'utf8mb4_unicode_520_ci';
$collation_fallbacks[] = 'utf8mb4_unicode_ci';
$collation_fallbacks[] = 'utf8mb4_general_ci';
$collation_fallbacks[] = 'utf8mb4_bin';
$collation_fallbacks[] = $target_charset . '_unicode_ci';
$collation_fallbacks[] = $target_charset . '_general_ci';
$collation_fallbacks[] = $target_charset . '_bin';

// Begin processing
$timer = time();
$tables = array();
$t = $i = 0;

echo "<strong>Database Charset/Collation Conversion</strong><br><br>\n\n";
if ($username == "your_database_username_here") die('<span style="color: red; font-weight: bold">Error: Database credentials required. Please edit this PHP file and supply your DB username/password/db-name details.</span>');

$conn = mysqli_connect($host, $username, $password);
mysqli_select_db($conn, $db);

// identify target tables
$res = mysqli_query($conn, "SHOW TABLES");
printMySqlErrors();
while (($row = mysqli_fetch_row($res)) != null) {
    if ($prefix == '') {
        $tables[] = $row[0];
    } else if (substr($row[0], 0, strlen($prefix)) == $prefix) {
        $tables[] = $row[0];
    }
}

// determine best supported target collation
$res = mysqli_query($conn, "SHOW COLLATION WHERE Charset = '{$target_charset}'");
printMySqlErrors();
$db_collations = array();
while (($row = mysqli_fetch_row($res)) != null) {
    $db_collations[] = $row[0];
}
foreach ($collation_fallbacks as $collation) {
    if (in_array($collation, $db_collations)) {
        $target_collate = $collation;
        break;
    }
}

if (empty($target_collate)) {
    die('ERROR: unable to determine a safe collation.');
}

echo "Converting tables " . ($prefix == '' ? '' : 'with prefix [' . $prefix . '] ') . "in database [$db] to $target_collate \n\n<br><br>\n\nThis may take awhile. Please wait ... <br><pre>\n\n";


// process tables
foreach ($tables as $table) {
    $t++;
    echo "\nProcessing table [{$table}]:\n";

    // collect indexes to drop and rebuild
    $res = mysqli_query($conn, "SHOW INDEX FROM `{$table}`");
    printMySqlErrors();
    $indices = array();
    while (($row = mysqli_fetch_array($res)) != null) {
        if ($row[2] != "PRIMARY") {
            if (sizeof($indices) == 0 || $indices[sizeof($indices) - 1]['name'] != $row[2]) {
                $indices[] = array("name" => $row[2], "unique" => (int)!($row[1] == "1"), "col" => $row[4] . ($row[7] < 1 ? '' : "($row[7])"));
                mysqli_query($conn, "ALTER TABLE `{$table}` DROP INDEX `{$row[2]}`");
                printMySqlErrors();
                echo "Temporarily dropped " . ($row[1] == '0' ? 'unique ' : '') . "index {$row[2]}.\n";
            } else {
                $indices[sizeof($indices) - 1]["col"] .= ', ' . $row[4] . ($row[7] < 1 ? '' : "($row[7])");
            }
        }
    }
    $res = mysqli_query($conn, "DESCRIBE `{$table}`");
    printMySqlErrors();
    while (($row = mysqli_fetch_array($res)) !== null) {
        $name = $row[0];
        $type = $row[1];
        $allownull = (strtoupper($row[2]) == 'YES') ? 'NULL' : 'NOT NULL';
        $defaultval = (trim($row[4]) == '') ? '' : "DEFAULT '{$row[4]}'";
        $set = false;
        if (preg_match("/^varchar\((\d+)\)$/i", $type, $matches)) {
            $size = $matches[1];
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` VARBINARY({$size}) {$allownull} {$defaultval}");
            printMySqlErrors();
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` VARCHAR({$size}) CHARACTER SET {$target_charset} {$allownull} {$defaultval}");
            printMySqlErrors();
            $set = true;
            echo "Altered field `{$name}`: `{$type}`\n";

        } elseif (preg_match("/^char\((\d+)\)$/i", $type, $matches)) {
            $size = $matches[1];
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` BINARY({$size}) {$allownull} {$defaultval}");
            printMySqlErrors();
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` CHAR({$size}) CHARACTER SET {$target_charset} {$allownull} {$defaultval}");
            printMySqlErrors();
            $set = true;
            echo "Altered field `{$name}`: `{$type}`\n";

        } elseif (!strcasecmp($type, "TINYTEXT")) {
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` TINYBLOB {$allownull} {$defaultval}");
            printMySqlErrors();
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` TINYTEXT CHARACTER SET {$target_charset} {$allownull} {$defaultval}");
            printMySqlErrors();
            $set = true;
            echo "Altered field `{$name}`: `{$type}`\n";

        } elseif (!strcasecmp($type, "MEDIUMTEXT")) {
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` MEDIUMBLOB {$allownull} {$defaultval}");
            printMySqlErrors();
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` MEDIUMTEXT CHARACTER SET {$target_charset} {$allownull} {$defaultval}");
            printMySqlErrors();
            $set = true;
            echo "Altered field `{$name}`: `{$type}`\n";

        } elseif (!strcasecmp($type, "LONGTEXT")) {
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` LONGBLOB {$allownull} {$defaultval}");
            printMySqlErrors();
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` LONGTEXT CHARACTER SET {$target_charset} {$allownull} {$defaultval}");
            printMySqlErrors();
            $set = true;
            echo "Altered field `{$name}`: `{$type}`\n";

        } elseif (!strcasecmp($type, "TEXT")) {
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` BLOB {$allownull} {$defaultval}");
            printMySqlErrors();
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` TEXT CHARACTER SET {$target_charset} {$allownull} {$defaultval}");
            printMySqlErrors();
            $set = true;
            echo "Altered field `{$name}`: `{$type}`\n";
        }
        if ($set) {
            mysqli_query($conn, "ALTER TABLE `{$table}` MODIFY `{$name}` COLLATE {$target_collate}");
        }
    }
    // re-build indices..
    foreach ($indices as $index) {
        $i++;
        mysqli_query($conn, "CREATE " . ($index["unique"] ? "UNIQUE " : '') . "INDEX {$index["name"]} ON {$table} ({$index["col"]})");
        printMySqlErrors();
        echo "Recreated " . ($index['unique'] == '1' ? 'unique ' : '') . "index {$index["name"]} on {$table} ({$index["col"]}).\n";
    }
    // set default collate
    mysqli_query($conn, "ALTER TABLE `{$table}` DEFAULT CHARACTER SET {$target_charset} COLLATE {$target_collate}");
    echo "Table collation updated.\n";
}
// set database charset
mysqli_query($conn, "ALTER DATABASE {$db} DEFAULT CHARACTER SET {$target_charset} COLLATE {$target_collate}");
mysqli_close($conn);
echo "</pre>\n";
$timer_diff = time() - $timer;
echo $t . ' Tables processed, ' . $i . ' Indexes processed, ' . $timer_diff . ' seconds elapsed time.' . "\n\n";
echo '<br><br><br><span style="color:red;font-weight:bold">NOTE: This conversion script should now be DELETED from your server for security reasons!!!!!</span><br><br><br>';

function printMySqlErrors()
{
    global $conn;
    if (mysqli_errno($conn)) {
        echo '<span style="color: red; font-weight: bold">MySQL Error: ' . mysqli_error($conn) . '</span>' . "\n";
    }
}
