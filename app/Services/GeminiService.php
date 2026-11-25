<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected ?string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model = config('services.gemini.model', 'gemini-3.5-flash');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Get available generation models from Gemini API.
     */
    public function getAvailableModels(): array
    {
        $defaultFallback = [
            ['id' => 'gemini-3.5-flash', 'name' => 'Gemini 3.5 Flash (Rekomendasi Utama & Stabil)'],
            ['id' => 'gemini-3.7-flash', 'name' => 'Gemini 3.7 Flash (Generasi Terbaru)'],
            ['id' => 'gemini-3.6-flash', 'name' => 'Gemini 3.6 Flash'],
            ['id' => 'gemini-3.1-flash-lite', 'name' => 'Gemini 3.1 Flash-Lite (Super Cepat)'],
            ['id' => 'gemini-3.1-pro-preview', 'name' => 'Gemini 3.1 Pro Preview (Analisis Mendalam - Akun Berbayar)'],
        ];


        if (!$this->isConfigured()) {
            return $defaultFallback;
        }

        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$this->apiKey}";
            $response = Http::timeout(10)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $models = [];
                foreach ($data['models'] ?? [] as $m) {
                    $methods = $m['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods)) {
                        $modelId = str_replace('models/', '', $m['name']);
                        // Filter out non-general text models (like image/audio/tts/robotics) to keep it clean
                        if (
                            !str_contains($modelId, 'image') &&
                            !str_contains($modelId, 'tts') &&
                            !str_contains($modelId, 'transcribe') &&
                            !str_contains($modelId, 'robotics') &&
                            !str_contains($modelId, 'embedding') &&
                            !str_contains($modelId, 'aqa')
                        ) {
                            $models[] = [
                                'id' => $modelId,
                                'name' => ($m['displayName'] ?? $modelId) . " ({$modelId})",
                                'description' => $m['description'] ?? '',
                            ];
                        }
                    }
                }
                if (!empty($models)) {
                    return $models;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to fetch models from Gemini API: " . $e->getMessage());
        }

        return $defaultFallback;
    }

    /**
     * Refine rough technical notes into clean, structured Markdown documentation.
     */
    public function refineContent(string $rawContent, string $customInstructions = '', ?string $model = null): string
    {
        $systemPrompt = <<<PROMPT
Anda adalah Principal Software Architect dan Technical Writer handal untuk DevDocs.
Tugas Anda adalah merapikan, menstrukturkan, dan melengkapi catatan teknis atau draft dokumen berikut agar menjadi Technical Specification Document (TSD) standar industri yang rapi dan profesional.

Aturan Penting:
1. Format output HANYA berupa Markdown valid tanpa komentar pembuka atau penutup.
2. Gunakan hierarki heading yang jelas (H1, H2, H3).
3. Jika terdapat alur proses, request/response, atau interaksi sistem antar-komponen, buatkan Mermaid sequenceDiagram atau flowchart yang valid di dalam blok ```mermaid ... ```.
4. Buat tabel jika ada parameter, header, atau status code.
5. Gunakan bahasa Indonesia teknis yang baku, presisi, dan mudah dipahami.
6. Pertahankan semua informasi teknis penting (URL endpoint, parameter, nama database, logic error handling).
PROMPT;

        $userPrompt = "Berikut adalah catatan teknis kasar yang perlu dirapikan:\n\n" . $rawContent;

        if (!empty($customInstructions)) {
            $userPrompt .= "\n\nInstruksi Tambahan dari Pengguna:\n" . $customInstructions;
        }

        return $this->callGemini($systemPrompt, $userPrompt, $model);
    }

    /**
     * Generate a complete TSD from a brief concept prompt.
     */
    public function generateTsDoc(string $prompt, ?string $model = null): string
    {
        $systemPrompt = <<<PROMPT
Anda adalah Lead Software Architect untuk DevDocs.
Tugas Anda adalah membuat draft Technical Specification Document (TSD) komprehensif berdasarkan ringkasan fitur atau arsitektur yang diberikan pengguna.

Struktur Dokumen yang harus dibuat:
# [Judul Fitur/Sistem]
## 1. Overview & Latar Belakang
## 2. Arsitektur & Sequence Diagram (Gunakan Mermaid sequenceDiagram valid)
## 3. Spesifikasi API / Endpoint (Method, Path, Headers, Request Body, Response Codes)
## 4. Skema Database / Entitas Terkait
## 5. Error Handling & Edge Cases

Keluarkan HANYA Markdown murni tanpa meta-komentar pembuka/penutup.
PROMPT;

        return $this->callGemini($systemPrompt, "Buatkan Technical Specification Document untuk fitur berikut:\n\n" . $prompt, $model);
    }

    /**
     * Generate a Mermaid diagram from description.
     */
    public function generateDiagram(string $description, string $diagramType = 'sequence', ?string $model = null): string
    {
        $systemPrompt = <<<PROMPT
Anda adalah spesialis Mermaid.js.
Tugas Anda adalah membuat kode diagram Mermaid valid berdasarkan deskripsi alur pengguna.
Keluarkan HANYA blok kode ```mermaid ... ``` tanpa teks pengantar atau penutup apapun.
Sintaks diagram Mermaid harus 100% valid dan bebas kesalahan.
PROMPT;

        $prompt = "Buatkan Mermaid {$diagramType} diagram untuk skenario berikut:\n\n" . $description;
        return $this->callGemini($systemPrompt, $prompt, $model);
    }

    /**
     * Call Google Gemini REST API.
     */
    protected function callGemini(string $systemInstruction, string $prompt, ?string $model = null): string
    {
        if (!$this->isConfigured()) {
            throw new Exception("GEMINI_API_KEY belum dikonfigurasi di file .env aplikasi DevDocs.");
        }

        $activeModel = $model ?: $this->model;
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$activeModel}:generateContent?key={$this->apiKey}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'systemInstruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 8192,
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->timeout(60)->post($url, $payload);

            if ($response->failed()) {
                $errorBody = $response->json();
                $errorMsg = $errorBody['error']['message'] ?? 'Gagal menghubungi Google Gemini API.';
                Log::error("Gemini API Error ({$activeModel}): " . $response->body());
                throw new Exception("Gemini API Error ({$activeModel}): " . $errorMsg);
            }

            $data = $response->json();
            $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Clean any wrapping markdown code block if the model wrapped the entire markdown
            $cleanText = trim($generatedText);
            if (str_starts_with($cleanText, "```markdown\n") && str_ends_with($cleanText, "\n```")) {
                $cleanText = substr($cleanText, 12, -4);
            } elseif (str_starts_with($cleanText, "```md\n") && str_ends_with($cleanText, "\n```")) {
                $cleanText = substr($cleanText, 6, -4);
            }

            return trim($cleanText);

        } catch (Exception $e) {
            Log::error("Gemini Service Exception: " . $e->getMessage());
            throw $e;
        }
    }
}

