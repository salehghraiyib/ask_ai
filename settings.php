<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // API Key setting.
    $settings->add(new admin_setting_configpasswordunmask(
        'block_ask_ai/gemini_api_key',
        get_string('api_key_label', 'block_ask_ai'),
        get_string('api_key_desc', 'block_ask_ai'),
        ''
    ));

    // Model selection setting.
    $settings->add(new admin_setting_configtext(
        'block_ask_ai/model',
        get_string('model_label', 'block_ask_ai'),
        get_string('model_desc', 'block_ask_ai'),
        'gemini-1.5-flash',
        PARAM_TEXT
    ));

}