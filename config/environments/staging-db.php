<?php
defined( 'ABSPATH' ) || exit;
$wpdb->charset = 'utf8mb4';
$wpdb->collate = 'utf8mb4_unicode_520_ci';
$wpdb->save_queries = false;
$wpdb->recheck_timeout = 0.1;
$wpdb->persistent = false;
$wpdb->allow_bail = false;
$wpdb->max_connections = 10;
$wpdb->check_tcp_responsiveness = true;
$wpdb->add_database([
        'host'     => DB_HOST,
        'user'     => DB_USER,
        'password' => DB_PASSWORD,
        'name'     => DB_NAME,
        'read'     => 2,
        'write'    => 1,
]);
$wpdb->add_database([
        'host'     => DB_RO_HOST,
        'user'     => DB_USER,
        'password' => DB_PASSWORD,
        'name'     => DB_NAME,
        'read'     => 1,
        'write'    => 0,
]);
