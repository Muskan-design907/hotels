<?php
// db.php - Database connection
 
$servername = "localhost";  // usually localhost
$username = "ur9iyguafpilu"; // your DB username
$password = "51gssrtsv3ei";  // your DB password
$dbname = "dbbgkgbvfjgk2n";  // your database name
 
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
 
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
// Set charset to utf8 for proper encoding
$conn->set_charset("utf8");
?>
 
