<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GeminiClient
{
    public function generateJson(string $prompt, array $schema): ?array
    {
        $key = config('services.gemini.key');

        if (!$key) {
            return null;
        }

        $url = 'https://generativelanguage.googleapis.com/v1beta/interactions';

        $data = [
            'model' => config('services.gemini.model', 'gemini-3.1-flash-lite'),
            'input' => $prompt,
            'store' => false,
            'response_format' => [
                'type' => 'text',
                'mime_type' => 'application/json',
                'schema' => $schema,
            ],
        ];

        $request = Http::withHeaders([
            'x-goog-api-key' => $key,
            'Api-Revision' => '2026-05-20',
        ])->connectTimeout(5)->timeout(25)->withoutRedirecting();

        try {
            $response = $request->post($url, $data);

            if ($response->status() === 503) {
                $response = $request->post($url, $data);
            }
        } catch (ConnectionException $error) {
            return null;
        }

        if (!$response->successful() || $response->json('status') !== 'completed') {
            return null;
        }

        $text = '';

        $steps = $response->json('steps', []);
        if (!is_array($steps)) return null;
        foreach ($steps as $step) {
            if (!is_array($step)) return null;
            if (($step['type'] ?? null) === 'model_output') {
                if (!is_array($step['content'] ?? null)) return null;
                foreach ($step['content'] as $block) {
                    if (!is_array($block)) return null;
                    if (($block['type'] ?? null) === 'text') {
                        if (!is_string($block['text'] ?? null)) return null;
                        $text .= $block['text'];
                    }
                }
            }
        }

        if (strlen($text) > 100_000) return null;
        $result = json_decode($text, true);

        return is_array($result) ? $result : null;
    }
}
