<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'block_ask_ai_get_response' => [
        'classname'   => 'block_ask_ai\external\chat_handler',
        'methodname'  => 'get_response',
        'description' => 'Sends a query to Gemini and returns the response.',
        'type'        => 'read', // Use 'read' for getting data, 'write' for saving
        'ajax'        => true,   // Must be true to allow calling from JS
        'capabilities'=> 'block/ask_ai:view', // Ensures the user has permission
    ],
];