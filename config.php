<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', '178.63.128.53');
define('DB_USERNAME', 'psyro_db1');
define('DB_PASSWORD', 'PaJhBFN3BL!h');
define('DB_NAME', 'psyro_db1');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
?>