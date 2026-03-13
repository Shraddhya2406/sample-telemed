<!-- resources/views/patient/medicines/show.blade.php -->


<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-4">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow-sm">
        <div class="flex gap-6">
            <div class="w-1/3">
                <?php if($medicine->image): ?>
                    <img src="<?php echo e(asset($medicine->image)); ?>" alt="<?php echo e($medicine->name); ?>" class="w-full h-56 object-cover">
                <?php else: ?>
                    <img src="<?php echo e(asset('images/medicine-default.svg')); ?>" alt="<?php echo e($medicine->name); ?>" class="w-full h-56 object-cover">
                <?php endif; ?>
            </div>
            <div class="flex-1">
                <h1 class="text-2xl font-semibold"><?php echo e($medicine->name); ?></h1>
                <p class="text-sm text-gray-600 mt-2"><?php echo e($medicine->category); ?></p>
                <p class="mt-4 text-gray-700"><?php echo e($medicine->description); ?></p>

                <div class="mt-4 flex items-center gap-4">
                    <span class="text-xl font-bold">₹<?php echo e(number_format($medicine->price, 2)); ?></span>
                    <span class="text-sm text-gray-500">Stock: <?php echo e($medicine->stock_quantity); ?></span>
                </div>

                <div class="mt-6">
                    <form action="<?php echo e(route('patient.cart.add')); ?>" method="POST" class="flex items-center gap-2 ajax-add-to-cart">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="medicine_id" value="<?php echo e($medicine->id); ?>">
                        <input type="number" name="quantity" value="1" min="1" max="<?php echo e($medicine->stock_quantity); ?>" class="w-24 border rounded px-2 py-1">
                        <button type="submit" class="ml-2 bg-blue-600 text-white px-4 py-2 rounded">Add to cart</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\vibe_code\sample-telemed\resources\views/patient/medicines/show.blade.php ENDPATH**/ ?>