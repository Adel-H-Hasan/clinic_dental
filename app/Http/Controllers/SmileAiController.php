<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateSmileRequest;
use App\Services\SmileAiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SmileAiController extends Controller
{
    public function create()
    {
        return view('smile-simulation.ai-create');
    }


    public function generate(GenerateSmileRequest $request, SmileAiClient $ai)
    {
        // Allow up to 10 minutes for AI image generation.
        set_time_limit(600);

        $data = $request->validated();

        try {
            $output = $ai->simulate(
                $request->file('image'),
                $data['mode'],
                $data['strength'],
                $data['treatment']
            );

            $raw = file_get_contents(
                $request->file('image')->getRealPath()
            );

            $source = @imagecreatefromstring($raw);

            if (!$source) {
                throw new RuntimeException(
                    'Cannot decode original image (PHP GD required)'
                );
            }

            ob_start();
            imagepng($source);
            $beforePng = ob_get_clean();
            imagedestroy($source);

            if (!is_string($beforePng)) {
                throw new RuntimeException(
                    'Failed to encode original'
                );
            }

            $id = (string) Str::uuid();

            $prefix = 'private/smile-ai-previews/' . $id;

            $disk = Storage::disk('local');

            if (!$disk->put($prefix . '/before.png', $beforePng)) {
                throw new RuntimeException('Cannot save original');
            }

            if (!$disk->put($prefix . '/after.png', $output)) {
                $disk->delete($prefix . '/before.png');

                throw new RuntimeException(
                    'Cannot save generated image'
                );
            }

            // Save preview information in the browser session.
            $previews = $request->session()->get(
                'smile_previews',
                []
            );

            $previews[$id] = [
                'id' => $id,
                'treatment' => $data['treatment'],
                'created_at' => now()->toDateTimeString(),
            ];

            if (count($previews) > 10) {
                $previews = array_slice(
                    $previews,
                    -10,
                    null,
                    true
                );
            }

            $request->session()->put(
                'smile_previews',
                $previews
            );

            return redirect()->route('smile.ai.show', $id);
        } catch (Throwable $e) {

            logger()->error(
                'SDXL Smile Simulation failed',
                [
                    'type' => get_class($e),
                    'message' => $e->getMessage(),
                ]
            );

            return back()
                ->withErrors([
                    'image' => 'Simulation failed. Check Laravel log and Python terminal. / فشلت المعالجة، راجع سجل Laravel ونافذة Python.'
                ])
                ->withInput();
        }
    }

    private function item(Request $request, string $id)
    {
        $data = $request->session()->get('smile_previews.' . $id);
        abort_unless(is_array($data), 404);
        return (object)$data;
    }
    public function show(Request $request, string $id)
    {
        $item = $this->item($request, $id);
        return view('smile-simulation.ai-result', compact('item'));
    }
    public function image(Request $request, string $id, string $side)
    {
        $this->item($request, $id);
        abort_unless(in_array($side, ['before', 'after'], true), 404);
        $path = 'private/smile-ai-previews/' . $id . '/' . $side . '.png';
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->file(Storage::disk('local')->path($path), ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }
    public function download(Request $request, string $id)
    {
        $this->item($request, $id);
        $path = 'private/smile-ai-previews/' . $id . '/after.png';
        abort_unless(Storage::disk('local')->exists($path), 404);
        return response()->download(Storage::disk('local')->path($path), 'medassist-sdxl-preview.png', ['Cache-Control' => 'private, no-store']);
    }
    public function gallery(Request $request)
    {
        $items = collect($request->session()->get('smile_previews', []))->reverse()->map(fn($item) => (object)$item);
        return view('smile-simulation.ai-gallery', compact('items'));
    }
}
