<?php

namespace App\Services;

use App\Models\HealthConversation;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class GeminiService
{
    public function generateReply(HealthConversation $conversation): array
    {
        $messages = $conversation->messages()
            ->latest('id')
            ->limit(16)
            ->get()
            ->reverse()
            ->values();

        return $this->generate($this->assessmentPrompt(), $messages->map(fn ($message) => [
            'role' => $message->sender_type === 'patient' ? 'Patient' : 'Assistant',
            'text' => $message->message,
        ])->all());
    }

    public function generateSummary(HealthConversation $conversation): array
    {
        $messages = $conversation->messages()->oldest('id')->get();

        return $this->generate($this->summaryPrompt(), $messages->map(fn ($message) => [
            'role' => $message->sender_type === 'patient' ? 'Patient' : 'Assistant',
            'text' => $message->message,
        ])->all());
    }

    public function generateMedicineSuggestions(HealthConversation $conversation, Collection $medicines): array
    {
        if ($medicines->isEmpty()) {
            return [];
        }

        $messages = $conversation->messages()->oldest('id')->get();

        $result = $this->generate($this->medicineSuggestionPrompt(), [
            'conversation' => $messages->map(fn ($message) => [
                'role' => $message->sender_type === 'patient' ? 'Patient' : 'Assistant',
                'text' => $message->message,
            ])->all(),
            'summary' => $conversation->summary,
            'urgency_level' => $conversation->urgency_level,
            'available_medicines' => $medicines->map(fn ($medicine) => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'category' => $medicine->category_name,
                'description' => $medicine->description,
                'composition' => $medicine->composition,
                'stock_quantity' => $medicine->stock_quantity,
            ])->values()->all(),
            'instruction' => 'Suggest medicines only from available_medicines using medicine_id values.',
        ]);

        $allowedIds = $medicines->pluck('id')->map(fn ($id) => (int) $id)->all();

        return collect($result['suggestions'] ?? [])
            ->filter(fn ($suggestion) => in_array((int) ($suggestion['medicine_id'] ?? 0), $allowedIds, true))
            ->take(4)
            ->map(fn ($suggestion) => [
                'medicine_id' => (int) $suggestion['medicine_id'],
                'reason' => trim((string) ($suggestion['reason'] ?? 'May help with symptoms discussed.')),
                'caution' => trim((string) ($suggestion['caution'] ?? 'Use only after doctor or pharmacist guidance.')),
            ])
            ->values()
            ->all();
    }

    private function generate(string $systemPrompt, array $history): array
    {
        $key = config('services.gemini.key');

        if (blank($key)) {
            throw new RuntimeException('Gemini API key is not configured.');
        }

        $model = config('services.gemini.model', 'gemini-2.5-flash-lite');
        $baseUrl = rtrim(config('services.gemini.base_url'), '/');
        $url = "{$baseUrl}/models/{$model}:generateContent";

        try {
            $response = Http::timeout((int) config('services.gemini.timeout', 25))
                ->withOptions([
                    'verify' => config('services.gemini.ca_bundle') ?: true,
                ])
                ->withHeaders([
                    'x-goog-api-key' => $key,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]],
                    ],
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [[
                            'text' => json_encode([
                                'payload' => $history,
                                'instruction' => 'Follow the system instructions and required JSON format.',
                            ], JSON_PRETTY_PRINT),
                        ]],
                    ]],
                    'generationConfig' => [
                        'temperature' => 0.35,
                        'maxOutputTokens' => 700,
                        'responseMimeType' => 'application/json',
                    ],
                ])
                ->throw();
        } catch (RequestException $exception) {
            $status = $exception->response?->status();
            $body = $exception->response?->body() ?? $exception->getMessage();

            Log::warning('Gemini health assistant request failed.', [
                'status' => $status,
                'body' => Str::limit($body, 500),
            ]);

            throw new RuntimeException($this->requestFailureMessage($status), previous: $exception);
        }

        $text = data_get($response->json(), 'candidates.0.content.parts.0.text');
        $decoded = is_string($text) ? json_decode($text, true) : null;

        if (! is_array($decoded)) {
            Log::warning('Gemini health assistant returned invalid JSON.', ['body' => Str::limit($response->body(), 500)]);
            throw new RuntimeException('Gemini returned an invalid response.');
        }

        return [
            'reply' => trim((string) ($decoded['reply'] ?? '')),
            'summary' => trim((string) ($decoded['summary'] ?? '')),
            'urgency_level' => $this->normalizeUrgency($decoded['urgency_level'] ?? 'low'),
            'should_end' => (bool) ($decoded['should_end'] ?? false),
            'suggestions' => is_array($decoded['suggestions'] ?? null) ? $decoded['suggestions'] : [],
        ];
    }

    private function assessmentPrompt(): string
    {
        return <<<'PROMPT'
You are a careful, professional general physician style healthcare assistant for a telemedicine app.
Your job is preliminary symptom assessment only. Never provide a final diagnosis. Never prescribe medicines, dosages, or treatment plans.
Ask exactly one concise follow-up question at a time unless emergency symptoms require immediate care advice.
Use simple, patient-friendly language.
Gather symptom type, duration, severity, age-relevant context when available, associated symptoms, existing conditions, pregnancy status when relevant, allergies, and red flags.
If symptoms suggest emergency risk such as chest pain, severe breathing difficulty, stroke symptoms, fainting, severe bleeding, suicidal thoughts, seizure, severe allergic reaction, or very high fever with confusion/stiff neck, tell the patient to seek immediate emergency medical attention and mark urgency_level as emergency.
When enough details are collected, provide a brief preliminary summary and recommend booking a doctor consultation for review.
Respond only as JSON:
{
  "reply": "one patient-facing message",
  "urgency_level": "low|medium|high|emergency",
  "should_end": false,
  "summary": "brief clinical-style summary when ending or empty string"
}
PROMPT;
    }

    private function summaryPrompt(): string
    {
        return <<<'PROMPT'
Summarize this preliminary AI health conversation for a doctor. Do not diagnose. Include chief symptoms, duration, severity, associated symptoms, red flags, patient concerns, and recommended next step. Respond only as JSON:
{
  "reply": "A short closing message for the patient",
  "urgency_level": "low|medium|high|emergency",
  "should_end": true,
  "summary": "doctor-facing summary"
}
PROMPT;
    }

    private function medicineSuggestionPrompt(): string
    {
        return <<<'PROMPT'
You suggest medicines from the pharmacy inventory for a telemedicine app after a preliminary AI health assessment.
This is not a prescription. Do not invent medicine names. Only choose medicines from available_medicines by medicine_id.
Do not suggest antibiotics or prescription-only medicines as self-treatment; if such an item appears relevant, caution that doctor review is required.
For emergency or high urgency cases, return an empty suggestions array and tell the patient to seek medical care in the caution if needed.
Avoid dosages and treatment schedules.
Prefer simple supportive OTC options when appropriate, such as fever relief, allergy relief, cough support, acid reflux support, or vitamins, only if the conversation supports it.
Respond only as JSON:
{
  "reply": "",
  "urgency_level": "low|medium|high|emergency",
  "should_end": false,
  "summary": "",
  "suggestions": [
    {
      "medicine_id": 1,
      "reason": "short reason tied to symptoms",
      "caution": "short safety caution, including doctor/pharmacist review"
    }
  ]
}
PROMPT;
    }

    private function normalizeUrgency(string $urgency): string
    {
        return in_array($urgency, ['low', 'medium', 'high', 'emergency'], true) ? $urgency : 'low';
    }

    private function requestFailureMessage(?int $status): string
    {
        return match ($status) {
            400 => 'Gemini rejected the request. Check the model name and request payload.',
            401, 403 => 'Gemini authentication failed. Check GEMINI_API_KEY and API access.',
            404 => 'Gemini model endpoint was not found. Check GEMINI_MODEL.',
            429 => 'Gemini rate limit or quota was reached. Wait and try again, or check Google AI Studio quota.',
            500, 502, 503, 504 => 'Gemini service is temporarily unavailable.',
            default => 'Gemini API request failed.',
        };
    }
}
