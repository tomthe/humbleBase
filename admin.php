<?php
header('Content-Type: text/html');

function sanitizeToken($token) {
    return preg_replace('/[^a-zA-Z0-9]/', '', $token);
}

$token = isset($_GET['token']) ? sanitizeToken($_GET['token']) : null;

if (!$token) {
    die('No token provided');
}

$dbPath = "databases/{$token}.sqlite";

try {
    if (!file_exists($dbPath)) {
        die('Database does not exist');
    }

    $db = new SQLite3($dbPath);
    $db->busyTimeout(5000);

    // Get all tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tableList = [];
    while ($table = $tables->fetchArray(SQLITE3_ASSOC)) {
        $tableList[] = $table['name'];
    }

    // Generate HTML
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>SimpleBase Admin - <?php echo htmlentities($token); ?></title>
        <style>
            table { border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; }
            th { background: #f5f5f5; }
        </style>
    </head>
    <body>
        <h1>Database Admin: <?php echo htmlentities($token); ?></h1>
        <?php
        foreach ($tableList as $tableName) {
            echo "<h2>" . htmlentities($tableName) . "</h2>";
            
            // Get column info
            $columns = $db->query("PRAGMA table_info($tableName)");
            $colNames = [];
            while ($col = $columns->fetchArray(SQLITE3_ASSOC)) {
                $colNames[] = $col['name'];
            }

            // Get data
            $result = $db->query("SELECT * FROM $tableName");
            echo "<table><tr>";
            foreach ($colNames as $col) {
                echo "<th>" . htmlentities($col) . "</th>";
            }
            echo "</tr>";

            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                echo "<tr>";
                foreach ($colNames as $col) {
                    echo "<td>" . htmlentities($row[$col]) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        ?>
    </body>
    </html>
    <?php

    $db->close();

} catch (Exception $e) {
    if (isset($db)) {
        $db->close();
    }
    die('Admin view failed: ' . $e->getMessage());
}
?>