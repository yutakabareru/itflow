<?php

function forkDatabaseVersionColumnExists($mysqli) {
    $result = mysqli_query($mysqli, "SHOW COLUMNS FROM `settings` LIKE 'config_fork_database_version'");
    return $result && mysqli_num_rows($result) > 0;
}

function ensureForkDatabaseVersionColumn($mysqli) {
    if (forkDatabaseVersionColumnExists($mysqli)) {
        return;
    }

    mysqli_query($mysqli, "ALTER TABLE `settings` ADD `config_fork_database_version` int(11) NOT NULL DEFAULT 0 AFTER `config_current_database_version`");
}

function getCurrentForkDatabaseVersion($mysqli) {
    if (!forkDatabaseVersionColumnExists($mysqli)) {
        return 0;
    }

    $result = mysqli_query($mysqli, "SELECT `config_fork_database_version` FROM `settings` WHERE `company_id` = 1 LIMIT 1");
    $row = mysqli_fetch_assoc($result);

    return intval($row['config_fork_database_version'] ?? 0);
}

function upstreamDatabaseNeedsUpdate() {
    return defined('LATEST_DATABASE_VERSION')
        && defined('CURRENT_DATABASE_VERSION')
        && version_compare(LATEST_DATABASE_VERSION, CURRENT_DATABASE_VERSION, '>');
}

function forkDatabaseNeedsUpdate($mysqli) {
    if (!defined('LATEST_FORK_DATABASE_VERSION')) {
        return false;
    }

    $current_fork_version = defined('CURRENT_FORK_DATABASE_VERSION')
        ? intval(CURRENT_FORK_DATABASE_VERSION)
        : getCurrentForkDatabaseVersion($mysqli);

    return LATEST_FORK_DATABASE_VERSION > $current_fork_version;
}

function anyDatabaseNeedsUpdate($mysqli) {
    return upstreamDatabaseNeedsUpdate() || forkDatabaseNeedsUpdate($mysqli);
}
