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
    // Check if database already exists
    if (file_exists($dbPath)) {
        echo json_encode(['error' => 'Database already exists']);
        exit;
    }

    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['query']) || $input['query'] !== 'createTable') {
        echo json_encode(['error' => 'Invalid request format']);
        exit;
    }

    // Validate required fields
    if (!isset($input['tablename']) || !isset($input['columns'])) {
        echo json_encode(['error' => 'Missing tablename or columns']);
        exit;
    }

    // Sanitize table name
    $tableName = preg_replace('/[^a-zA-Z0-9_]/', '', $input['tablename']);
    
    // Create database directory if it doesn't exist
    if (!file_exists('databases')) {
        mkdir('databases', 0755, true);
    }

    // Create new SQLite database
    $db = new SQLite3($dbPath);
    $db->busyTimeout(5000);

    // Prepare CREATE TABLE statement
    $columnsDef = [];
    foreach ($input['columns'] as $column) {
        if (is_array($column)) {
            $colName = preg_replace('/[^a-zA-Z0-9_]/', '', $column['cname']);
            $colType = isset($column['type']) ? strtoupper($column['type']) : 'VARCHAR';
            // Validate column type
            $validTypes = ['INTEGER', 'VARCHAR', 'TEXT', 'REAL'];
            $colType = in_array($colType, $validTypes) ? $colType : 'VARCHAR';
            $columnsDef[] = "$colName $colType";
        }
    }

    if (empty($columnsDef)) {
        echo json_encode(['error' => 'No valid columns specified']);
        $db->close();
        unlink($dbPath);
        exit;
    }

    $createQuery = "CREATE TABLE $tableName (" . implode(', ', $columnsDef) . ")";
    $db->exec($createQuery);

    $db->close();
    
    echo json_encode(['success' => true, 'message' => "Table $tableName created successfully"]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->close();
    }
    if (file_exists($dbPath)) {
        unlink($dbPath);
    }
    echo json_encode(['error' => 'Database creation failed: ' . $e->getMessage()]);
}
?>