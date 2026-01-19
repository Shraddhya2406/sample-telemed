<!-- Result Card (Partial) -->
<div class="text-center">
    <div class="mb-8">
        <svg class="w-16 h-16 mx-auto text-green-600 mb-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        <h2 class="text-3xl font-bold text-gray-800 mb-4">Quiz Complete!</h2>
    </div>

    <div class="bg-blue-50 p-6 rounded mb-6">
        {{-- <div class="mb-6">
            <p class="text-gray-600 text-sm mb-2">Your Score</p>
            <p class="text-4xl font-bold text-blue-600" id="result-score">0</p>
        </div> --}}

        <hr class="my-4">

        <div class="text-left">
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Recommendation</h3>
            
            <div class="mb-4">
                <p class="text-gray-600 text-sm">Disease Category</p>
                <p class="text-lg font-semibold text-gray-800" id="result-disease">-</p>
            </div>

            <div class="mb-4">
                <p class="text-gray-600 text-sm">Recommended Medicine</p>
                <p class="text-lg font-semibold text-gray-800" id="result-medicine">-</p>
            </div>

            <div>
                <p class="text-gray-600 text-sm">Advice</p>
                <p class="text-gray-800" id="result-advice">-</p>
            </div>
        </div>
    </div>

    <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded mb-6">
        <p class="text-yellow-800 text-sm">
            <strong>⚠️ Important:</strong> This is not a medical diagnosis. Please consult a doctor for proper treatment.
        </p>
    </div>

    <div class="flex gap-4 justify-center">
        <a href="{{ route('dashboard.patient') }}" class="bg-gray-600 text-white px-6 py-2 rounded hover:bg-gray-700">
            Go to Dashboard
        </a>
        <button onclick="location.reload()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Retake Quiz
        </button>
    </div>
</div>