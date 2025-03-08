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
    if (!$input || !isset($input['query']) || $input['query'] !== 'updateRow' || !isset($input['tablename']) || !isset($input['where']) || !isset($input['newdata'])) {
        echo json_encode(['error' => 'Invalid request format']);
        exit;
    }

    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $input['tablename']);
    $db = new SQLite3($dbPath);
    $db->busyTimeout(5000);

    if (!$db) {
        throw new Exception('Failed to initialize SQLite3 object');
    }

    $tableCheck = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'");
    if (!$tableCheck) {
        $db->close();
        echo json_encode(['error' => "$tableName is not a table"]);
        exit;
    }

    $where = $db->escapeString($input['where']);
    $newData = is_array($input['newdata']) && isset($input['newdata'][0]) ? $input['newdata'] : [$input['newdata']];
    
    $setParts = [];
    foreach ($newData as $data) {
        $colName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['cname']);
        $value = $db->escapeString($data['value']);
        $setParts[] = "$colName = '$value'";
    }

    if (empty($setParts)) {
        $db->close();
        echo json_encode(['error' => 'No valid update data provided']);
        exit;
    }

    $updateQuery = "UPDATE $tableName SET " . implode(', ', $setParts) . " WHERE $where";
    
    // Log the query for debugging
    error_log("Executing query: $updateQuery");

    $result = $db->exec($updateQuery);

    if ($result === false) {
        throw new Exception($db->lastErrorMsg());
    }

    $rowsAffected = $db->changes();
    $db->close();

    echo json_encode(['success' => true, 'message' => 'Data updated successfully', 'rows_affected' => $rowsAffected]);

} catch (Exception $e) {
    if (isset($db) && $db instanceof SQLite3) {
        $db->close();
    }
    // Log the error for debugging
    error_log("Update failed: " . $e->getMessage());
    echo json_encode(['error' => 'Update failed: ' . $e->getMessage()]);
}
?>