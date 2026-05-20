<?php
/**
 * Slim bridge bar between Process 5 IEP form and Process 6 workspace (same IEP context).
 *
 * @var string $basePath
 * @var int    $iepId
 * @var string $navActive 'form' | 'workspace'
 * @var bool   $showWorkspaceLink  false for roles without iep.implement (e.g. parent on read-only form)
 */
$navActive = $navActive ?? 'form';
$showWorkspaceLink = $showWorkspaceLink ?? true;
$formHref = htmlspecialchars($basePath . '/iep/form/' . (int) $iepId, ENT_QUOTES, 'UTF-8');
$wsHref   = htmlspecialchars($basePath . '/iep/implementation/workspace/' . (int) $iepId, ENT_QUOTES, 'UTF-8');
?>
<div class="rounded mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2 px-3 py-2" style="background-color:#1e4072;">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="<?= htmlspecialchars($basePath . '/iep', ENT_QUOTES, 'UTF-8') ?>"
           class="text-white text-decoration-none small rounded px-2 py-1" style="opacity:0.95;border:1px solid rgba(255,255,255,0.45);">
            <i class="bi bi-folder2-open me-1"></i>All IEPs
        </a>
        <?php if ($navActive === 'form'): ?>
            <span class="rounded px-2 py-1 small fw-semibold" style="background-color:#a01422;color:#fff;">← IEP Form</span>
        <?php else: ?>
            <a href="<?= $formHref ?>" class="text-white text-decoration-none small fw-semibold rounded px-2 py-1" style="opacity:0.95;">← IEP Form</a>
        <?php endif; ?>
        <button type="button" class="btn btn-sm rounded text-white border"
                style="border-color:rgba(255,255,255,0.55)!important;background:transparent;"
                data-bs-toggle="modal" data-bs-target="#pdspRefModal">
            <i class="bi bi-file-earmark-pdf me-1"></i>Signed PDSP
        </button>
    </div>
    <div>
        <?php if ($showWorkspaceLink): ?>
            <?php if ($navActive === 'workspace'): ?>
                <span class="rounded px-2 py-1 small fw-semibold" style="background-color:#a01422;color:#fff;">IEP Workspace →</span>
            <?php else: ?>
                <a href="<?= $wsHref ?>" class="text-white text-decoration-none small fw-semibold rounded px-2 py-1" style="opacity:0.95;">IEP Workspace →</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
