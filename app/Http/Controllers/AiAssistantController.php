<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiAssistantController extends Controller
{
    public function status(GeminiService $gemini): JsonResponse
    {
        return response()->json([
            'configured' => $gemini->isConfigured(),
            'default_model' => config('services.gemini.model', 'gemini-3.5-flash'),
            'models' => $gemini->getAvailableModels(),
        ]);
    }

    public function refine(Request $request, GeminiService $gemini): JsonResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string'],
            'instructions' => ['nullable', 'string', 'max:500'],
            'model' => ['nullable', 'string', 'max:100'],
        ]);

        if (!$gemini->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'API Key Gemini belum disetting. Buka file .env lalu isi GEMINI_API_KEY=kunci_anda.',
            ], 422);
        }

        try {
            $refined = $gemini->refineContent(
                $validated['content'],
                $validated['instructions'] ?? '',
                $validated['model'] ?? null
            );

            return response()->json([
                'success' => true,
                'result' => $refined,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function generate(Request $request, GeminiService $gemini): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string'],
            'model' => ['nullable', 'string', 'max:100'],
        ]);

        if (!$gemini->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'API Key Gemini belum disetting. Buka file .env lalu isi GEMINI_API_KEY=kunci_anda.',
            ], 422);
        }

        try {
            $generated = $gemini->generateTsDoc(
                $validated['prompt'],
                $validated['model'] ?? null
            );

            return response()->json([
                'success' => true,
                'result' => $generated,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
