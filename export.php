<?php
include_once('phpmydumper-1.10/phpMyDumper.php');
require('config.php');
require('functions.php');

/*
 * Making connections
 */

$db_left = new mysqli($db_left_host, $db_left_user, $db_left_pass);
$db_right = new mysqli($db_right_host, $db_right_user, $db_right_pass);

$filename  = ''; // Filename of dump, default: "dump.php"
$compress  = false; // Dump as a compressed file, default: false

//$connection_left = mysql_connect($db_left_host,$db_left_user,$db_left_pass);
//$connection_right = mysql_connect($db_right_host,$db_right_user,$db_right_pass);

//$input = json_decode($_POST['tables'], true);
$input = $_REQUEST['tables'];
$dbname_left = $_REQUEST['db_left'];
$dbname_right = $_REQUEST['db_right'];
$empty = $_REQUEST['empty'];
$diff = $_REQUEST['diff'];
$preset = $_REQUEST['preset'];
$cur_time=date("Y-m-d-H-i");

$tables = array();
$exportfile = '';
$exportfilename = "backup/db_merged_".$dbname_left."-".$dbname_right."_".$cur_time.".sql";

//unlink($exportfilename);
array_map('unlink', glob("backup/*.sql"));
array_map('unlink', glob("backup/*.gz"));

foreach ($input as $pairs)
{
    foreach ($pairs as $row => $pair)
    {
        // current($pair) will give you the value
        // you could use key($pair) to get the key too
        if ($pair != 'exclude')
        {
        	// $exportfile .= 'trying '.$row.'<br />';
	        $tables[$row] = $pair;
	        $table = $row;
	        //$filename = 'backup/export.sql';
			$filename = "backup/db_merged_".$dbname_left."-".$dbname_right."_".$cur_time.".sql";

	        if ($pair == "left" || $pair == "fuseleft")
	        {
				$dump = new phpMyDumper($dbname_left,$db_left,$filename,$compress,$table);
	        }
	        elseif ($pair == "right" || $pair == "fuseright" || $pair == "empty")
	        {
				$dump = new phpMyDumper($dbname_right,$db_right,$filename,$compress,$table);
	        }

			if ($pair == "empty") {
				$dump->tableData = false; // Dump table data, default: true
			}
			else {
				$dump->tableData = true; // Dump table data, default: true
			}

			$dump->dropTable = true; // Dump DROP TABLE statement, default: true
			$dump->createTable = true; // Dump CREATE TABLE statement, default: true
			$dump->expInsert = true; // Dump expanded INSERT statements, default: false
			$dump->hexValue = true; // Dump strings as hex values, default: false
			$dump->phpMyAdmin = true; // Formats dump file like phpMyAdmin export, default: true
			$dump->utf8 = true; // Uses UTF-8 connection with MySQL server, default: true
			$dump->autoincrement = false; // Dump AUTO_INCREMENT statement using older MySQL servers, default: false

			$dump->doDump();
			//$exportfile .= $dump->filestream;
        }
    }
}
gzcompressfile($exportfilename,'9');
echo $exportfilename.'.gz';
//unlink($exportfilename);
