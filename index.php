
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
require('functions.php');
require('config.php');

/*
 * Making connections
 */

$db_left = new mysqli($db_left_host, $db_left_user, $db_left_pass);
$db_right = new mysqli($db_right_host, $db_right_user, $db_right_pass);

connectDB($db_left, $db_right);

$preset = (!empty($_REQUEST['preset'])) ? $_REQUEST['preset'] : '';
$diff = (!empty($_REQUEST['diff'])) ? $_REQUEST['diff'] : '';
$emptycache = (!empty($_REQUEST['emptycache'])) ? $_REQUEST['emptycache'] : '';

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
    <form id="mergeform">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">
          <ul class="nav nav-sidebar">
            <li class="active"><a href="#">DB Merge Tool</a></li>
          </ul>
          <label class="checkbox" for="empty"><input type="checkbox" name="emptycache" id="empty" value="1" <?php if (!empty($emptycache)) echo "checked"; ?>>Empty cache tables</label>
          <label class="checkbox" for="diff"><input type="checkbox" name="diff" id="diff" value="1" <?php if (!empty($diff)) echo "checked"; ?>>Auto select unique</label>
          <select id="preset" name="preset" class="form-control input-sm">
            <option value="">--- use preset? ---</option>
            <option value="drupal-auto" <?php if ($preset == 'drupal-auto') echo "selected"; ?>>drupal-auto</option>
            <option value="drupal-6" <?php if ($preset == 'drupal-6') echo "selected"; ?>>drupal-6</option>
            <option value="drupal-7" <?php if ($preset == 'drupal-7') echo "selected"; ?>>drupal-7</option>
          </select>
          <br />
          <button type="submit" class="btn btn-primary">Load Tables</button><br />
          <br />
          <button type="button" class="btn btn-primary" id="export" data-toggle="modal" data-target="#myModal">Export</button><br />
        </div>
        <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
          <h1 class="page-header">Merge Dashboard</h1>

          <div class="table-responsive">
            <table class="table table-condensed">
              <thead>
                <tr>
                  <th></th>
                  <th>Left DB (#develop env) <select id="db_left" name="db_left" class="form-control input-sm">
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
                  <th>
                    </th>
                  <th>Right DB (#live env) <select id="db_right" name="db_right" class="form-control input-sm">
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
                </tr>
              </thead>
              <tbody>
<?php
	if (!empty($_REQUEST['db_left']) && !empty($_REQUEST['db_right'])) {
    /* draw up query */
    $query_cols = "
      SELECT c.TABLE_NAME,GROUP_CONCAT(c.COLUMN_NAME ORDER BY c.COLUMN_NAME SEPARATOR ',') FROM INFORMATION_SCHEMA.COLUMNS c
        JOIN INFORMATION_SCHEMA.TABLES t ON (c.TABLE_NAME = t.TABLE_NAME AND t.TABLE_SCHEMA LIKE ? AND t.TABLE_TYPE = 'BASE TABLE')
        WHERE c.TABLE_SCHEMA LIKE ? GROUP BY c.TABLE_NAME ORDER BY c.TABLE_NAME, c.COLUMN_NAME
    ";
		/* start with left db */
		$db_left->select_db($_REQUEST['db_left']);
		/* Select queries return a resultset */
    $stmt = $db_left->prepare($query_cols);
    $stmt->bind_param('ss', $_REQUEST['db_left'], $_REQUEST['db_left']);
    $stmt->execute();
    if ($result = $stmt->bind_result($col1,$col2)) {
        /* fetch array */
		    while ($stmt->fetch()) {
		    	$tables_left_collection[] = array( 
		    		'table' => $col1,
		    		'fields' => $col2,
		    		);
        $tables_left[$col1] = $col2;
		    }
		    /* free result set */
		    $stmt->close();
		}
		/* now do right db */
		$db_right->select_db($_REQUEST['db_right']);
		/* Select queries return a resultset */
    $stmt = $db_right->prepare($query_cols);
    $stmt->bind_param('ss', $_REQUEST['db_right'], $_REQUEST['db_right']);
    $stmt->execute();
    if ($result = $stmt->bind_result($col1,$col2)) {
        /* fetch array */
        while ($stmt->fetch()) {
          $tables_right_collection[] = array( 
            'table' => $col1,
            'fields' => $col2,
            );
        $tables_right[$col1] = $col2;
        }
        /* free result set */
        $stmt->close();
    }
    /* merge left and right array */
    //$tables_merged = array_merge($tables_left, $tables_right);
    $tables_merged = $tables_left + $tables_right;
    //sort($tables_merged);
    ksort($tables_merged);
    $tables_merged = array_unique(array_keys($tables_merged));
    $tables_presets = presetTables($tables_merged, $tables_left, $tables_right, $preset, $diff, $emptycache);
    /* display merged array */
    foreach ($tables_merged as $table) {
      $left = (array_key_exists($table, $tables_presets) && $tables_presets[$table]['preset'] == 'left') ? "active" : "";
      $right = (array_key_exists($table, $tables_presets) && $tables_presets[$table]['preset'] == 'right') ? "active" : "";
      $empty = (array_key_exists($table, $tables_presets) && $tables_presets[$table]['preset'] == 'empty') ? "active" : "";
      echo "<tr>";
      if ((array_key_exists($table, $tables_left)) && (array_key_exists($table, $tables_right)) && ($tables_left[$table] != $tables_right[$table]))
      {
        $only_left = implode(',',array_diff(explode(',',$tables_left[$table]),explode(',',$tables_right[$table])));
        $only_right = implode(',',array_diff(explode(',',$tables_right[$table]),explode(',',$tables_left[$table])));
        echo '<td><div id="myDiv" class="btn-group btn-group-xs"><a id="pop" href-"#" class="btn btn-sm btn-danger" data-toggle="popover" data-content="Column Mismatch<br /><br /><strong>Only left:</strong><br />' . $only_left . '<br /><br /><strong>Only right:</strong><br />' . $only_right . '"><span class="glyphicon glyphicon-fire"></span></a></div></td>';
      }
      else 
      { 
        echo '<td></td>';
      }
      if (array_key_exists($table, $tables_left)) printf ("<td><div id='myDiv' class='btn-group btn-group-xs'><a id='pop' href-'#'' class='btn btn-sm' data-toggle='popover' data-content='Columns<br /><br />%s'><span class='glyphicon glyphicon-collapse-down'></span></a></div>%s</td>\n",  $tables_left[$table], $table);
      else { echo "<td></td>"; }
        //<label class='btn btn-primary' data-toggle='popover' data-content='merge tables with left table as leading'><input type='radio' name='%s' id='fuseleft' value='fuseleft'><span class='glyphicon glyphicon-circle-arrow-right'><span class='glyphicon glyphicon-transfer'></span></label>
        //<label class='btn btn-primary' data-toggle='popover' data-content='merge tables with right table as leading'><input type='radio' name='%s' id='fuseright' value='fuseright'><span class='glyphicon glyphicon-transfer'><span class='glyphicon glyphicon-circle-arrow-left'></span></label>
      printf ("<td><div id='action' class='btn-group btn-group-xs' data-toggle='buttons'>
        <label class='btn btn-primary %s' data-toggle='popover' data-content='use left table for export'><input type='radio' name='%s' id='left' value='left'><span class='glyphicon glyphicon-chevron-right'></span></label>
        <label class='btn btn-primary remove' data-toggle='popover' data-content='exclude table from export'><input type='radio' name='%s' id='exclude' value='exclude'><span class='glyphicon glyphicon-remove'></span></label>
        <label class='btn btn-primary trash %s' data-toggle='popover' data-content='use empty table for export'><input type='radio' name='%s' id='empty' value='empty'><span class='glyphicon glyphicon-trash'></span></label>
        <label class='btn btn-primary %s' data-toggle='popover' data-content='use right table for export'><input type='radio' name='%s' id='right' value='right'><span class='glyphicon glyphicon-chevron-left'></span></label>
        </div></td>\n"
        , $left, $table, $table, $empty, $table, $right, $table);
      if (array_key_exists($table, $tables_right)) printf ("<td><div id='myDiv' class='btn-group btn-group-xs'><a id='pop' href-'#'' class='btn btn-sm' data-toggle='popover' data-content='Columns<br /><br />%s'><span class='glyphicon glyphicon-collapse-down'></span></a></div>%s</td>\n", $tables_right[$table], $table);
      else { echo "<td></td>"; }
      echo "<tr>";
    }
	}
?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      </form>
    </div>
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">Exporting</h4>
          </div>
          <div class="modal-body">
            <span class='glyphicon glyphicon-floppy-save'></span> Exporting merged databases, please wait...
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script type="text/javascript">

      $('[data-toggle="popover"]').popover({trigger: 'hover','placement': 'bottom',html: true});
      $('[data-toggle="tooltip"]').tooltip({container: 'body'});

      $(document).ready(function() {
        $('#export').click(function () {
          $('#myModal').modal('show')
          $("form").find("label.btn.btn-primary.active input:radio").prop('checked', true);
          var data = '';
          var data = $.map($("input:radio:checked"), function(elem, idx) {
                var table = {};
                table[$(elem).attr("name")] = $(elem).val();
                return table;
            });
          $.ajax({
              url: "export.php",
              type: "POST",
              data: { 
                tables: data,
                db_left: $('#db_left').val(),
                db_right: $('#db_right').val(),
                empty: $('#empty').val(),
                diff: $('#diff').val(),
                preset: $('#preset').val(),
                },
              cache: false,
              success: function (html) {
                $("body").append("<iframe src='" + html + "' style='display: none;' ></iframe>");
                $('#myModal').modal('hide')
              }
          });
          return false;
        });
      });


    </script>
  </body>
</html>
<?php
closeDB();
?>
