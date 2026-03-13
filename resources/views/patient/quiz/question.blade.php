@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <p class="text-sm text-gray-500 mb-4">Question {{ $currentQuestionNumber }} of {{ $totalQuestions }}</p>
    <h2 class="text-xl font-bold mb-4">{{ $question->question }}</h2>

    <form method="POST" action="{{ route('patient.health-quiz.submit-answer') }}">
        @csrf
        <input type="hidden" name="quiz_attempt_id" value="{{ $quizAttemptId }}">
        <input type="hidden" name="health_question_id" value="{{ $question->id }}">

        @foreach ($options as $option)
            <div class="mb-2">
                <label class="flex items-center space-x-2">
                    <input type="radio" name="health_option_id" value="{{ $option->id }}" class="form-radio">
                    <span>{{ $option->option_text }}</span>
                </label>
            </div>
        @endforeach

        @error('health_option_id')
            <p class="text-red-500 text-sm">{{ $message }}</p>
        @enderror

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded mt-4" disabled id="next-button">Next</button>
    </form>
</div>

<script>
    const nextButton = document.getElementById('next-button');
    const radioButtons = document.querySelectorAll('input[name="health_option_id"]');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => {
            nextButton.disabled = false;
        });
    });
</script>
@endsection