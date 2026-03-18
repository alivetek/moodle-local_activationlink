<?php

defined('MOODLE_INTERNAL') || die();

$observers = [
    [
        'eventname'   => '\core\event\user_created',
        'callback'    => 'local_activationlink\observer::user_created',
        'internal'    => false,
    ],
];
