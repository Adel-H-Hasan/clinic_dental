<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SmileAiClient
{
    public function simulate(
        UploadedFile $file,
        string $mode,
        string $strength,
        string $treatment
    ): string {

        $base = rtrim(
            (string) config('services.medassist_ai.url'),
            ' /'
        );

        $token = (string) config('services.medassist_ai.token');

        if (!$base || !$token) {
            throw new RuntimeException('AI service not configured');
        }

        $stream = fopen($file->getRealPath(), 'rb');

        if (!$stream) {
            throw new RuntimeException('Cannot read image');
        }

        try {
            $response = Http::withHeaders([
                'X-AI-Token' => $token
            ])
                ->connectTimeout(10)
                ->timeout(max(1, min(540, (int) config('services.medassist_ai.timeout', 540))))
                ->withoutRedirecting()
                ->attach(
                    'file',
                    $stream,
                    'photo.' . $file->extension()
                )
                ->post(
                    $base . '/api/v1/simulate',
                    [
                        'mode' => $mode,
                        'strength' => $strength,
                        'treatment' => $treatment
                    ]
                );
        } finally {
            fclose($stream);
        }

        if (
            !$response->successful() ||
            !str_starts_with(
                strtolower($response->header('Content-Type', '')),
                'image/png'
            )
        ) {
            throw new RuntimeException(
                'AI HTTP ' . $response->status()
            );
        }

        $dimensions = @getimagesizefromstring($response->body());
        if (
            strlen($response->body()) > 25 * 1024 * 1024 ||
            !$dimensions || $dimensions[2] !== IMAGETYPE_PNG ||
            $dimensions[0] * $dimensions[1] > 16_000_000
        ) {
            throw new RuntimeException(
                'Invalid image returned by AI service'
            );
        }

        return $response->body();
    }
}
