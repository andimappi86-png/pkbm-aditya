<?php

namespace App\Http\Controllers;

use App\Services\QuizAiGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class QuizAiController extends Controller
{
    public function __construct(private readonly QuizAiGeneratorService $service) {}

    /**
     * Generate quiz questions via AI.
     *
     * POST /quiz/ai/generate
     * Protected by: auth, verified, can:quizzes.manage
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topic' => 'required|string|min:10|max:5000',
            'count' => 'required|integer|in:5,10,15,20',
            'type' => 'required|string|in:multiple_choice,true_false,essay,mixed',
            'difficulty' => 'required|string|in:easy,medium,hard',
        ]);

        try {
            $result = $this->service->generate($validated);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'questions' => $result['questions'],
        ]);
    }
}
