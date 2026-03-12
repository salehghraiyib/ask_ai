<?php
namespace block_ask_ai;

global $CFG;
require_once($CFG->libdir . '/filelib.php');

class gemini_client {
public static function generate_response($prompt, $system_instruction = '') {
    $apikey = get_config('block_ask_ai', 'gemini_api_key');
    $model = get_config('block_ask_ai', 'model');
    
    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apikey}";

    $payload = [
        "contents" => [[
            "parts" => [["text" => $prompt]]
        ]],
        "system_instruction" => [
            "parts" => [["text" => $system_instruction]]
        ],
    
        "generationConfig" => [
            "temperature" => 0.1,
            "topP" => 0.95,
            "maxOutputTokens" => 1000
        ]
    ];

    $curl = new \curl();
    $curl->setHeader(['Content-Type: application/json']);
    
    $response = $curl->post($url, json_encode($payload));
    $result = json_decode($response);

    // Improved error handling
    if (isset($result->error)) {
        throw new \Exception("Gemini API Error: " . $result->error->message);
    }

    return $result->candidates[0]->content->parts[0]->text ?? 'Error: No response from Gemini.';
}

    public static function get_embedding($text) {
    $apikey = get_config('block_ask_ai', 'gemini_api_key');
    $url = "https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key={$apikey}";

    $payload = [
        "model" => "models/text-embedding-004",
        "content" => ["parts" => [["text" => $text]]]
    ];

    $curl = new \curl();
    $curl->setHeader(['Content-Type: application/json']);
    $response = $curl->post($url, json_encode($payload));
    $result = json_decode($response);

    return $result->embedding->values; // This returns an array of floats
}
}