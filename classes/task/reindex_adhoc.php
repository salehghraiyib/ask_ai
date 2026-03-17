<?php
namespace block_ask_ai\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Ad-hoc task to run the course reindexing.
 */
class reindex_adhoc extends \core\task\adhoc_task {
    public function execute() {
        // We simply call the existing logic from your scheduled task.
        $task = new \block_ask_ai\task\reindex_courses();
        $task->execute();
    }
}