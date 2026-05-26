<?php
/*
 * Fork-specific database migrations.
 * These run independently of admin/database_updates.php (upstream ITFlow).
 */

require_once __DIR__ . '/../includes/fork_database_helpers.php';

if (!defined("LATEST_FORK_DATABASE_VERSION") || !isset($mysqli)) {
    echo "Cannot access this file directly.";
    exit();
}

$current_fork_db_version = getCurrentForkDatabaseVersion($mysqli);

while ($current_fork_db_version < LATEST_FORK_DATABASE_VERSION) {

    if ($current_fork_db_version == 0) {
        ensureForkDatabaseVersionColumn($mysqli);

        // Legacy: an earlier fork build reused upstream version 2.4.5 for this feature.
        $result = mysqli_query($mysqli, "SELECT `config_current_database_version` FROM `settings` WHERE `company_id` = 1 LIMIT 1");
        $row = mysqli_fetch_assoc($result);
        if (($row['config_current_database_version'] ?? '') === '2.4.5') {
            mysqli_query($mysqli, "UPDATE `settings` SET `config_current_database_version` = '2.4.4' WHERE `company_id` = 1");
        }

        mysqli_query($mysqli, "
            CREATE TABLE IF NOT EXISTS `asset_interface_tagged_networks` (
                `interface_id` int(11) NOT NULL,
                `network_id` int(11) NOT NULL,
                PRIMARY KEY (`interface_id`,`network_id`),
                KEY `network_id` (`network_id`),
                CONSTRAINT `asset_interface_tagged_networks_ibfk_1` FOREIGN KEY (`interface_id`) REFERENCES `asset_interfaces` (`interface_id`) ON DELETE CASCADE,
                CONSTRAINT `asset_interface_tagged_networks_ibfk_2` FOREIGN KEY (`network_id`) REFERENCES `networks` (`network_id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        mysqli_query($mysqli, "UPDATE `settings` SET `config_fork_database_version` = 1 WHERE `company_id` = 1");
    }

    $new_version = getCurrentForkDatabaseVersion($mysqli);
    if ($new_version <= $current_fork_db_version) {
        break;
    }

    $current_fork_db_version = $new_version;
}
