<?php
header('Content-Type: text/html');
session_start();

function sanitizeToken($token) {
    return preg_replace('/[^a-zA-Z0-9]/', '', $token);
}

function sanitizeTableName($name) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
}

function sanitizeColumnName($name) {
    return preg_replace('/[^a-zA-Z0-9_]/', '', $name);
}

$token = isset($_GET['token']) ? sanitizeToken($_GET['token']) : null;
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : null);
$table = isset($_POST['table']) ? sanitizeTableName($_POST['table']) : (isset($_GET['table']) ? sanitizeTableName($_GET['table']) : null);
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$perPage = 10;

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
    $db->enableExceptions(true);
    
    // Process form actions
    $message = '';
    
    if ($action === 'editrow' && isset($_POST['id']) && isset($_POST['data']) && $table) {
        $id = intval($_POST['id']);
        $data = $_POST['data'];
        
        $cols = [];
        $params = [];
        foreach ($data as $col => $value) {
            $cols[] = sanitizeColumnName($col) . " = ?";
            $params[] = $value;
        }
        
        $stmt = $db->prepare("UPDATE $table SET " . implode(', ', $cols) . " WHERE ROWID = ?");
        $paramIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex, $id);
        $stmt->execute();
        $message = "Record updated successfully";
    }
    
    else if ($action === 'deleterow' && isset($_POST['id']) && $table) {
        $id = intval($_POST['id']);
        $stmt = $db->prepare("DELETE FROM $table WHERE ROWID = ?");
        $stmt->bindValue(1, $id);
        $stmt->execute();
        $message = "Record deleted successfully";
    }
    
    else if ($action === 'addrow' && isset($_POST['data']) && $table) {
        $data = $_POST['data'];
        $cols = array_keys($data);
        $placeholders = array_fill(0, count($cols), '?');
        
        $stmt = $db->prepare("INSERT INTO $table (" . implode(', ', array_map('sanitizeColumnName', $cols)) . 
                           ") VALUES (" . implode(', ', $placeholders) . ")");
        
        $paramIndex = 1;
        foreach ($data as $value) {
            $stmt->bindValue($paramIndex++, $value);
        }
        
        $stmt->execute();
        $message = "Record added successfully";
    }
    
    else if ($action === 'createtable' && isset($_POST['tableName']) && isset($_POST['columns'])) {
        $tableName = sanitizeTableName($_POST['tableName']);
        $columns = $_POST['columns'];
        
        $columnDefs = [];
        foreach ($columns as $column) {
            if (!empty($column['name'])) {
                $name = sanitizeColumnName($column['name']);
                $type = in_array($column['type'], ['TEXT', 'INTEGER', 'REAL', 'BLOB']) ? $column['type'] : 'TEXT';
                $columnDefs[] = "$name $type" . (!empty($column['primary']) ? ' PRIMARY KEY' : '');
            }
        }
        
        if (!empty($columnDefs)) {
            $db->exec("CREATE TABLE $tableName (" . implode(', ', $columnDefs) . ")");
            $message = "Table '$tableName' created successfully";
            $table = $tableName;
        }
    }
    
    else if ($action === 'addcolumn' && isset($_POST['columnName']) && isset($_POST['columnType']) && $table) {
        $columnName = sanitizeColumnName($_POST['columnName']);
        $columnType = in_array($_POST['columnType'], ['TEXT', 'INTEGER', 'REAL', 'BLOB']) ? $_POST['columnType'] : 'TEXT';
        
        $db->exec("ALTER TABLE $table ADD COLUMN $columnName $columnType");
        $message = "Column '$columnName' added to table '$table'";
    }

    // Get all tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table'");
    $tableList = [];
    while ($tableRow = $tables->fetchArray(SQLITE3_ASSOC)) {
        $tableList[] = $tableRow['name'];
    }

    // Generate HTML
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>SimpleBase Admin - <?php echo htmlentities($token); ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
            h1 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
            h2 { color: #2980b9; margin-top: 30px; }
            
            .message { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 15px 0; border-radius: 4px; }
            .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
            
            /* Navigation */
            .nav { background: #f8f9fa; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
            .nav a { margin-right: 15px; text-decoration: none; color: #3498db; font-weight: bold; }
            .nav a:hover { text-decoration: underline; }
            
            /* Tables */
            table { border-collapse: collapse; width: 100%; margin: 20px 0; box-shadow: 0 2px 3px rgba(0,0,0,0.1); }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background: #f5f5f5; position: sticky; top: 0; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            tr:hover { background-color: #f1f1f1; }
            
            /* Forms */
            form { margin: 20px 0; }
            input, select, button { padding: 8px; margin: 5px 0; }
            button, .btn { background: #3498db; color: white; border: none; padding: 10px 15px; cursor: pointer; border-radius: 4px; }
            button:hover, .btn:hover { background: #2980b9; }
            button.danger { background: #e74c3c; }
            button.danger:hover { background: #c0392b; }
            
            /* Responsive */
            .table-container { overflow-x: auto; }
            
            /* Pagination */
            .pagination { margin: 20px 0; text-align: center; }
            .pagination a, .pagination span { display: inline-block; padding: 8px 16px; text-decoration: none; color: #3498db; margin: 0 4px; border-radius: 4px; }
            .pagination a:hover { background-color: #ddd; }
            .pagination .active { background-color: #3498db; color: white; }
            
            /* Tabs */
            .tab { border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 15px; }
            .tab button { background: #f1f1f1; border: 1px solid #ddd; border-bottom: none; padding: 10px 20px; cursor: pointer; }
            .tab button:hover { background: #ddd; }
            .tab button.active { background: #3498db; color: white; }
            .tabcontent { display: none; padding: 15px; border-top: none; }
        </style>
    </head>
    <body>
        <h1>SimpleBase Admin: <?php echo htmlentities($token); ?></h1>
        
        <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlentities($message); ?></div>
        <?php endif; ?>
        
        <div class="nav">
            <a href="?token=<?php echo urlencode($token); ?>">Tables</a>
            <a href="?token=<?php echo urlencode($token); ?>&action=newTable">Create New Table</a>
        </div>
        
        <?php if ($action === 'newTable'): ?>
            <h2>Create New Table</h2>
            <form method="post" action="?token=<?php echo urlencode($token); ?>">
                <input type="hidden" name="action" value="createtable">
                <div>
                    <label>Table Name:</label>
                    <input type="text" name="tableName" required pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores allowed">
                </div>
                <h3>Columns</h3>
                <div id="columns">
                    <div class="column">
                        <input type="text" name="columns[0][name]" placeholder="Column Name" required pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores allowed">
                        <select name="columns[0][type]">
                            <option value="TEXT">TEXT</option>
                            <option value="INTEGER">INTEGER</option>
                            <option value="REAL">REAL</option>
                            <option value="BLOB">BLOB</option>
                        </select>
                        <label><input type="checkbox" name="columns[0][primary]"> Primary Key</label>
                    </div>
                </div>
                <button type="button" id="addColumn">Add Column</button>
                <button type="submit">Create Table</button>
            </form>
            
            <script>
                let columnCount = 1;
                document.getElementById('addColumn').addEventListener('click', function() {
                    const columnsDiv = document.getElementById('columns');
                    const newColumn = document.createElement('div');
                    newColumn.className = 'column';
                    newColumn.innerHTML = `
                        <input type="text" name="columns[${columnCount}][name]" placeholder="Column Name" required pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores allowed">
                        <select name="columns[${columnCount}][type]">
                            <option value="TEXT">TEXT</option>
                            <option value="INTEGER">INTEGER</option>
                            <option value="REAL">REAL</option>
                            <option value="BLOB">BLOB</option>
                        </select>
                        <label><input type="checkbox" name="columns[${columnCount}][primary]"> Primary Key</label>
                    `;
                    columnsDiv.appendChild(newColumn);
                    columnCount++;
                });
            </script>
        <?php elseif ($action === 'addcolumnform' && $table): ?>
            <h2>Add Column to <?php echo htmlentities($table); ?></h2>
            <form method="post" action="?token=<?php echo urlencode($token); ?>&table=<?php echo urlencode($table); ?>">
                <input type="hidden" name="action" value="addcolumn">
                <div>
                    <label>Column Name:</label>
                    <input type="text" name="columnName" required pattern="[a-zA-Z0-9_]+" title="Only letters, numbers, and underscores allowed">
                </div>
                <div>
                    <label>Column Type:</label>
                    <select name="columnType">
                        <option value="TEXT">TEXT</option>
                        <option value="INTEGER">INTEGER</option>
                        <option value="REAL">REAL</option>
                        <option value="BLOB">BLOB</option>
                    </select>
                </div>
                <button type="submit">Add Column</button>
            </form>
        <?php elseif ($table): ?>
            <?php
            // Get table structure
            $columns = $db->query("PRAGMA table_info($table)");
            $colNames = [];
            $primaryKey = null;
            
            while ($col = $columns->fetchArray(SQLITE3_ASSOC)) {
                $colNames[] = $col['name'];
                if ($col['pk'] == 1) {
                    $primaryKey = $col['name'];
                }
            }
            
            // Count total records for pagination
            $countStmt = $db->query("SELECT COUNT(*) as count FROM $table");
            $count = $countStmt->fetchArray(SQLITE3_ASSOC)['count'];
            $totalPages = ceil($count / $perPage);
            $offset = ($page - 1) * $perPage;
            
            // Get data with pagination
            $result = $db->query("SELECT rowid, * FROM $table LIMIT $perPage OFFSET $offset");
            ?>
            
            <div class="nav">
                <a href="?token=<?php echo urlencode($token); ?>">« Back to Tables</a>
                <a href="?token=<?php echo urlencode($token); ?>&table=<?php echo urlencode($table); ?>&action=addcolumnform">Add Column</a>
            </div>
            
            <h2>Table: <?php echo htmlentities($table); ?></h2>
            
            <div class="tab">
                <button class="tablinks active" onclick="openTab(event, 'viewData')">View Data</button>
                <button class="tablinks" onclick="openTab(event, 'addData')">Add Record</button>
            </div>
            
            <div id="viewData" class="tabcontent" style="display:block;">
                <div class="table-container">
                    <table>
                        <tr>
                            <th>Actions</th>
                            <?php foreach ($colNames as $col): ?>
                                <th><?php echo htmlentities($col); ?></th>
                            <?php endforeach; ?>
                        </tr>
                        
                        <?php while ($row = $result->fetchArray(SQLITE3_ASSOC)): ?>
                            <tr id="row-<?php echo $row['rowid']; ?>">
                                <td>
                                    <button onclick="editRow(<?php echo $row['rowid']; ?>)" class="btn">Edit</button>
                                    <button onclick="deleteRow(<?php echo $row['rowid']; ?>)" class="btn danger">Delete</button>
                                </td>
                                <?php foreach ($colNames as $col): ?>
                                    <td class="data" data-column="<?php echo htmlentities($col); ?>"><?php echo htmlentities($row[$col]); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                </div>
                
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?token=<?php echo urlencode($token); ?>&table=<?php echo urlencode($table); ?>&page=<?php echo $page-1; ?>">Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?token=<?php echo urlencode($token); ?>&table=<?php echo urlencode($table); ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?token=<?php echo urlencode($token); ?>&table=<?php echo urlencode($table); ?>&page=<?php echo $page+1; ?>">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div id="addData" class="tabcontent">
                <h3>Add New Record</h3>
                <form method="post" action="?token=<?php echo urlencode($token); ?>&table=<?php echo urlencode($table); ?>">
                    <input type="hidden" name="action" value="addrow">
                    <?php foreach ($colNames as $col): ?>
                        <?php if ($primaryKey !== $col): ?>
                        <div>
                            <label><?php echo htmlentities($col); ?>:</label>
                            <input type="text" name="data[<?php echo htmlentities($col); ?>]">
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <button type="submit">Add Record</button>
                </form>
            </div>
            
            <!-- Edit Modal -->
            <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.7); z-index:1000;">
                <div style="background-color:white; margin:10% auto; padding:20px; width:60%; border-radius:5px;">
                    <h2>Edit Record</h2>
                    <form id="editForm" method="post" action="?token=<?php echo urlencode($token); ?>&table=<?php echo urlencode($table); ?>">
                        <input type="hidden" name="action" value="editrow">
                        <input type="hidden" id="rowId" name="id" value="">
                        <div id="editFields"></div>
                        <button type="submit">Save Changes</button>
                        <button type="button" onclick="document.getElementById('editModal').style.display='none'">Cancel</button>
                    </form>
                </div>
            </div>
            
            <script>
                function editRow(rowId) {
                    const row = document.getElementById('row-' + rowId);
                    const fields = row.querySelectorAll('.data');
                    const formFields = document.getElementById('editFields');
                    formFields.innerHTML = '';
                    
                    fields.forEach(field => {
                        const column = field.dataset.column;
                        const value = field.textContent;
                        
                        const div = document.createElement('div');
                        div.innerHTML = `
                            <label>${column}:</label>
                            <input type="text" name="data[${column}]" value="${value}">
                        `;
                        formFields.appendChild(div);
                    });
                    
                    document.getElementById('rowId').value = rowId;
                    document.getElementById('editModal').style.display = 'block';
                }
                
                function deleteRow(rowId) {
                    if (confirm('Are you sure you want to delete this record?')) {
                        const form = document.createElement('form');
                        form.method = 'post';
                        form.action = `?token=<?php echo urlencode($token); ?>&table=<?php echo urlencode($table); ?>`;
                        
                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = 'deleterow';
                        form.appendChild(actionInput);
                        
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id';
                        idInput.value = rowId;
                        form.appendChild(idInput);
                        
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
                
                function openTab(evt, tabName) {
                    const tabcontent = document.getElementsByClassName("tabcontent");
                    for (let i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }
                    
                    const tablinks = document.getElementsByClassName("tablinks");
                    for (let i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                    }
                    
                    document.getElementById(tabName).style.display = "block";
                    evt.currentTarget.className += " active";
                }
            </script>
            
        <?php else: ?>
            <h2>Database Tables</h2>
            <?php if (empty($tableList)): ?>
                <p>No tables found in this database.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($tableList as $tableName): ?>
                        <li>
                            <a href="?token=<?php echo urlencode($token); ?>&table=<?php echo urlencode($tableName); ?>">
                                <?php echo htmlentities($tableName); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
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