<?php
// config/db.php - DUAL: Local XAMPP + Live AwardSpace

$is_local = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
             $_SERVER['HTTP_HOST'] === '127.0.0.1');

if ($is_local) {
    // LOCAL XAMPP
    $host     = "localhost";
    $dbname   = "todo_db";
    $username = "root";
    $password = "";
} else {
    // LIVE AWARDSPACE (use YOUR exact values)
    $host     = "fdb1032.awardspace.net";   // From your screenshot: fdb1032.awardspace.net (this is the real host!)
    $dbname   = "4741554_todo";             // Exact name from panel
    $username = "4741554_todo";             // Usually same as dbname (confirm in panel)
    $password = "Yld#UA,95SOWbV;A";          // The password you set (change if you regenerated)
}

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Optional: debug line (remove after testing)
// echo "<!-- Connected OK - Host: $host, DB: $dbname -->";
?>