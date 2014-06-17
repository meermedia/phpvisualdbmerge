<?php

function connectDB($db_left, $db_right) {

	/* check connection */
	if ($db_left->connect_errno) {
	    printf("Connect left DB failed: %s\n", $db_left->connect_error);
	    exit();
	}

	/* check connection */
	if ($db_right->connect_errno) {
	    printf("Connect right DB failed: %s\n", $db_right->connect_error);
	    exit();
	}

}

function voidFunc() {
	/* Create table doesn't return a resultset */
	if ($mysqli->query("CREATE TEMPORARY TABLE myCity LIKE City") === TRUE) {
	    printf("Table myCity successfully created.\n");
	}

	/* Select queries return a resultset */
	if ($result = $mysqli->query("SELECT Name FROM City LIMIT 10")) {
	    printf("Select returned %d rows.\n", $result->num_rows);

	    /* free result set */
	    $result->close();
	}

	/* If we have to retrieve large amount of data we use MYSQLI_USE_RESULT */
	if ($result = $mysqli->query("SELECT * FROM City", MYSQLI_USE_RESULT)) {

	    /* Note, that we can't execute any functions which interact with the
	       server until result set was closed. All calls will return an
	       'out of sync' error */
	    if (!$mysqli->query("SET @a:='this will not work'")) {
	        printf("Error: %s\n", $mysqli->error);
	    }
	    $result->close();
	}
}

function closeDB() {
	if (isset($db_global)) $db_global->close();
	if (isset($db_left)) $db_left->close();
	if (isset($db_rigt)) $db_right->close();
}

  function searchKey($needle, $tables)
  {
    foreach($tables as $key => $table)
    {
      if ( $table['table'] === $needle )
        return $key;
    }
    return false;
  }

  function array_key_exists_wildcard($key,$array)
  {
    foreach ($array as $matchto)
    {
      if (preg_match("/^".$matchto."$/", $key)) {
        return true;
      }
    }
  }

  function presetTables($tables_merged, $tables_left, $tables_right, $preset, $diff, $emptycache)
  {
    require('presets.php');
    switch ($preset) {
      case 'drupal-auto':
        foreach ($tables_merged as $table) {
          if ( array_key_exists($table, $tables_left) && ( $drupalauto != array_diff( $drupalauto, explode(',',$tables_left[$table]) ) ) )
          {
            $tables[$table]['preset'] = 'right';
          }
          else
          {
            $tables[$table]['preset'] = 'left';
          }
        }
        break;
      case 'drupal-6':
        foreach ($tables_merged as $table) {
          if (array_key_exists_wildcard($table,$drupal6content))
          {
            $tables[$table]['preset'] = 'right';
          }
          else
          {
            $tables[$table]['preset'] = 'left';
          }
        }
        break;
      case 'drupal-7':
        foreach ($tables_merged as $table) {
          if (array_key_exists_wildcard($table,$drupal7content))
          {
            $tables[$table]['preset'] = 'right';
          }
          else
          {
            $tables[$table]['preset'] = 'left';
          }
        }
        break;
      default:
        foreach ($tables_merged as $table) {
          $tables[$table]['preset'] = '';
        }
        break;
    }
    /*
     * Check for tables that only exist in one database and select them
     */
    if ($diff) {
      foreach ($tables_merged as $table) {
            if ( !array_key_exists($table, $tables_left) )
            {
              $tables[$table]['preset'] = 'right';
            }
            elseif ( !array_key_exists($table, $tables_right) )
            {
              $tables[$table]['preset'] = 'left';
            }
      }
    }
    /*
     * Use empty cache tables if selected
     */
    if ($emptycache) {
      foreach ($tables_merged as $table) {
            if ( array_key_exists_wildcard($table,$drupalcache) )
            {
              $tables[$table]['preset'] = 'empty';
            }
      }
    }
    return $tables;
  }

  function gzcompressfile($source,$level=false)
  { 
    $dest=$source.'.gz'; 
    $mode='wb'.$level; 
    $error=false; 
    if($fp_out=gzopen($dest,$mode))
    { 
      if($fp_in=fopen($source,'rb'))
      {
        while(!feof($fp_in)) 
        {
          gzwrite($fp_out,fread($fp_in,1024*512)); 
        }
        fclose($fp_in); 
      } 
      else 
      { 
        $error=true; 
      }
      gzclose($fp_out); 
    } 
    else
    { 
      $error=true;
    }
    if($error) { return false; }
    else { return $dest; }
  } 
