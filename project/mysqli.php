<?php
$conn = new mysqli($dbservername, $dbusername, $dbpassword, $dbname);
if ($conn->connect_error) {
    die("Connection Error " . $conn->connect_error);
}
if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading utf8mb4: %s\n", $conn->error);
    die();
}