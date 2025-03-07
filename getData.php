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

    $page = isset($input['page']) ? max(1, intval($input['page'])) : 1;
    $limit = isset($input['limit']) ? min(10000, max(1, intval($input['limit']))) : 100;
    $offset = ($page - 1) * $limit;

    $orderBy = '';
    if (isset($input['orderBy'])) {
        $orderCol = preg_replace('/[^a-zA-Z0-9_]/', '', $input['orderBy']);
        $orderDir = isset($input['orderDir']) && strtoupper($input['orderDir']) === 'DESC' ? 'DESC' : 'ASC';
        $orderBy = " ORDER BY $orderCol $orderDir";
    }

    if ($input['query'] === 'getall') {
        $result = $db->query("SELECT * FROM $tableName$orderBy LIMIT $limit OFFSET $offset");
    } elseif ($input['query'] === 'getwhere') {
        if (!isset($input['where'])) {
            $db->close();
            echo json_encode(['error' => 'Missing where clause']);
            exit;
        }
        $where = $db->escapeString($input['where']);
        $columns = isset($input['columns']) ? implode(', ', array_map(function($col) { return preg_replace('/[^a-zA-Z0-9_]/', '', $col); }, $input['columns'])) : '*';
        $result = $db->query("SELECT $columns FROM $tableName WHERE $where$orderBy LIMIT $limit OFFSET $offset");
    } else {
        $db->close();
        echo json_encode(['error' => 'Invalid query type']);
        exit;
    }

    $data = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $data[] = $row;
    }

    $countQuery = $input['query'] === 'getall' 
        ? "SELECT COUNT(*) as count FROM $tableName"
        : "SELECT COUNT(*) as count FROM $tableName WHERE $where";
    $totalItems = $db->querySingle($countQuery);
    $totalPages = ceil($totalItems / $limit);

    $db->close();
    echo json_encode([
        'success' => true, 
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $totalItems,
            'totalPages' => $totalPages
        ]
    ]);

} catch (Exception $e) {
    if (isset($db)) {
        $db->close();
    }
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}
?>