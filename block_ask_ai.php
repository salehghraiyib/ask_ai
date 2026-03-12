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

    public function init() {
        $this->title = get_string('pluginname', 'block_ask_ai');
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
        
        // Ensure we are in a course context.
        if ($COURSE->id <= 1) { // 1 is usually the site/frontpage ID.
            $this->content->text = get_string('notincourse', 'block_ask_ai');
            return $this->content;
        }

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
            'course-view'    => true, // Allow on course main page.
            'site'           => true, // Disallow on front page.
            'mod'            => true,  // Allow inside activities
            'my'             => true, // Disallow on Dashboard
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