<?php
require_once('../../config.php');
require_login();
require_admin();

$action = optional_param('action', '', PARAM_ALPHA);

if ($action === 'reindex') {
    require_sesskey();

    // Queue the adhoc task safely
    $task = new \block_ask_ai\task\reindex_adhoc();
    \core\task\manager::queue_adhoc_task($task, true);

    // Redirect back to settings with a success message
    $url = new moodle_url('/admin/settings.php', ['section' => 'blocksettingask_ai']);
    redirect($url, get_string('reindex_queued', 'block_ask_ai'));
}