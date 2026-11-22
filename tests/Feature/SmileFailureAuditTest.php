<?php
namespace Tests\Feature;

use App\Services\SmileAiClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmileFailureAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function beforeRefreshingDatabase(): void
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.url' => null, 'mail.default' => 'array',
            'cache.default' => 'array', 'session.driver' => 'array']);
        \Illuminate\Support\Facades\DB::purge('sqlite');
    }

    public function test_external_ai_failure_returns_localized_recoverable_error_without_saving_results(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'patient', 'email_verified_at' => now(), 'is_active' => true]));
        Log::spy();
        $this->mock(SmileAiClient::class, function ($mock) {
            if (function_exists('imagecreatefromstring')) $mock->shouldReceive('simulate')->once()->andThrow(new \RuntimeException('Synthetic unavailable service'));
            else $mock->shouldNotReceive('simulate');
        });
        $png=file_get_contents(base_path('tests/Fixtures/synthetic-smile.png'));
        $this->withSession(['locale'=>'ar'])->from('/smile-ai')->post('/smile-ai/generate',[
            'image'=>UploadedFile::fake()->createWithContent('qa.png',$png),'mode'=>'mouth',
            'treatment'=>'whitening','strength'=>'natural','consent'=>'1',
        ])->assertRedirect('/smile-ai')->assertSessionHasErrors('image')->assertSessionMissing('smile_previews');
        $response=$this->get('/smile-ai')->assertOk()->assertSee('تعذرت محاكاة الصورة');
        $this->assertSame(1,substr_count($response->getContent(),'تعذرت محاكاة الصورة'));
    }
}
