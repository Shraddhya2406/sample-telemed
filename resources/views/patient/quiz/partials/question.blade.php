<!-- Question Card (Partial) -->
<div class="bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ $question->question }}</h2>
    
    <form id="quiz-form" class="mb-6">
        @csrf
        <input type="hidden" name="health_question_id" value="{{ $question->id }}">
        <input type="hidden" name="quiz_attempt_id" value="{{ $quizAttemptId }}">
        
        <div class="space-y-3">
            @foreach ($question->healthOptions as $option)
                <div class="flex items-center">
                    <input 
                        type="radio" 
                        id="option-{{ $option->id }}" 
                        name="health_option_id" 
                        value="{{ $option->id }}" 
                        class="form-radio text-blue-600"
                    >
                    <label for="option-{{ $option->id }}" class="ml-3 text-gray-700 cursor-pointer">
                        {{ $option->option_text }}
                    </label>
                </div>
            @endforeach
        </div>

        @error('health_option_id')
            <p class="text-red-500 text-sm mt-4">{{ $message }}</p>
        @enderror
    </form>
</div>