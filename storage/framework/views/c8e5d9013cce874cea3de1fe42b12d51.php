

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto">
    <!-- Quiz Container -->
    <div id="quiz-container" class="bg-white p-8 rounded shadow" data-dashboard-url="<?php echo e(route('dashboard.patient')); ?>" data-api-base="<?php echo e(url('')); ?>">
        <!-- Initial Loading State -->
        <div id="quiz-loading" class="text-center">
            <div class="animate-spin inline-block w-8 h-8 border-4 border-blue-600 border-t-transparent rounded-full"></div>
            <p class="mt-2 text-gray-600">Starting quiz...</p>
        </div>

        <!-- Question Section -->
        <div id="quiz-content" class="hidden">
            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Question <span id="current-question">1</span> of <span id="total-questions">10</span></span>
                    <span id="progress-percentage">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div id="progress-bar" class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                </div>
            </div>

            <!-- Question Text -->
            <h2 id="question-text" class="text-2xl font-bold mb-6 text-gray-800"></h2>

            <!-- Options -->
            <form id="quiz-form" class="mb-6">
                <?php echo csrf_field(); ?>
                <input type="hidden" id="question-id" name="health_question_id">
                <input type="hidden" id="quiz-attempt-id" name="quiz_attempt_id">
                
                <div id="options-container" class="space-y-3">
                    <!-- Radio buttons will be inserted here -->
                </div>

                <!-- Validation Error -->
                <div id="error-message" class="hidden text-red-500 text-sm mt-4"></div>
            </form>

            <!-- Navigation Buttons -->
            <div class="flex justify-between gap-4">
                <button id="prev-button" class="bg-gray-400 text-white px-6 py-2 rounded hover:bg-gray-500 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Previous
                </button>
                <button id="next-button" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    Next
                </button>
            </div>
        </div>

        <!-- Result Section -->
        <div id="quiz-result" class="hidden">
            <!-- Result content will be inserted here -->
        </div>

        <!-- Error State -->
        <div id="quiz-error" class="hidden text-center">
            <p class="text-red-600 mb-4">An error occurred. Please try again.</p>
            <button onclick="location.reload()" class="bg-blue-600 text-white px-4 py-2 rounded">Reload Page</button>
        </div>
    </div>

    <!-- Disclaimer -->
    <div class="mt-6 bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded">
        <p class="text-yellow-800 text-sm">
            <strong>⚠️ Disclaimer:</strong> This quiz provides medicine recommendations, not a medical diagnosis. 
            Please consult with a qualified healthcare professional for proper diagnosis and treatment.
        </p>
    </div>
</div>

<?php echo app('Illuminate\Foundation\Vite')('resources/js/quiz-ajax.js'); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\vibe_code\sample-telemed\resources\views/patient/quiz/ajax.blade.php ENDPATH**/ ?>