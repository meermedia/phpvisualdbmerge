<?php
/**
 * Use a safe GLOBAL user with only 'SELECT' and 'SHOW TABLES' permissions!
 */

$db_left = new mysqli("localhost", "my_user", "my_password");
$db_right = new mysqli("localhost", "my_user", "my_password");
