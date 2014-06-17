<?php
/*
* phpMyDumper
* -------------
* Version: 1.10
* Copyright (c) 2009 by Micky Holdorf
* Holdorf.dk/Software - micky.holdorf@gmail.com
* GNU Public License http://opensource.org/licenses/gpl-license.php
*
*/

@include_once('phpMyDumper.php');

$dbhost = "localhost";
$dbuser = "user";
$dbpass = "password";
$dbname = "database";

$yyyymmdd_mmss  = date("Ymd_Hi");
$path      = "backup/";
$filename  = $path.$dbname."_".$yyyymmdd_mmss.".sql"; // Filename of dump, default: "dump.php"
$compress  = false; // Dump as a compressed file, default: false

$connection = @mysql_connect($dbhost,$dbuser,$dbpass);
$dump = new phpMyDumper($dbname,$connection,$filename,$compress);

$dump->dropTable = true; // Dump DROP TABLE statement, default: true
$dump->createTable = true; // Dump CREATE TABLE statement, default: true
$dump->tableData = true; // Dump table data, default: true
$dump->expInsert = false; // Dump expanded INSERT statements, default: false
$dump->hexValue = false; // Dump strings as hex values, default: false
$dump->phpMyAdmin = true; // Formats dump file like phpMyAdmin export, default: true
$dump->utf8 = true; // Uses UTF-8 connection with MySQL server, default: true
$dump->autoincrement = false; // Dump AUTO_INCREMENT statement using older MySQL servers, default: false

$dump->doDump();
?>