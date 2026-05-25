<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\HealthConversation;
use App\Models\HealthMessage;
use App\Models\Medicine;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AIHealthController extends Controller
{
    public function __construct(private readonly GeminiService $gemini)
    {
    }

    public function index(Request $request): View
    {
        $conversations = HealthConversation::forUser($request->user()->id)
            ->with(['messages' => fn ($query) => $query->oldest('id')])
            ->latest()
            ->limit(8)
            ->get();

        $activeConversation = $conversations->firstWhere('status', 'active');

        return view('patient.ai-health-chat', compact('conversations', 'activeConversation'));
    }

    public function start(Request $request): JsonResponse
    {
        $conversation = HealthConversation::firstOrCreate(
            ['user_id' => $request->user()->id, 'status' => 'active'],
            ['urgency_level' => 'low']
        );

        if ($conversation->messages()->doesntExist()) {
            $conversation->messages()->create([
                'sender_type' => 'assistant',
                'message' => 'What symptoms are you experiencing today?',
                'created_at' => now(),
            ]);
        }

        return response()->json($this->conversationResource($conversation->load('messages')));
    }

    public function send(Request $request, HealthConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        if ($conversation->status !== 'active') {
            return response()->json(['message' => 'This assessment is already completed. Please start a new conversation.'], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1200'],
        ]);

        $localEmergency = $this->detectEmergency($validated['message']);

        $conversation->messages()->create([
            'sender_type' => 'patient',
            'message' => $validated['message'],
            'created_at' => now(),
        ]);

        if ($localEmergency) {
            $reply = [
                'reply' => 'Your symptoms may need urgent medical attention. Please contact local emergency services or go to the nearest emergency department now. Are you currently safe and able to get help?',
                'urgency_level' => 'emergency',
                'should_end' => false,
                'summary' => 'Patient reported possible emergency symptoms during AI preliminary assessment.',
            ];
        } else {
            try {
                $reply = $this->gemini->generateReply($conversation->fresh('messages'));
            } catch (\Throwable $exception) {
                return $this->geminiFailureResponse($exception);
            }
        }

        $conversation->messages()->create([
            'sender_type' => 'assistant',
            'message' => $reply['reply'],
            'created_at' => now(),
        ]);

        $conversation->update([
            'urgency_level' => $this->highestUrgency($conversation->urgency_level, $reply['urgency_level']),
            'status' => $reply['should_end'] ? 'completed' : 'active',
            'summary' => $reply['summary'] ?: $conversation->summary,
        ]);

        if ($reply['should_end']) {
            try {
                $this->attachMedicineSuggestions($conversation->fresh('messages'));
            } catch (\Throwable $exception) {
                return $this->geminiFailureResponse($exception);
            }
        }

        return response()->json($this->conversationResource($conversation->fresh('messages')));
    }

    public function complete(Request $request, HealthConversation $conversation): JsonResponse
    {
        $this->authorizeConversation($request, $conversation);

        if ($conversation->messages()->where('sender_type', 'patient')->doesntExist()) {
            return response()->json(['message' => 'Please share your symptoms before ending the assessment.'], 422);
        }

        try {
            $summary = $this->gemini->generateSummary($conversation->load('messages'));
        } catch (\Throwable $exception) {
            return $this->geminiFailureResponse($exception);
        }

        $conversation->messages()->create([
            'sender_type' => 'assistant',
            'message' => $summary['reply'],
            'created_at' => now(),
        ]);

        $conversation->update([
            'status' => 'completed',
            'summary' => $summary['summary'],
            'urgency_level' => $this->highestUrgency($conversation->urgency_level, $summary['urgency_level']),
        ]);

        try {
            $this->attachMedicineSuggestions($conversation->fresh('messages'));
        } catch (\Throwable $exception) {
            return $this->geminiFailureResponse($exception);
        }

        return response()->json($this->conversationResource($conversation->fresh('messages')));
    }

    public function restart(Request $request): JsonResponse
    {
        HealthConversation::forUser($request->user()->id)
            ->where('status', 'active')
            ->update(['status' => 'completed']);

        $conversation = HealthConversation::create([
            'user_id' => $request->user()->id,
            'status' => 'active',
            'urgency_level' => 'low',
        ]);

        $conversation->messages()->create([
            'sender_type' => 'assistant',
            'message' => 'What symptoms are you experiencing today?',
            'created_at' => now(),
        ]);

        return response()->json($this->conversationResource($conversation->load('messages')));
    }

    private function authorizeConversation(Request $request, HealthConversation $conversation): void
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);
    }

    private function detectEmergency(string $message): bool
    {
        return str($message)->lower()->contains([
            'chest pain',
            'can not breathe',
            "can't breathe",
            'difficulty breathing',
            'shortness of breath',
            'stroke',
            'face drooping',
            'slurred speech',
            'severe bleeding',
            'unconscious',
            'fainted',
            'seizure',
            'suicidal',
            'suicide',
            'anaphylaxis',
            'swollen throat',
            'stiff neck',
            'confusion',
        ]);
    }

    private function highestUrgency(string $current, string $incoming): string
    {
        $rank = ['low' => 1, 'medium' => 2, 'high' => 3, 'emergency' => 4];

        return ($rank[$incoming] ?? 1) > ($rank[$current] ?? 1) ? $incoming : $current;
    }

    private function geminiFailureResponse(\Throwable $exception): JsonResponse
    {
        return response()->json([
            'message' => 'Gemini connection failed, so the AI assistant could not generate a response.',
            'details' => config('app.debug') ? $exception->getMessage() : null,
        ], 503);
    }

    private function attachMedicineSuggestions(HealthConversation $conversation): void
    {
        if (in_array($conversation->urgency_level, ['high', 'emergency'], true)) {
            $conversation->update(['medicine_suggestions' => []]);
            return;
        }

        $medicines = Medicine::query()
            ->with(['medicineCategory', 'images'])
            ->where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->where(fn ($query) => $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today()))
            ->orderBy('name')
            ->get();

        $suggestions = $this->gemini->generateMedicineSuggestions($conversation, $medicines);
        $hydratedSuggestions = $this->hydrateMedicineSuggestions($suggestions, $medicines);

        if (empty($hydratedSuggestions)) {
            $hydratedSuggestions = $this->localMedicineSuggestions($conversation, $medicines);
        }

        $conversation->update(['medicine_suggestions' => $hydratedSuggestions]);
    }

    private function hydrateMedicineSuggestions(array $suggestions, Collection $medicines): array
    {
        $medicinesById = $medicines->keyBy('id');

        return collect($suggestions)->map(function (array $suggestion) use ($medicinesById) {
            $medicine = $medicinesById->get($suggestion['medicine_id']);

            if (! $medicine) {
                return null;
            }

            return [
                'medicine_id' => $medicine->id,
                'name' => $medicine->name,
                'category' => $medicine->category_name,
                'price' => (float) $medicine->price,
                'stock_quantity' => $medicine->stock_quantity,
                'image_url' => $medicine->image_url,
                'url' => route('patient.medicines.show', $medicine),
                'reason' => $suggestion['reason'],
                'caution' => $suggestion['caution'],
            ];
        })->filter()->values()->all();
    }

    private function localMedicineSuggestions(HealthConversation $conversation, Collection $medicines): array
    {
        $context = $this->assessmentContext($conversation);
        $searchTerms = $this->searchTerms($context);

        if (empty($searchTerms)) {
            return [];
        }

        return $medicines
            ->map(function (Medicine $medicine) use ($searchTerms) {
                $score = $this->medicineMatchScore($medicine, $searchTerms);

                if ($score < 2 || $this->isPrescriptionLikeMedicine($medicine)) {
                    return null;
                }

                return [
                    'score' => $score,
                    'medicine' => $medicine,
                    'matched_terms' => $this->matchedTerms($medicine, $searchTerms),
                ];
            })
            ->filter()
            ->sortByDesc('score')
            ->take(4)
            ->map(fn (array $match) => [
                'medicine_id' => $match['medicine']->id,
                'name' => $match['medicine']->name,
                'category' => $match['medicine']->category_name,
                'price' => (float) $match['medicine']->price,
                'stock_quantity' => $match['medicine']->stock_quantity,
                'image_url' => $match['medicine']->image_url,
                'url' => route('patient.medicines.show', $match['medicine']),
                'reason' => $this->localSuggestionReason($match['matched_terms']),
                'caution' => 'This is a non-prescription suggestion from available stock. Confirm with a doctor or pharmacist before use.',
            ])
            ->values()
            ->all();
    }

    private function assessmentContext(HealthConversation $conversation): string
    {
        $messageText = $conversation->messages()
            ->oldest('id')
            ->get()
            ->map(fn (HealthMessage $message) => $message->message)
            ->implode(' ');

        return Str::lower(trim(($conversation->summary ?? '').' '.$messageText));
    }

    private function searchTerms(string $context): array
    {
        $terms = collect(preg_split('/[^a-z0-9]+/i', $context) ?: [])
            ->map(fn ($term) => trim(Str::lower($term)))
            ->filter(fn ($term) => strlen($term) >= 3 && ! in_array($term, $this->medicineStopWords(), true))
            ->values();

        $aliases = [
            'fever' => ['fever', 'temperature', 'paracetamol', 'acetaminophen'],
            'temperature' => ['fever', 'temperature', 'paracetamol', 'acetaminophen'],
            'pain' => ['pain', 'ache', 'analgesic', 'paracetamol', 'acetaminophen'],
            'headache' => ['headache', 'pain', 'paracetamol', 'acetaminophen'],
            'cough' => ['cough', 'throat', 'cold'],
            'cold' => ['cold', 'cough', 'nasal', 'congestion'],
            'allergy' => ['allergy', 'allergic', 'antihistamine', 'cetirizine'],
            'allergic' => ['allergy', 'allergic', 'antihistamine', 'cetirizine'],
            'acidity' => ['acidity', 'acid', 'reflux', 'antacid'],
            'reflux' => ['acidity', 'acid', 'reflux', 'antacid'],
            'nausea' => ['nausea', 'vomiting', 'oral rehydration', 'ors'],
            'vomiting' => ['nausea', 'vomiting', 'oral rehydration', 'ors'],
            'diarrhea' => ['diarrhea', 'loose motion', 'oral rehydration', 'ors'],
            'dehydration' => ['dehydration', 'oral rehydration', 'ors'],
            'weakness' => ['weakness', 'vitamin', 'supplement'],
            'deficiency' => ['deficiency', 'vitamin', 'supplement'],
        ];

        return $terms
            ->flatMap(fn ($term) => $aliases[$term] ?? [$term])
            ->map(fn ($term) => trim(Str::lower($term)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function medicineMatchScore(Medicine $medicine, array $searchTerms): int
    {
        $name = Str::lower((string) $medicine->name);
        $category = Str::lower((string) $medicine->category_name);
        $description = Str::lower((string) $medicine->description);
        $composition = Str::lower((string) $medicine->composition);
        $brand = Str::lower((string) $medicine->brand);
        $score = 0;

        foreach ($searchTerms as $term) {
            if (Str::contains($name, $term)) {
                $score += 5;
            }

            if (Str::contains($category, $term) || Str::contains($brand, $term)) {
                $score += 3;
            }

            if (Str::contains($description, $term) || Str::contains($composition, $term)) {
                $score += 2;
            }
        }

        return $score;
    }

    private function matchedTerms(Medicine $medicine, array $searchTerms): array
    {
        $medicineText = Str::lower(implode(' ', array_filter([
            $medicine->name,
            $medicine->brand,
            $medicine->category_name,
            $medicine->description,
            $medicine->composition,
        ])));

        return collect($searchTerms)
            ->filter(fn ($term) => Str::contains($medicineText, $term))
            ->take(3)
            ->values()
            ->all();
    }

    private function localSuggestionReason(array $matchedTerms): string
    {
        if (empty($matchedTerms)) {
            return 'Matched with the symptoms discussed and current pharmacy inventory.';
        }

        return 'Matched with '.$this->humanList($matchedTerms).' mentioned in the assessment.';
    }

    private function humanList(array $items): string
    {
        $items = array_values(array_unique($items));

        if (count($items) <= 1) {
            return $items[0] ?? 'the symptoms';
        }

        $last = array_pop($items);

        return implode(', ', $items).' and '.$last;
    }

    private function isPrescriptionLikeMedicine(Medicine $medicine): bool
    {
        $text = Str::lower(implode(' ', array_filter([
            $medicine->name,
            $medicine->category_name,
            $medicine->description,
            $medicine->composition,
        ])));

        return Str::contains($text, [
            'antibiotic',
            'amoxicillin',
            'azithromycin',
            'cefixime',
            'ciprofloxacin',
            'doxycycline',
            'metronidazole',
            'prednisolone',
            'steroid',
            'prescription',
        ]);
    }

    private function medicineStopWords(): array
    {
        return [
            'about', 'after', 'again', 'also', 'been', 'before', 'brief', 'care', 'doctor', 'does',
            'during', 'from', 'have', 'having', 'help', 'know', 'like', 'medical', 'medicine',
            'need', 'only', 'patient', 'please', 'review', 'said', 'should', 'summary', 'that',
            'their', 'them', 'then', 'there', 'this', 'today', 'what', 'when', 'with', 'your',
        ];
    }

    private function medicineSuggestionMessage(HealthConversation $conversation): ?string
    {
        if ($conversation->status !== 'completed' || ! empty($conversation->medicine_suggestions ?? [])) {
            return null;
        }

        if (in_array($conversation->urgency_level, ['high', 'emergency'], true)) {
            return 'No medicine suggestions are shown because your assessment may need prompt medical review.';
        }

        return 'No suitable in-stock medicine matched your assessment from the current pharmacy inventory. Please book a doctor consultation or check with a pharmacist for safe guidance.';
    }

    private function conversationResource(HealthConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'status' => $conversation->status,
            'summary' => $conversation->summary,
            'urgency_level' => $conversation->urgency_level,
            'medicine_suggestions' => $conversation->medicine_suggestions ?? [],
            'medicine_suggestion_message' => $this->medicineSuggestionMessage($conversation),
            'created_at' => $conversation->created_at?->format('d M Y h:i A'),
            'messages' => $conversation->messages->sortBy('id')->values()->map(fn (HealthMessage $message) => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'message' => $message->message,
                'created_at' => $message->created_at?->format('h:i A'),
            ])->all(),
        ];
    }
}
