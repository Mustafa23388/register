<?php
$servername = "mysql.railway.internal";
$username = "root";
$password = "EpnwUfuIqJIamdLjTOGroEaoyXfpIHtN";
$dbname = "railway";

// Create connection
$mysqli = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


$conn = mysqli_connect('mysql.railway.internal','root','EpnwUfuIqJIamdLjTOGroEaoyXfpIHtN','railway'); // change as needed
if (!$conn) { die('DB connection failed: '.mysqli_connect_error()); }

?>
