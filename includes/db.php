<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'skillbridge';

// Enable error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    echo "<div style='background: #fee; color: #b00; padding: 20px; border: 1px solid #f00; margin: 20px; font-family: sans-serif;'>";
    echo "<h3>Database Connection Error</h3>";
    echo "<p>Please ensure:</p>";
    echo "<ul>
            <li>XAMPP/MySQL is running.</li>
            <li>You have created the database <strong>$db</strong>.</li>
            <li>You have imported <strong>schema.sql</strong>.</li>
          </ul>";
    echo "<strong>Error details:</strong> " . $e->getMessage();
    echo "</div>";
    exit();
}
