<?php
/**
 * Ask AI Block - Course Level Assistant
 *
 * @package    block_ask_ai
 * @copyright  2026 Saleh/TUCED
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_ask_ai extends block_base {

    public function hide_header() {
        return true;
    }

public function init() {
    $this->title = get_string('pluginname', 'block_ask_ai');

    // ONLY run this if we are explicitly in the admin area and the action is set.
    // This prevents the code from 'pre-loading' and crashing the JS on other pages.
    if (is_siteadmin() && optional_param('action', '', PARAM_ALPHA) === 'reindex') {
        
        // We delay the task loading until the last possible second.
        $task = new \block_ask_ai\task\reindex_adhoc();
        \core\task\manager::queue_adhoc_task($task, true);
        
        // Redirect back to the settings page to 'clean' the URL and show the message properly.
        $returnurl = new moodle_url('/admin/settings.php', ['section' => 'blocksettingask_ai']);
        redirect($returnurl, get_string('reindex_queued', 'block_ask_ai'), 5);
    }
}

    public function instance_allow_config() {
        return true;
    }

    /**
     * Returns the content to be displayed inside the block.
     *
     * @return stdClass
     */
    public function get_content() {
        global $OUTPUT, $COURSE, $PAGE;

        // If content is already generated, return it.
        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass();
        

        // Prepare data for the Mustache template.
        $renderdata = [
            'instanceid' => $this->instance->id,
            'courseid'   => $COURSE->id,
            'coursename' => format_string($COURSE->fullname),
        ];

        // Render the HTML from the Mustache template.
        $this->content->text = $OUTPUT->render_from_template('block_ask_ai/chat', $renderdata);

        $this->content->footer = '';

        // Initialize the JavaScript (AMD module).
        $PAGE->requires->js_call_amd('block_ask_ai/chatv2', 'init', [
            $this->instance->id, 
            $COURSE->id
        ]);

        return $this->content;
    }

    /**
     * Defines where this block can be added.
     * We want this specifically on course pages.
     */
    public function applicable_formats() {
        return [
            'course-view'    => true,
            'site'           => true,
            'mod'            => true, 
            'my'             => true, 
        ];
    }

    /**
     * Allow multiple instances of the block in one course? 
     * Usually false for a chat assistant.
     */
    public function instance_allow_multiple() {
        return false;
    }

    /**
     * Enable global configuration (the settings.php file).
     */
    public function has_config() {
        return true;
    }
}