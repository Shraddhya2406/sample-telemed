<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\HealthQuestion;
use App\Models\HealthOption;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\MedicineRecommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthQuizController extends Controller
{
    /**
     * Start a new quiz attempt
     */
    public function startQuiz(Request $request)
    {
        $quizAttempt = QuizAttempt::create([
            'user_id' => Auth::id(),
            'total_score' => 0,
            'result_category' => 'pending',
        ]);

        return response()->json([
            'quiz_attempt_id' => $quizAttempt->id,
        ]);
    }

    /**
     * Display the first question of the quiz.
     */
    public function index()
    {
        $questions = HealthQuestion::where('is_active', true)->orderBy('order')->take(10)->get();
        return response()->json($questions);
    }

    /**
     * Get a specific question by order number
     */
    public function getQuestion($order)
    {
        $question = HealthQuestion::where('is_active', true)
            ->orderBy('order')
            ->skip($order - 1)
            ->first();

        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        $options = $question->healthOptions->map(function ($option) {
            return [
                'id' => $option->id,
                'text' => $option->option_text,
                'score' => $option->score,
            ];
        });

        return response()->json([
            'question_id' => $question->id,
            'question' => $question->question,
            'options' => $options,
            'current' => $order,
            'total' => 10,
        ]);
    }

    /**
     * Display a specific question by ID.
     */
    public function show($questionId)
    {
        $question = HealthQuestion::with('healthOptions')->findOrFail($questionId);
        return response()->json($question);
    }

    /**
     * Store an answer for a question
     */
    public function submitAnswer(Request $request)
    {
        $validated = $request->validate([
            'quiz_attempt_id' => 'required|exists:quiz_attempts,id',
            'health_question_id' => 'required|exists:health_questions,id',
            'health_option_id' => 'required|exists:health_options,id',
        ]);

        QuizAnswer::create($validated);

        return response()->json(['message' => 'Answer submitted successfully']);
    }

    /**
     * Finish the quiz and calculate score
     */
    public function finishQuiz(Request $request)
    {
        $quizAttempt = QuizAttempt::findOrFail($request->input('quiz_attempt_id'));

        // Calculate total score
        $totalScore = $quizAttempt->quizAnswers()
            ->with('healthOption')
            ->get()
            ->sum('healthOption.score');

        // Get recommendation - handle scores above max_score by finding the highest range
        $recommendation = MedicineRecommendation::where('min_score', '<=', $totalScore)
            ->where('max_score', '>=', $totalScore)
            ->first();
        
        // If no match found (score exceeds all max_score values), get the highest range
        if (!$recommendation) {
            $recommendation = MedicineRecommendation::orderBy('max_score', 'desc')->first();
        }

        // Update quiz attempt
        $quizAttempt->update([
            'total_score' => $totalScore,
            'result_category' => $recommendation?->disease_name ?? 'Unknown',
        ]);

        return response()->json([
            'total_score' => $totalScore,
            'recommendation' => $recommendation,
        ]);
    }

    /**
     * Show the result of a completed quiz
     */
    public function showResult($id)
    {
        $quizAttempt = QuizAttempt::with('quizAnswers.healthOption')->findOrFail($id);
        $recommendation = MedicineRecommendation::where('min_score', '<=', $quizAttempt->total_score)
            ->where('max_score', '>=', $quizAttempt->total_score)
            ->first();
        
        // If no match found (score exceeds all max_score values), get the highest range
        if (!$recommendation) {
            $recommendation = MedicineRecommendation::orderBy('max_score', 'desc')->first();
        }

        return view('patient.quiz.result', [
            'quizAttempt' => $quizAttempt,
            'recommendation' => $recommendation,
        ]);
    }
}