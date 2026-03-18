<?php

defined('MOODLE_INTERNAL') || die();

$definitions = [
    'failed_attempts' => [
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => false,
        'simpledata' => true,
        'ttl' => 3600, // 1 hour
    ],
];
