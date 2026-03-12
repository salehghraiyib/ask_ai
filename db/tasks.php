<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'block_ask_ai\task\reindex_courses',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '2', // Runs at 2 AM daily
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ],
];