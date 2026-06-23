<?php $__env->startSection('title', 'Forfaits – LireX Admin'); ?>
<?php $__env->startSection('page-title', 'Forfaits de publication'); ?>
<?php $__env->startSection('page-subtitle', 'Gérez les offres proposées aux auteurs'); ?>

<?php $__env->startSection('page-actions'); ?>
<a href="<?php echo e(route('admin.plans.create')); ?>"
   class="btn-aws flex items-center gap-2">
    <i class="fa-solid fa-plus text-xs"></i> Nouveau forfait
</a>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>


<div class="grid grid-cols-4 gap-4 mb-6">
    <?php
        $total   = $plans->count();
        $active  = $plans->where('is_active', true)->count();
        $subs    = $plans->sum('author_plans_count');
    ?>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-slate-800"><?php echo e($total); ?></p>
        <p class="text-sm text-slate-500 mt-1">Forfaits total</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-green-600"><?php echo e($active); ?></p>
        <p class="text-sm text-slate-500 mt-1">Actifs</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-slate-800"><?php echo e($total - $active); ?></p>
        <p class="text-sm text-slate-500 mt-1">Désactivés</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-blue-600"><?php echo e($subs); ?></p>
        <p class="text-sm text-slate-500 mt-1">Souscriptions</p>
    </div>
</div>


<div class="stat-card p-0 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-left text-xs text-slate-500 uppercase tracking-wide">
                <th class="px-5 py-3 font-semibold">#</th>
                <th class="px-5 py-3 font-semibold">Forfait</th>
                <th class="px-5 py-3 font-semibold">Prix mensuel</th>
                <th class="px-5 py-3 font-semibold">Prix annuel</th>
                <th class="px-5 py-3 font-semibold">Royalties</th>
                <th class="px-5 py-3 font-semibold">Livres max</th>
                <th class="px-5 py-3 font-semibold">Options</th>
                <th class="px-5 py-3 font-semibold">Souscripteurs</th>
                <th class="px-5 py-3 font-semibold">Statut</th>
                <th class="px-5 py-3 font-semibold">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                <td class="px-5 py-4 text-slate-400 font-mono text-xs"><?php echo e($plan->sort_order); ?></td>
                <td class="px-5 py-4">
                    <p class="font-semibold text-slate-800"><?php echo e($plan->name); ?></p>
                    <p class="text-slate-400 text-xs mt-0.5 max-w-xs truncate"><?php echo e($plan->description); ?></p>
                </td>
                <td class="px-5 py-4 font-semibold text-slate-700">
                    <?php echo e($plan->price_monthly == 0 ? 'Gratuit' : number_format($plan->price_monthly, 0, ',', ' ') . ' ' . $plan->currency); ?>

                </td>
                <td class="px-5 py-4 text-slate-600">
                    <?php echo e($plan->price_annual == 0 ? '–' : number_format($plan->price_annual, 0, ',', ' ') . ' ' . $plan->currency); ?>

                </td>
                <td class="px-5 py-4">
                    <span class="font-bold text-green-600"><?php echo e(number_format($plan->royalty_percent, 0)); ?>%</span>
                </td>
                <td class="px-5 py-4 text-slate-600">
                    <?php echo e($plan->max_books === -1 ? '∞' : $plan->max_books); ?>

                </td>
                <td class="px-5 py-4">
                    <div class="flex gap-1.5 flex-wrap">
                        <?php if($plan->allow_physical): ?> <span class="px-2 py-0.5 text-xs rounded-full bg-blue-50 text-blue-700">Physique</span> <?php endif; ?>
                        <?php if($plan->allow_academic): ?> <span class="px-2 py-0.5 text-xs rounded-full bg-purple-50 text-purple-700">Académique</span> <?php endif; ?>
                        <?php if($plan->allow_audio): ?>    <span class="px-2 py-0.5 text-xs rounded-full bg-orange-50 text-orange-700">Audio</span> <?php endif; ?>
                        <?php if($plan->ai_review): ?>      <span class="px-2 py-0.5 text-xs rounded-full bg-slate-100 text-slate-600">IA</span> <?php endif; ?>
                    </div>
                </td>
                <td class="px-5 py-4 text-center">
                    <span class="font-bold text-slate-800"><?php echo e($plan->author_plans_count); ?></span>
                </td>
                <td class="px-5 py-4">
                    <?php if($plan->is_active): ?>
                        <span class="badge-published">Actif</span>
                    <?php else: ?>
                        <span class="badge-draft">Inactif</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        <a href="<?php echo e(route('admin.plans.edit', $plan)); ?>"
                           class="px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition">
                           <i class="fa-solid fa-pen-to-square"></i>
                        </a>
                        <form method="POST" action="<?php echo e(route('admin.plans.toggle', $plan)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition
                                <?php echo e($plan->is_active ? 'bg-amber-50 text-amber-700 hover:bg-amber-100' : 'bg-green-50 text-green-700 hover:bg-green-100'); ?>">
                                <?php echo e($plan->is_active ? 'Désactiver' : 'Activer'); ?>

                            </button>
                        </form>
                        <form method="POST" action="<?php echo e(route('admin.plans.destroy', $plan)); ?>"
                              onsubmit="return confirm('Supprimer le forfait « <?php echo e($plan->name); ?> » ?')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit"
                                class="px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr>
                <td colspan="10" class="px-5 py-12 text-center text-slate-400">
                    <i class="fa-solid fa-box-open text-3xl mb-3 block"></i>
                    Aucun forfait créé.
                    <a href="<?php echo e(route('admin.plans.create')); ?>" class="text-blue-600 underline ml-1">Créer le premier</a>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/plans/index.blade.php ENDPATH**/ ?>