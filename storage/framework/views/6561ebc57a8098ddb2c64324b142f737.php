

<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-4">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded shadow-sm">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">Order #<?php echo e($order->id); ?></h1>
                <p class="text-sm text-gray-600">Placed: <?php echo e($order->created_at->format('M d, Y H:i')); ?></p>
                <p class="mt-2 text-gray-700">Payment: <?php echo e($order->payment_method ?? 'Not specified'); ?></p>
            </div>

            <div class="text-right">
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
        </div>

        <hr class="my-4">

        <table class="w-full table-auto">
            <thead>
                <tr class="text-left text-sm text-gray-600">
                    <th class="py-2">Medicine</th>
                    <th class="py-2">Price</th>
                    <th class="py-2">Qty</th>
                    <th class="py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="border-t">
                        <td class="py-3"><?php echo e($item->medicine->name ?? 'Deleted'); ?></td>
                        <td class="py-3">₹<?php echo e(number_format($item->price, 2)); ?></td>
                        <td class="py-3"><?php echo e($item->quantity); ?></td>
                        <td class="py-3">₹<?php echo e(number_format($item->quantity * $item->price, 2)); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>

        <div class="mt-4 flex justify-between items-center">
            <a href="<?php echo e(route('patient.orders.index')); ?>" class="text-sm text-blue-600">&larr; Back to orders</a>
            <div class="text-right">
                <div class="text-lg font-semibold">Total: ₹<?php echo e(number_format($order->total_amount, 2)); ?></div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\vibe_code\sample-telemed\resources\views/patient/orders/show.blade.php ENDPATH**/ ?>