<?php
namespace block_ask_ai\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use context_course;
use block_ask_ai\gemini_client;

/**
 * External API for the Ask AI block.
 */
class chat_handler extends external_api {

    /**
     * Define the parameters for the get_response function.
     */
    public static function get_response_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'The ID of the course context'),
            'query'    => new external_value(PARAM_TEXT, 'The student question')
        ]);
    }

    /**
     * The logic that processes the AI request.
     */
    public static function get_response($courseid, $query) {
        global $USER, $DB;

        // 1. Validate parameters.
        $params = self::validate_parameters(self::get_response_parameters(), [
            'courseid' => $courseid,
            'query'    => $query
        ]);

        // 2. Security Check: Ensure user is enrolled or has access to the course.
        $context = context_course::instance($params['courseid']);
        self::validate_context($context);
        
        // 3. Optional: Check a custom capability if you defined one in access.php.
        //require_capability('block/ask_ai:view', $context);

        // 4. Fetch Course Data to provide "Context" to Gemini.
        $course = $DB->get_record('course', ['id' => $params['courseid']], '*', MUST_EXIST);

        // 5. Construct the System Prompt.
        // This prevents the AI from just being a general chatbot.
        $system_prompt = "You are a helpful teaching assistant for the Moodle course: '{$course->fullname}'. " .
                         "The course summary is: " . strip_tags($course->summary) . ". " .
                         "Answer the student as concisely as possible. Student asks: ";
        
        $full_prompt = $system_prompt . $params['query'];

        // 6. Call your custom Gemini Client.
        try {
            $answer = gemini_client::generate_response($full_prompt);
            $status = 200;
        } catch (\Exception $e) {
            $answer = "I'm sorry, I encountered an error: " . $e->getMessage();
            $status = 500;
        }

        // 7. Return the response to the JavaScript frontend.
        return [
            'status' => $status,
            'answer' => $answer
        ];
    }

    /**
     * Define what the function returns to the JS caller.
     */
    public static function get_response_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, 'HTTP-style status code'),
            'answer' => new external_value(PARAM_RAW, 'The plain text or markdown response from Gemini')
        ]);
    }
}