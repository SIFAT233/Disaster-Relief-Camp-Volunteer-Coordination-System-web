<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "disaster_relief_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

echo "<style>
    body { font-family: Arial, sans-serif; padding: 30px; background: #1a1a2e; color: white; }
    .box { padding: 20px; border-radius: 10px; margin: 10px 0; font-size: 18px; }
    .success { background: #1b5e20; border-left: 5px solid #4caf50; }
    .error { background: #b71c1c; border-left: 5px solid #f44336; }
    .info { background: #0d47a1; border-left: 5px solid #2196f3; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th { background: #2196f3; padding: 10px; text-align: left; }
    td { padding: 8px 10px; border-bottom: 1px solid #444; }
    tr:hover { background: #333; }
</style>";

echo "<h2>🔗 Database Connection Test</h2>";

// Check connection
if ($conn->connect_error) {
    echo "<div class='box error'>❌ Connection FAILED: " . $conn->connect_error . "
    <br><br>কারণ হতে পারে:
    <ul>
        <li>XAMPP এ MySQL চালু নেই</li>
        <li>Database <b>disaster_relief_db</b> তৈরি হয়নি</li>
        <li>Username/Password ভুল</li>
    </ul>
    </div>";
} else {
    echo "<div class='box success'>✅ Database Connected Successfully!</div>";
    echo "<div class='box info'>📦 Database: <b>$dbname</b> | Server: <b>$servername</b></div>";

    // Show all tables
    $result = $conn->query("SHOW TABLES");
    if ($result && $result->num_rows > 0) {
        echo "<h3>📋 Tables Found (" . $result->num_rows . " টি):</h3>";
        echo "<table><tr><th>#</th><th>Table Name</th><th>Row Count</th></tr>";
        $i = 1;
        while ($row = $result->fetch_array()) {
            $table = $row[0];
            $count_result = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
            $count_row = $count_result->fetch_assoc();
            echo "<tr><td>$i</td><td>$table</td><td>" . $count_row['cnt'] . " rows</td></tr>";
            $i++;
        }
        echo "</table>";
    } else {
        echo "<div class='box error'>⚠️ কোনো Table পাওয়া যায়নি! database.sql ফাইল import করো।</div>";
    }

    $conn->close();
}
?>
