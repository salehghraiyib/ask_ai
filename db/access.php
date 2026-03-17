<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = [
    // 1. Viewing Permission: Set to 'user' so it can be managed at the system level
    'block/ask_ai:view' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'user' => CAP_ALLOW
        ]
    ],

    // 2. Adding to Dashboard: Restricted to Managers/Admins
    'block/ask_ai:myaddinstance' => [
        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ],
        'clonepermissionsfrom' => 'moodle/my:manageblocks'
    ],

    // 3. Adding to Course: Restricted to Managers/Admins
    'block/ask_ai:addinstance' => [
        'riskbitmask' => RISK_SPAM | RISK_XSS,
        'captype' => 'write',
        'contextlevel' => CONTEXT_BLOCK,
        'archetypes' => [
            'manager' => CAP_ALLOW
        ],
        'clonepermissionsfrom' => 'moodle/site:manageblocks'
    ],
];