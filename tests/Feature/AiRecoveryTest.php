<?php

namespace Tests\Feature;

use App\Services\GeminiClient;
use App\Services\GeminiNoteReviewer;
use App\Services\SmileAiClient;
use App\Models\MedicalNotes;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AiRecoveryTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null,
            'mail.default' => 'array',
            'cache.default' => 'array',
            'session.driver' => 'array'
        ]);
        \Illuminate\Support\Facades\DB::purge('sqlite');
    }

    private function signInPatient(): void
    {
        $patient = User::factory()->create(['role' => 'patient', 'email_verified_at' => now(), 'is_active' => true]);
        $this->actingAs($patient);
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('synthetic.png', file_get_contents(base_path('tests/Fixtures/synthetic-smile.png')));
    }

    public function test_smile_client_preserves_contract_and_validates_real_png_bytes(): void
    {
        config(['services.medassist_ai.url' => 'http://ai.test', 'services.medassist_ai.token' => 'synthetic-token']);
        $png = file_get_contents(base_path('tests/Fixtures/synthetic-smile.png'));
        Http::fake(function ($request) use ($png) {
            $this->assertStringContainsString('name="treatment"', $request->body());
            $this->assertStringContainsString('whitening', $request->body());
            return Http::response($png, 200, ['Content-Type' => 'image/png']);
        });
        $this->assertSame($png, app(SmileAiClient::class)->simulate($this->image(), 'mouth', 'natural', 'whitening'));
        Http::assertSent(fn($r) => $r->hasHeader('X-AI-Token', 'synthetic-token') && $r->url() === 'http://ai.test/api/v1/simulate');
    }

    public function test_smile_http_error_never_contains_response_body(): void
    {
        config(['services.medassist_ai.url' => 'http://ai.test', 'services.medassist_ai.token' => 'synthetic-token']);
        Http::fake(['*' => Http::response('SYNTHETIC_PRIVATE_MEDICAL_TEXT', 503)]);
        try {
            app(SmileAiClient::class)->simulate($this->image(), 'mouth', 'natural', 'whitening');
            $this->fail('Expected unavailable service');
        } catch (\RuntimeException $e) {
            $this->assertSame('AI HTTP 503', $e->getMessage());
        }
    }

    public function test_smile_rejects_fake_png_and_invalid_upload_dimensions(): void
    {
        config(['services.medassist_ai.url' => 'http://ai.test', 'services.medassist_ai.token' => 'synthetic-token']);
        Http::fake(['*' => Http::response('not png', 200, ['Content-Type' => 'image/png'])]);
        try {
            app(SmileAiClient::class)->simulate($this->image(), 'mouth', 'natural', 'whitening');
            $this->fail('Expected invalid image');
        } catch (\RuntimeException $e) {
            $this->assertSame('Invalid image returned by AI service', $e->getMessage());
        }
        $this->signInPatient();
        $this->post('/smile-ai/generate', [
            'image' => UploadedFile::fake()->createWithContent('bad.png', 'invalid'),
            'mode' => 'mouth',
            'strength' => 'natural',
            'treatment' => 'whitening',
            'consent' => '1'
        ])->assertSessionHasErrors('image');
    }

    public function test_gemini_uses_steps_and_does_not_store_clinical_prompt(): void
    {
        config(['services.gemini.key' => 'synthetic-key']);
        Http::fake(['*' => Http::response(['status' => 'completed', 'steps' => [['type' => 'model_output', 'content' => [['type' => 'text', 'text' => '{"report":"مسودة اصطناعية"}']]]]])]);
        $this->assertSame(['report' => 'مسودة اصطناعية'], app(GeminiClient::class)->generateJson('synthetic', ['type' => 'object']));
        Http::assertSent(fn($r) => $r->hasHeader('x-goog-api-key', 'synthetic-key') && $r['store'] === false && $r['response_format']['mime_type'] === 'application/json');
    }

    public function test_gemini_malformed_payload_and_timeout_fail_closed(): void
    {
        config(['services.gemini.key' => 'synthetic-key']);
        Http::fake(['*' => Http::response(['status' => 'completed', 'steps' => 'invalid'])]);
        $this->assertNull(app(GeminiClient::class)->generateJson('synthetic', []));
        Http::fake(fn() => throw new \Illuminate\Http\Client\ConnectionException('Synthetic timeout'));
        $this->assertNull(app(GeminiClient::class)->generateJson('synthetic', []));
    }

    public function test_gemini_retries_only_transient_503_once(): void
    {
        config(['services.gemini.key' => 'synthetic-key']);
        Http::fake(['*' => Http::sequence()->push([], 503)->push(['status' => 'completed', 'steps' => [['type' => 'model_output', 'content' => [['type' => 'text', 'text' => '{"suggestions":[]}']]]]], 200)]);
        $this->assertSame(['suggestions' => []], app(GeminiClient::class)->generateJson('synthetic', []));
        Http::assertSentCount(2);
    }

    public function test_note_reviewer_includes_prescription_and_discards_invented_evidence(): void
    {
        $note = (new MedicalNotes())->forceFill(['diagnosis' => 'synthetic', 'prescription' => 'synthetic prescription', 'notes' => 'test']);
        $this->mock(GeminiClient::class, fn($m) => $m->shouldReceive('generateJson')->once()->withArgs(fn($prompt, $schema) => str_contains($prompt, 'synthetic prescription'))->andReturn(['suggestions' => [
            ['kind' => 'missing_information', 'evidence' => ['synthetic prescription'], 'question' => 'هل هناك تفاصيل؟'],
            ['kind' => 'possible_conflict', 'evidence' => ['invented detail'], 'question' => 'غير مدعوم'],
        ]]));
        $result = app(GeminiNoteReviewer::class)->review($note);
        $this->assertCount(1, $result);
        $this->assertSame(['synthetic prescription'], $result[0]['evidence']);
    }

    public function test_preview_storage_and_cross_session_privacy(): void
    {
        Storage::fake('local');
        $this->signInPatient();
        $png = file_get_contents(base_path('tests/Fixtures/synthetic-smile.png'));
        if (!function_exists('imagecreatefromstring')) {
            $this->mock(SmileAiClient::class, fn($m) => $m->shouldNotReceive('simulate'));
            $this->post('/smile-ai/generate', ['image' => $this->image(), 'mode' => 'mouth', 'strength' => 'natural', 'treatment' => 'whitening', 'consent' => '1'])->assertSessionHasErrors('image');
            return;
        }
        $this->mock(SmileAiClient::class, fn($m) => $m->shouldReceive('simulate')->once()->andReturn($png));
        $this->post('/smile-ai/generate', ['image' => $this->image(), 'mode' => 'mouth', 'strength' => 'natural', 'treatment' => 'whitening', 'consent' => '1'])->assertRedirect();
        $id = array_key_first(session('smile_previews'));
        Storage::disk('local')->assertExists('private/smile-ai-previews/' . $id . '/before.png');
        $response = $this->get('/smile-ai/result/' . $id)->assertOk();

        $cacheDirectives = array_map(
            'trim',
            explode(',', (string) $response->headers->get('Cache-Control'))
        );

        $this->assertContains('private', $cacheDirectives);
        $this->assertContains('no-store', $cacheDirectives);
        $this->get('/smile-ai/result/' . $id . '/image/after')->assertOk();
        $this->get('/smile-ai/result/' . $id . '/image/invalid')->assertNotFound();
        $this->withSession(['smile_previews' => []])->get('/smile-ai/result/' . $id)->assertNotFound();
        $this->get('/smile-ai/result/' . $id . '/download')->assertNotFound();
    }
}
