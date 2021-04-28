<?php
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
