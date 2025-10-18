<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "event_pass_system";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


$conn = mysqli_connect('localhost','root','','event_pass_system'); // change as needed
if (!$conn) { die('DB connection failed: '.mysqli_connect_error()); }

?>
