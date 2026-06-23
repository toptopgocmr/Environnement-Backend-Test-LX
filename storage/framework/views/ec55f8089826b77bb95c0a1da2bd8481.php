<?php $__env->startSection('page-title', 'Analyses IA'); ?>
<?php $__env->startSection('page-subtitle', 'Rapports d\'analyse automatique des contenus soumis'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
      <tr>
        <th class="px-6 py-3 text-left">Livre</th>
        <th class="px-6 py-3 text-left">Auteur</th>
        <th class="px-6 py-3 text-left">Score global</th>
        <th class="px-6 py-3 text-left">Plagiat</th>
        <th class="px-6 py-3 text-left">Recommandation</th>
        <th class="px-6 py-3 text-left">Statut</th>
        <th class="px-6 py-3 text-right">Action</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
      <tr class="hover:bg-slate-50">
        <td class="px-6 py-4 font-medium text-slate-800 max-w-xs truncate"><?php echo e($review->book->title ?? '—'); ?></td>
        <td class="px-6 py-4 text-slate-600"><?php echo e($review->book->author->name ?? '—'); ?></td>
        <td class="px-6 py-4">
          <?php if($review->score_overall !== null): ?>
            <span class="font-bold <?php echo e($review->score_overall >= 70 ? 'text-green-600' : ($review->score_overall >= 40 ? 'text-amber-600' : 'text-red-600')); ?>">
              <?php echo e($review->score_overall); ?>/100
            </span>
          <?php else: ?>
            <span class="text-slate-400">—</span>
          <?php endif; ?>
        </td>
        <td class="px-6 py-4">
          <?php if($review->plagiarism_flag): ?>
            <span class="badge-rejected"><?php echo e($review->plagiarism_score); ?>% détecté</span>
          <?php else: ?>
            <span class="badge-published">OK</span>
          <?php endif; ?>
        </td>
        <td class="px-6 py-4">
          <?php $recoMap = ['approve'=>['Approuver','badge-published'],'review'=>['À examiner','badge-pending'],'reject'=>['Rejeter','badge-rejected']]; ?>
          <?php if($review->recommendation): ?>
            <span class="<?php echo e($recoMap[$review->recommendation][1]); ?>"><?php echo e($recoMap[$review->recommendation][0]); ?></span>
          <?php else: ?>
            <span class="text-slate-400">—</span>
          <?php endif; ?>
        </td>
        <td class="px-6 py-4">
          <?php $statusMap = ['pending'=>'badge-draft','processing'=>'badge-pending','completed'=>'badge-published','failed'=>'badge-rejected']; ?>
          <span class="<?php echo e($statusMap[$review->status]); ?>"><?php echo e(ucfirst($review->status)); ?></span>
        </td>
        <td class="px-6 py-4 text-right">
          <a href="<?php echo e(route('admin.ai-reviews.show', $review)); ?>" class="text-blue-600 hover:underline font-medium">Voir le rapport</a>
        </td>
      </tr>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
      <tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">Aucune analyse IA pour le moment.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<div class="mt-6"><?php echo e($reviews->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\Lenovo Yoga\Downloads\LireX-APP\backend\resources\views/admin/ai_reviews/index.blade.php ENDPATH**/ ?>