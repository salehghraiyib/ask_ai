<?php
namespace block_ask_ai\external;

defined('MOODLE_INTERNAL') || die();

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_single_structure;
use core_external\external_value;
use context_system;
use block_ask_ai\gemini_client;

/**
 * External API for the Knowledge Navigator.
 */
class chat_handler extends external_api {

    public static function get_response_parameters() {
        return new external_function_parameters([
            // Changed courseid to optional since we are searching the whole site.
            'courseid' => new external_value(PARAM_INT, 'Context ID', VALUE_DEFAULT, 0),
            'query'    => new external_value(PARAM_TEXT, 'The user question')
        ]);
    }

    public static function get_response($courseid, $query) {
        global $DB, $CFG;

        $params = self::validate_parameters(self::get_response_parameters(), [
            'courseid' => $courseid,
            'query'    => $query
        ]);

        // Validate system context since this is now a site-wide assistant.
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('block/ask_ai:view', $context);

        try {
            // 1. Convert user query into an embedding vector.
            $query_vector = gemini_client::get_embedding($params['query']);

            // 2. Fetch all course embeddings from your custom index table.
            $index_records = $DB->get_records('block_ask_ai_index');
            
            $matches = [];
            foreach ($index_records as $record) {
                $course_vector = json_decode($record->embedding);
                // 3. Perform Cosine Similarity to find the best content matches.
                $similarity = self::cosine_similarity($query_vector, $course_vector);
                $matches[] = [
                    'similarity' => $similarity,
                    'content'    => $record->content,
                    'courseid'   => $record->courseid
                ];
            }

            // 4. Sort matches by highest similarity and take the top 3.
            usort($matches, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
            $top_matches = array_slice($matches, 0, 3);

            // 5. Build the "Knowledge Navigator" Context.
$context_text = "";
foreach ($top_matches as $match) {
    $url = $CFG->wwwroot . "/course/view.php?id=" . $match['courseid'];
    // We explicitly label the context for the AI to reference.
    $context_text .= "QUELLE (Kurs ID: {$match['courseid']}):\n{$match['content']}\nLink zum Kurs: {$url}\n---\n";
}

// Updated professional German instruction for a "Navigator" persona.
$instruction = "Du bist der CATI Wissens-Navigator. Deine Aufgabe ist es, Nutzer präzise durch das Wissensökosystem zu führen. 
Regeln:
1. Antworte nur auf Basis des Kontexts.
2. Wenn du einen Kurs empfiehlst, schreibe am Ende deiner Antwort für JEDEN Kurs exakt dieses Format: 
   [BUTTON:Kursname|URL]
3. Erkläre im Text davor kurz, warum der Kurs für den Nutzer relevant ist.
4. Antworte immer auf Deutsch.";

$user_prompt = "Hier ist der verfügbare Wissenskontext:\n" . $context_text . "\n\nAnfrage des Nutzers: " . $query;

$answer = gemini_client::generate_response($user_prompt, $instruction);
            $status = 200;

        } catch (\Exception $e) {
    $answer = "Bei der Analyse des Wissensnetzwerks ist ein Fehler aufgetreten: " . $e->getMessage();
    $status = 500;
}

        return [
            'status' => $status,
            'answer' => $answer
        ];
    }

    private static function cosine_similarity($vec1, $vec2) {
        $dot_product = 0;
        $mag1 = 0;
        $mag2 = 0;
        foreach ($vec1 as $i => $val) {
            $dot_product += $val * $vec2[$i];
            $mag1 += $val ** 2;
            $mag2 += $vec2[$i] ** 2;
        }
        return $dot_product / (sqrt($mag1) * sqrt($mag2));
    }

    public static function get_response_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_INT, 'Status code'),
            'answer' => new external_value(PARAM_RAW, 'Response')
        ]);
    }
}