
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="PHP Visual DB Merge">
    <meta name="author" content="MeerMedia">
    <link rel="shortcut icon" href="../../assets/ico/favicon.ico">

    <title>PHP Visual DB Merge</title>

    <!-- Bootstrap core CSS -->
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/dashboard.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

<?php
require('config.php');
require('functions.php');
connectDB($db_left, $db_right);
?>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">PHP Visual DB Merge</a>
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <li class="active"><a href="#">DB Merge Tool</a></li>
          </ul>
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          <h1 class="page-header">Merge Dashboard</h1>

          <div class="table-responsive"><form name="dbselect">
            <table class="table table-condensed">
              <thead>
                <tr>
                  <th>Left DB (#develop env) <select name="db_left" class="form-control input-sm">
<?php
	/* Select queries return a resultset */
	if ($result = $db_left->query("SHOW DATABASES")) {

        /* fetch associative array */
	    while ($row = $result->fetch_assoc()) {
	    	$selected = (!empty($_REQUEST['db_left']) && $row["Database"] == $_REQUEST['db_left']) ? "selected" : "";
	        printf ("<option %s>%s</option>\n", $selected, $row["Database"]);
	    }

	    /* free result set */
	    $result->close();
	}
?>
					</select>
                  </th>
                  <th></th>
                  <th>Right DB (#live env) <select name="db_right" class="form-control input-sm">
<?php
	/* Select queries return a resultset */
	if ($result = $db_right->query("SHOW DATABASES")) {

        /* fetch associative array */
	    while ($row = $result->fetch_assoc()) {
	    	$selected = (!empty($_REQUEST['db_right']) && $row["Database"] == $_REQUEST['db_right']) ? "selected" : "";
	        printf ("<option %s>%s</option>\n", $selected, $row["Database"]);
	    }

	    /* free result set */
	    $result->close();
	}
?>
					</select>
                  </th>
                  <th><button type="submit" class="btn btn-primary">Load Tables</button></th>
                </tr>
              </thead>
              <tbody>
<?php
	if (!empty($_REQUEST['db_left']) && !empty($_REQUEST['db_right'])) {
		/* start with left db */
		$db_left->select_db($_REQUEST['db_left']);
		/* Select queries return a resultset */
		if ($result = $db_left->query("SHOW TABLES")) {
	        /* fetch array */
		    while ($row = $result->fetch_row()) {
		    	$tables_left[] = $row[0];
		    }
		    /* free result set */
		    $result->close();
		}
		/* now do right db */
		$db_right->select_db($_REQUEST['db_right']);
		/* Select queries return a resultset */
		if ($result = $db_right->query("SHOW TABLES")) {
	        /* fetch array */
		    while ($row = $result->fetch_row()) {
		    	$tables_right[] = $row[0];
		    }
		    /* free result set */
		    $result->close();
		}
	}
	/* merge left and right array */
	$tables_merged = array_merge($tables_left, $tables_right);
	sort($tables_merged);
	$tables_merged = array_unique($tables_merged);
    /* display merged array */
    foreach ($tables_merged as $table) {
    	echo "<tr>";
        if (in_array($table, $tables_left)) printf ("<td>%s</td>\n", $table);
    	else { echo "<td></td>"; }
    	$left = (in_array($table, $tables_left) && !in_array($table, $tables_right)) ? "active" : "";
    	$right = (!in_array($table, $tables_left) && in_array($table, $tables_right)) ? "active" : "";
    	printf ("<td><div class='btn-group btn-group-xs' data-toggle='buttons'>
    		<label class='btn btn-primary %s'><input type='radio' name='%s' id='left'><span class='glyphicon glyphicon-chevron-left'></span></label>
    		<label class='btn btn-primary'><input type='radio' name='%s' id='fuseleft'><span class='glyphicon glyphicon-circle-arrow-left'><span class='glyphicon glyphicon-transfer'></span></label>
    		<label class='btn btn-primary'><input type='radio' name='%s' id='fuseright'><span class='glyphicon glyphicon-transfer'><span class='glyphicon glyphicon-circle-arrow-right'></span></label>
    		<label class='btn btn-primary %s'><input type='radio' name='%s' id='right'><span class='glyphicon glyphicon-chevron-right'></span></label>
    		</div></td>\n"
    		, $left, $table, $table, $table, $right, $table);
        if (in_array($table, $tables_right)) printf ("<td>%s</td>\n", $table);
    	else { echo "<td></td>"; }
    	echo "<td></td>";
    	echo "<tr>";
    }
?>
              </tbody>
            </table>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/docs.min.js"></script>
  </body>
</html>
<?php
closeDB();
?>
