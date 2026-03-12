<?php
namespace block_ask_ai\task;

defined('MOODLE_INTERNAL') || die();

use block_ask_ai\gemini_client;

class reindex_courses extends \core\task\scheduled_task {
    
    public function get_name() {
        return get_string('pluginname', 'block_ask_ai');
    }

    public function execute() {
        global $DB;

        // 1. Fetch all courses (excluding site home).
        $courses = $DB->get_records('course', [], '', 'id, fullname, summary, summaryformat');

        foreach ($courses as $course) {
            if ($course->id == 1) continue;

            // Start building the content string with the main course summary.
            $content = "Course Name: {$course->fullname}. ";
            $content .= "Overview: " . strip_tags($course->summary) . " ";

            // 2. Fetch all visible sections for this course to get paragraph text.
            $sections = $DB->get_records('course_sections', ['course' => $course->id, 'visible' => 1], 'section ASC');

            foreach ($sections as $section) {
                if (!empty($section->summary)) {
                    $sectiontext = strip_tags($section->summary);
                    $content .= "Section {$section->section} Content: {$sectiontext} ";
                }
            }
            
            try {
                // 3. Generate the vector for the combined course + section text.
                $vector = gemini_client::get_embedding($content);
                
                $record = new \stdClass();
                $record->courseid = $course->id;
                $record->content  = $content; // Now contains all section paragraphs.
                $record->embedding = json_encode($vector);
                $record->timemodified = time();

                // 4. Update or Insert into your index table.
                if ($existing = $DB->get_record('block_ask_ai_index', ['courseid' => $course->id])) {
                    $record->id = $existing->id;
                    $DB->update_record('block_ask_ai_index', $record);
                } else {
                    $DB->insert_record('block_ask_ai_index', $record);
                }
                
                mtrace("Deep-Indexed: " . $course->fullname);
            } catch (\Exception $e) {
                mtrace("Failed to index course {$course->id}: " . $e->getMessage());
            }
        }
    }
}