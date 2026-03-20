<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'block_ask_ai/gemini_api_key',
        get_string('api_key_label', 'block_ask_ai'),
        get_string('api_key_desc', 'block_ask_ai'),
        '',
        PARAM_RAW
    ));

    $settings->add(new admin_setting_configtext(
        'block_ask_ai/model',
        get_string('model_label', 'block_ask_ai'),
        get_string('model_desc', 'block_ask_ai'),
        'gemini-2.5-flash-lite',
        PARAM_TEXT
    ));

    $reindexurl = new \moodle_url('/blocks/ask_ai/action.php', [
        'action' => 'reindex',
        'sesskey' => sesskey()
    ]);

    $settings->add(new admin_setting_heading(
        'block_ask_ai/reindex_heading',
        get_string('index_mgmt', 'block_ask_ai'),
        '<a href="' . $reindexurl . '" class="btn btn-primary">' . get_string('reindex_button', 'block_ask_ai') . '</a>'
    ));
}