

<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Checkout</h1>

    <?php if(! $cart || $cart->items->isEmpty()): ?>
        <div class="bg-white p-6 rounded shadow-sm">Your cart is empty. <a href="<?php echo e(url('/patient/medicines')); ?>" class="text-blue-600">Browse medicines</a></div>
    <?php else: ?>
        <div class="bg-white p-4 rounded shadow-sm">
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
                    <?php $__currentLoopData = $cart->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t">
                            <td class="py-3"><?php echo e($item->medicine->name ?? 'Deleted item'); ?></td>
                            <td class="py-3">₹<?php echo e(number_format($item->price, 2)); ?></td>
                            <td class="py-3"><?php echo e($item->quantity); ?></td>
                            <td class="py-3">₹<?php echo e(number_format($item->sub_total, 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            <div class="mt-4 text-right">
                <div class="text-lg font-semibold">Total: ₹<?php echo e(number_format($cart->total, 2)); ?></div>
            </div>

            <form action="<?php echo e(route('patient.orders.place')); ?>" method="POST" class="mt-6 bg-gray-50 p-4 rounded">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Payment Method (optional)</label>
                        <select name="payment_method" class="mt-1 block w-full rounded border-gray-300">
                            <option value="">Select (pay later)</option>
                            <option value="card">Card</option>
                            <option value="upi">UPI</option>
                            <option value="cod">Cash on Delivery</option>
                        </select>
                    </div>

                    <div class="flex items-end justify-end">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Place Order</button>
                    </div>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\vibe_code\sample-telemed\resources\views/patient/orders/checkout.blade.php ENDPATH**/ ?>