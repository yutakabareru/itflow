<?php

if (!defined('CURRENT_DATABASE_VERSION')) {
    require_once __DIR__ . '/load_global_settings.php';
}

enforceModuleEnabled('ticketing', 'json');
