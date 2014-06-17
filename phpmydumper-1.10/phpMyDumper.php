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

class phpMyDumper {
	/**
	* @access private
	*/
	var $database = null;
	var $connection = null;
	var $compress = null;
	var $hexValue = null;
	var $dropTable = null;
	var $createTable = null;
	var $tableData = null;
	var $expInsert = null;
	var $phpMyAdmin = null;
	var $utf8 = null;
	var $autoincrement = null;

	var $filename = null;
	var $file = null;
	var $filestream = null;
	var $outputTofile = false;
	var $isWritten = false;

	/**
	* Class constructor
	* @param string $db The database name
	* @param string $connection The database connection handler
	* @param boolean $compress It defines if the output/import file is compressed (gzip) or not
	* @param string $filepath The file where the dump will be written
	*/
	function phpMyDumper($db=null, $connection=null, $filepath='dump.php', $compress=false, $table=null) {
		$this->connection = $connection;
		$this->compress = $compress;

		$this->hexValue = false;
		$this->dropTable = true;
		$this->createTable = true;
		$this->tableData = true;
		$this->expInsert = false;
		$this->phpMyAdmin = true;
		$this->utf8 = true;
		$this->autoincrement = false;
		$this->table = $table;

		$this->outputTofile = ( $filepath!='' ) ? true : false;
		if ( $this->outputTofile && !$this->setOutputFile($filepath) ) {
			//if filepath is null, then we want stream
			return false;
		}

		return $this->setDatabase($db);
	}

	/**
	* Sets the database to work on
	* @param string $db The database name
	*/
	function setDatabase($db){
		$this->database = $db;
		if ( !$this->connection->select_db($this->database) ) {
			return false;
		}
		return true;
  	}

	/**
	* Sets the output file
	* @param string $filepath The file where the dump will be written
	*/
	function setOutputFile($filepath) {
		if ( $this->isWritten )
			return false;
		//echo "Creating file '".$filepath."': ";
		$this->filename = $filepath;
		$this->file = $this->openFile($this->filename);
		//echo " DONE!\n";
		return $this->file;
  	}
	
	/**
 	* Writes to file all the selected database tables structure with SHOW CREATE TABLE
	* @param string $table The table name
	*/
	function getTableStructure($table) {
		// Header
		$structure = "\n-- --------------------------------------------------------\n";
		$structure .= "-- \n";
		$structure .= "-- Table structure for table `$table`\n";
		$structure .= "-- \n\n";

		// Dump Structure
		if ( $this->dropTable )
			$structure .= "DROP TABLE IF EXISTS `$table`;\n";
		$records = $this->connection->query("SHOW CREATE TABLE `$table`");
		if ( @$records->num_rows == 0 )
			return false;
		while ( $record = $records->fetch_assoc() ) {
			$structure .= $record['Create Table'];
		}
		$records = $this->connection->query("SHOW TABLE STATUS LIKE '$table'");
		while ( $row = $records->fetch_assoc() ) {
			if ($this->autoincrement AND $row['Name']==$table AND $row['Auto_increment']!='') {
				$structure .= " AUTO_INCREMENT=".$row['Auto_increment'];
			}
		}
		$structure .= ";\n";
		$this->saveToFile($this->file,$structure);
  	}

	/**
	* Writes to file the $table's data
	* @param string $table The table name
	* @param boolean $hexValue It defines if the output is base 16 or not
	*/
	function getTableData($table,$hexValue = true) {
		// Header
		$data = "\n-- --------------------------------------------------------\n";
		$data .= "-- \n";
		$data .= "-- Dumping data for table `$table`\n";
		$data .= "-- \n\n";
			
		// Field names
		if ($this->expInsert || $this->hexValue) {
			$records = $this->connection->query("SHOW FIELDS FROM `$table`");
			$num_fields = $records->num_rows;
			if ( $num_fields == 0 )
				return false;
			$hexField = array();

			$insertStatement = "INSERT INTO `$table` (";
			$selectStatement = "SELECT ";
			for ($x = 0; $x < $num_fields; $x++) {
				$record = $records->fetch_assoc();
				if ( ($hexValue) && ($this->isTextValue($record['Type'])) ) {
					$selectStatement .= 'HEX(`'.$record['Field'].'`)';
					$hexField [$x] = true;
				}
				else
					$selectStatement .= '`'.$record['Field'].'`';
				
				$insertStatement .= '`'.$record['Field'].'`';
				$insertStatement .= ", ";
				$selectStatement .= ", ";
			}
			$insertStatement = @substr($insertStatement,0,-2).') VALUES';
			$selectStatement = @substr($selectStatement,0,-2).' FROM `'.$table.'`';
		}
		if (!$this->expInsert)
			$insertStatement = "INSERT INTO `$table` VALUES";
		if (!$this->hexValue)
			$selectStatement = "SELECT * FROM $table";
		
		// Dump data
		$records = $this->connection->query($selectStatement);
		$num_rows = @$records->num_rows;
		$num_fields = @$records->field_count;

		$procent = 0;
		for ($i = 1; $i <= $num_rows; $i++) {
			$data .= $insertStatement;
			$record = $records->fetch_assoc();
			while ($property = $records->fetch_field()) {
 			   $fieldname[] = $property->name;
			}
			$data .= ' (';
			for ($j = 0; $j < $num_fields; $j++) {
				$field_name = $fieldname[$j];
				if (is_null($record[$field_name])) {
				 	$data .= "NULL";
				} 
				else {
					if ( isset($hexField[$j]) && (@strlen($record[$field_name]) > 0) ) {
						$data .= "0x".$record[$field_name];
					}
					else {
						$data .= '\''.@str_replace('\"','"',@mysql_escape_string($record[$field_name])).'\'';
					}
				}
				$data .= ',';
			}
			$data = @substr($data,0,-1).");\n";

			$this->saveToFile($this->file,$data);
			$data = '';
		}
	}

