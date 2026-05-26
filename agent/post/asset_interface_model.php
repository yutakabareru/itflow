<?php
defined('FROM_POST_HANDLER') || die("Direct file access is not allowed");

$name = sanitizeInput($_POST['name']);
$primary_interface = sanitizeInput($_POST['primary_interface']) ?? 0;
$description = sanitizeInput($_POST['description']);
$type = sanitizeInput($_POST['type']);
$mac = sanitizeInput($_POST['mac']);
$ip = sanitizeInput($_POST['ip']);
if ($_POST['dhcp'] == 1){
    $ip = 'DHCP';
}
$nat_ip = sanitizeInput($_POST['nat_ip']);
$ipv6 = sanitizeInput($_POST['ipv6']);
$network = intval($_POST['network']);
$tagged_networks = [];
if (isset($_POST['tagged_networks']) && is_array($_POST['tagged_networks'])) {
    foreach ($_POST['tagged_networks'] as $tagged_network_id) {
        $tagged_network_id = intval($tagged_network_id);
        if ($tagged_network_id > 0) {
            $tagged_networks[] = $tagged_network_id;
        }
    }
}
$notes = sanitizeInput($_POST['notes']);
$connected_to = intval($_POST['connected_to']);
