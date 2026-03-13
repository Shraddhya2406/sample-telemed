<!-- resources/views/patient/cart/index.blade.php -->


<?php $__env->startSection('content'); ?>
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-semibold mb-4">Your Cart</h1>

    <div id="cart-wrapper">
    <?php if(!$cart || $cart->items->isEmpty()): ?>
        <div class="bg-white p-6 rounded shadow-sm">Your cart is empty. <a href="<?php echo e(route('patient.medicines.index')); ?>" class="text-blue-600">Browse medicines</a></div>
    <?php else: ?>
        <div class="bg-white p-4 rounded shadow-sm">
            <table class="w-full table-auto">
                <thead>
                    <tr class="text-left text-sm text-gray-600">
                        <th class="py-2">Medicine</th>
                        <th class="py-2">Price</th>
                        <th class="py-2">Qty</th>
                        <th class="py-2">Subtotal</th>
                        <th class="py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $cart->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="border-t" id="cart-item-<?php echo e($item->id); ?>">
                            <td class="py-3"><?php echo e($item->medicine->name ?? 'Deleted item'); ?></td>
                            <td class="py-3">₹<?php echo e(number_format($item->price, 2)); ?></td>
                            <td class="py-3">
                                <form action="<?php echo e(route('patient.cart.update')); ?>" method="POST" class="flex items-center gap-2 ajax-update-cart" data-item-id="<?php echo e($item->id); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="cart_item_id" value="<?php echo e($item->id); ?>">
                                    <input type="number" name="quantity" value="<?php echo e($item->quantity); ?>" min="1" max="<?php echo e($item->medicine->stock_quantity ?? $item->quantity); ?>" class="w-20 border rounded px-2 py-1">
                                    <button type="submit" class="bg-gray-700 text-white px-2 py-1 rounded text-sm">Update</button>
                                </form>
                            </td>
                            <td class="py-3" id="item-subtotal-<?php echo e($item->id); ?>">₹<?php echo e(number_format($item->sub_total, 2)); ?></td>
                            <td class="py-3">
                                <form action="<?php echo e(route('patient.cart.remove')); ?>" method="POST" class="ajax-remove-item" data-item-id="<?php echo e($item->id); ?>">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="cart_item_id" value="<?php echo e($item->id); ?>">
                                    <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded text-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            <div class="mt-4 flex justify-between items-center">
                <div>
                    <form action="<?php echo e(route('patient.cart.clear')); ?>" method="POST" class="ajax-clear-cart">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="bg-yellow-600 text-white px-3 py-1 rounded">Clear cart</button>
                    </form>
                </div>
                <div class="text-right">
                    <div id="cart-total" class="text-lg font-semibold">Total: ₹<?php echo e(number_format($cart->total, 2)); ?></div>
                    <a href="<?php echo e(route('patient.checkout')); ?>" class="inline-block mt-2 bg-green-600 text-white px-4 py-2 rounded">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\vibe_code\sample-telemed\resources\views/patient/cart/index.blade.php ENDPATH**/ ?>