 	/**
	* Writes to file all the selected database tables structure
	* @return boolean
	*/
	function getDatabaseStructure() {
		$records = $this->connection->query("SHOW TABLES LIKE '".$this->table."'");
		if ( $records->num_rows == 0 )
			return false;
		while ( $record = $records->fetch_row() ) {
			//echo "Exporting table structure for '".$record[0]."': ";
			$this->getTableStructure($record[0]);
			//echo " DONE!\n";
		}
		return true;
 	}

	/**
	* Writes to file all the selected database tables data
	* @param boolean $hexValue It defines if the output is base-16 or not
	*/
	function getDatabaseData($hexValue = true) {
		$records = $this->connection->query("SHOW TABLES LIKE '".$this->table."'");
		if ( $records->num_rows == 0 )
			return false;
		while ( $record = $records->fetch_row() ) {
			//if ($this->filename) echo "Exporting table data for '".$record[0]."': ";
			$this->getTableData($record[0],$hexValue);
			//if ($this->filename) echo " DONE!\n";
		}
  	}

	/**
	* Writes to file all the selected database tables data
	* @param boolean $hexValue It defines if the output is base-16 or not
	*/
	function getDatabaseStructureData($hexValue = true){
		$records = $this->connection->query("SHOW TABLES LIKE '".$this->table."'");
		if ( $records->num_rows == 0 )
		{
			return false;
		}
		while ( $record = $records->fetch_row() ) {
			if ( $this->createTable) {
				//if ($this->filename) echo "Exporting table structure for '".$record[0]."': ";
				$this->getTableStructure($record[0]);
				//if ($this->filename) echo " DONE!\n";
			}
			if ( $this->tableData) {
				//if ($this->filename) echo "Exporting table data for '".$record[0]."': ";
				$this->getTableData($record[0],$hexValue);
				//if ($this->filename) echo " DONE!\n";
			}
		}
  	}

	/**
	* Writes the selected database to file 
	*/
	function doDump() {		
		if ( !$this->setDatabase($this->database) )
		{
			return false;
		}

		if ( $this->utf8 ) {
			$encoding = $this->connection->query("SET NAMES 'utf8'");
		}

		$cur_time=date("Y-m-d H:i");
		//$server_info=$this->connection->server_info;
		//echo $server_info;
		$this->saveToFile($this->file,"-- Generation Time: $cur_time\n");
		//$this->saveToFile($this->file,"-- MySQL Server Version: $server_info\n");
		$this->saveToFile($this->file,"-- Database: `$this->database`\n");

		if ($this->phpMyAdmin) {
			$this->getDatabaseStructureData($this->hexValue);
		}
		else {	
			if ( $this->createTable )
				$this->getDatabaseStructure();
			if ( $this->tableData )
				$this->getDatabaseData($this->hexValue);
		}
		
		if ($this->outputTofile){
			$this->closeFile($this->file);
			return true;
		}
		else {
			return $this->filestream;
		}
	}

 	/**
	* @access private
	*/
	function isTextValue($field_type) {
		switch ($field_type) {
			case "tinytext":
			case "text":
			case "mediumtext":
			case "longtext":
			case "binary":
			case "varbinary":
			case "tinyblob":
			case "blob":
			case "mediumblob":
			case "longblob":
				return True;
				break;
			default:
				return False;
		}
	}
	
	/**
	* @access private
	*/
	function openFile($filename) {
		$file = false;
		if ( $this->compress )
			$file = @gzopen($filename, "w9");
		else
			$file = @fopen($filename, "a");
		return $file;
	}

	/**
	* @access private
	*/
	function saveToFile($file, $data) {
		if ($this->outputTofile){
			if ( $this->compress )
				@gzwrite($file, $data);
			else
				@fwrite($file, $data);
			$this->isWritten = true;
		}
		else {
			$this->saveToStream($data);
		}
	}

	/**
	* @access private
	*/
	function saveToStream($data) {
		$this->filestream .= $data;
	}
	
	/**
	* @access private
	*/
	function closeFile($file) {
		if ( $this->compress )
			@gzclose($file);
		else
			@fclose($file);
	}
}
?>