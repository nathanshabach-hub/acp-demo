<?php
require_once __DIR__ . '/config/my_const.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT id, conventionseasons_id, conflict_user_ids, conflict_user_ids_group FROM schedulings");
    $schedulings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasConflicts = false;
    foreach ($schedulings as $row) {
        if (!empty($row['conflict_user_ids']) || !empty($row['conflict_user_ids_group'])) {
            echo "Convention Season ID: " . $row['conventionseasons_id'] . "\n";
            echo "  Individual Conflicts (User IDs): " . ($row['conflict_user_ids'] ? $row['conflict_user_ids'] : 'None') . "\n";
            echo "  Group Conflicts (Scheduling IDs): " . ($row['conflict_user_ids_group'] ? $row['conflict_user_ids_group'] : 'None') . "\n";
            $hasConflicts = true;
        }
    }

    if (!$hasConflicts) {
        echo "No conflicts found in any convention season schedulings.\n";
    }

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
