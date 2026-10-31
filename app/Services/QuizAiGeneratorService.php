<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class QuizAiGeneratorService
{
    /**
     * Generate quiz questions using OpenAI Chat Completions API.
     *
     * @param  array{topic: string, count: int, type: string, difficulty: string}  $params
     * @return array{questions: array<int, array{type: string, text: string, points: int, options: string[], correctIndex: int|null, correctAnswer: string|null, explanation: string, rubric: string|null}>}
     *
     * @throws RuntimeException
     */
    public function generate(array $params): array
    {
        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY belum dikonfigurasi di file .env.');
        }

        $prompt = $this->buildPrompt($params);

        try {
            $response = Http::timeout(60)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => config('services.openai.model', 'gpt-4o-mini'),
                    'temperature' => 0.7,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Kamu adalah asisten pembuat soal pendidikan profesional untuk platform PKBM (Pusat Kegiatan Belajar Masyarakat). Selalu balas HANYA dengan JSON valid, tanpa markdown code fence, tanpa teks pembuka/penutup.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'response_format' => ['type' => 'json_object'],
                ]);
        } catch (ConnectionException $e) {
            throw new RuntimeException('Gagal menghubungi API AI: '.$e->getMessage());
        }

        if ($response->failed()) {
            $error = $response->json('error.message', 'Kesalahan tidak diketahui dari API AI.');
            throw new RuntimeException($error);
        }

        $raw = $response->json('choices.0.message.content', '{}');
        $decoded = json_decode((string) $raw, true);

        if (! is_array($decoded) || ! isset($decoded['questions']) || ! is_array($decoded['questions'])) {
            throw new RuntimeException('Respons AI tidak dalam format yang diharapkan. Coba lagi.');
        }

        $sanitizedQuestions = $this->sanitizeQuestions($decoded['questions']);

        if (empty($sanitizedQuestions)) {
            throw new RuntimeException('AI tidak menghasilkan soal yang valid. Silakan coba lagi.');
        }

        return ['questions' => $sanitizedQuestions];
    }

    /**
     * Sanitize and validate generated questions.
     *
     * @param  array<int, mixed>  $rawQuestions
     * @return array<int, array{type: string, text: string, points: int, options: string[], correctIndex: int|null, correctAnswer: string|null, explanation: string, rubric: string|null}>
     */
    private function sanitizeQuestions(array $rawQuestions): array
    {
        $validTypes = ['multiple_choice', 'true_false', 'essay', 'short_answer'];
        $clean = [];

        foreach ($rawQuestions as $q) {
            if (! is_array($q) || empty($q['text'])) {
                continue;
            }

            $type = in_array($q['type'] ?? '', $validTypes, true) ? $q['type'] : 'multiple_choice';
            $text = trim((string) $q['text']);
            $points = isset($q['points']) && is_numeric($q['points']) ? max(1, (int) $q['points']) : 10;
            $explanation = trim((string) ($q['explanation'] ?? ''));
            $rubric = isset($q['rubric']) ? trim((string) $q['rubric']) : null;
            $correctAnswer = isset($q['correctAnswer']) ? trim((string) $q['correctAnswer']) : null;

            $options = [];
            $correctIndex = null;

            if ($type === 'multiple_choice') {
                $rawOptions = is_array($q['options'] ?? null) ? $q['options'] : [];
                foreach ($rawOptions as $opt) {
                    $options[] = trim((string) $opt);
                }
                while (count($options) < 4) {
                    $options[] = 'Pilihan '.(count($options) + 1);
                }
                $options = array_slice($options, 0, 5);
                $ci = isset($q['correctIndex']) ? (int) $q['correctIndex'] : 0;
                $correctIndex = max(0, min(count($options) - 1, $ci));
            } elseif ($type === 'true_false') {
                $options = ['Benar', 'Salah'];
                $ci = isset($q['correctIndex']) ? (int) $q['correctIndex'] : 0;
                $correctIndex = ($ci === 1) ? 1 : 0;
            } elseif ($type === 'essay') {
                $options = [];
                $correctIndex = null;
                $rubric = $rubric ?: ($correctAnswer ?: 'Penilaian berdasarkan kedalaman analisis, ketepatan konsep, dan kejelasan uraian.');
            }

            $clean[] = [
                'type' => $type,
                'text' => $text,
                'points' => $points,
                'options' => $options,
                'correctIndex' => $correctIndex,
                'correctAnswer' => $correctAnswer,
                'explanation' => $explanation,
                'rubric' => $rubric,
            ];
        }

        return $clean;
    }

    /**
     * @param  array{topic: string, count: int, type: string, difficulty: string}  $params
     */
    private function buildPrompt(array $params): string
    {
        $typeInstructions = match ($params['type']) {
            'multiple_choice' => 'Semua soal harus bertipe pilihan ganda (multiple_choice) dengan 4 pilihan jawaban (A, B, C, D). Field "correctIndex" diisi indeks jawaban benar (0–3). Field "options" berisi array 4 string teks opsi. Field "correctAnswer" diisi null. Field "rubric" diisi null.',
            'true_false' => 'Semua soal harus bertipe benar/salah (true_false). Field "options" diisi ["Benar", "Salah"]. Field "correctIndex" diisi 0 untuk Benar, 1 untuk Salah. Field "correctAnswer" diisi null. Field "rubric" diisi null.',
            'essay' => 'Semua soal harus bertipe esai (essay). Tanyakan pertanyaan uraian/analisis yang mendalam. Field "options" diisi []. Field "correctIndex" diisi null. Field "correctAnswer" diisi poin-poin jawaban yang diharapkan. Field "rubric" diisi kriteria atau rubrik penilaian untuk pengajar.',
            default => 'Buat variasi soal campuran seimbang antara: (1) pilihan ganda ("type": "multiple_choice", 4 options), (2) benar/salah ("type": "true_false", options ["Benar","Salah"]), dan (3) esai ("type": "essay", options [], rubric diisi kriteria penilaian).',
        };

        $difficultyLabel = match ($params['difficulty']) {
            'easy' => 'Mudah — soal dasar, pengenalan istilah, hafalan fakta, konsep langsung.',
            'hard' => 'Sulit / HOTS — soal analisis, pemecahan masalah kontekstual, evaluasi, penalaran tingkat tinggi.',
            default => 'Sedang — pemahaman konsep dan penerapan praktis dalam kehidupan.',
        };

        $count = max(1, min(20, (int) $params['count']));

        return <<<PROMPT
Buatkan tepat {$count} soal kuis untuk peserta didik PKBM (Pendidikan Kesetaraan Paket A/B/C) berdasarkan materi berikut:

--- MATERI ---
{$params['topic']}
--- AKHIR MATERI ---

INSTRUKSI TIPE SOAL:
{$typeInstructions}

TINGKAT KESULITAN: {$difficultyLabel}

ATURAN:
- Bobot poin setiap soal: 10.
- Field "explanation" wajib diisi dengan pembahasan singkat dan edukatif (1–2 kalimat) dalam Bahasa Indonesia.
- Teks soal, opsi, dan pembahasan harus dalam Bahasa Indonesia yang baik dan mudah dipahami.
- Jangan sertakan nomor urut (seperti "1.", "2.") di dalam teks pertanyaan ("text").
- Output HANYA JSON dengan format persis berikut:

{
  "questions": [
    {
      "type": "multiple_choice",
      "text": "Teks pertanyaan pilihan ganda...",
      "points": 10,
      "options": ["Opsi A", "Opsi B", "Opsi C", "Opsi D"],
      "correctIndex": 0,
      "correctAnswer": null,
      "explanation": "Penjelasan singkat...",
      "rubric": null
    },
    {
      "type": "essay",
      "text": "Jelaskan bagaimana proses...",
      "points": 10,
      "options": [],
      "correctIndex": null,
      "correctAnswer": "Poin 1: ..., Poin 2: ...",
      "explanation": "Uraian kunci jawaban...",
      "rubric": "Rubrik: pemahaman konsep (4 poin), contoh riil (3 poin), kejelasan (3 poin)"
    }
  ]
}
PROMPT;
    }
}
