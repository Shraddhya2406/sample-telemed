<!-- resources/views/patient/orders/index.blade.php -->


<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">My Orders</h1>

    <?php if($orders->isEmpty()): ?>
        <div class="bg-white p-6 rounded shadow-sm">You have no orders yet. <a href="<?php echo e(route('patient.medicines.index')); ?>" class="text-blue-600">Shop medicines</a></div>
    <?php else: ?>
        <div class="space-y-4">
            <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white p-4 rounded shadow-sm flex justify-between items-center">
                    <div>
                        <div class="font-semibold">Order #<?php echo e($order->id); ?></div>
                        <div class="text-sm text-gray-600">Placed: <?php echo e($order->created_at->format('M d, Y H:i')); ?></div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-semibold">₹<?php echo e(number_format($order->total_amount, 2)); ?></div>
                        <div class="mt-1">
                            <?php
                                $status = $order->status;
                                $color = match($status) {
                                    'pending' => 'bg-yellow-200 text-yellow-800',
                                    'confirmed' => 'bg-blue-200 text-blue-800',
                                    'shipped' => 'bg-indigo-200 text-indigo-800',
                                    'delivered' => 'bg-green-200 text-green-800',
                                    'cancelled' => 'bg-red-200 text-red-800',
                                    default => 'bg-gray-200 text-gray-800',
                                };
                            ?>
                            <span class="px-3 py-1 rounded text-sm <?php echo e($color); ?>"><?php echo e(ucfirst($status)); ?></span>
                        </div>
                        <a href="<?php echo e(route('patient.orders.show', $order)); ?>" class="block mt-2 text-sm text-blue-600">View details</a>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <div>
                <?php echo e($orders->links()); ?>

            </div>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\vibe_code\sample-telemed\resources\views/patient/orders/index.blade.php ENDPATH**/ ?>