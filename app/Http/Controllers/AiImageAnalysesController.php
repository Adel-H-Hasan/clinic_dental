<?php

namespace App\Http\Controllers;

use App\Models\AiImageAnalyses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AiImageAnalysesController extends Controller
{
    public function store(Request $request)
    {
        // 1. التأكد من تسجيل دخول المستخدم
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Please login first.'
            ], 401);
        }
        abort_unless($user->is_active, 403);
        if ($user->role === 'patient') abort_unless($user->hasVerifiedEmail(), 403);

        // 2. التحقق من الصورة المرفوعة
        $validated = $request->validate([
            'before_image' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        // 3. حفظ الصورة في مساحة خاصة بالمريض
        $path = $validated['before_image']->store(
            'smile-simulations/' . $user->id,
            'local'
        );
        abort_unless(is_string($path) && $path !== '', 503);

        // 4. إنشاء سجل في قاعدة البيانات
        try {
            $analysis = AiImageAnalyses::create([
                'user_id' => $user->id,
                'image_url' => $path,
                'before_image' => $path,
                'after_image' => null,
                // Required by the existing schema; empty means no analysis has run.
                'ai_analyses' => '',
            ]);
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        // 5. إرجاع نتيجة نجاح العملية
        return response()->json([
            'message' => __('Image uploaded. Automatic analysis is not available yet.'),
            'analysis_id' => $analysis->id,
            'status' => 'pending',
            'processing_available' => false,
        ], 201);
    }
}
