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