<!-- resources/views/patient/medicines/index.blade.php -->


<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Medicine Store</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php $__currentLoopData = $medicines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $medicine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="border rounded p-4 bg-white shadow-sm">
                <?php if($medicine->image): ?>
                    <img src="<?php echo e(asset($medicine->image)); ?>" alt="<?php echo e($medicine->name); ?>" class="h-32 w-full object-cover mb-2">
                <?php else: ?>
                    <div class="h-32 w-full bg-gray-100 flex items-center justify-center mb-2">No image</div>
                <?php endif; ?>

                <h2 class="font-medium"><?php echo e($medicine->name); ?></h2>
                <p class="text-sm text-gray-600"><?php echo e(Str::limit($medicine->description, 80)); ?></p>
                <div class="mt-2 flex items-center justify-between">
                    <span class="font-semibold">₹<?php echo e(number_format($medicine->price, 2)); ?></span>
                    <span class="text-xs text-gray-500">Stock: <?php echo e($medicine->stock_quantity); ?></span>
                </div>

                <div class="mt-3">
                    <form action="<?php echo e(route('patient.cart.add')); ?>" method="POST" class="flex items-center gap-2 ajax-add-to-cart">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="medicine_id" value="<?php echo e($medicine->id); ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo e($medicine->stock_quantity); ?>" class="w-20 border rounded px-2 py-1">
                        <button type="submit" class="ml-auto bg-blue-600 text-white px-3 py-1 rounded text-sm">Add</button>
                    </form>
                </div>

                <a href="<?php echo e(route('patient.medicines.show', $medicine)); ?>" class="block mt-2 text-xs text-blue-600">View details</a>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="mt-6">
        <?php echo e($medicines->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\vibe_code\sample-telemed\resources\views/patient/medicines/index.blade.php ENDPATH**/ ?>