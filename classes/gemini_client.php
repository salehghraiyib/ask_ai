<?php
namespace block_ask_ai;

class gemini_client {
    public static function generate_response($prompt) {
        $apikey = get_config('block_ask_ai', 'gemini_api_key');
        $model = get_config('block_ask_ai', 'model');
        
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apikey}";

        $payload = [
            "contents" => [[
                "parts" => [["text" => $prompt]]
            ]]
        ];

        $curl = new \curl();
        $curl->setHeader(['Content-Type: application/json']);
        
        $response = $curl->post($url, json_encode($payload));
        $result = json_decode($response);

        return $result->candidates[0]->content->parts[0]->text ?? 'Error: No response from Gemini.';
    }
}