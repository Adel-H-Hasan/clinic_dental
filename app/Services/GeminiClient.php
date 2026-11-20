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
            'model' => 'gemini-3.1-flash-lite',
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
        ])->connectTimeout(5)->timeout(25);

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

        foreach ($response->json('steps', []) as $step) {
            if (($step['type'] ?? null) === 'model_output') {
                foreach ($step['content'] ?? [] as $block) {
                    if (($block['type'] ?? null) === 'text') {
                        $text .= $block['text'] ?? '';
                    }
                }
            }
        }

        $result = json_decode($text, true);

        return is_array($result) ? $result : null;
    }
}
