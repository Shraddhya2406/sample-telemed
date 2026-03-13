<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Sample Telemed'); ?> - <?php echo e(config('app.name', 'Laravel')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')('resources/css/app.css'); ?>
</head>
<body class="bg-gradient-to-br from-blue-50 via-teal-50 to-green-50 min-h-screen">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-5">
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgb(59, 130, 246) 1px, transparent 0); background-size: 40px 40px;"></div>
    </div>

    <!-- Main Content -->
    <div class="relative min-h-screen flex items-center justify-center px-4 py-12">
        <!-- Logo Section -->
        <div class="absolute top-6 left-6" style="top: 6px;">
            <a href="<?php echo e(url('/')); ?>" class="flex items-center space-x-2 text-blue-600 hover:text-blue-700 transition-colors">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span class="text-xl font-bold">Sample Telemed</span>
            </a>
        </div>

        <!-- Auth Card -->
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-xl p-8 md:p-10 border border-gray-100">
                <?php echo $__env->yieldContent('content'); ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\wamp64\www\vibe_code\sample-telemed\resources\views/layouts/auth.blade.php ENDPATH**/ ?>