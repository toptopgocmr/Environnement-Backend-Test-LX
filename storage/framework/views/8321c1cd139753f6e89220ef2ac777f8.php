<?php $__env->startSection('title', 'Paramètres'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Paramètres de la plateforme</h1>
        <p class="text-slate-400 text-sm mt-1">Configuration globale de LireX</p>
    </div>

    <?php if(session('success')): ?>
        <div class="bg-green-900/30 border border-green-700 text-green-300 rounded-xl p-4 text-sm"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <form action="<?php echo e(route('admin.settings.update')); ?>" method="POST" class="space-y-6">
        <?php echo csrf_field(); ?>

        
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold text-lg flex items-center gap-2">⚙️ Général</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Nom de la plateforme</label>
                    <input type="text" name="platform_name" value="<?php echo e($settings['platform_name'] ?? 'LireX'); ?>"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Email de contact</label>
                    <input type="email" name="contact_email" value="<?php echo e($settings['contact_email'] ?? 'contact@lirex.africa'); ?>"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
                <div class="col-span-2">
                    <label class="block text-slate-300 text-sm font-medium mb-2">Description / Slogan</label>
                    <input type="text" name="platform_tagline" value="<?php echo e($settings['platform_tagline'] ?? 'Plateforme Universelle du Savoir'); ?>"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
            </div>
        </div>

        
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold text-lg flex items-center gap-2">💰 Royalties & Commissions</h2>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Commission plateforme (%)</label>
                    <input type="number" name="platform_commission" value="<?php echo e($settings['platform_commission'] ?? 20); ?>" min="0" max="50" step="0.5"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Royaltie auteur (%)</label>
                    <input type="number" name="author_royalty" value="<?php echo e($settings['author_royalty'] ?? 80); ?>" min="50" max="100" step="0.5"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
                <div>
                    <label class="block text-slate-300 text-sm font-medium mb-2">Retrait minimum (FCFA)</label>
                    <input type="number" name="min_withdrawal" value="<?php echo e($settings['min_withdrawal'] ?? 5000); ?>" step="500"
                        class="w-full px-4 py-3 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        style="background:#0F2044;border:1px solid #1E3A6A">
                </div>
            </div>
        </div>

        
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold text-lg flex items-center gap-2">📱 Moyens de paiement</h2>
            <div class="space-y-3">
                <?php $__currentLoopData = [['mtn_momo', '🟡 MTN Mobile Money'], ['airtel_money', '🔴 Airtel Money'], ['stripe', '💳 Stripe (carte)'], ['free', '🆓 Livres gratuits']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$key, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center gap-3 p-4 rounded-xl cursor-pointer" style="background:#0F2044;border:1px solid #1E3A6A">
                        <input type="checkbox" name="payment_methods[]" value="<?php echo e($key); ?>"
                            <?php echo e(in_array($key, $settings['payment_methods'] ?? ['mtn_momo','airtel_money','free']) ? 'checked' : ''); ?>

                            class="w-4 h-4 rounded">
                        <span class="text-white text-sm"><?php echo e($label); ?></span>
                    </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="rounded-2xl p-6 space-y-5" style="background:#162035;border:1px solid #1E3A6A">
            <h2 class="text-white font-semibold text-lg flex items-center gap-2">🛡️ Modération</h2>
            <div class="space-y-3">
                <label class="flex items-center justify-between p-4 rounded-xl" style="background:#0F2044;border:1px solid #1E3A6A">
                    <span class="text-white text-sm">Approbation manuelle des livres</span>
                    <div class="relative">
                        <input type="hidden" name="manual_book_approval" value="0">
                        <input type="checkbox" name="manual_book_approval" value="1" <?php echo e(($settings['manual_book_approval'] ?? true) ? 'checked' : ''); ?>

                            class="sr-only peer" id="toggle-approval">
                        <label for="toggle-approval" class="w-11 h-6 rounded-full cursor-pointer transition-colors peer-checked:bg-blue-600"
                            style="background:#1E3A6A;display:block;position:relative">
                            <div class="w-4 h-4 bg-white rounded-full absolute top-1 left-1 transition-transform peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </label>
                <label class="flex items-center justify-between p-4 rounded-xl" style="background:#0F2044;border:1px solid #1E3A6A">
                    <span class="text-white text-sm">Notifications SMS activées</span>
                    <div class="relative">
                        <input type="hidden" name="sms_enabled" value="0">
                        <input type="checkbox" name="sms_enabled" value="1" <?php echo e(($settings['sms_enabled'] ?? true) ? 'checked' : ''); ?>

                            class="sr-only peer" id="toggle-sms">
                        <label for="toggle-sms" class="w-11 h-6 rounded-full cursor-pointer transition-colors peer-checked:bg-blue-600"
                            style="background:#1E3A6A;display:block;position:relative">
                            <div class="w-4 h-4 bg-white rounded-full absolute top-1 left-1 transition-transform peer-checked:translate-x-5"></div>
                        </label>
                    </div>
                </label>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-8 py-3 rounded-xl font-semibold text-white text-sm hover:opacity-90 transition-all"
                style="background:linear-gradient(135deg,#1D4ED8,#2563EB)">
                Enregistrer les paramètres
            </button>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/settings/index.blade.php ENDPATH**/ ?>