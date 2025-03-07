<?php
header('Content-Type: application/json');

// Function to sanitize token for filename
function sanitizeToken($token) {
    return preg_replace('/[^a-zA-Z0-9]/', '', $token);
}

// Get token from URL parameter
$token = isset($_GET['token']) ? sanitizeToken($_GET['token']) : null;

if (!$token) {
    echo json_encode(['error' => 'No token provided']);
    exit;
}

// Database path
$dbPath = "databases/{$token}.sqlite";

try {
    // Check if database exists
    if (!file_exists($dbPath)) {
        echo json_encode(['error' => 'Database does not exist']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['query']) || $input['query'] !== 'newRow') {
        echo json_encode(['error' => 'Invalid request format']);
        exit;
    }

    // Validate required fields
    if (!isset($input['tablename']) || !isset($input['newdata'])) {
        echo json_encode(['error' => 'Missing tablename or newdata']);
        exit;
    }

    // Sanitize table name
    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $input['tablename']);

    // Connect to database
    $db = new SQLite3($dbPath);
    $db->busyTimeout(5000);

    // Check if table exists
    $tableCheck = $db->querySingle("SELECT name FROM sqlite_master WHERE type='table' AND name='$tableName'");
    if (!$tableCheck) {
        $db->close();
        echo json_encode(['error' => "$tableName is not a table"]);
        exit;
    }

    // Prepare insert data
    $columns = [];
    $values = [];
    $newData = is_array($input['newdata']) && isset($input['newdata'][0]) ? $input['newdata'] : [$input['newdata']];
    
    foreach ($newData as $data) {
        $colName = preg_replace('/[^a-zA-Z0-9_]/', '', $data['cname']);
        $value = $db->escapeString($data['value']);
        $columns[] = $colName;
        $values[] = "'$value'";
    }

    if (empty($columns)) {
        $db->close();
        echo json_encode(['error' => 'No valid data provided']);
        exit;
    }

    // Create and execute INSERT query
    $insertQuery = "INSERT INTO $tableName (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ")";
    $result = $db->exec($insertQuery);

    $db->close();

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Data inserted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to insert data']);
    }

} catch (Exception $e) {
    if (isset($db)) {
        $db->close();
    }
    echo json_encode(['error' => 'Operation failed: ' . $e->getMessage()]);
}
?>