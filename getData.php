<?php
header('Content-Type: application/json');

function sanitizeToken($token) {
    return preg_replace('/[^a-zA-Z0-9]/', '', $token);
}

$token = isset($_GET['token']) ? sanitizeToken($_GET['token']) : null;

if (!$token) {
    echo json_encode(['error' => 'No token provided']);
    exit;
}

$dbPath = "databases/{$token}.sqlite";

try {
    if (!file_exists($dbPath)) {
        echo json_encode(['error' => 'Database does not exist']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['query']) || !isset($input['tablename'])) {
        echo json_encode(['error' => 'Invalid request format']);
        exit;
    }

    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $input['tablename']);
    $db = new SQLite3($dbPath);
    $db->busyTimeout(5000);

    $tableCheck = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'");
    if (!$tableCheck) {
        $db->close();
        echo json_encode(['error' => "$tableName is not a table"]);
        exit;
    }

    if ($input['query'] === 'getall') {
        $result = $db->query("SELECT * FROM $tableName");
    } elseif ($input['query'] === 'getwhere') {
        if (!isset($input['where'])) {
            $db->close();
            echo json_encode(['error' => 'Missing where clause']);
            exit;
        }
        $where = $db->escapeString($input['where']);
        $columns = isset($input['columns']) ? implode(', ', array_map(function($col) { return preg_replace('/[^a-zA-Z0-9_]/', '', $col); }, $input['columns'])) : '*';
        $result = $db->query("SELECT $columns FROM $tableName WHERE $where");
    } else {
        $db->close();
        echo json_encode(['error' => 'Invalid query type']);
        exit;
    }

    $data = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $data[] = $row;
    }

    $db->close();
    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->close();
    }
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}
?>